<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/8/19
 * Time: 16:37
 */

namespace app\api\controller\cms;

use app\api\model\Activity as ActivityModel;
use app\api\validate\activity\ActivityDataValidate;
use app\api\validate\IDMustBePositiveIntValidate;
use app\api\validate\PaginationValidate;
use think\Controller;
use think\facade\Hook;
use think\Request;

class Activity1 extends Controller
{
    public function index($num=10, $page=1)
    {
        return ActivityModel::getAllSimpleInfo($num, $page);
    }

    

    /**
     *   统一处理活动操作
     * @param Request $request
     * @return array|\think\response\Json
     * @throws \app\lib\exception\InvalidParamException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */

    public function info(Request $request)
    {
        $data = $request->param();
        if ($request->isGet()) {
            return $this->_read($data);
        } else if ($request->isPost()) {
            return $this->_create($data);
        } else if ($request->isDelete()) {
            return $this->delete($data);
        } else if ($request->isPut()) {
            return $this->_update($data);
        } else {
            return writeJson(403, $data, '非法操作');
        }
    }


    /**
     * 创建活动数据
     * @param $data array
     * @return \think\response\Json
     * @throws \app\lib\exception\InvalidParamException
     */
    public function _create($data)
    {
        (new ActivityDataValidate())->validate();
        $res = ActivityModel::createActivity($data);
        if ($res['error']) {
            return writeJson(400, $data, $res['error']['msg'], 1);
        } else {
            Hook::listen('logger', '创建了一个新活动');
            return writeJson(201, '', '新建活动成功');
        }
    }

    /**
     * 查询具体的活动信息
     * @param $data
     * @return array
     * @throws \app\lib\exception\InvalidParamException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function _read($data)
    {
        (new IDMustBePositiveIntValidate())->validate();
        $act = ActivityModel::getSimpleInfo($data['id'])->toArray();
        $act['description'] = htmlspecialchars_decode(stripslashes($act['description']));

        return $act;
    }

    /**
     * 查询活动信息
     * @param Request $request
     * @return array
     * @throws \app\lib\exception\InvalidParamException
     * @throws \think\exception\DbException
     */
    public function search(Request $request)
    {
        (new PaginationValidate())->validate();
        $param = $request->param();
        $param['num'] = !empty($param['num']) ? $param['num'] : 50;
        $param['page'] = !empty($param['page']) ? $param['page'] : 1;
        return ActivityModel::where('title', "like", ["%{$param['query']}%", "{$param['query']}%", "%{$param['query']}" ], 'OR')
            ->paginate($param['num'], false, ['page' => $param['page']])
            ->toArray();
    }

    /**
     *隐藏当前活动
     * @param $id
     * @return bool
     * @throws \app\lib\exception\InvalidParamException
     */
    public function hide($id)
    {
        (new IDMustBePositiveIntValidate())->validate();
        $act = (new ActivityModel())->save(['status' => 0], ['id' => $id]);
        return $act;
    }

    /**
     * 更新活动数据
     * @param $data
     * @return \think\response\Json
     * @throws \app\lib\exception\InvalidParamException
     */
    protected function _update($data)
    {
        (new ActivityDataValidate())->validate();
        $res = ActivityModel::updateActivity($data);
        if ($res['error']) {
            return writeJson(400, $data, $res['error']['msg'], 1);
        } else {
            Hook::listen('logger', '更新活动');
            return writeJson(200, '', '更新活动成功');
        }
    }

    /**
     * @param $data
     * @return \think\response\Json
     * @throws \app\lib\exception\InvalidParamException
     */
    protected function _delete($data)
    {
        (new IDMustBePositiveIntValidate())->validate();
        $res = (new ActivityModel())->deleteOne($data['id']);
        if ($res['error']) {
            return writeJson(400, ['id' => $data['id']], '删除失败', 1);
        }
        Hook::listen('logger', '删除了id为' . $data['id'] . '的赞');
        return writeJson(201, '', '删除成功');
    }

}