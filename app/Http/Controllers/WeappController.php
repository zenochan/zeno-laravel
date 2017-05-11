<?php

namespace App\Http\Controllers;

use App\Entity\Result;
use App\Wechat\WXBizDataCrypt;
use Illuminate\Http\Request;

/**
 * 微信小程序相关业务
 * Class WeappController
 * @package App\Http\Controllers
 */
class WeappController extends Controller
{
  var $appId = "wx4f3447b4bfcbc4eb";
  var $appSecret = "78b1a8ac30218e5e2c80b36ea369b6da";

  //
  function login(Request $request)
  {
    $this->validate($request, [
      "code" => "required|size:32",
      "encryptedData" => "required",
      "iv" => "required|size:24"
    ]);

    $iv = $request->input("iv");
    $encryptedData = $request->input("encryptedData");

    $session = $this->getSessionKey($this->appId, $this->appSecret, $request->input("code"));
    $pc = new WXBizDataCrypt($this->appId, $session->session_key);
    $errCode = $pc->decryptData($encryptedData, $iv, $data);

    if ($errCode == 0) {
      return Result::success([json_decode($data), $session]);
    } else {
      return Result::error($errCode, "错误了");
    }
  }

  /**
   * @param $appId
   * @param $appSecret
   * @param $code
   * @return {openid: string, session_key: string, expires_in: int}
   */
  function getSessionKey($appId, $appSecret, $code)
  {
    $url = "https://api.weixin.qq.com/sns/jscode2session"
      . "?appid=" . $appId
      . "&secret=" . $appSecret
      . "&js_code=" . $code
      . "&grant_type=authorization_code";

    return GuzzleClient::get($url);
  }
}
