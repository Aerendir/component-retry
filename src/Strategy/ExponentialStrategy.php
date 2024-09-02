<?php

declare(strict_types=1);

/*
 * This file is part of the Serendipity HQ Then When Component.
 *
 * Copyright (c) Adamo Aerendir Crespi <aerendir@serendipityhq.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SerendipityHQ\Component\ThenWhen\Strategy;

final class ExponentialStrategy extends AbstractStrategy
{
    /** @var string */
    public const STRATEGY = 'exponential';

    private int $exponentialBase;

    public function __construct(
        int $maxAttempts,
        int $incrementBy,
        string $timeUnit = StrategyInterface::TIME_UNIT_SECONDS,
        int $exponentialBase = 2,
    ) {
        $this->setExponentialBase($exponentialBase);

        parent::__construct($maxAttempts, $incrementBy, $timeUnit);
    }

    public function retryOn(): \DateTime|false
    {
        // If we can retry...
        if (parent::canRetry()) {
            // ... return the date on which to retry
            return (new \DateTime())->modify('+' . $this->waitFor() . ' ' . self::TIME_UNIT_SECONDS);
        }

        // No more retries
        return false;
    }

    public function waitFor(): int
    {
        $incrementBy = 1 === $this->getAttempts()
            ? $this->getIncrementBy()
            : $this->getExponentialBase() ** $this->getAttempts() * $this->getIncrementBy();

        return $this->convertToSeconds($incrementBy, $this->getTimeUnit());
    }

    public function getExponentialBase(): int
    {
        return $this->exponentialBase;
    }

    protected function setExponentialBase(int $exponentialBase): self
    {
        if (2 > $exponentialBase) {
            throw new \InvalidArgumentException('The exponential base must be greater than or equal to 2.');
        }

        $this->exponentialBase = $exponentialBase;

        return $this;
    }
}
