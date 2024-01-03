<?php
namespace Giles\Library\Component\Log;

use Giles\Library\Component\Log\Formatter\JsonFormatter;
use Giles\Library\Component\Log\Processor\ContextProcessor;
use Giles\Library\Component\Log\Processor\HostProcessor;
use Giles\Library\Component\Log\Processor\UidProcessor;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Handler\HandlerInterface;

class Writer
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var array
     */
    protected $levels = [
        'debug'     => Logger::DEBUG,
        'info'      => Logger::INFO,
        'notice'    => Logger::NOTICE,
        'warning'   => Logger::WARNING,
        'error'     => Logger::ERROR,
        'critical'  => Logger::CRITICAL,
        'alert'     => Logger::ALERT,
        'emergency' => Logger::EMERGENCY,
    ];

    /**
     * Create a new log writer instance.
     *
     * @param  Logger $monolog
     * @return void
     */
    public function __construct(Logger $monolog)
    {
        $this->logger = $monolog;
    }

    /**
     * 单个文件写入
     *
     * @param string $path
     * @param int    $level
     * @author Giles <giles.wang@aliyun.com|giles.wang@qq.com>
     * @date 2024/1/3 13:51
     */
    public function useFiles(string $path, int $level = 100)
    {
        $handler = new StreamHandler($path, $level);
        $this->setFormatter($handler);
    }

    /**
     * 使用dateFile 形式来记录日志
     *
     * @param string $path
     * @param int $days
     * @param int $level
     * @author Giles <giles.wang@aliyun.com|giles.wang@qq.com>
     * @date 2024/1/3 13:52
     */
    public function useDateFiles(string $path, int $days = 0, int $level = 100)
    {
        $handler = new RotatingFileHandler($path, $days, $level);
        $handler->setFilenameFormat('{date}.{filename}', 'Ymd');
        $this->setFormatter($handler);
    }

    /**
     * 设置日志写入格式
     *
     * @param HandlerInterface $handler
     * @author Giles <giles.wang@aliyun.com|giles.wang@qq.com>
     * @date 2024/1/3 13:52
     */
    protected function setFormatter(HandlerInterface $handler)
    {
        $handler->setFormatter(new JsonFormatter());
        $this->logger->pushHandler($handler);
        // 增加当前脚本的文件名和类名等信息
        $this->logger->pushProcessor(new IntrospectionProcessor(Logger::DEBUG, array('Illuminate\\')));
        // 把机器IP添加到日志中
        $this->logger->pushProcessor(new HostProcessor());
        // 日志UUID
        $this->logger->pushProcessor(new UidProcessor(24));
        // 格式化数据添加到日志中
        $this->logger->pushProcessor(new ContextProcessor());
    }

    /**
     * getLogger
     *
     * @return Logger
     * @author Giles <giles.wang@aliyun.com|giles.wang@qq.com>
     * @date 2024/1/3 14:17
     */
    public function getLogger()
    {
        return $this->logger;
    }
}