<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/10/17
 * Time: 10:56
 */

namespace app\api\controller\cms;


use app\api\model\Activity as ActivityModel;
use app\api\model\ActivityUser as ActivityUserModel;
use app\api\model\Winner as WinnerModel;
use app\api\model\User as UserModel;
use app\api\validate\activity\ActivityIDValidate;
use app\api\validate\activity\ActivityUserValidate;
use app\api\validate\PaginationValidate;
use think\Controller;
use think\Exception;
use think\Request;

class ActivityUser extends Controller
{

    /**
     * * 增加活动假人
     * @param Request $request
     * @return \think\response\Json
     * @throws \Exception
     * @throws \app\lib\exception\InvalidParamException
     */
    public function activityFakers(Request $request)
    {
        (new ActivityUserValidate())->validate();
        $data = $request->param();
        $activity = ActivityModel::getOrFail($data['activity_id']);
        $curFakers = ActivityUserModel::where(['is' => 0, 'activity_id' => $data['activity_id']])->column('user_id');
        $data['user_ids'] = array_diff($data['user_ids'], $curFakers);
        if (!empty($data['user_ids'])) {
            $dataSet = array_map(function($item) use ($data) {
                $item = ["user_id" => $item, "is" => 0, 'activity_id' => $data['activity_id']];
                return $item;
            }, $data['user_ids']);

            try{
                (new ActivityUserModel())->saveAll($dataSet);
                return writeJson(201, '', $activity->title."添加了".count($data['user_ids']).'个Fakers');
            }catch(Exception $e){
                return writeJson(400, $dataSet, $e->getMessage());
            }
        } else {
            return writeJson(400, $data, '请勿重复添加');
        }
    }


    /**
     * 为现有活动添加winner
     * 通过activity_user 中的数据进行添加
     * @method Put
     * @param Request $request
     * @return \think\response\Json
     * @throws \Exception
     * @throws \app\lib\exception\InvalidParamException
     */
    public function activityWinners(Request $request)
    {
        (new ActivityUserValidate())->validate();
        $data = $request->param();
        $activity = ActivityModel::getOrFail($data['activity_id']);
        $curWinners = WinnerModel::where(['activity_id' => $data['activity_id']])->column('user_id');
        $data['user_ids'] = array_diff($data['user_ids'], $curWinners);
        if (count($data['user_ids']) > 0){
            $users = (new UserModel())->whereIn('id', $data['user_ids'])->field(['id', 'name'])->select()->toArray();
            $users = array_column($users, 'name', 'id');
            $dataSet = array_map(function($item) use ($data, $users) {
                $level = !empty($data['level'])? $data['level']: 1;
                $item = ["user_id" => $item, "level" => $level, 'activity_id' => $data['activity_id'], 'name' => $users[$item]];
                return $item;
            }, $data['user_ids']);
            try{
                (new WinnerModel())->saveAll($dataSet);
                return writeJson(201, '', $activity->title."添加了".count($data['user_ids']).'个Winner(s)');
            }catch(Exception $e){
                return writeJson(400, $dataSet, $e->getMessage());
            }
        } else {
            return writeJson(400, $data, '请勿重复添加');
        }
    }

    /**
     * 查找当前活动的所有参与人员
     * @param Request $request
     * @return array
     * @throws \app\lib\exception\InvalidParamException
     * @throws \think\exception\DbException
     */
    public function index(Request $request)
    {
        (new PaginationValidate())->validate();
        (new ActivityIDValidate())->validate();
        $data = $request->only(['num', 'page', 'activity_id', 'fake',  'name']);
        ActivityModel::getOrFail($data['activity_id']);
        $model = new ActivityUserModel();
        $activityUsers = $model->getActivityUserByCondition($data);
        return $activityUsers;
    }

}