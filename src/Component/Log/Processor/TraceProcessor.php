<?php
namespace Giles\Library\Component\Log\Processor;

use Giles\Library\Snowflake;
use Monolog\Processor\ProcessorInterface;

class TraceProcessor implements ProcessorInterface
{
    /** @var string */
    private static $traceId;

    /**
     * 增加TraceId
     *
     * @param array $record
     *
     * @return array
     * @author  Giles <giles.wang@aliyun.com|giles.wang@qq.com>
     * @date    2019/11/29 17:31
     */
    public function __invoke(array $record): array
    {

        self::$traceId = self::$traceId ?? Snowflake::uniqueId();

        $record['traceId'] = self::$traceId;
        $record['uri']     = $_SERVER['REQUEST_URI'] ?? null;
        return $record;
    }
}