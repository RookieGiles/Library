<?php
namespace Giles\Library;
/**
 *
 * 生成Uuid
 * @package Giles\Library
 *
 * @author Giles <giles.wang@aliyun.com|giles.wang@qq.com>
 * @date 2024/2/23 16:47
 */
class Uuid
{
    /**
     * 生成Uuid
     *
     * @param int $length
     * @return string
     * @author Giles <giles.wang@aliyun.com|giles.wang@qq.com>
     * @date 2024/2/23 16:48
     */
    private static function generateUuid(int $length):string
    {
        $characters = '0123456789abcdef';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    /**
     * 生成UUid
     *
     * @param int $length
     * @return string
     * @author Giles <giles.wang@aliyun.com|giles.wang@qq.com>
     * @date 2024/2/23 16:56
     */
    public static function generate(int $length = 16):string
    {
        switch ($length) {
            case 8:
                return self::generateUuid(8);
            case 32:
                return self::generateUuid(32);
            case 64:
                return self::generateUuid(64);
            case 128:
                return self::generateUuid(128);
            default:
                return self::generateUuid(16);
        }
    }
}