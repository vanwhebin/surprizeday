<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/6/17
 * Time: 16:01
 */

namespace app\api\model;

use app\api\model\User as UserModel;

class ActivityUser extends BaseModel
{
    protected $hidden = ['delete_time'];

    public function user()
    {
        return $this->belongsTo('User', 'user_id', 'id');
    }

    public function activity()
    {
        return $this->belongsTo('Activity', 'activity_id', 'id');
    }

    /**
     * @param int $num
     * @param int $page
     * @param $activityID
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getUsersByActivityID($num=7, $page=1, $activityID)
    {
        return self::with(['user' => function($query){
            $query->visible(['name', 'avatar', 'id']);
        }])->where(['activity_id' => $activityID])
            ->order('id desc')
            ->visible(['user'])
            ->paginate($num, false, ['page' => $page]);
    }

    /**
     * @param $activityID
     * @param $userID
     * @return array|null|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getOne($activityID, $userID)
    {
        return self::where([
            'user_id'       =>  $userID,
            'activity_id'   =>  $activityID,
        ])->find();
    }

    /**
     * @param $userID
     * @param $activityID
     * @return array|null|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public  static function getUserInfo($userID, $activityID)
    {
        return self::with(['user' => function($query){
           $query->field(['id', 'name', 'avatar'])->hidden(['id']);
        }])->where(['user_id' => $userID, 'activity_id' => $activityID])
            ->field('user_id')
            ->visible(['user'])
            ->find();
    }

    /**
     * @param $activityID
     * @param $userID
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function check($activityID, $userID)
    {
        $res = self::where([
            'activity_id' => $activityID,
            'user_id' => $userID,
        ])->find();
        if ($res) {
            if (!$res->checked) {
                $res->checked = 1;
                $res->save();
                return false;
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * 通过部分条件查询参与活动用户信息
     * @param $cond
     * @return array|\think\Paginator
     * @throws \think\exception\DbException
     */
    public function getActivityUserByCondition($cond)
    {
        $where = ['activity_id' => $cond['activity_id']];
        if (isset($cond['fake']) && $cond['fake'] !== "") {
            $where['is'] = intval($cond['fake']) ? 1 : 0;
        }
        if (!empty($cond['name'])) {
            $subQuery = function($query) use ($cond, $where) {
                $query->table('activity_user')->where($where)->field('user_id')->select();
            };

            $res = (new UserModel())->whereIn('id', $subQuery)
                    ->where(['name' => $cond['name']])
                ->order('id', 'desc')
                ->field(['id', 'name', 'avatar'])
                ->paginate($cond['num'], false, ['page' =>  $cond['page']])
                ->toArray();
            $res['data'] = array_map(function($item){
                $item = ['user' => $item];
                return $item;
            }, $res['data']);
        } else {
            $res = self::with(['user' => function($query){
                $query->field(['name', 'avatar', 'id']);
            }])->where($where)
                ->order('id desc')
                ->visible(['user'])
                ->paginate($cond['num'], false, ['page' =>  $cond['page']])
                ->toArray();
        }

        return $res;
    }


    /**
     * 处理用户发送过来的确认信息
     * @param $userID
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function handleConfirmMessage($userID)
    {
        $activityUser = self::where(['user_id' => $userID])->order('id', 'desc')->find();
        if ($activityUser) {
            $activityUser->confirm = 1;
            $activityUser->save();
            return true;
        }
        return false;
    }




}