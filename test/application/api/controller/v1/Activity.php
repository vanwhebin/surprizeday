<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/6/17
 * Time: 15:39
 */

namespace app\api\controller\v1;

use app\api\controller\BaseController;
use app\api\model\Activity as ActivityModel;
use app\api\model\ActivityUser;
use app\api\model\ActivityUser as ActivityUserModel;
use app\api\model\Winner as WinnerModel;
use app\api\service\Activity as ActivityService;
use app\api\validate\activity\ActivityIDValidate;
use app\api\validate\activity\ActivityValidate;
use app\api\validate\CountValidate;
use app\lib\exception\MissingException;
use think\facade\Cache;


class Activity extends BaseController
{
    public $winnerKeyPrefix = 'winners:activity:slug:';
    public $activityKeyPrefix = 'activity:index:slug:';

    /**
     * @url /activity/latest?num=1,15&page=0
     * @param int $num
     * @param int $page
     * @return mixed
     * @throws
     */
    public function latest($num = 15, $page = 1)
    {
        (new CountValidate())->validate();
        // 获取最新的活动信息，每个抽奖活动要包含多个产品
        // 活动信息包含讲明名称，开奖时间图片
        // 显示用户是否已经参与该活动
        $activityArr = ActivityService::home($num, intval(abs($page)));
        return $activityArr;
    }


    /**
     * 返回所有该用户已参与的活动
     * @url /activity/account?num=1,15&page=0
     * @param int $num
     * @param int $page
     * @param boolean $expired
     * @return mixed
     * @throws
     */
    public function account($num = 3, $page = 1, $expired=false)
    {
        (new CountValidate())->validate();
        // 获取最新的活动信息，每个抽奖活动要包含多个产品
        // 活动信息包含讲明名称，开奖时间图片
        // 显示用户是否已经参与该活动
        $accountArr = ActivityService::account($num, intval(abs($page)), $expired);
        return $accountArr;
    }

    /**
     * 用户获取当前活动详情
     * @param $slug
     * @return array
     * @throws MissingException
     * @throws \app\lib\exception\InvalidParamException
     * @throws \app\lib\exception\TokenException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index($slug)
    {
        (new ActivityValidate())->validate();

        $activityIndexKey = $this->activityKeyPrefix.substr($slug,0,10);
        $activityInfo = Cache::store('redis')->get($activityIndexKey);
        if (!$activityInfo) {
            $activityInfo = ActivityModel::where('slug','=', $slug)
                ->where('status =:status or private =:private ', ['status' => 1, 'private' => 1])
                ->field(['id', 'title', 'start_time'])
                ->find()
                ->toArray();
            Cache::store('redis')->set($activityIndexKey, json_encode($activityInfo), 60);
        } else {
            $activityInfo = json_decode($activityInfo, true);
        }

        if (!$activityInfo) {
            throw new MissingException();
        }

        $participants = ActivityService::currentUsers($activityInfo['id']);
        $result = ['activity' => $activityInfo, 'users' => $participants];
        if ($activityInfo['start_time'] <= time()) {
            $result['info'] = ActivityService::checkResult($participants, $activityInfo['id']);
        }
        $result['activity']['start_time'] = dateFormat($result['activity']['start_time']);
        return $result;
    }

    /**
     * 用户参与活动，提交活动ID和用户ID
     * @url /activity/participant
     * @param $activity_id
     * @method POST
     * @throws
     * @return mixed
     */
    public function participant($activity_id)
    {
        (new ActivityIDValidate())->validate();
        $enroll = ActivityService::enroll();
        if ($enroll) {
            return json('ok', 201);
        } else {
            return json('error', 400);
        }
    }


    /**
     * 所有当前活动的参与用户
     * @param int $num
     * @param int $page
     * @param $slug
     * @return array
     * @throws \DomainException
     * @throws \app\lib\exception\InvalidParamException
     * @throws \think\exception\DbException
     */


    public function users($num = 50, $page = 1, $slug)
    {
        // 获取全部活动用户，显示在页面
        (new ActivityValidate())->validate();
        $activity_id = ActivityModel::where(['slug' =>$slug])->column('id');

        $data = (new ActivityUserModel())->getUsersByActivityID($num, $page, $activity_id);
        return ['data' => $data, 'total' => $data->total()];
    }

    /**
     * 所有提交活动的中奖用户
     * @param int $num
     * @param int $page
     * @param $slug
     * @return array|mixed
     * @throws \app\lib\exception\InvalidParamException
     * @throws \think\exception\DbException
     */
    public function winner($num = 50, $page = 1, $slug)
    {
        (new ActivityValidate())->validate();
        $activity_id = ActivityModel::where(['slug' =>$slug])->value('id');
        return WinnerModel::getWinnerByActivityID($num, $page, $activity_id);
    }


    /**
     * 中奖用户领取用户奖品
     * @param $slug
     * @return bool
     * @throws \app\lib\exception\InvalidParamException
     * @throws \app\lib\exception\TokenException
     * @throws \think\Exception
     */
    public function claimPrize($slug)
    {
        (new ActivityValidate())->validate();
        return ActivityService::claim($slug);
    }

    /**
     * 分页获取所有已过期的活动
     * @param int $num
     * @param int $page
     * @return array
     * @throws \app\lib\exception\InvalidParamException
     * @throws \think\exception\DbException
     */
    public function expired($num=3, $page=1)
    {
        (new CountValidate())->validate();
        return ActivityService::expired($num, $page);
    }

}