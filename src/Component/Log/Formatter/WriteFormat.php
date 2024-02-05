<?php
namespace Giles\Library\Component\Log\Formatter;

class WriteFormat
{
    public static function getLineFormat(): string
    {
        return "%datetime% [%level_name%]:%host% %file%:%line% %uri% %traceId% %message% %contextFmt% \n";
    }
}