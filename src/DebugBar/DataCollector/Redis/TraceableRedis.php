<?php

namespace DebugBar\DataCollector\Redis;

use Redis;
use RedisException;

/**
 * A Redis proxy which traces statements
 */
class TraceableRedis extends BaseTraceableRedis
{

    /**
     * Add methods available in \Redis that shouldnt be logged, e.g. passwords and hostnames
     *
     * @var array
     */
    private static $confidentialCalls = [
        'connect',
        'auth',
    ];

    /**
     * @param Redis $redis
     */
    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    /**
     * Profiles a call on a Redis method
     *
     * @param string $method
     * @param string $sql
     * @param array $args
     * @return mixed The result of the call
     */
    protected function profileCall($method, array $args)
    {
        if(!in_array($method, self::$confidentialCalls)) {
            return $this->execProfiledCall($method, $args);
        }

        return $this->execCall($method, $args);
    }

    /**
     * Run call to redis object WITH profiling
     *
     * @param $method
     * @param array $args
     * @return mixed
     */
    protected function execProfiledCall($method, array $args)
    {
        $trace = new TracedStatement($method, $args);
        $trace->start();

        $ex = null;
        try {
            $result = $this->execCall($method, $args);
        } catch (RedisException $e) {
            $ex = $e;
        }

        $trace->end($ex);
        $this->addExecutedStatement($trace);

        return $result;
    }

    /**
     * Run call standalone, can be used when not profiling
     *
     * @param $method
     * @param array $args
     * @return mixed
     */
    protected function execCall($method, array $args)
    {
        return call_user_func_array(array($this->redis, $method), $args);
    }

}
