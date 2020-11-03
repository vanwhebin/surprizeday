<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/11/8
 * Time: 15:50
 */

namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\validate\PaginationValidate;
use app\api\service\Deals as DealService;
use think\Request;

class Deals extends BaseController
{

    /**
     * 最新deals产品
     * @param int $num
     * @param int $page
     * @return \think\response\Json
     * @throws \app\lib\exception\InvalidParamException
     * @throws \think\exception\DbException
     */
    public function latest($num = 15, $page = 1)
    {
        (new PaginationValidate())->validate();
        $deals = DealService::getAll($num, $page);
        return json($deals);
    }

}