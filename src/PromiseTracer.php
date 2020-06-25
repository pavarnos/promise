<?php
/**
 * @file
 * @author Lightly Salted Software Ltd
 * @date   25 Jun 2020
 */

declare(strict_types=1);

namespace React\Promise;

/**
 * Trace unhandled promises / rejections
 */
class PromiseTracer
{
    /** @var UnhandledRejectionTracer */
    private static $tracer;

    /**
     * @param callable $onUnhandledPromise function(string $trace, string $reason)
     */
    public static function register($onUnhandledPromise)
    {
        if (isset(self::$tracer)) {
            throw new \RuntimeException('Tracer already registered');
        }
        self::$tracer = new UnhandledRejectionTracer($onUnhandledPromise);
        RejectedPromise::setTracer(self::$tracer);
        FulfilledPromise::setTracer(self::$tracer);
        // relies on tracer->__destruct() to report unhandled promises
    }
}