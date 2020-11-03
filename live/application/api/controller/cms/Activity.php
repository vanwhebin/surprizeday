<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/8/19
 * Time: 16:37
 */

namespace app\api\controller\cms;

use app\api\model\Activity as ActivityModel;
use app\api\validate\IDMustBePositiveIntValidate;
use app\api\validate\PaginationValidate;
use think\Controller;
use think\facade\Hook;
use think\Request;

class Activity extends Controller
{
    /**
     * @param int $num
     * @param int $page
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function index($num=10, $page=1)
    {
        return ActivityModel::getAllSimpleInfo($num, $page);
    }

    public function create(Request $request)
    {
        $data = $request->param();
        $res = ActivityModel::createActivity($data);
        if ($res['error']) {
            return writeJson(400, $data, $res['error']['msg'], 1);
        } else {
            // Hook::listen('logger', '创建了一个新活动');
            return writeJson(201, '', '新建活动成功');
        }
    }

    /**
     * 查询具体的活动信息
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
        $act = ActivityModel::getSimpleInfo($id)->toArray();
        $act['description'] = htmlspecialchars_decode(stripslashes($act['description']));

        return $act;
    }

    /**
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

    public function update(Request $request)
    {
        $data = $request->param();
        $res = ActivityModel::updateActivity($data);
        if ($res['error']) {
            return writeJson(400, $data, $res['error']['msg'], 1);
        } else {
            // Hook::listen('logger', '更新活动');
            return writeJson(200, '', '更新活动成功');
        }
    }

    /**
     * @param $id
     * @return \think\response\Json
     * @throws \app\lib\exception\InvalidParamException
     */
    public function delete($id)
    {
        (new IDMustBePositiveIntValidate())->validate();
        $res = (new ActivityModel())->deleteOne($id);
        if ($res['error']) {
            return writeJson(400, ['id' => $id], '删除失败', 1);
        }
        Hook::listen('logger', '删除了id为' . $id . '的赞');
        return writeJson(201, '', '删除成功');
    }

}