<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/8/7
 * Time: 10:56
 */

namespace app\api\model;


class ActivityTeam extends BaseModel
{
    public function teamUser()
    {
        return $this->hasMany('ActivityTeamUser', 'team_id', 'id');
    }
}