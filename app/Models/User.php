<?php

namespace App\Models;

use App\Constants\DatabaseTable;
use Illuminate\Contracts\Auth\Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends BaseModel implements JWTSubject, Authenticatable
{
    protected $table = DatabaseTable::USER;

    protected $appends = [
        'type'
    ];

    protected $hidden = [
        'api_token',
        'created_at',
        'updated_at',
        'password'
    ];

    protected static function boot()
    {
        parent::boot();
        static::enableUuid();
    }

    protected $casts = [
        'is_disabled' => 'boolean'
    ];

    public function getIsAdminAttribute()
    {
        return $this->username === "admin";
    }

    //---------------- authenticate
    public function getAuthIdentifierName()
    {
        return "username";
    }

    public function getAuthIdentifier()
    {
        return $this->username;
    }

    public function getAuthPassword()
    {
        return $this->password;
    }

    public function getRememberToken()
    {
        return null;
    }

    public function setRememberToken($value)
    {
        // TODO: not using
    }

    public function getRememberTokenName()
    {
        return "null";
    }

    // ---------------------------- JWT Subject -----------------------------
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'usr' => self::query()->find($this->id)
        ];
    }
}
