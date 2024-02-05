<?php
namespace Giles\Library\Component\Log\Manager;

use Giles\Library\Component\Log\Config;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class LogManager implements LoggerInterface
{
    /** @var array 日志实例 */
    protected static $logger = [];

    /** @var string 当前通道名称 */
    protected $logChannel;

    /** @var LogManager|null 当前实例 */
    protected static $instance = null;

    /**
     * 初始化日志组件 初始化配置文件&&初始化链路
     *
     * LogManager constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        Config::set($config);

        if (self::$instance === null) {
            self::$instance = $this;
        }
    }

    /**
     * 获取当前实例
     *
     * @return LogManager|static|null
     * @author Giles <giles.wang@aliyun.com|giles.wang@qq.com>
     * @date 2024/2/5 15:52
     */
    public static function getInstance(): ?LogManager
    {
        return self::$instance;
    }

    protected function driver($driver = null)
    {
        if (isset(self::$logger[$this->logChannel()])) {
            return self::$logger[$this->logChannel()];
        }
        $driver = $driver ?? Config::get('driver');
        switch ($driver) {
            case 'daily':
                return $this->dailyDriver();
                break;
            case 'single':
                return $this->singleDriver();
                break;
            case 'mongodb':
                return $this->mongoDbDriver();
            default:
                return $this->singleDriver();
        }
    }

    protected function mongoDbDriver()
    {
        return true;
    }

    /**
     * 单文件写入方式 实例
     *
     * @return mixed
     * @author Giles <giles.wang@aliyun.com|giles.wang@qq.com>
     * @date 2024/2/5 15:56
     */
    protected function singleDriver()
    {
        $logger = new Write(
            new Logger($this->logChannel())
        );
        $logger->module = $this->module;
        $logger->useSingle($this->logChannel());
        $this->delChannel();
        return $logger->getLogger();
    }

    /**
     * 多文件&&日期切割方式 实例
     *
     * @return mixed
     * @author Giles <giles.wang@aliyun.com|giles.wang@qq.com>
     * @date 2024/2/5 15:56
     */
    protected function dailyDriver()
    {
        $logger = new Write(
            new Logger($this->logChannel())
        );
        $logger->module = $this->module;
        $logger->useDaily($this->logChannel());
        $this->delChannel();
        return $logger->getLogger();
    }

    protected function logChannel()
    {
        if (!empty($this->logChannel)) {
            return $this->logChannel;
        }
        if (strnatcmp(PHP_SAPI, 'cli') === 0) {
            $this->module = 'CLI';
        }

        return Config::$defaultLog;
    }


    public function emergency($message, array $context = array())
    {
        $this->driver()->emergency($message, $context);
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
     * @throws \Exception
     */
    public function alert($message, array $context = array())
    {
        $this->driver()->alert($message, $context);
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
     * @throws \Exception
     */
    public function critical($message, array $context = array())
    {
        $this->driver()->critical($message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     * @throws \Exception
     */
    public function error($message, array $context = array())
    {
        $this->driver()->error($message, $context);
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
     * @throws \Exception
     */
    public function warning($message, array $context = array())
    {
        $this->driver()->warning($message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     * @throws \Exception
     */
    public function notice($message, array $context = array())
    {
        $this->driver()->notice($message, $context);
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
     * @throws \Exception
     */
    public function info($message, array $context = array())
    {
        $this->driver()->info($message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     * @throws \Exception
     */
    public function debug($message, array $context = array())
    {
        $this->driver()->debug($message, $context);
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
     * @throws \Exception
     */
    public function log($level, $message, array $context = array())
    {
        $this->driver()->log($level, $message, $context);
    }

    /**
     * Dynamically call the default driver instance.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     * @throws \Exception
     */
    public function __call($method, $parameters)
    {
        return $this->driver()->$method(...$parameters);
    }
}