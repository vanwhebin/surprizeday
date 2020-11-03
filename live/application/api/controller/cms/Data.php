<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/7/16
 * Time: 10:10
 */

namespace app\api\controller\cms;

use app\api\service\Data as DataService;
use app\api\controller\BaseController;

class Data extends BaseController
{
    /**
     * 获取用户增长数据
     * @url api/cms.Data/userCount?model=activity_user
     * @param string $model (activity_user, user)
     * @return array
     */
    public function userCount($model="user")
    {
        $dataService = new DataService();
        return $dataService->usersCount($model);
    }

    public function userDataFlow()
    {

    }


}