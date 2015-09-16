<?php

namespace DebugBar\DataCollector\Redis;

/**
 * A Redis proxy which traces statements
 */
abstract class BaseTraceableRedis
{

    /**
     * @var \Redis|\Predis\Client
     */
    protected $redis;

    /**
     * @var array
     */
    protected $executedStatements = array();

    /**
     * Run any function call on the redis object
     *
     * @param $name
     * @param array $args
     * @return mixed
     */
    public function __call($name, array $args)
    {
        return $this->profileCall($name, $args);
    }

    /**
     * Get parameters from redis object
     *
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->redis->$name;
    }

    /**
     * Set parameters to redis object
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->redis->$name = $value;
    }

    /**
     * Adds an executed TracedStatement
     *
     * @param TracedStatement $stmt
     */
    public function addExecutedStatement(TracedStatement $stmt)
    {
        array_push($this->executedStatements, $stmt);
    }

    /**
     * Returns the list of executed statements as TracedStatement objects
     *
     * @return array
     */
    public function getExecutedStatements()
    {
        return $this->executedStatements;
    }

    /**
     * Returns the list of failed statements
     *
     * @return array
     */
    public function getFailedExecutedStatements()
    {
        return array_filter($this->executedStatements, function ($s) { return !$s->isSuccess(); });
    }

    /**
     * Returns the accumulated execution time of statements
     *
     * @return int
     */
    public function getAccumulatedStatementsDuration()
    {
        return array_reduce($this->executedStatements, function ($v, $s) { return $v + $s->getDuration(); });
    }

    /**
     * Returns the peak memory usage while performing statements
     *
     * @return int
     */
    public function getMemoryUsage()
    {
        return array_reduce($this->executedStatements, function ($v, $s) { return $v + $s->getMemoryUsage(); });
    }

    /**
     * Returns the peak memory usage while performing statements
     *
     * @return int
     */
    public function getPeakMemoryUsage()
    {
        return array_reduce($this->executedStatements, function ($v, $s) { $m = $s->getEndMemory(); return $m > $v ? $m : $v; });
    }

}
