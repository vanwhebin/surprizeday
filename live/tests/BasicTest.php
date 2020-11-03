<?php

namespace tests;
require_once __DIR__ . '/../vendor/autoload.php';
use app\index\controller\Index;

class BasicTest extends TestCase
{
    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \app\lib\exception\InvalidParamException
     * @throws \think\exception\DbException
     */
    public function testBasicFunctions()
    {
        $this->assertTrue(true);
        $this->assertEquals(2, 1 + 1);
        $app = new Index();
        $res = $app->home(10,1);
        // 假设 index/index/index 方法返回的字符串中包含 "index"
        $this->assertStringContainsString('index', $res);
    }

    public function testThinkPHPStyleTests()
    {
        // 假设我访问 "/" 会看到 "index"
        $this->visit('/')->see('index');
    }
}