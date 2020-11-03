<?php

namespace app\api\controller\cms;

//use app\api\validate\user\LoginForm;  # 开启注释验证器以后，本行可以去掉，这里做更替说明
//use app\api\validate\user\RegisterForm; # 开启注释验证器以后，本行可以去掉，这里做更替说明
use app\api\model\Fakers as FakersModel;
use app\api\model\Winner as WinnerModel;
use app\api\model\Activity as ActivityModel;
use app\api\validate\activity\ActivityIDValidate;
use app\api\validate\IDMustBePositiveIntValidate;
use app\api\validate\PaginationValidate;
use app\lib\token\Token;
use LinCmsTp5\admin\model\LinUser;
use think\Controller;
use think\facade\Hook;
use think\Request;

class User extends Controller
{
    /**
     * 账户登陆
     * @param Request $request
     * @validate('LoginForm')
     * @return array
     * @throws \think\Exception
     */
    public function login(Request $request)
    {
//        (new LoginForm())->goCheck();  # 开启注释验证器以后，本行可以去掉，这里做更替说明
        $params = $request->post();

        $user = LinUser::verify($params['nickname'], $params['password']);
        $result = Token::getToken($user);

        Hook::listen('logger', array('uid' => $user->id, 'nickname' => $user->username, 'msg' => '登陆成功获取了令牌'));

        return $result;
    }


    /**
     *   * 查询自己拥有的权限
     * @return array|\PDOStatement|string|\think\Model
     * @throws \LinCmsTp5\admin\exception\user\UserException
     * @throws \UnexpectedValueException
     * @throws \app\lib\exception\token\TokenException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAllowedApis()
    {
        $uid = Token::getCurrentUID();
        $result = LinUser::getUserByUID($uid);
        return $result;
    }

    /**
     * @auth('创建用户','管理员','hidden')
     * @param Request $request
     * @validate('RegisterForm')
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function register(Request $request)
    {
//        (new RegisterForm())->goCheck(); # 开启注释验证器以后，本行可以去掉，这里做更替说明

        $params = $request->post();
        LinUser::createUser($params);

        Hook::listen('logger', '创建了一个用户');

        return writeJson(201, '', '用户创建成功');
    }

    /**
     * @return mixed
     * @throws \UnexpectedValueException
     * @throws \app\lib\exception\token\TokenException
     * @throws \think\Exception
     */
    public function getInformation()
    {
        $user = Token::getCurrentUser();
        return $user;
    }

    /**
     * @param Request $request
     * @return \think\response\Json
     * @throws \LinCmsTp5\admin\exception\user\UserException
     * @throws \UnexpectedValueException
     * @throws \app\lib\exception\token\TokenException
     * @throws \think\Exception
     */
    public function setAvatar(Request $request)
    {
        $url = $request->put('avatar');
        $uid = Token::getCurrentUID();
        LinUser::updateUserAvatar($uid, $url);

        return writeJson(201, '', '更新头像成功');
    }


    /**
     * @return array
     * @throws \UnexpectedValueException
     * @throws \app\lib\exception\token\TokenException
     * @throws \think\Exception
     */
    public function refresh()
    {
        $result = Token::refreshToken();
        return $result;
    }

    /**
     * 获取活动假人
     * @param Request $request
     * @return array
     * @throws \app\lib\exception\InvalidParamException
     * @throws \think\exception\DbException
     */
    public function fakers(Request $request)
    {
        (new PaginationValidate())->validate();
        $data = $request->param();
        $fakeModel = new FakersModel();
        if (isset($data['rand']) & $data['rand'] !== "") {
            $total = FakersModel::count('id');
            $ids = unique_rand(1, $total, $data['rand']);
            return $fakeModel::with(['user'=>function($q){ $q->field('id, avatar')->visible(['avatar']);}])
                ->where(['id' => $ids])->field('user_id')
                ->paginate($data['rand'], false, ['page' => 1])
                ->toArray();
        }
        $res = $fakeModel->with(['user'=>function($q){ $q->field('id, avatar')->visible(['avatar']);}])->order('id desc')
            ->field('user_id')
            ->paginate($data['num'], false, ['page' => $data['page']])
            ->toArray();
        return $res;
    }

    /**
     * *统一处理活动winners
     * @param Request $request
     * @return array|\think\response\Json
     * @throws \app\lib\exception\InvalidParamException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function winners(Request $request)
    {
        $data = $request->param();
        if ($request->isGet()) {
            return $this->_readWinners($data);
        } elseif  ($request->isDelete()) {
            (new IDMustBePositiveIntValidate())->validate();
            return $this->_deleteWinners($data);
        } else {
            return writeJson(400, $data, '非法操作');
        }
    }


    /**
     * 查询winners
     * @param $data
     * @return array
     * @throws \app\lib\exception\InvalidParamException
     * @throws \think\exception\DbException
     */
    protected function _readWinners($data)
    {
        (new PaginationValidate())->validate();
        return WinnerModel::with(['activity' => function($query){ $query->field(['id', 'title', 'start_time'])->hidden(['id']);}])
            ->field(['id','user_id', 'activity_id', 'name', 'update_time', 'level'])
            ->order('id desc, activity_id desc')
            ->paginate($data['num'], false, ['page' => $data['page']])
            ->toArray();
    }

    /**
     * 删除winners
     * @param $data
     * @return \think\response\Json
     */
    protected function _deleteWinners($data)
    {
        $res = (new WinnerModel())->destroy($data['id']);
        if ($res) {
            Hook::listen('logger', '删除了ActivityID为' . $data['id'] . '的Winners');
            return writeJson(201, '', '删除成功');
        } else {
            return writeJson(400, $data, '删除失败');
        }
    }

}
