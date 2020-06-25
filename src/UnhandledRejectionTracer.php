<?php

namespace React\Promise;

/**
 * @internal
 * from https://gist.github.com/arnaud-lb/a2a5a5480bbd80013f756ff968282936 with some minor tweaks
 */
class UnhandledRejectionTracer
{
    /** @var array */
    private $promises;

    /** @var callable */
    private $onUnhandledPromise;

    public function __construct(callable $onUnhandledPromise)
    {
        $this->promises           = [];
        $this->onUnhandledPromise = $onUnhandledPromise;
    }

    /**
     * Called when a promise is instanciated
     * @param PromiseInterface $promise
     * @param mixed            $reason
     */
    public function instantiated(PromiseInterface $promise, $reason = null)
    {
        $hash = spl_object_hash($promise);

        // Providing the trace as a string, not as an Exception instance,
        // because this could trigger PHP GC issues; e.g. making a variable
        // reachable again during a __destructor call.
        $exception = new \Exception();
        $trace     = "Promise started here: \n" . $exception->getTraceAsString();

        $this->promises[$hash] = [
            'trace'  => $trace,
            'reason' => $reason ? $this->stringifyReason($reason) : null,
        ];
    }

    /**
     * Called when a promise has been handled
     * @param PromiseInterface $promise
     */
    public function handled(PromiseInterface $promise)
    {
        $hash = spl_object_hash($promise);

        if (!isset($this->promises[$hash])) {
            return;
        }

        unset($this->promises[$hash]);
    }

    /**
     * Called when a promise is destroyed
     * @param PromiseInterface $promise
     */
    public function destroyed(PromiseInterface $promise)
    {
        $hash = spl_object_hash($promise);

        $this->notifyUnhandledPromise($this->promises[$hash]);

        unset($this->promises[$hash]);
    }

    public function __destruct()
    {
        foreach ($this->promises as $info) {
            $this->notifyUnhandledPromise($info);
        }
    }

    private function notifyUnhandledPromise(array $promiseInfo)
    {
        if (!is_null($this->onUnhandledPromise)) {
            call_user_func_array($this->onUnhandledPromise, $promiseInfo);
        }
    }

    private function stringifyReason($reason)
    {
        if (is_object($reason) && method_exists($reason, '__toString')) {
            return (string)$reason;
        }

        return is_string($reason) ? $reason : json_encode($reason);
    }
}