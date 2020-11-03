<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/10/4
 * Time: 11:48
 */

namespace app\api\validate\influencer;


use app\api\validate\BaseValidate;

class influencerSourceRelationValidate extends BaseValidate
{
    protected $rule= [
        'platform' => 'array|require|min:1'
    ];


    public function sceneAdd()
    {
        return $this->append('platform', 'checkAddItem');
    }

    public function sceneEdit()
    {
        return $this->append('platform', 'checkEditItem');
    }

    public function checkAddItem($value)
    {
        foreach($value as $k=> $v){
            if (!empty($v['id'])) {
                return '新增不能包含ID值';
            }

            if (empty($value['source_id']) || empty($value['influ_id'])) {
                return '数据信息不完整';
            }
        }
        return true;
    }

    public function checkEditItem($value)
    {
        foreach($value as $k=> $v){
            if (empty($v['id'])) {
                return '记录值ID不能为空';
            }

            if (empty($v['source_id']) || empty($v['influ_id'])) {
                return '数据信息不完整';
            }
        }
        return true;
    }





}