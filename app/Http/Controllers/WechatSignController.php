<?php

namespace App\Http\Controllers;

use App\Entity\Result;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class WechatSignController extends Controller
{
  private $APP_ID = "wx291bb9db109f0d63";
  private $APP_SECRET = "d4624c36b6795d1d99dcf0547af5443d";

  var $client;

  public function __construct()
  {
    $this->client = new Client();
  }


  /**
   * <a href="http://mp.weixin.qq.com/wiki/7/aaa137b55fb2e0456bf8dd9148dd613f.html#.E9.99.84.E5.BD.951-JS-SDK.E4.BD.BF.E7.94.A8.E6.9D.83.E9.99.90.E7.AD.BE.E5.90.8D.E7.AE.97.E6.B3.95">
   * 附录1-JS-SDK使用权限签名算法
   * </a>
   */
  public function jssdkSign(Request $request)
  {
    $signUrl = $request->input("url");
    $accessToken = $this->getAccessToken($this->APP_ID, $this->APP_SECRET);

    $jsapiTicket = \Cache::get("jsapi_ticket", null);
    if (!$jsapiTicket) {
      $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=" . $accessToken->access_token . "&type=jsapi";
      $res = $this->client->request("GET", $url);
      if ($res->getStatusCode() != "200") {
        return Result::error(1, "jssdk 签名失败");
      }
      /**
       * {
       * "errcode":0,
       * "errmsg":"ok",
       * "ticket":"bxLdikRXVbTPdHSM05e5u5sUoXNKd8-41ZO3MhKoyN5OfkWITDGgnr2fwJ0m9E8NYzWKVZvdVtaUgWvsdshFKA",
       * "expires_in":7200
       * }
       */
      $jsapiTicket = json_decode($res->getBody()->getContents());
      \Cache::put("jsapi_ticket", $jsapiTicket, $jsapiTicket->expires_in / 60);
    }

    $nonceStr = $this->getRandChar(16);
    $timestamp = time();

    $signString =
      "jsapi_ticket=" . $jsapiTicket->ticket
      . "&noncestr=" . $nonceStr
      . "&timestamp=" . $timestamp
      . "&url=" . $signUrl;

    return Result::successSingle([
      "appId" => $this->APP_ID,
      "nonceStr" => $nonceStr,
      "timestamp" => $timestamp,
      "signature" => sha1($signString)
    ]);
  }

  /**
   * <a href="http://mp.weixin.qq.com/wiki/15/54ce45d8d30b6bf6758f68d2e95bc627.html">
   * 获取access token
   * </a>
   * @return {"access_token":"ACCESS_TOKEN","expires_in":7200}
   */
  public function getAccessToken(string $appid, string $appSecret)
  {
    $key = "access_token_" . $appid;
    $accessToken = \Cache::get($key, null);
    if ($accessToken) {
      return $accessToken;
    }

    $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appid . "&secret=" . $appSecret;
    $res = $this->client->request('GET', $url);
    if ($res->getStatusCode() == 200) {
      $accessToken = json_decode($res->getBody()->getContents());

      \Cache::put($key, $accessToken, $accessToken->expires_in / 60);
      return $accessToken;
    }

    return null;
  }

  //生成随机字符串
  function getRandChar($length)
  {
    $str = null;
    $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz1234567890";
    $max = strlen($strPol) - 1;

    for ($i = 0; $i < $length; $i++) {
      $str .= $strPol[rand(0, $max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
    }

    return $str;
  }
}
