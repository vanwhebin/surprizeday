<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/5/22
 * Time: 16:13
 */

namespace app\api\validate;


class MsgTagValidate extends BaseValidate
{
   protected  $rule = [
       'userTag' => 'require|isNotEmpty'
   ];

   protected  $message = [
       'userTag' => 'Token is not available without a tag.'
   ];

   protected function isNotEmpty($value, $rule = '', $data = '', $field = '')
   {
       return parent::isNotEmpty($value, $rule, $data, $field);
   }


}