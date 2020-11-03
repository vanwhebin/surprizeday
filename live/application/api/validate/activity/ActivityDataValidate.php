<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/10/18
 * Time: 16:55
 */

namespace app\api\validate\activity;


use app\api\validate\BaseValidate;

class ActivityDataValidate extends BaseValidate
{
    protected $rule = [
        'title'         => 'require|isNotEmpty',
        'seo_title'     => 'require|isNotEmpty',
        'sponsor_id'    => 'require|isPositiveInteger',
        'start_time'    => 'require|isPositiveInteger',
        'status'        => 'require',
        'type'          => 'require|isPositiveInteger',
        'thumb'         => 'require|isPositiveInteger',
    ];

    protected $message = [
        'title'         => 'title is required',
        'seo_title'     => 'title for sharing is required',
        'sponsor_id'    => 'sponsor is required',
        'start_time'    => 'winner draw time is required',
        'status'        => 'status is required',
        'type'          => 'please make sure the type of the activity',
        'thumb'         => 'the cover of this activity is required'
    ];

}