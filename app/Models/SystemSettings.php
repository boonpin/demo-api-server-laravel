<?php

namespace App\Models;

use App\Constants\DatabaseTable;

class SystemSettings extends BaseModel
{
    protected $table = DatabaseTable::SYS_SETTING;

    protected $primaryKey = "key";

    public $casts = [
        'value' => 'array'
    ];

    public static function getValue($key, $default = null)
    {
        $setting = self::query()->where("key", $key)->first();
        return empty($setting) ? $default : $setting->value;
    }

    public static function removeKey($key)
    {
        if (is_null($key)) {
            return false;
        }
        return self::query()->where("key", $key)->delete();
    }

    public static function setKeyValue($key, $value)
    {
        if (is_null($value)) {
            return false;
        }
        $setting = self::query()->where("key", $key)->first();
        if (empty($setting)) {
            $setting = new self();
            $setting->key = $key;
        }
        $setting->value = $value;
        return $setting->save();
    }
}
