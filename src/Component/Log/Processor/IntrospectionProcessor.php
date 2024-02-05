<?php
namespace Giles\Library\Component\Log\Processor;

use Giles\Library\Component\Log\Config;
use Monolog\Logger;
use Monolog\Processor\ProcessorInterface;

class IntrospectionProcessor implements ProcessorInterface
{
    private $level;

    private $skipClassesPartials;

    public function __construct(
        $level = Logger::DEBUG,
        array $skipClassesPartials = ['Psr\\', 'Bhc\\', 'Monolog\\', 'Illuminate\\', 'illuminate\\']
    ) {
        $this->level = Logger::toMonologLevel($level);
        $this->skipClassesPartials = $skipClassesPartials;
    }

    /**
     * @param  array $record
     * @return array
     */
    public function __invoke(array $record)
    {
        // return if the level is not high enough
        if ($record['level'] < $this->level) {
            return $record;
        }
        // 自定义写入文件和行数
        $fileLine = Config::getFileLine();
        if (!empty($fileLine)) {
            $record['file'] = $this->fileSplit($fileLine['file']);
            $record['line'] = $fileLine['line'];

            return $record;
        }

        $trace = debug_backtrace();
        // skip first since it's always the current method
        array_shift($trace);
        // the call_user_func call is also skipped
        array_shift($trace);

        $i = 0;
        while (isset($trace[$i]['class'])) {
            foreach ($this->skipClassesPartials as $part) {
                if (strpos($trace[$i]['class'], $part) !== false) {
                    $i++;
                    continue 2;
                }
            }
            break;
        }

        $file = null;
        $line = null;

        if (isset($trace[$i -1 ]['file'])) {
            $file = $trace[$i - 1]['file'];
            $line = $trace[$i - 1]['line'];
        } elseif (isset($trace[$i]['file'])) {
            $file = $trace[$i]['file'];
            $line = $trace[$i]['line'];
        }

        $action = null;
        if (isset($trace[$i]['class'])) {
            $action = $trace[$i]['class'];
            if (isset($trace[$i]['class'])) {
                $action = $action . '->' . $trace[$i]['function'];
            }
        }

        $record['file'] = $this->fileSplit($file);
        $record['line'] = $line;
        $record['action'] = $action;

        return $record;
    }

    /**
     * 切割写入文件路径
     *
     * @param $file
     *
     * @return string
     * @author Giles <giles.wang@aliyun.com|giles.wang@qq.com>
     * @date   2019/12/6 17:35
     */
    private function fileSplit($file): string
    {
        $split = explode('/', $file);
        for($i=0; $i<4; $i++) {
            array_shift($split);
        }

        return implode('/', $split);
    }
}