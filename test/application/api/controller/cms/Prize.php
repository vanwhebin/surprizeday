<?php

namespace app\api\controller\cms;

use app\api\model\ActivityPrize as ActivityPrizeModel;
use app\api\model\Prize as PrizeModel;
use app\api\model\PrizeImage as PrizeImageModel;
use app\api\validate\CountValidate;
use app\api\validate\IDMustBePositiveIntValidate;
use app\api\validate\activity\ActivityIDValidate;
use think\Controller;
use think\facade\Hook;
use think\Request;

class Prize extends Controller
{

    // TODO 增加权限控制
    /** 获取奖品列表
     * @param int $num
     * @param int $page
     * @return mixed
     * @throws \app\lib\exception\InvalidParamException
     */
    public function index($num=15, $page=1)
    {
        (new CountValidate())->validate();
        $model = new PrizeModel();
        return $model->getAll($num, $page);
    }

    /**
     * @param Request $request
     * @return \think\response\Json
     */

    public function create(Request $request)
    {
        $param = $request->post();
        $res = PrizeModel::createOne($param);
        if ($res['error'] == 1) {
            return writeJson(400, $param, "创建失败，\r\n 错误信息：". $res['msg']);
        } else {
            return writeJson(201,'', '创建成功');
        }
    }


    /**
     * 显示编辑资源表单页.
     * @param  int  $id
     * @return array
     * @throws \app\lib\exception\InvalidParamException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function read($id)
    {
        (new IDMustBePositiveIntValidate())->validate();
        return PrizeModel::getOne($id)->toArray();
    }


    /**
     * @param Request $request
     * @return array
     * @throws \think\exception\DbException
     */
    public function search(Request $request)
    {
        $param = $request->param();
        $param['num'] = !empty($param['num']) ? $param['num'] : 50;
        $param['page'] = !empty($param['page']) ? $param['page'] : 1;
        return PrizeModel::where('name', "like", ["%{$param['query']}%", "{$param['query']}%", "%{$param['query']}" ], 'OR')
            ->paginate($param['num'], false, ['page' => $param['page']])
            ->toArray();
    }


    /**
     * @param Request $request
     * @return \think\response\Json
     * @throws \app\lib\exception\InvalidParamException
     */
    public function update(Request $request)
    {
        (new IDMustBePositiveIntValidate())->validate();
        $data = $request->put();
        $res = PrizeModel::updateOne($data);
        if ($res['error'] == 1) {
            return writeJson(400, $data, "创建失败，\r\n 错误信息：". $res['msg']);
        } else {
            return writeJson(201,'', '更新成功');
        }
    }


    /**
     * 删除指定资源
     * @param $id
     * @return \think\response\Json
     * @throws \app\lib\exception\InvalidParamException
     */
    public function delete($id)
    {
        (new IDMustBePositiveIntValidate())->validate();
        $res = (new PrizeModel())->deleteOne($id);
        if ($res['error']){
            return writeJson(400, ['id' => $id], '删除失败', 1);
        }
        Hook::listen('logger', '删除了id为' . $id . '的奖品');
        return writeJson(201, '', '删除成功');
    }


    /**
     * @param Request $request
     * @return mixed
     * @throws \app\lib\exception\InvalidParamException
     */
    public function activity(Request $request)
    {
        (new ActivityIDValidate())->validate();
        $activityID = $request->get('activity_id');
        $prizeID = ActivityPrizeModel::where(['activity_id'=>$activityID])->column('prize_id');
        $prize = PrizeModel::get($prizeID);
        return $prize;
    }
}
