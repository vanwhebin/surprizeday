<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/11/7
 * Time: 19:29
 */

namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\validate\PaginationValidate;

class Freebies extends BaseController
{
    public function deals($num = 15, $page = 1)
    {
        (new PaginationValidate())->validate();
        // $deals = Freebie


    }
}