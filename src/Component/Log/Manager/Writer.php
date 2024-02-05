<?php
namespace Giles\Library\Component\Log\Manager;

use Giles\Library\Component\Log\Config;
use Monolog\Handler\BufferHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class Writer
{
    /** @var logger */
    protected $logger;

    /** @var array  Monolog 日志级别对应的数值*/
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
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getLogger()
    {
        return $this->logger;
    }

    public function useSingle(string $logChannel)
    {
        $level = Config::isDebug()  ? 'debug' : Config::lowLevel();
        $file = Config::get('path') . '/'. $logChannel .'.log';
        $handler = new StreamHandler(
            $file,
            $this->level($level)
        );
        $this->setFormatter($handler, Config::get('format'));
    }



    protected function level(string $level)
    {
        $level = $level ?? 'debug';

        if (isset($this->levels[$level])) {
            return $this->levels[$level];
        }

        throw new InvalidArgumentException('Invalid log level.');
    }

    protected function setFormatter(HandlerInterface $handler, $format = 'json')
    {
        $handler->setFormatter(SelectFormat::get($format));
        //验证是否开启Buffer
        $handler = Config::isBuffer() ? new BufferHandler($handler) : $handler;
        $this->logger->pushHandler($handler);
        $this->logger->pushProcessor(new HandleDataProcessor());
        $this->logger->pushProcessor(new ProjectProcessor());
        if (!empty($this->module)) {
            $this->logger->pushProcessor(new ModuleProcessor($this->module));
        }
        $this->logger->pushProcessor(new HostProcessor());
        $this->logger->pushProcessor(new TraceProcessor());
        $this->logger->pushProcessor(new IntrospectionProcessor(Config::get('level')));
    }
}