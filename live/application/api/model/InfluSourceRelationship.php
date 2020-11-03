<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/9/27
 * Time: 17:23
 */

namespace app\api\model;


use think\exception\DbException;

class InfluSourceRelationship extends BaseModel
{
    protected $hidden = ['delete_time'];

    public function influ()
    {
        return $this->belongsTo('Influencer', 'influ_id', 'id');
    }

    public function source()
    {
        return $this->belongsTo('InfluencerSource', 'source_id', 'id');
    }


    /**
     * * 编辑红人平台关系
     * @param $relationArr
     * @param $influID
     * @return string
     * @throws \Exception
     */
    public function editRelation($relationArr, $influID)
    {
        try {
            $curSource = self::where(['influ_id' => $influID])->column('source_id');
            if ($curSource) {
                $remove = array_values(array_diff($curSource, $relationArr));
                $more = array_values(array_diff($relationArr, $curSource));
                if ($remove) {
                    // 删除多余部分
                    self::destroy(['influ_id' => $influID, 'source_id' => $remove], true);
                }

                if ($more) {
                    // 增加一部分
                    $moreRelation = Influencer::generateRelationArr($more, $influID);
                    if ($this->saveAll($moreRelation)){
                        throw new DbException('保存红人平台关系失败');
                    }
                }
            }
        } catch (DbException $exception) {
            return $exception->getLine().': '.$exception->getMessage();
        }
    }


    public function delRelation()
    {

    }


}