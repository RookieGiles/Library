<?php
namespace Giles\Library\Component\Log\Manager;

use Giles\Library\Component\Log\Config;
use Giles\Library\Component\Log\Formatter\JsonFormatter;
use Giles\Library\Component\Log\Formatter\LineFormatter;
use Giles\Library\Component\Log\Formatter\WriteFormat;

class SelectFormat
{
    /**
     * get 获取格式
     *
     * @static
     * @param string $type
     * @return LineFormatter
     *
     * @author liuxd <liuxd@guahao.com>
     * @date   2021/8/25 下午4:36
     */
    public static function get(string $type = 'line'): LineFormatter
    {
        if (! method_exists(new self, $type)) {
            return self::line();
        }

        return self::$type();
    }

    /**
     * json json格式
     *
     * @static
     * @return JsonFormatter
     *
     * @author liuxd <liuxd@guahao.com>
     * @date   2021/8/25 下午4:37
     */
    protected static function json(): JsonFormatter
    {
        return new JsonFormatter();
    }

    /**
     * line 非json格式的每行记录
     *
     * @static
     * @return LineFormatter
     *
     * @author liuxd <liuxd@guahao.com>
     * @date   2021/8/25 下午4:37
     */
    protected static function line(): LineFormatter
    {
        return new LineFormatter(
            WriteFormat::getLineFormat(),
            Config::$dateFormat,
            true,
            true
        );
    }
}