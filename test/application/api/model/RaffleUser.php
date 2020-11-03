<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/10/22
 * Time: 11:35
 */

namespace app\api\model;


use think\Exception;

class RaffleUser extends BaseModel
{
    protected $hidden = ['delete_time'];
    const VALID_PERIOD = (3600 * 6);  // 6小时内不允许重复参与

    public function user()
    {
        return $this->belongsTo('User', 'user_id', 'id');
    }

    public function activity()
    {
        return $this->belongsTo('Activity', 'activity_id', 'id');
    }

    public static function createUser($activityID, $userID)
    {
        return self::create([
            'activity_id'  => $activityID,
            'user_id'  => $userID,
        ]);
    }


    /**
     * 用户参与raffle活动
     * @param $activityID
     * @param $userID
     * @return array
     * error 0表示参与成功
     * error 1表示重复成功
     * error 2表示参与失败
     */
    public function joinRaffle($activityID, $userID)
    {
        try{
            $validTime = (time() - self::VALID_PERIOD);
            $joinedUser = self::where(['create_time', '<', $validTime])
                ->where(['activity_id' => $activityID, 'user_id' => $userID])
                ->find();
            if ($joinedUser) {
                return ['error' => 1];
            } else {
                $res = self::create([
                    "activity_id" => $activityID,
                    "user_id"     => $userID,
                ]);
                if ($res) {
                    return ['error' => 0];
                } else {
                    return ['error' => 2];
                }
            }
        }catch(Exception $e){
            // TODO 做日志记录
            return ['error' => 2, 'msg' => $e->getMessage()];
        }
    }


    /**
     * 在规定时间内是否重复参加
     * @param $activityID
     * @param $userID
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function validRaffleUser($activityID, $userID)
    {
        $validTime = (time() - self::VALID_PERIOD);
        $joinedUser = self::where('create_time', '>', $validTime)
            ->where(['activity_id' => $activityID, 'user_id' => $userID])
            ->find();

        if (!$joinedUser) {
            return true;
        } else {
            return false;
        }
    }




}