<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/11/8
 * Time: 15:52
 */

namespace app\api\service;


use app\api\model\Freebies as DealsModel;

class Deals
{
    /**
     * 获取所有的Deals产品
     * @param int $num
     * @param int $page
     * @return array
     * @throws \think\exception\DbException
     */
    public static function getAll($num = 15, $page = 1)
    {
        $all = DealsModel::allDeals($num, $page)->toArray();
        return $all;
    }
}