<?php


namespace app\api\validate;


class IDCollectionValidate extends BaseValidate
{

    protected $rule = [
        'ids' => 'require|IDCollectionCheck'
    ];

    protected $message = [
        'ids' => 'ID must be an integer or a collection of integer',
    ];

    public function IDCollectionCheck($value)
    {
        $value = explode(',', $value);
        foreach($value as $k=>$v) {
            $r = (new IDMustBePositiveIntValidate())->isPositiveInteger($v);
            if(!$r) {
                return false;
            }
        }
        return true;
    }

}