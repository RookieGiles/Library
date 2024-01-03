<?php
namespace Giles\Library\Component\Log;

use Illuminate\Log\Logger;
use Illuminate\Log\LogManager;
use Illuminate\Support\HigherOrderTapProxy;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Throwable;
use Monolog\Logger as Monolog;

/**
 *
 * 基于 illuminate/log 扩展的日志组件
 * @package Giles\Library\Component\Log
 *
 * @author Giles <giles.wang@aliyun.com|giles.wang@qq.com>
 * @date 2024/1/3 11:58
 */
class LoggerManager extends LogManager
{
    const PHP_BUSINESS_LOGIC = 'logic';

    /** @var array 日志驱动句柄 */
    protected $logger = [];
    /** @var string 日志名，可用于切分文件存储 */
    protected $logChannel;

    /**
     * getLogger 设置Logger 设置日志名
     *
     * @param $logger
     * @return $this|mixed
     * @author Giles <giles.wang@aliyun.com|giles.wang@qq.com>
     * @date 2024/1/3 11:59
     */
    public function getLogger($logger = null)
    {
        if(empty($logger)) {
            return $this;
        }

        return $this->logger[$logger] ?? $this->createLogger($logger);
    }

    /**
     * createLogger 创建一个日志句柄
     *
     * @param $name
     * @return HigherOrderTapProxy|mixed
     * @author Giles <giles.wang@aliyun.com|giles.wang@qq.com>
     * @date 2024/1/3 12:00
     */
    private function createLogger($name = null)
    {
        $this->logChannel =  $name;
        return tap($this->driver(), function ($logger) {
            $this->logChannel = null;
        });
    }

    /**
     * get 获取日志写入实例
     *
     * @param $name
     * @return HigherOrderTapProxy|mixed|LoggerInterface
     * @author Giles <giles.wang@aliyun.com|giles.wang@qq.com>
     * @date 2024/1/3 13:34
     */
    protected function get($name)
    {
        try {
            return $this->logger[$this->logChannel()] ?? with($this->resolve($name), function ($logger) use ($name) {
                return $this->logger[$this->logChannel()] =
                    $this->tap($name, new Logger($logger, $this->app['events']));
            });
        } catch (Throwable $e) {
            return tap($this->createEmergencyLogger(), function ($logger) use ($e) {
                $logger->emergency('Unable to create configured logger. Using emergency logger.', [
                    'exception' => $e,
                ]);
            });
        }
    }

    protected function createDailyDriver(array $config)
    {
        $logger = new Writer(
            new Monolog($this->logChannel())
        );
        $logger->useFiles(
            $this->getLogPath($config) . '/' . $this->logChannel() . '.log',
            $this->level($config)
        );

        return $logger->getLogger();
    }

    /**
     * getLogChannel 获取当前日志名
     *
     * @return string
     * @author Giles <giles.wang@aliyun.com|giles.wang@qq.com>
     * @date 2024/1/3 13:35
     */
    private function logChannel() :string
    {
        if (!empty($this->logChannel)) {
            return $this->logChannel;
        }

        //cli模式默认文件名称为 cli+命令
        if ($this->app->runningInConsole()) {
            return 'cli.' . str_replace(':', '.', (new ArgvInput())->getFirstArgument());
        }

        return self::PHP_BUSINESS_LOGIC;
    }

    /**
     * getLogPath 获取日志写入路径
     *
     * @param $config
     * @return string
     * @author Giles <giles.wang@aliyun.com|giles.wang@qq.com>
     * @date 2024/1/3 13:37
     */
    protected function getLogPath($config) :string
    {
        return $config['path'] ?? $this->app->storagePath('logs');
    }
}