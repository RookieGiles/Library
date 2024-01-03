<?php
namespace Giles\Library\Component\Log\Processor;

use Monolog\Processor\ProcessorInterface;


/**
 *
 * 处理上下文处理上下文
 * @package Giles\Library\Component\Log\Processor
 *
 * @author Giles <giles.wang@aliyun.com|giles.wang@qq.com>
 * @date 2024/1/3 14:09
 */
class ContextProcessor implements ProcessorInterface
{
    public function __invoke(array $record): array
    {
        if (!empty($record['context']) && is_array($record['context'])) {
            $record['contextFmt'] = '###start###' . json_encode($record['context'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '###end###';
        }
        
        return $record;
    }
}
