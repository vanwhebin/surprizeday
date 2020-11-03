<?php

namespace app\api\controller\v1;

use app\api\validate\CountValidate;
use app\api\validate\IDMustBePositiveIntValidate;
use app\lib\exception\MissingException;
use app\lib\exception\PrizeException;
use app\api\controller\BaseController;
use app\api\model\Prize as PrizeModel;

class Prize extends BaseController
{

    /**
     * @url /product/latest?num=1,15
     * @param int $num
     * @return array
     * @throws \app\lib\exception\InvalidParamException
     */
    public function latest($num=15)
    {
        (new CountValidate())->batch()->validate();
        $prize = new PrizeModel();
        $res = $prize->latest($num);
        if ($res->isEmpty()) {
            throw new MissingException(['errorCode' => 20000]);
        }
        $res = $res->hidden(['summary']);
        return $res;
    }

    /**
     * @url /product/cate/:id
     * @http get
     * @param $id int
     * @return array
     */
    public function listInCate($id)
    {
        (new IDMustBePositiveIntValidate())->validate();
        $products = PrizeModel::productInCate($id);
        if (!$products) {
            throw new MissingException(['errorCode' => 20000]);
        }
        $products->hidden(['summary']);
        return $products;
    }

    /**
     * @url /product/:id
     * @http get
     */
    public function productDetail($id)
    {
        (new IDMustBePositiveIntValidate())->validate();
        // 查询产品
        $product = PrizeModel::getOne($id);
        if (!$product) {
            throw new PrizeException();
        }

        return $product;

    }

}
