<?php
/**
 * Created by PhpStorm.
 * User: zenochan
 * Date: 2017/5/11
 * Time: 上午9:52
 */

namespace app\Libs\WechatEnterprise;


/**
 * PKCS7Encoder class
 *
 * 提供基于PKCS7算法的加解密接口.
 */
class PKCS7Encoder
{
  public static $block_size = 32;

  /**
   * 对需要加密的明文进行填充补位
   * @param String $text 需要进行填充补位操作的明文
   * @return String 补齐明文字符串
   */
  function encode($text)
  {
    $block_size = PKCS7Encoder::$block_size;
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
   * @param decrypted String 解密后的明文
   * @return String  删除填充补位后的明文
   */
  function decode($text)
  {

    $pad = ord(substr($text, -1));
    if ($pad < 1 || $pad > PKCS7Encoder::$block_size) {
      $pad = 0;
    }
    return substr($text, 0, (strlen($text) - $pad));
  }

}
