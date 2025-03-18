<?php

namespace Spatie\RateLimitedMiddleware;

use Illuminate\Support\Facades\Cache;

/**
 * @source https://github.com/artisansdk/ratelimiter
 * We've combined the Bucket and Limiter into one class
 * here as we only need this specific implementation
 */
class LeakyBucket
{
    protected int $drips = 0;

    protected float $timer = 0;

    protected string $timeoutKey;

    public function __construct(
        protected string $key = 'default',
        protected int $max = 60,
        protected float|int $rate = 1,
    ) {
        $bucket = Cache::get($this->key);

        $this->timeoutKey = "{$this->key}:timeout";

        if (! $bucket) {
            return;
        }

        $this->drips = $bucket['drips'];
        $this->timer = $bucket['timer'];
    }

    public function isOverflowing(): bool
    {
        if (Cache::has($this->timeoutKey)) {
            return true;
        }

        $this->leak();

        if ($this->drips >= $this->max) {
            $this->timeout();

            return true;
        }

        return false;
    }

    protected function timeout(): void
    {
        if (Cache::has($this->timeoutKey)) {
            return;
        }

        $duration = (int) $this->duration();

        Cache::put(
            $this->timeoutKey,
            ((int) $this->timer) + $duration,
            $duration
        );
    }

    public function fill(): int
    {
        $this->drips++;

        Cache::put(
            $this->key,
            [
                'timer' => $this->timer,
                'drips' => $this->drips,
            ],
            (int) max(1, ceil($this->duration())) // $ttl to $seconds conversion requires minimally 1s
        );

        return $this->drips;
    }

    protected function duration(): float
    {
        return (float) (
            max(
                0,
                microtime(true)
                + ($this->drips / $this->rate)
                - $this->timer
            )
        );
    }

    protected function leak(): self
    {
        $drips = $this->drips;

        $elapsed = $this->reset()->timer - $this->timer;
        $drops = (int) floor($elapsed * $this->rate);

        $this->drips = $this->bounded($drips - $drops);

        return $this;
    }

    protected function bounded(int $drips): int
    {
        return (int) max(0, min($this->max, $drips));
    }

    protected function reset(): self
    {
        $this->drips = 0;
        $this->timer = microtime(true);

        return $this;
    }
}
