<?php

namespace tests\gc;

use think\Validate;

class GCValidate extends Validate
{
    public static function cleanMaker(): void
    {
        static::$maker = [];
    }
}