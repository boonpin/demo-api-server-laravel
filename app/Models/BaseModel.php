<?php

namespace App\Models;

use App\Helpers\UuidHelper;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    protected $primaryKey = "id";

    protected $keyType = 'string';

    public $incrementing = false;

    protected $hidden = [
        'created_at',
        'updated_at',
        'password'
    ];

    protected static function enableUuid()
    {
        static::creating(function ($model) {
            if (empty($model->{$model->primaryKey})) {
                $model->{$model->primaryKey} = UuidHelper::uuid();
            }
        });
    }

    protected function __encryptString($value)
    {
        return \Crypt::encryptString($value);
    }

    protected function __decryptGetString($attribute)
    {
        if (!empty($this->{$attribute})) {
            try {
                return \Crypt::decryptString($this->{$attribute});
            } catch (\Exception $e) {
            }
        }
        return null;
    }
}
