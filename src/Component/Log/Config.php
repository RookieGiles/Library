<?php
namespace Giles\Library\Component\Log;

use InvalidArgumentException;

class Config
{
    /** @var string 默认使用驱动 */
    protected static $drive;
    /** @var array 文件流支持的驱动类型 */
    protected static $streamDrive = ['single', 'daily', 'mongodb'];
    /** @var array 日志基础配置文件配置 */
    protected static $checkConf = [
        'driver'   => ['null' => false, 'value' => ['single', 'daily', 'mongodb']],
        'path'     => ['null' => false],
        'database' => ['null' => true],
        'level'    => ['null' => true, 'value' => ['debug', 'info', 'notice', 'warning']],
        'format'   => ['null' => true],
        'debug'    => ['null' => true],
        'buffer'   => ['null' => true],
    ];
    /** @var array 组件配置文件 */
    protected static $conf = [
        'debug'      => false,
        'buffer'     => true,
        'format'     => 'json',
        'level'      => 'debug',
        'database'   => '',
    ];

    /** @var string 日志写入日期格式 */
    public static $dateFormat = 'Y-m-d H:i:s.u';

    /** @var string 默认通道名称 最终生成文件名 */
    public static $defaultLog = 'application';

    /**
     * 是否开启debug 模式
     *
     * @return mixed
     * @author Giles <giles.wang@aliyun.com|giles.wang@qq.com>
     * @date   2019/12/3 10:24
     */
    public static function isDebug()
    {
        return self::$conf['debug'];
    }

    /**
     * 是否开启Buffer支持
     *
     * @return mixed
     * @author Giles <giles.wang@aliyun.com|giles.wang@qq.com>
     * @date   2019/12/3 10:24
     */
    public static function isBuffer()
    {
        return self::$conf['buffer'];
    }

    /**
     * 获取mongoDb 链接配置
     *
     * @return mixed
     * @author Giles <giles.wang@aliyun.com|giles.wang@qq.com>
     * @date 2024/2/6 13:47
     */
    public static function getMongoConf()
    {
        return [
            'host'     => self::$conf['path'],
            'database' => self::$conf['database']
        ];
    }

    /**
     * 写入最小日志级别
     *
     * @return mixed
     * @author Giles <giles.wang@aliyun.com|giles.wang@qq.com>
     * @date   2019/12/3 10:24
     */
    public static function lowLevel()
    {
        return self::$conf['level'];
    }

    /**
     * 设置日志配置
     *
     * @param array $config
     *
     * @return void
     * @author  Giles <giles.wang@aliyun.com|giles.wang@qq.com>
     * @date    2019/11/28 13:40
     */
    public static function set(array $config)
    {
        self::parse($config);
    }

    /**
     * 获取写入格式配置
     *
     * @param string $style
     * @param null $module
     * @return array|string
     * @author  Giles <giles.wang@aliyun.com|giles.wang@qq.com>
     * @date    2019/11/30 10:33
     */
    public static function getFormat(string $style = 'json', $module = null)
    {
        $baseField = [
            'datetime',
            'level_name',
            'level',
            'project',
            'module',
            'traceId',
            'host',
            'uri',
            'method',
            'file',
            'line',
            'message',
            'context',
        ];

        if (strcasecmp($style, 'json') !== 0) {
            $format = "";
            foreach ($baseField as $field) {
                if (strcasecmp($field, 'module') === 0 && empty($module)) {
                    continue;
                }
                if (strcasecmp($field, 'datetime') === 0) {
                    $format .="[%$field%] ";
                } else {
                    $format .="%$field% ";
                }
            }
            $format .= "\n";
        } else {
            $format = $baseField;
        }

        return $format;
    }

    /**
     * 解析日志组件初始化 参数配置
     *
     * @param array $config
     *
     * @return void
     * @author  Giles <giles.wang@aliyun.com|giles.wang@qq.com>
     * @date    2019/11/28 13:37
     */
    private static function parse(array $config)
    {
        // 检查配置文件默认驱动
        if (empty($config['default'])) {
            throw new InvalidArgumentException('日志组件初始化失败，缺少必要参数配置');
        }
        self::$drive = $config['default'];

        if (!in_array(self::$drive, self::$streamDrive)) {
            throw new InvalidArgumentException('当前日志组件只支持， single, daily 两种驱动方式');
        }

        $driverConf = $config['drivers'][self::$drive];
        isset($config['project']) && $driverConf['project'] = $config['project'];
        isset($config['debug']) && $driverConf['debug'] = $config['debug'];
        isset($config['buffer']) && $driverConf['buffer'] = $config['buffer'];
        //CLI模式下 buffer false
        strnatcmp(PHP_SAPI, 'cli') === 0 && $driverConf['buffer'] = false;
        //解析必要配置
        foreach (self::$checkConf as $key => $value) {
            //检测必要的配置参数
            if ($value['null'] === false && (empty($driverConf[$key]))) {
                throw new InvalidArgumentException('日志组件初始化失败，缺少必要参数配置'. $key);
            }
            //检测参数配置值合法性
            if (isset($value['value']) && !in_array($driverConf[$key], $value['value'])) {
                throw new InvalidArgumentException(
                    '日志组件初始化失败，参数配置不合法'. $driverConf[$key]. '| 未在可选值'. var_export($value['value'], true). '当中'
                );
            }

            isset($driverConf[$key]) && self::$conf[$key] = $driverConf[$key];
        }
    }

    /**
     * 获取单独的日志配置
     *
     * @param string $key
     *
     * @return String
     * @author  Giles <giles.wang@aliyun.com|giles.wang@qq.com>
     * @date    2019/11/28 13:47
     */
    public static function get(string $key): String
    {
        if (isset(self::$conf[$key])) {
            return self::$conf[$key];
        }

        throw new InvalidArgumentException('不存在的日志配置'. '【' .$key. '】');
    }
}