<?php
namespace Giles\Library\Component\Log\Formatter;

use Monolog\Formatter\LineFormatter as MonoLogLineFormatter;
use Monolog\Utils;

class LineFormatter extends MonoLogLineFormatter
{
    /**
     * format Explain
     *
     * @param array $record
     *
     * @return string
     * @author Giles <giles.wang@aliyun.com|giles.wang@qq.com>
     * @date   2020/7/14 16:55
     */
    public function format(array $record) :string
    {
        $output = $this->format;
        $record['contextFmt'] = '###start###'. $this->toJson($this->normalize($record['context'])). '###end###';
        $record['message']    = str_replace(["\r\n", "\n"], '', $record['message']);

        foreach ($record as $var => $val) {
            //时间格式化
            if ($val instanceof \DateTime) {
                $val = $val->format('Y-m-d H:i:s.u');
            }
            if (false !== strpos($output, '%'.$var.'%')) {
                $output = str_replace('%'.$var.'%', $this->stringify($val), $output);
            }
        }

        return $output;
    }

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

            if ($data instanceof \Throwable) {

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