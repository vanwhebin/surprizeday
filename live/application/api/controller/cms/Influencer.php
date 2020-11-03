<?php

namespace app\api\controller\cms;

use app\api\model\Activity as ActivityModel;
use app\api\model\Influencer as InfluencerModel;
use app\api\model\InfluencerSource;
use app\api\validate\CountValidate;
use app\api\validate\IDMustBePositiveIntValidate;
use app\api\validate\influencer\InfluencerBindValidate;
use app\api\validate\influencer\InfluencerSearchValidate;
use app\api\validate\influencer\InfluencerValidate;
use think\Controller;
use think\facade\Hook;
use think\Request;

class Influencer extends Controller
{

    /**
     * 获取红人列表
     * @url api/cms/influencer
     * @method GET
     * @param int $num
     * @param int $page
     * @return array
     * @throws \app\lib\exception\InvalidParamException
     * @throws \think\exception\DbException
     */
    public function index($num=10, $page=1)
    {
        (new CountValidate())->validate();
        $model = new InfluencerModel();
        return $model->getAll($num, $page)->toArray();
    }

    /**
     * @param Request $request
     * @return \think\response\Json
     * @throws \app\lib\exception\InvalidParamException
     */

    public function create(Request $request)
    {
        (new InfluencerValidate())->validate();
        $param = $request->post();
        $res = InfluencerModel::createOne($param);
        if ($res['error']) {
            return writeJson(400, $param, "创建失败，\r\n 错误信息：". $res['error']['msg']);
        } else {
            return writeJson(201,'', '创建成功');
        }
    }


    /**
     *  * 显示编辑资源表单页.
     * @param $id
     * @return array
     * @throws \app\lib\exception\InvalidParamException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */

    public function read($id)
    {
        (new IDMustBePositiveIntValidate())->validate();
        return InfluencerModel::with(['relationship', 'relationship.source'])->where(['id' => $id])->find()->toArray();
    }

    /**
     * 模糊查询活动
     * @param Request $request
     * @method GET
     * @return array
     * @throws \app\lib\exception\InvalidParamException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function activity(Request $request)
    {
        (new InfluencerSearchValidate())->validate();
        $param = $request->param();
        $cond = ["%{$param['query']}%", "{$param['query']}%", "%{$param['query']}"];
        // 获取当前的私人活动
        return (new ActivityModel())->whereLike('title',  $cond, "OR")
            ->where('start_time', 'GT', time())
            ->where(['private' => 1])
            ->visible(['title', 'id'])
            ->select()
            ->toArray();
    }

    /**
     * 查询网红具体信息
     * @param Request $request
     * @method GET
     * @return mixed
     * @throws \app\lib\exception\InvalidParamException
     */
    public function search(Request $request)
    {
        (new InfluencerSearchValidate())->validate();
        $param = $request->param();
        $cond = ["%{$param['query']}%", "{$param['query']}%", "%{$param['query']}"];
        return (new InfluencerModel)->whereLike('name', $cond, 'OR')
            ->visible(['name', 'id'])
            ->select()
            ->toArray();
    }

    /**
     * 绑定红人活动
     * @param Request $request
     * @return \think\response\Json
     * @throws \app\lib\exception\InvalidParamException
     */
    public function bind(Request $request)
    {

        (new InfluencerBindValidate())->validate();
        $param = $request->param();
        $model = new InfluencerModel;
        $res = $model->bindActivity($param);

        if ($res['error']) {
            return writeJson(400, $param, "绑定失败，\r\n 错误信息：". $res['error']['msg']);
        } else {
            return writeJson(201,'', '绑定成功');
        }
    }

    /**
     * 分页查出所有红人及其绑定活动
     * @param int $num
     * @param int $page
     * @return array
     * @throws \app\lib\exception\InvalidParamException
     * @throws \think\exception\DbException
     */
    public function influActivity($num=10, $page=1)
    {
        (new CountValidate())->validate();
        $model = new ActivityModel();
        return $model->getBindActivity($num, $page)->toArray();

    }


    /**
     * @param Request $request
     * @return \think\response\Json
     * @throws \Exception
     * @throws \app\lib\exception\InvalidParamException
     * @throws \app\lib\exception\MissingException
     */
    public function update(Request $request)
    {
        (new InfluencerValidate())->validate();
        (new IDMustBePositiveIntValidate())->validate();
        $data = $request->put();

        $res = (new InfluencerModel())->updateOne($data);
        if ($res['error']) {
            return writeJson(400, $data, "更新失败，\r\n 错误信息：". $res['error']['msg']);
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
        $res = (new InfluencerModel())->delete($id);
        if ($res['error']){
            return writeJson(400, ['id' => $id], '删除失败', 1);
        }
        Hook::listen('logger', '删除了id为' . $id . '的奖品');
        return writeJson(201, '', '删除成功');
    }

    public function source()
    {
        $model = new InfluencerSource();
        return $model->select()->visible(['id', 'name'])->toArray();
    }

}
