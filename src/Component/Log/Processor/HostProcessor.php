<?php
namespace Giles\Library\Component\Log\Processor;

use Monolog\Processor\ProcessorInterface;

class HostProcessor implements ProcessorInterface
{
    /**
     * 增加host
     *
     * @param array $record
     *
     * @return array
     * @author  Giles <giles.wang@aliyun.com|giles.wang@qq.com>
     * @date    2019/11/29 17:31
     */
    public function __invoke(array $record): array
    {
        $record['host'] =  gethostbyname(gethostname()) ?? '127.0.0.1';

        return $record;
    }
}