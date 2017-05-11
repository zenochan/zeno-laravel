<?php
namespace App\Wechat;


/**
 * 对微信小程序用户加密数据的解密示例代码.
 *
 * @copyright Copyright (c) 1998-2014 Tencent Inc.
 */
class WXBizDataCrypt
{
  private $appid;
  private $sessionKey;

  /**
   * 构造函数
   * @param $sessionKey string 用户在小程序登录后获取的会话密钥
   * @param $appid string 小程序的appid
   */
  public function __construct($appid, $sessionKey)
  {
    $this->sessionKey = $sessionKey;
    $this->appid = $appid;
  }


  /**
   * 检验数据的真实性，并且获取解密后的明文.
   * @param $encryptedData string 加密的用户数据
   * @param $iv string 与用户数据一同返回的初始向量
   * @param $data string 解密后的原文
   *
   * @return int 0:成功，其他：失败返回对应的错误码
   */
  public function decryptData($encryptedData, $iv, &$data): int
  {
    if (strlen($this->sessionKey) != 24) {
      return ErrorCode::$IllegalAesKey;
    }
    $aesKey = base64_decode($this->sessionKey);


    if (strlen($iv) != 24) {
      return ErrorCode::$IllegalIv;
    }
    $aesIV = base64_decode($iv);

    $aesCipher = base64_decode($encryptedData);

    $result = self::decryptAes128cbc($aesCipher, $aesKey, $aesIV);

    if ($result[0] != 0) {
      return $result[0];
    }

    $dataObj = json_decode($result[1]);
    if ($dataObj == NULL) {
      return ErrorCode::$IllegalBuffer;
    }
    if ($dataObj->watermark->appid != $this->appid) {
      return ErrorCode::$IllegalBuffer;
    }
    $data = $result[1];
    return ErrorCode::$OK;
  }


  /**
   * 对密文进行解密
   * @param string $aesCipher 需要解密的密文
   * @param string $aesIV 解密的初始向量
   * @return string|array 解密得到的明文
   */
  private static function decryptAes128cbc($aesCipher, $key, $aesIV)
  {
    try {
      // php 7.1 + 不鼓励使用 mcrypt,主要是我本地没有这个模块 - -，顺便找了这个解决方案
      //    try {
      //
      //
      //      $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
      //
      //      mcrypt_generic_init($module, $this->key, $aesIV);
      //
      //      //解密
      //      $decrypted = mdecrypt_generic($module, $aesCipher);
      //      mcrypt_generic_deinit($module);
      //      mcrypt_module_close($module);
      //    } catch (Exception $e) {
      //      return array(ErrorCode::$IllegalBuffer, null);
      //    }
      $decrypted = openssl_decrypt($aesCipher, "aes-128-cbc", $key, OPENSSL_RAW_DATA, $aesIV);
      //去除补位字符
      $result = self::pkcs7_decode($decrypted);
    } catch (\Exception $e) {
      return array(ErrorCode::$IllegalBuffer, null);
    }
    return array(0, $result);
  }


  /**
   * 对需要加密的明文进行填充补位
   * @param $text string 需要进行填充补位操作的明文
   * @return string 补齐明文字符串
   */
  static function pkcs7_encode(string $text): string
  {
    $block_size = 16;
    $text_length = strlen($text);
    //计算需要填充的位数
    $amount_to_pad = $block_size - ($text_length % $block_size);
    if ($amount_to_pad == 0) {
      $amount_to_pad = $block_size;
    }
    //获得补位所用的字符
    $pad_chr = chr($amount_to_pad);
    $tmp = "";
    for ($index = 0; $index < $amount_to_pad; $index++) {
      $tmp .= $pad_chr;
    }
    return $text . $tmp;
  }

  /**
   * 对解密后的明文进行补位删除
   * @param decrypted string 解密后的明文
   * @return string 删除填充补位后的明文
   */
  static function pkcs7_decode($text): string
  {

    $pad = ord(substr($text, -1));
    if ($pad < 1 || $pad > 32) {
      $pad = 0;
    }
    return substr($text, 0, (strlen($text) - $pad));
  }

}


