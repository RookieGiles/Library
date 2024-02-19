<?php
namespace Giles\Library\Component\Log\Formatter;

use Monolog\Formatter\JsonFormatter as MonoJsonFormatter;
use Monolog\Utils;
use Throwable;

class JsonFormatter extends MonoJsonFormatter
{
    public function __construct($batchMode = self::BATCH_MODE_JSON, $appendNewline = true)
    {
        parent::__construct($batchMode, $appendNewline);
    }

    public function format(array $record) :string
    {
        //过滤level_name
        if (isset($record['level_name'])) {
            $record['level'] = strtolower($record['level_name']);
            unset($record['level_name']);
        }

        //过滤get请求URI
        if (isset($record['uri']) && strpos($record['uri'], '?')) {
            $handleUri = explode('?', $record['uri']);
            $record['uri']                 = $handleUri[0] ?? '';
            $record['context']['uriParam'] = $handleUri[1] ?? '';
        }
        //处理message 保留1024 字节 大概3K 数据
        if (!empty($record['message']) && strlen($record['message']) > 2048) {
            $record['message'] = substr($record['message'], 0, 2048);
        }
        //处理context
        $record['context'] = json_encode($this->normalize($record['context']), JSON_UNESCAPED_UNICODE);
        return $this->toJson($record, true) . ($this->appendNewline ? "\n" : '');
    }

    /**
     * 处理上下文数组处理为字符串  copy monolog NormalizerFormatter::normalize
     *
     * @param mixed $data
     * @param int   $depth
     *
     * @return array|bool|float|int|string|null
     * @author Giles <giles.wang@aliyun.com|giles.wang@qq.com>
     * @date   2020/1/6 10:07
     */
    protected function normalize($data, int $depth = 0)
    {
        if ($depth > 9) {
            return 'Over 9 levels deep, aborting normalization';
        }

        if (null === $data || is_scalar($data)) {
            if (is_float($data)) {
                if (is_infinite($data)) {
                    return ($data > 0 ? '' : '-') . 'INF';
                }
                if (is_nan($data)) {
                    return 'NaN';
                }
            }

            return $data;
        }

        if (is_array($data)) {
            $normalized = array();

            $count = 1;
            foreach ($data as $key => $value) {
                if ($count++ > 500) {
                    $normalized['...'] = 'Over 50 items ('.count($data).' total), aborting normalization';
                    break;
                }

                $normalized[$key] = $this->normalize($value, $depth+1);
            }

            return $normalized;
        }

        if ($data instanceof \DateTime) {
            return $data->format($this->dateFormat);
        }

        if (is_object($data)) {
            if ($data instanceof Throwable) {
                return $this->normalizeException($data);
            }

            // non-serializable objects that implement __toString
            if (method_exists($data, '__toString') && !$data instanceof \JsonSerializable) {
                $value = $data->__toString();
            } else {
                $value = $this->toJson($data, true);
            }

            return sprintf("[object] (%s: %s)", Utils::getClass($data), $value);
        }

        if (is_resource($data)) {
            return sprintf('[resource] (%s)', get_resource_type($data));
        }

        return '[unknown('.gettype($data).')]';
    }
}