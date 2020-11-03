<?php

namespace app\api\controller\cms;

use app\api\validate\IDMustBePositiveIntValidate;
use app\api\validate\CountValidate;
use think\App;
use think\Controller;
use think\facade\Hook;
use think\Request;
use app\api\model\Sponsor as SponsorModel;

class Sponsor extends Controller
{
    protected $model;

    public function __construct(App $app = null)
    {
        parent::__construct($app);
        $this->model= new SponsorModel();
    }


    /**
     * * 显示资源列表
     * @param Request $request
     * @param int $num
     * @param int $page
     * @return array
     * @throws \app\lib\exception\InvalidParamException
     * @throws \think\exception\DbException
     */

    public function index(Request $request, $num=15, $page=1)
    {
        (new CountValidate())->validate();
        return $this->model->paginate($num, false, ['page' => $page])->toArray();
    }

    /**
     * 显示创建资源表单页.
     * @param Request $request
     * @return \think\response\Json
     */

    public function create(Request $request)
    {
        $param = $request->post();
        $res = $this->model->create([
            'name' => $param['name'],
            'link' => $param['link'],
        ]);
        if (!$res) {
            return writeJson(400, $param, "创建失败");
        } else {
            return writeJson(201,'', '创建成功');
        }
    }

    /**
     * @param $id
     * @return mixed
     * @throws \app\lib\exception\InvalidParamException
     */
    public function read($id)
    {
        (new IDMustBePositiveIntValidate())->validate();
        return $this->model->get($id);
    }

    /**
     * @param Request $request
     * @param $id
     * @return \think\response\Json
     * @throws \app\lib\exception\InvalidParamException
     */
    public function update(Request $request, $id)
    {
        (new IDMustBePositiveIntValidate())->validate();
        $param = $request->put();
        $res = $this->model->update([
            'id' => $param['id'],
            'name' => $param['name'],
            'link' => $param['link'],
        ]);

        if (!$res) {
            return writeJson(400, $param, "更新失败");
        } else {
            return writeJson(201,'', '更新成功');
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
        $this->model->destroy($id);
        Hook::listen('logger', '删除了id为' . $id . '的赞助商');
        return writeJson(201, '', '删除成功');
    }
}
