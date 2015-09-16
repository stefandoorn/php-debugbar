<?php

namespace DebugBar\DataCollector\Redis;

use Redis;
use RedisException;

/**
 * A Redis proxy which traces statements
 */
class TraceableRedis extends BaseTraceableRedis
{

    private $redis;
    private $executedStatements = array();

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    public function __call($name, array $args)
    {
        return $this->profileCall($name, $args);
    }

    public function __get($name)
    {
        return $this->redis->$name;
    }

    public function __set($name, $value)
    {
        $this->redis->$name = $value;
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
        $trace = new TracedStatement($method, $args);
        $trace->start();

        $ex = null;
        try {
            $result = call_user_func_array(array($this->redis, $method), $args);
        } catch (RedisException $e) {
            $ex = $e;
        }

        $trace->end($ex);
        $this->addExecutedStatement($trace);

        return $result;
    }

}
