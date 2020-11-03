<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/6/18
 * Time: 17:37
 */

namespace app\api\model;


use app\lib\exception\InvalidParamException;
use think\facade\Cache;

class Winner extends BaseModel
{
    protected $hidden = ['delete_time'];

    public function info()
    {
        return $this->belongsTo('User', 'user_id', 'id');
    }

    public function activity()
    {
        return $this->belongsTo('Activity', 'activity_id', 'id');
    }

    public function getLevelAttr($value, $data)
    {
        $level = "";
        if ($value === 1) {
            $level = "一等奖";
        } else if ($value === 2) {
            $level = "二等奖";
        }

        return $level;
    }

    /**
     * 获取当前活动下的获奖用户
     * @param $num
     * @param $page
     * @param $activityID
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public static function getWinnerByActivityID($num, $page, $activityID)
    {
        $allWinnerActivityKey = "allWinners:num:". $num .':page:'. $page. ":activityID:". $activityID;
        $allWinners = Cache::store('redis')->get($allWinnerActivityKey);
        if (!$allWinners) {
            $winners = self::with(['info' => function($query){
                $query->visible(['name', 'avatar']);
            }])->where(['activity_id' => $activityID ,'level' => 1])
                ->order('id desc')
                ->visible(['info'])
                ->paginate($num, false, ['page' => $page])
                ->toArray();
            Cache::store('redis')->set($allWinnerActivityKey, json_encode($winners), 300);
        } else {
            $winners = json_decode($allWinners, true);
        }

        return $winners;
    }

    /**
     * @param $activityID
     * @param $userID
     * @return bool
     * @throws InvalidParamException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function claim($activityID, $userID)
    {
        $winner = self::where([
            'activity_id' => $activityID,
            'user_id' => $userID,
        ])->find();
        if (!$winner) {
            throw new InvalidParamException();
        }
        if (!$winner->claimed) {
            $winner->claimed = 1;
            return $winner->save();
        } else {
            throw new InvalidParamException(['msg' => 'You have claimed the prize before.']);
        }
    }

}