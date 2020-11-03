<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/10/5
 * Time: 11:30
 */

namespace app\index\controller;

use app\api\model\Activity as ActivityModel;
use app\api\model\RaffleUser;
use app\api\service\Activity as ActivityService;
use app\api\service\Token as TokenService;
use app\api\validate\activity\ActivityValidate;
use app\index\service\Raffle as RaffleService;
use app\lib\exception\InvalidParamException;
use think\Controller;
use think\Request;

class Instant extends Controller
{
    /**
     * 即时抽奖活动
     * @param $slug
     * @return mixed
     * @throws \app\lib\exception\InvalidParamException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function instantPrize($slug)
    {
        (new ActivityValidate())->validate();
        $activity_id = ActivityModel::where(['slug' => $slug, 'type' => 3])->value('id');
        if (!$activity_id) {
            throw new InvalidParamException();
        }
        $activityInfo = ActivityService::activityInfo($activity_id);
        // return json($activityInfo);
        $this->assign('activityInfo', $activityInfo);
        return $this->fetch('index/pc/openBox');
    }

    /**
     * @param Request $request
     * @return array
     * @throws InvalidParamException
     * @throws \app\lib\exception\TokenException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function claim(Request $request)
    {
        // 参与前需要登录
        // 领奖方式 使用msgr
        // 参与一次之后不能
        // 一天只能抽中一次amazon code
        // 六小时参与一次
        $data = $request->param();
        //  参与成功
        $userID = TokenService::getCurrentTokenVar('uid');
        $activityID = ActivityModel::getIDBySlug($data['slug']);
        if (!$activityID) {
            throw new InvalidParamException();
        }
        // 抽奖
        $result = (new RaffleService())->findLuckyDog($activityID, $userID);
        // 是否中奖
        return json($result);

    }




}