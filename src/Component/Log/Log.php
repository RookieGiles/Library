<?php
namespace Giles\Library\Component\Log;

use BadMethodCallException;
use Giles\Library\Component\Log\Manager\LogManager;
use Monolog\Logger;
use RuntimeException;

/**
 * 日志组件调用入口
 *
 * @method static Logger getLogger(string $name = null, bool $forever = false, string $file = null, string $line = null)
 * @method static void emergency(string $message, array $context = [])
 * @method static void alert(string $message, array $context = [])
 * @method static void critical(string $message, array $context = [])
 * @method static void error(string $message, array $context = [])
 * @method static void warning(string $message, array $context = [])
 * @method static void notice(string $message, array $context = [])
 * @method static void info(string $message, array $context = [])
 * @method static void debug(string $message, array $context = [])
 *
 * @see LogManager
 *
 */

class Log
{
    /**
     * 魔术方法记录
     *
     * @param $method
     * @param $arguments
     * @return mixed
     * @author Giles <giles.wang@aliyun.com|giles.wang@qq.com>
     * @date 2024/2/20 14:01
     */
    public static function __callStatic($method, $arguments)
    {
        $instance = LogManager::getInstance();
        if ($instance === null) {
            throw new RuntimeException(' 日志组件获取实例失败，请查看组件是否正常被初始化！');
        }
        if (method_exists($instance, $method)) {
            return $instance->$method(...$arguments);
        }

        throw new BadMethodCallException('Log 日志组件异常，不存在的方法：'. '--> '. $method);
    }
}