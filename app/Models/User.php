<?php

namespace App\Models;

use App\Constants\DatabaseTable;
use Illuminate\Contracts\Auth\Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends BaseModel implements JWTSubject, Authenticatable
{
    protected $table = DatabaseTable::USER;

    protected $appends = [

    ];

    protected $hidden = [
        'api_token',
        'created_at',
        'updated_at',
        'password',

        'refresh_token',
        'refresh_token_expiry'
    ];

    protected static function boot()
    {
        parent::boot();
        static::enableUuid();
    }

    protected $casts = [
        'is_disabled' => 'boolean',
        'refresh_token_expiry' => 'number'
    ];

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = \Hash::make($value);
    }

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
