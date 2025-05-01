<?php

namespace tests\gc;

use think\Model;

class GCModel extends Model
{
    public static function cleanMaker(): void
    {
        static::$_maker = [];
    }
}