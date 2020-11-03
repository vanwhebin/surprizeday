<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/9/24
 * Time: 20:44
 */

namespace app\api\model;

use app\api\model\Activity as ActivityModel;
use app\lib\exception\InvalidParamException;
use app\lib\exception\MissingException;
use think\Db;
use think\Exception;
use think\exception\DbException;

class Influencer extends BaseModel
{
    protected $hidden = ['delete_time'];

    public function relationship()
    {
        return $this->hasMany('InfluSourceRelationship',  'influ_id', 'id');
    }

    public function activity()
    {
        return $this->hasMany('Activity', 'influ_id', 'id');
    }

    /**
     * 生成关联数组
     * @param $platformArr
     * @param $influID
     * @return array
     */
    public static function generateRelationArr($platformArr, $influID)
    {
        $platformRelation = [];
        if ($platformArr) {
            foreach ($platformArr as $item) {
                $platformRelation[] = ['source_id' => $item, 'influ_id' => $influID];
            }
        }
        return $platformRelation;
    }


    /**
     * @param $data
     * @return array|static
     */
    public static function createOne($data)
    {
        Db::startTrans();
        try{
            $data['memo'] = !empty($data['memo']) ? htmlspecialchars(addslashes($data['memo'])):"";
            $influ =  self::create($data, true);
            $platformRelation = self::generateRelationArr($data['platform'], $influ->id);
            $influ->relationship()->saveAll($platformRelation);
        }catch(DbException $exception){
            Db::rollback();
            return ['error' => ['code' =>  $exception->getCode(), 'msg' =>  $exception->getMessage()]];
        }
        Db::commit();
        return ['error' => ''];
    }

    /**
     *  * 获取所有的红人
     * @param int $num
     * @param int $page
     * @return \think\Paginator
     * @throws \think\exception\DbException
     *
     */
    public function getAll($num=15, $page=1)
    {
        return self::with([
            'relationship' => function($query){ $query->visible(['source_id', 'source']);},
            'relationship.source'
        ])->order('update_time desc, id desc')->paginate($num, false, ['page' => $page]);
    }

    /**
     * @param $data
     * @return array
     * @throws MissingException
     * @throws \Exception
     */
    public function updateOne($data)
    {
        Db::startTrans();
        try {
            if (!self::get($data['id'])) {
                throw new MissingException();
            }
            $data['memo'] = !empty($data['memo']) ? htmlspecialchars(addslashes($data['memo'])): "";
            (new InfluSourceRelationship())->editRelation($data['platform'], $data['id']);
            $res = $this->save($data, ['id' => $data['id']]);
        } catch (Exception $e){
            Db::rollback();
            return ['error' => ['code' =>  $e->getCode(), 'msg' => $e->getMessage()]];
        }
        Db::commit();
        if (!$res) {
            return ['error' => ['code' => 10000, 'msg' => '数据未做更改']];
        }

        return ['error' => ''];
    }

    /**
     * 给红人绑定对应的活动
     * @param $data
     * @return array
     */
    public function bindActivity($data)
    {

        try{
            (new ActivityModel())->save(['influ_id' => $data['influ_id']], ['id' =>  $data['activity_id']]);
        } catch(Exception $exception){
            return ['error' => ['code' =>$exception->getCode(), 'msg' =>  $exception->getMessage()]];
        }
        return ['error' => ''];
    }


}