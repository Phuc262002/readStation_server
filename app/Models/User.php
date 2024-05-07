<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'role_id',
        'status',
        'referral_code',
        'avatar',
        'phone',
        'address',
        'city',
        'state',
        'street',
        'google_id',
        'email_verified_at',
        'remember_token',
        'refresh_token',
        'name',
        'email',
        'password',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'role_id' => '1',
        'status' => 'active',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Thiết lập giá trị mặc định cho trường 'referral_code' khi tạo mới
            $model->referral_code = strtoupper(substr(md5(uniqid()), 0, 10));
        });
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'refresh_token',
        'remember_token',
        'role_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getRoleAttribute()
    {
        return [
            'name' => $this->role->name,
            'description' => $this->role->description
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
