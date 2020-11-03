<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/7/16
 * Time: 10:24
 */

namespace app\api\service;
use app\api\model\User as UserModel;
use app\api\model\ActivityUser as ActivityUserModel;
use app\lib\exception\InvalidParamException;
use think\Db;

class Data
{
    protected $userTable = 'user';
    protected $activityUserTable = 'activity_user';
    protected $startTime;
    protected $endTime;
    protected $real = 1; // 真实用户
    protected $allowTableArr = [
        'activity_user',
        'user',
    ];


    /**
     * Data constructor.
     * @throws InvalidParamException
     */
    public function __construct()
    {
        $this->startTime = strtotime(date('Y-m-d',strtotime('-1 day')));
        $this->endTime = strtotime(date('Y-m-d'));
        $table = input('model', '');
        if (!in_array($table, $this->allowTableArr)){
            throw new InvalidParamException(['msg' => '非法请求']);
        };
    }



    public function usersCount($table="user")
    {
        $field = (strtolower($table) == $this->userTable)? ['id'] : ['userid'] ;

        if (strtolower($table) == $this->activityUserTable) {
            // 真实用户参与活动数量总计
            $total = Db::table($this->activityUserTable)
                ->where('is', '=', $this->real)
                ->where('create_time', '<>', '')
                ->field($field)
                ->count();
            // 真实用户当日参与活动数量总计
            $new = Db::table($this->activityUserTable)
                ->where('create_time', '>', $this->startTime)
                ->whereBetweenTime('create_time', $this->startTime, $this->endTime)
                ->where('is', '=', $this->real)
                ->field($field)
                ->count();
        } else {
            // 真实用户数量总计
            $total = Db::table($this->userTable)->where('create_time', '<>', '')->count();
            // 真实当日用户参与活动后获取message_id 或 邮箱之后的总计
            $new = Db::table('user')
                ->whereBetweenTime('create_time', $this->startTime, $this->endTime)
                ->where('message_id != "" OR email != "" ')
                ->field($field)
                ->count();
        }
        return ['total' => $total, 'new' =>  $new];
    }


    public static function total($model="user",$date=[])
    {


    }

    public static function newData($model='user', $date=[], $num=20, $page=1)
    {
        // 计算时间段内新用户数目
    }

}