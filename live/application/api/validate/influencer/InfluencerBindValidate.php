<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/9/27
 * Time: 17:32
 */

namespace app\api\validate\influencer;

use app\api\validate\BaseValidate;

class InfluencerBindValidate extends BaseValidate
{
    protected $rule = [
        'activity_id'      => 'require|min:1|isPositiveInteger',
        'influ_id'      => 'require|min:1|isPositiveInteger',
    ];

    protected $message = [
        'activity_id'      => 'activity id is required',
        'influ_id'      => 'influencer id is required',
    ];
}