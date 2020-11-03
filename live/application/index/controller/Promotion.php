<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/10/5
 * Time: 11:30
 */

namespace app\index\controller;


use think\Controller;

class Promotion extends Controller
{
    public function instantPrize()
    {
        return $this->fetch('index/pc/openBox');
    }
}