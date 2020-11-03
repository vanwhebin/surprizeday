<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/6/17
 * Time: 11:00
 */

namespace app\api\controller\v1;

use app\api\controller\BaseController;
use app\api\validate\UserValidate;

class User extends BaseController
{
    public function addOrUpdateUserInfo()
    {
        (new UserValidate())->validate();
        // 前端提交用户ID过来，查询数据库，如果存在则返回不操作
        // 如果用户ID不存在，则进行插入操作，增加一个用户




    }

}