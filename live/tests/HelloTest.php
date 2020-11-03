<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/10/21
 * Time: 9:55
 */

namespace tests;

use PHPUnit\Framework\TestCase;

class HelloTest extends TestCase
{

    public function testHello()
    {
        $expect = "hello world";
        $result = $this->requestApi(); //请求api 或 调用 service 后返回的结果
        $this->assertEquals($expect, $result); //使用断言方法 比较结果值
    }


    //假装请求数据
    private function requestApi()
    {
        echo $date_1 = date('Y-m-d H:i:s');
        echo "<br>";;
        echo $date_2 = date('Y-m-d H:i:s');
        echo "<br>";;
        echo $date_3 = date('Y-m-d H:i:s');
        echo "<br>";;
        echo $date_4 = date('Y-m-d H:i:s');
        echo "<br>";;
        echo $date_5 = date('Y-m-d H:i:s');
        echo "<br>";;
        $j = 0;
        for ($i = 0; $i < 10; $i++) {
            $j = $i * 2;
            $i = $i + 2;
            echo $i;
            echo "<br>";
            echo $j;
            echo "<br>";
        }
        return 'hello world';
    }

}
