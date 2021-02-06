<?php

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
 * Ever returns false stopping the retrying.
 */
final class NeverRetryStrategy extends AbstractStrategy
{
    /** @var string */
    const STRATEGY = 'never_retry';

    /**
     * This doesn't accept parameters as ever returns false.
     */
    public function __construct()
    {
        parent::__construct(0, 0);
    }

    /**
     * {@inheritdoc}
     */
    public function retryOn(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function waitFor(): int
    {
        return 0;
    }
}
