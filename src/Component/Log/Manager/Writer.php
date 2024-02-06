<?php
namespace Giles\Library\Component\Log\Manager;

use Giles\Library\Component\Log\Config;
use Giles\Library\Component\Log\Processor\HandleDataProcessor;
use Giles\Library\Component\Log\Processor\HostProcessor;
use Giles\Library\Component\Log\Processor\IntrospectionProcessor;
use Giles\Library\Component\Log\Processor\TraceProcessor;
use InvalidArgumentException;
use MongoClient;
use Monolog\Handler\BufferHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\MongoDBHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class Writer implements LoggerInterface
{
    /** @var LoggerInterface logger */
    protected $logger;
    public $module = null;

    /** @var array Monolog 日志级别对应的数值 */
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
     * 构造函数 初始化实例
     *
     * Write constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * 获取当前实例
     *
     * @return LoggerInterface
     * @author  Giles <giles.wang@aliyun.com|giles.wang@qq.com>
     * @date    2019/11/29 17:21
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * 单文件写入操作
     *
     * @param string $logChannel
     *
     * @return void
     * @author  Giles <giles.wang@aliyun.com|giles.wang@qq.com>
     * @date    2019/11/29 17:22
     */
    public function useSingle(string $logChannel)
    {
        $level = Config::isDebug()  ? 'debug' : Config::lowLevel();
        $file = Config::get('path') . '/'. $logChannel .'.log';
        $handler = new StreamHandler($file, $this->level($level));
        $handler->setFormatter(SelectFormat::get(Config::get('format')));
        $this->setFormatter($handler);
    }

    /**
     * @throws \MongoConnectionException
     */
    public function useMongoDb(string $logChannel)
    {
        $mongoClient = new MongoClient(Config::getMongoConf());

        $handler = new MongoDBHandler($mongoClient, 'scs_log', '');

        $handler->setFormatter(SelectFormat::get(Config::get('format')));
        $this->setFormatter($handler);
    }

    /**
     * 分文件写入日志 并且按天拆分目录
     *
     * @param string $logChannel
     *
     * @return void
     * @author  Giles <giles.wang@aliyun.com|giles.wang@qq.com>
     * @date    2019/11/29 17:23
     */
    public function useDaily(string $logChannel)
    {
        $file = Config::get('path') . '/'. $logChannel .'.log';
        $level = Config::isDebug()  ? 'debug' : Config::lowLevel();
        $handler = new RotatingFileHandler($file, 0, $this->level($level));
        $handler->setFilenameFormat('{date}.{filename}', 'Ymd');
        $handler->setFormatter(SelectFormat::get(Config::get('format')));

        $this->setFormatter($handler, Config::get('format'));
    }

    /**
     * 写入日志格式化操作
     *
     * @param HandlerInterface $handler
     *
     * @return void
     * @author  Giles <giles.wang@aliyun.com|giles.wang@qq.com>
     * @date    2019/11/29 17:22
     */
    protected function setFormatter(HandlerInterface $handler)
    {
        //验证是否开启Buffer
        $handler = Config::isBuffer() ? new BufferHandler($handler) : $handler;
        $this->logger->pushHandler($handler);

        $this->logger->pushProcessor(new HandleDataProcessor());
        $this->logger->pushProcessor(new HostProcessor());
        $this->logger->pushProcessor(new TraceProcessor());
        $this->logger->pushProcessor(new IntrospectionProcessor(Config::get('level')));
    }

    /**
     * 获取日志级别对应的数值
     *
     * @param string $level
     *
     * @return mixed
     * @author  Giles <giles.wang@aliyun.com|giles.wang@qq.com>
     * @date    2019/11/28 14:59
     */
    protected function level(string $level)
    {
        $level = $level ?? 'debug';

        if (isset($this->levels[$level])) {
            return $this->levels[$level];
        }

        throw new InvalidArgumentException('Invalid log level.');
    }

    /**
     * 写入日志
     *
     * @param $level
     * @param $message
     * @param $context
     *
     * @return void
     * @author  Giles <giles.wang@aliyun.com|giles.wang@qq.com>
     * @date    2019/11/29 17:25
     */
    protected function writeLog($level, $message, $context)
    {
        $this->logger->{$level}($message, $context);
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function emergency($message, array $context = array())
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function alert($message, array $context = array())
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function critical($message, array $context = array())
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function error($message, array $context = array())
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function warning($message, array $context = array())
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function notice($message, array $context = array())
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function info($message, array $context = array())
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function debug($message, array $context = array())
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     *
     * @return void
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    public function log($level, $message, array $context = array())
    {
        $this->writeLog($level, $message, $context);
    }

    /**
     * __call 魔术方法
     *
     * @param $method
     * @param $parameters
     *
     * @return mixed
     * @author  Giles <giles.wang@aliyun.com|giles.wang@qq.com>
     * @date    2019/11/29 17:26
     */
    public function __call($method, $parameters)
    {
        return $this->logger->{$method}(...$parameters);
    }
}