<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/10/22
 * Time: 11:35
 */

namespace app\api\model;


class RaffleWinner extends BaseModel
{
    protected $hidden = ['delete_time'];

    public function prize()
    {
        return $this->belongsTo('Prize', 'prize_id', 'id');
    }

    public function activity()
    {
        return $this->belongsTo('Activity', 'activity_id', 'id');
    }

    /**
     * @param $prizeID
     * @param $userID
     * @return array
     */
    public static function getWinnerByPrize($prizeID, $userID)
    {
        return self::where(['prize_id' => $prizeID, 'user_id' => $userID])->value('id');
    }

    /**
     * @param $activityID
     * @param $userID
     * @return array|null|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getWinnerByActivity($activityID, $userID)
    {
        return self::where(['activity_id' => $activityID, 'user_id' =>  $userID])->find();
    }

    public static function createWinner($activityID, $userID, $prizeID)
    {
        return self::create([
            'prize_id' =>  $prizeID,
            'activity_id' =>  $activityID,
            'user_id' =>  $userID,
        ]);
    }


}