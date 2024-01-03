<?php
namespace Giles\Library\Component\Log\Processor;

/**
 *
 * 日志UUID
 * @package Giles\Library\Component\Log\Processor
 *
 * @author Giles <giles.wang@aliyun.com|giles.wang@qq.com>
 * @date 2024/1/3 14:06
 */
class UidProcessor
{
    public function __construct($length = 7)
    {
        if (!defined('LOG_UUID')) {
            if (!is_int($length) || $length > 32 || $length < 1) {
                throw new \InvalidArgumentException('The uid length must be an integer between 1 and 32');
            }

            define('LOG_UUID', substr(hash('md5', uniqid('', true)), 0, $length));
        }

    }

    /**
     * 获取频道名称
     *
     * @param array $record
     * @return array
     * @author Giles <giles.wang@aliyun.com|giles.wang@qq.com>
     * @date 2024/1/3 14:07
     */
    public function __invoke(array $record): array
    {

        $record['extra']['uid'] = LOG_UUID;

        return $record;
    }
}