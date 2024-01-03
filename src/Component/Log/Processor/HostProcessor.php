<?php
namespace Giles\Library\Component\Log\Processor;

use Monolog\Processor\ProcessorInterface;

/**
 * Class HostProcessor
 *
 * @package Bhc\Library\Component\Log\Processor
 *
 * @author  Giles <giles.wang@aliyun.com|giles.wang@qq.com>
 * @date    2019/11/29 17:31
 */
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
    public function __invoke(array $record)
    {
        return $record;
    }
}
