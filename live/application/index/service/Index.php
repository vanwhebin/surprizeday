<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/7/17
 * Time: 15:35
 */

namespace app\index\service;

use app\api\model\Activity as ActivityModel;
use app\api\model\Freebies as FreebiesModel;
use app\api\model\Winner as WinnerModel;

class Index
{
    /**
     * @param int $num
     * @param int $page
     * @return array
     * @throws \think\exception\DbException
     */
    public static function allActivity($num=20, $page=1)
    {
        $allActivity= ActivityModel::allActivityWithPagination($num, $page);
        $activityArr =  $allActivity->toArray();
        $page = $allActivity->render();
        $activityArr['data'] = array_map(function($item){
            $item['expired'] = time() > $item['start_time'];
            $item['start_time'] = dateFormat($item['start_time']);
            return $item;
        }, $activityArr['data']);
        return ['activityArr' => $activityArr, 'page' => $page];
    }


    /**
     * 获取所有的可用deals产品数据
     * @param int $num
     * @param int $page
     * @return array
     * @throws \think\exception\DbException
     */
    public static function allDeals($num=20, $page=1)
    {
        $allActivity= FreebiesModel::allDeals($num, $page);
        $activityArr =  $allActivity->toArray();
        $page = $allActivity->render();
        return ['dealsArr' => $activityArr, 'page' => $page];
    }


    /**
     * @param $activityID
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public static function activityWinners($activityID)
    {
        $winners = WinnerModel::getWinnerByActivityID(999, 1, $activityID);
        $winners['data'] = array_map(function($item){
            $item['info']['name'] = hideUserName($item['info']['name']);
            return $item;
        }, $winners['data']);
        return $winners;
    }
}