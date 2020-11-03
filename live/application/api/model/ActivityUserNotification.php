<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/6/19
 * Time: 20:15
 */

namespace app\api\model;


class ActivityUserNotification extends BaseModel
{
    protected $hidden = ['delete_time'];

    public function user()
    {
        return $this->belongsTo('User', 'user_id','id');
    }

    public function activity()
    {
        return $this->belongsTo('Activity', 'activity_id', 'id');
    }



}