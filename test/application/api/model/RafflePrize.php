<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/10/22
 * Time: 11:35
 */

namespace app\api\model;


class RafflePrize extends BaseModel
{
    protected $hidden = ['delete_time'];


    public function prize()
    {
        return $this->belongsTo('Prize', 'prize_id', 'id');
    }

    /**
     * 抽中用户之后需要进行更新奖品库存
     * @param $activityID
     * @param $prizeID
     * @return int|true
     * @throws \think\Exception
     */
    public static function reduceStock($activityID, $prizeID)
    {
        return self::where(['activity_id' => $activityID, 'prize_id' => $prizeID])
            ->setDec('stock', 1);
    }

}