<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/8/7
 * Time: 10:56
 */

namespace app\api\model;


class ActivityTeamUser extends BaseModel
{
    public function team()
    {
        return $this->belongsTo('ActivityTeam', 'team_id', 'id');
    }

    public static function getOne($teamID, $userID)
    {
        return self::where(['user_id' => $userID, 'team_id' => $teamID])->find();
    }

}