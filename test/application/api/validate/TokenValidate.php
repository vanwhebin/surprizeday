<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/5/22
 * Time: 16:13
 */

namespace app\api\validate;


class TokenValidate extends BaseValidate
{
   protected  $rule = [
       'userID' => 'require|isNotEmpty'
   ];

   protected  $message = [
       'userID' => 'Token is not available without userID.'
   ];

   protected function isNotEmpty($value, $rule = '', $data = '', $field = '')
   {
       return parent::isNotEmpty($value, $rule, $data, $field);
   }


}