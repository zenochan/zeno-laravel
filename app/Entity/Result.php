<?php
/**
 * Created by PhpStorm.
 * User: zenochan
 * Date: 2016/12/12
 * Time: 上午10:55
 */

namespace App\Entity;


class Result
{
  public $data = [];
  public $total;
  public $err_code;
  public $err_msg;

  public static function successSingle($data, $total = 1)
  {
    return self::success([$data], $total);
  }

  public static function success($data, $total = 0)
  {
    $r = new Result();
    if ($data != null) {
      $r->data = $data;
    }
    $r->err_code = 0;
    $r->total = $total;
    $r->err_msg = "success";

    return $r;
  }

  /**
   * @param $err_code
   * @param $err_msg
   * @return Result
   */
  public static function error($err_code, $err_msg, $data=null)
  {
    $r = new Result();
    $r->err_code = $err_code;
    $r->err_msg = $err_msg;
    $r->data = $data;

    return $r;
  }

  public function __toString()
  {
    return json_encode($this, JSON_UNESCAPED_UNICODE);
  }

}