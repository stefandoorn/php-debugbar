<?php

include 'bootstrap.php';

use DebugBar\DataCollector\Redis\TraceableRedis;
use DebugBar\DataCollector\Redis\RedisCollector;

$redis = new TraceableRedis(new Redis());
$redis->connect('localhost');
$debugbar->addCollector(new RedisCollector($redis));

$redis->set('a', 1);
$redis->get('a');

render_demo_page();
