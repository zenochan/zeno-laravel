<?php
/**
 * Created by PhpStorm.
 * User: zenochan
 * Date: 2017/1/11
 * Time: 上午11:01
 */

namespace app\Http\Controllers;


use GuzzleHttp\Client;

/**
 * GuzzleClient 封装
 * @package app\Http\Controllers
 */
class GuzzleClient
{
  static $client;

  static function get($url)
  {
    $res = self::client()->request("GET", $url);
    if ($res->getStatusCode() == "200") {
      $r = json_decode($res->getBody()->getContents());
    } else {
      $r = $res->getStatusCode();
    }
    return $r;
  }

  static function post($url)
  {

  }

  private static function client(): Client
  {
    if (self::$client == null) {
      self::$client = new Client();
    }

    return self::$client;
  }
}