<?php

use \think\facade\Env;

// +----------------------------------------------------------------------
// | 缓存设置
// +----------------------------------------------------------------------

return [
    // // 驱动方式
    // 'type'   => 'File',
    // // 缓存保存目录
    // 'path'   => '',
    // // 缓存前缀
    // 'prefix' => '',
    // // 缓存有效期 0表示永久缓存
    // 'expire' => 0,

  	'type' => 'Redis',
    'host' => Env::get('redis.redis_hostname','127.0.0.1'),
    'port' => Env::get('redis.redis_hostport',6379),
    'password' => Env::get('redis.redis_password','aukeys@2019'),
    'select' => Env::get('redis.redis_database',2),
    'prefix'=>  'general_',
    'timeout' => 3600
];
