<?php
namespace Giles\Library\Component\Signature;

/**
 *
 * 加密 解密
 * @package Giles\Library\Component\Signature
 *
 * @author Giles <giles.wang@aliyun.com|giles.wang@qq.com>
 * @date 2024/2/23 17:26
 */
class Encrypt
{
    /** @var string 加密算法 */
    protected $cipher = 'des-ede3';

    /**
     * 加密
     *
     * @param string $input
     * @param string $key
     * @return false|string
     * @author Giles <giles.wang@aliyun.com|giles.wang@qq.com>
     * @date 2024/2/23 17:28
     */
    function encrypt(string $input, string $key='fcbeu51p')
    {
        return openssl_encrypt($input, $this->cipher, $key);
    }

    /**
     * 解密
     *
     * @param string $encrypted
     * @param string $key
     * @return false|string
     * @author Giles <giles.wang@aliyun.com|giles.wang@qq.com>
     * @date 2024/2/23 17:28
     */
    function decrypt(string $encrypted, string $key='fcbeu51p')
    {
        return openssl_decrypt($encrypted,$this->cipher,$key);
    }
}