<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/6/17
 * Time: 21:41
 */

namespace app\api\model;

use think\Db;

class ActivityPrize extends BaseModel
{
    protected $hidden = ['delete_time'];

    public static function getVarCount($var=[])
    {
        return Db::table('activity_prize')->where($var)->count();
    }
}