<?php

namespace Giles\Library\Component\Log\Formatter;

use Monolog\Formatter\MongoDBFormatter as MonoMongoDBFormatter;

/**
 *
 * MongoDb 数据格式加工
 * @package Giles\Library\Component\Log\Formatter
 *
 * @author Giles <giles.wang@aliyun.com|giles.wang@qq.com>
 * @date 2024/2/19 15:43
 */
class MongoFormatter extends MonoMongoDBFormatter
{
    public function format(array $record) :array
    {
        unset($record['level']);
        unset($record['extra']);

        return parent::format($record);
    }
}