<?php

namespace App\Http\Controllers;

use App\Libs\WechatEnterprise\WxBizMsgCrypt;
use DOMDocument;
use Illuminate\Http\Request;

class WechatEnterpriseController extends Controller
{
  private $encodingAesKey = "FAJxyBCC3She3hsRpCEAd6wFVEacqpnUWTUU9fOwTye";
  private $token = "zenochan";
  private $corpId = "wx099344e1db5925d1";
  private $wxcpt;

  /**
   * WechatEnterpriseController constructor.
   */
  public function __construct()
  {
    $this->wxcpt = new WXBizMsgCrypt($this->token, $this->encodingAesKey, $this->corpId);
  }

  public function callback(Request $request)
  {
    $msg_signature = $request->query("msg_signature");
    $timestamp = $request->query("timestamp");
    $nonce = $request->query("nonce");
    $echostr = $request->query("echostr");

    $result = "";
    $errCode = $this->wxcpt->VerifyURL($msg_signature, $timestamp, $nonce, $echostr, $result);
    if ($errCode == 0) {
      return $result;
    } else {
      $error = "ERR: " . $errCode;
      return $error;
    }
  }

  /**
   * 企业收到post请求之后应该
   * 1.解析出url上的参数，包括消息体签名(msg_signature)，时间戳(timestamp)以及随机数字串(nonce)
   * 2.验证消息体签名的正确性。
   * 3.将post请求的数据进行xml解析，并将<Encrypt>标签的内容进行解密，解密出来的明文即是用户回复消息的明文，明文格式请参考官方文档
   * 第2，3步可以用公众平台提供的库函数DecryptMsg来实现。
   */
  public function receiveMsg(Request $request)
  {
    $msg_signature = $request->query("msg_signature");
    $timestamp = $request->query("timestamp");
    $nonce = $request->query("nonce");
    $msg = $request->getContent();
    $errCode = $this->wxcpt->DecryptMsg($msg_signature, $timestamp, $nonce, $msg, $msg);
    if ($errCode == 0) {
      // 解密成功，sMsg即为xml格式的明文
      // TODO: 对明文的处理
      // For example:
      $xml = new DOMDocument();
      $xml->loadXML($msg);
      \Log::info("Content:" . $msg);
      $content = $xml->getElementsByTagName('Content')->item(0)->nodeValue;
      $toUserName = $xml->getElementsByTagName("FromUserName")->item(0)->nodeValue;

      // 被动回复消息
      $time = time();
      $resp = "
        <xml>
            <ToUserName><![CDATA[{$toUserName}]]></ToUserName>
            <FromUserName><![CDATA[{$this->corpId}]]></FromUserName> 
            <CreateTime>{$time}</CreateTime>
            <MsgType><![CDATA[text]]></MsgType>
            <Content><![CDATA[{$content}]]></Content>
        </xml>";
      $sEncryptMsg = ""; //xml格式的密文
      $errCode = $this->wxcpt->EncryptMsg($resp, $time, $nonce, $sEncryptMsg);
      if ($errCode == 0) {
        \Log::info("resp:" . $sEncryptMsg);
        return $sEncryptMsg;
      } else {
        \Log::error("ERR: " . $errCode);
      }
    } else {
      \Log::error("ERR: " . $errCode);
    }

    return "";

  }

  public function replay($toUserName)
  {
  }
}

