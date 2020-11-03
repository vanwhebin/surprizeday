<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/11/2
 * Time: 17:55
 */

namespace app\index\controller;


use app\api\model\Log;
use think\Controller;
use think\Request;
use app\api\model\Freebies as FreebiesModel;

class Common extends Controller
{
    /**
     *  * freebies页面
     * 跳转亚马逊页面的中转页面
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */

    public function landing(Request $request)
    {
        $landingCode = $request->get('ref', '');
        $messageID = $request->get('message_id', '');
        $curFreebie = '';
        if ($landingCode) {
            $freebie = FreebiesModel::getFreebieByCode($landingCode);
            if (!$freebie || empty($freebie['stock'])) {
                $curFreebie = 'soldOut';
            } else {
                Log::create([
                    'log'    => json_encode(["message_id" => $messageID, "freebie"    => $freebie]),
                    "topic"  => "landingPage ".$freebie['name']
                ]);
                header("Location: {$freebie['amazon_url']}", true, 302);
                exit;
            }
        }

        $freebies = FreebiesModel::allFreebies();
        $freebies = $freebies->toArray();
        $this->assign('curFreebieLeft', $curFreebie);
        $this->assign('freebies', $freebies);
        return $this->fetch('index/pc/freebies');

    }

    public function errorPop(Request $request)
    {
        // type = 1 表示是来自freebies的错误提示
        $type = intval($request->get('type', 1));
        $info = [];
        if ($type === 1) {
            $info = [
                'title' => 'Oops',
                'desc' => 'Something wrong, please try later',
                'type' => 'error',
                'btnText' => 'More giveaways',
                'url' => config('domain'),
            ];
        } else if ($type === 2) {
            $info = [
                'title' => 'Oops',
                'desc' => 'This product is sold out. <br>Please check out other products that we offer.👇',
                'type' => 'info',
                'btnText' => 'More giveaways',
                'url' => config('domain'),
            ];
        }
        $this->assign('info', json_encode($info));
        return $this->fetch('template/error');
    }
}