<?php

namespace SerendipityHQ\Component\ThenWhen;

use SerendipityHQ\Component\ThenWhen\Strategy\StrategyInterface;

/**
 * Manges te retry logic implementing various strategies.
 */
class TryAgain
{
    /** @var array $strategies The strategies to use to manage exceptions */
    private $strategies = [];

    /** @var array $middleHandlers What to do when exceptions are catched during the retryings */
    private $middleHandlers = [];

    /** @var array $finalHandlers What to do when exceptions are catched and no other retries are possible */
    private $finalHandlers = [];

    /**
     * @param array $strategies
     * @param array $middleHandlers
     * @param array $finalHandlers
     */
    public function __construct(array $strategies, array $middleHandlers, array $finalHandlers)
    {
        $this->strategies = $strategies;
        $this->middleHandlers = $middleHandlers;
        $this->finalHandlers = $finalHandlers;
    }

    /**
     * @param callable $callback
     * @return mixed
     * @throws \Exception
     */
    public function try(callable $callback)
    {
        try {
            return call_user_func($callback);
        } catch (\Exception $e) {
            // An exception were catched: of which type?
            $exception = get_class($e);

            // If no strategies exist for this excpetion...
            if (false === isset($this->strategies[$exception])) {
                // ... throw it
                throw $e;
            }

            /** @var StrategyInterface $strategy A strategy exists: use it **/
            $strategy = $this->strategies[$exception];

            // First check if the operation can be retried
            if (false === $strategy->canRetry()) {
                // The maximum number of attempts is reached: check if there is a failing handler
                if (isset($this->finalHandlers[$exception])) {
                    // Return the result of the set final handler
                    return call_user_func($this->finalHandlers[$exception], $e);
                }

                // No handler set: throw the exception
                throw $e;
            }

            // Increment the attempts counter
            $strategy->newAttempt();

            // Now check if there is a middle handler
            if (isset($this->middleHandlers[$exception])) {
                $result = call_user_func($this->middleHandlers[$exception], $e);

                // If the result is false...
                if (false === $result) {
                    // ... We throw the exception
                    throw $e;
                }

                // If the result a callable
                if (is_callable($result)) {
                    // ... we use it to retry
                    return self::try($result);
                }

                // For any other result...
            }

            // Wait the defined time
            sleep($strategy->waitFor());

            // Try again with the same original callable
            return self::try($callback);
        }
    }
}