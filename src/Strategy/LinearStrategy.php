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

/**
 * Each time adds increment by multiplied by the number of attempts.
 */
class LinearStrategy extends AbstractStrategy
{
    /** @var string */
    public const STRATEGY = 'linear';

    public function retryOn(): \DateTime|false
    {
        // If we can retry...
        if (parent::canRetry()) {
            // ... return the date on which to retry
            return (new \DateTime())->modify('+' . $this->waitFor() . ' ' . self::TIME_UNIT_SECONDS);
        }

        return false;
    }

    public function waitFor(): int
    {
        $incrementBy = $this->getIncrementBy() * $this->getAttempts();

        return $this->convertToSeconds($incrementBy, $this->getTimeUnit());
    }
}
