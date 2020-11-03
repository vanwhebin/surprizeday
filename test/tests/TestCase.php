<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------
namespace tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use think\App;

abstract class TestCase extends PHPUnitTestCase
{
    use ApplicationTrait, AssertionsTrait, CrawlerTrait;

    // protected $baseUrl = 'admin.demo.local';
    protected $baseUrl = '';

    public function __construct(?string $name = null, array $data = [], string $dataName = '') {
        // 引入需要的环境
        require_once __DIR__ . '/../thinkphp/base.php';
        // 初始化 App 对象，并将 APP_PATH 指向项目的 application 目录
        App::getInstance()->path(__DIR__ . '/../application/')->initialize();

        parent::__construct($name, $data, $dataName);
    }
}
