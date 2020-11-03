<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/6/18
 * Time: 17:37
 */

namespace app\api\model;


class Subscription extends BaseModel
{
    protected $hidden = ['delete_time'];

    /**
     * @param $email
     * @return array|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function checkSubscribeStatus($email){
        // 查看是否拒绝接受邮件
        return self::where(['email' => $email])->field('status')->findOrEmpty();
    }
}