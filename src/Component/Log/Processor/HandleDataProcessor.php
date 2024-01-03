<?php
namespace Giles\Library\Component\Log\Processor;

use Monolog\Processor\ProcessorInterface;

/**
 * Class HandleDataProcessor
 *
 * @package Bhc\Library\Component\Log\Processor
 *
 * @author  Giles <giles.wang@aliyun.com|giles.wang@qq.com>
 * @date    2019/11/29 17:31
 */
class HandleDataProcessor implements ProcessorInterface
{
    /** @var array 不需要的字段 */
    protected $removes = [
        'channel'
    ];

    /**
     * @param array $record
     *
     * @return array
     * @author  Giles <giles.wang@aliyun.com|giles.wang@qq.com>
     * @date    2019/11/29 17:31
     */
    public function __invoke(array $record)
    {
        foreach ($record as $key => $item) {
            //移出不需要的字段
            if (in_array($key, $this->removes)) {
                unset($record[$key]);
            }
            //时间格式化
            if ($item instanceof \DateTime) {
                $record[$key] = $item->format('Y-m-d H:i:s.u');
            }
        }
        //json 格式日志排序
        if (strcasecmp(Config::get('format'), 'json') === 0) {
            $jsonFormat = [];
            foreach (Config::getFormat() as $value) {
                isset($record[$value]) &&  $jsonFormat[$value] = $record[$value];
            }
            return $jsonFormat;
        }

        return $record;
    }
}
