<?php
/**
 * @file
 * @author Lightly Salted Software Ltd
 * @date   25 Jun 2020
 */

declare(strict_types=1);

namespace React\Promise;

/**
 * @internal
 * Added to promises to help them trace their lifecycle
 */
trait TraceableTrait
{
    /** @var UnhandledRejectionTracer */
    private static $tracer;

    public static function setTracer(UnhandledRejectionTracer $tracer)
    {
        self::$tracer = $tracer;
    }

    public static function getTracer(): UnhandledRejectionTracer
    {
        return self::$tracer;
    }

    protected function traceInstantiated($reason = null)
    {
        if (isset(self::$tracer)) {
            self::$tracer->instantiated($this, $reason);
        }
    }

    protected function traceHandled()
    {
        if (isset(self::$tracer)) {
            self::$tracer->handled($this);
        }
    }

    protected function traceDestroyed()
    {
        if (isset(self::$tracer)) {
            self::$tracer->destroyed($this);
        }
    }
}