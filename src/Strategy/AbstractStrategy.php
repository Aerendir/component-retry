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

use function Safe\sprintf;

/**
 * Abstract class to manage strategies.
 */
abstract class AbstractStrategy implements StrategyInterface
{
    /** @var int $attempts How many retry attempts have been done */
    private int $attempts = 0;

    private int $incrementBy;
    private int $maxAttempts = 0;
    private string $timeUnit;

    public function __construct(
        int $maxAttempts, int $incrementBy, string $timeUnit = StrategyInterface::TIME_UNIT_SECONDS,
    ) {
        $this->setMaxAttempts($maxAttempts)->setIncrementBy($incrementBy)->setTimeUnit($timeUnit);
    }

    public function canRetry(): bool
    {
        return $this->attempts < $this->maxAttempts;
    }

    public function getAttempts(): int
    {
        return $this->attempts;
    }

    public function getIncrementBy(): int
    {
        return $this->incrementBy;
    }

    public function getMaxAttempts(): int
    {
        return $this->maxAttempts;
    }

    public function getTimeUnit(): string
    {
        return $this->timeUnit;
    }

    public function getStrategyName(): string
    {
        return $this::STRATEGY;
    }

    public function newAttempt(): StrategyInterface
    {
        ++$this->attempts;

        return $this;
    }

    public function jsonSerialize(): array
    {
        if (false === \defined(\get_class($this) . '::STRATEGY')) {
            throw new \RuntimeException('The Strategy doesn\'t tells its own name. Create the contant "STRATEGY" that tells the name of the strategy.');
        }

        return [
            'attempts'       => $this->getAttempts(),
            'max_attempts'   => $this->getMaxAttempts(),
            'increment_by'   => $this->getIncrementBy(),
            'increment_unit' => $this->getTimeUnit(),
        ];
    }

    protected function convertToSeconds(int $increment, string $timeUnit): int
    {
        $this->validateTimeUnit($timeUnit);

        switch ($timeUnit) {
            case StrategyInterface::TIME_UNIT_YEARS:
                return $this->convertToSeconds($increment * 12, StrategyInterface::TIME_UNIT_MONTHS);
            case StrategyInterface::TIME_UNIT_MONTHS:
                // We average to 30 days in a month, without taking care of the ones long 31 days or 28 or 29
                return $this->convertToSeconds($increment * 30, StrategyInterface::TIME_UNIT_DAYS);
            case StrategyInterface::TIME_UNIT_DAYS:
                return $this->convertToSeconds($increment * 24, StrategyInterface::TIME_UNIT_HOURS);
            case StrategyInterface::TIME_UNIT_HOURS:
                return $this->convertToSeconds($increment * 60, StrategyInterface::TIME_UNIT_MINUTES);
            case StrategyInterface::TIME_UNIT_MINUTES:
                return $increment * 60;
            case StrategyInterface::TIME_UNIT_SECONDS:
                return $increment;
            default:
                throw new \RuntimeException(sprintf('Unrecognized time unit "%s". Allowed time units are...', $timeUnit));
        }
    }

    protected function setAttempts(int $attempts): self
    {
        $this->attempts = $attempts;

        return $this;
    }

    protected function setIncrementBy(int $incremenetBy): self
    {
        $this->incrementBy = $incremenetBy;

        return $this;
    }

    protected function setTimeUnit(string $timeUnit): self
    {
        $this->validateTimeUnit($timeUnit);

        $this->timeUnit = $timeUnit;

        return $this;
    }

    protected function setMaxAttempts(int $maxAttempts): self
    {
        $this->maxAttempts = $maxAttempts;

        return $this;
    }

    protected function validateTimeUnit(string $timeUnit): string
    {
        if (false === \in_array($timeUnit, StrategyInterface::TIME_UNITS)) {
            throw new \InvalidArgumentException(sprintf('The increment unit "%s" is not supported. Supported increment units are: %s.', $timeUnit, \implode(' ', StrategyInterface::TIME_UNITS)));
        }

        return $timeUnit;
    }
}
