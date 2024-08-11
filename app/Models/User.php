<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Notifications\Notifiable;
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
        'avatar',
        'fullname',
        'job',
        'story',
        'gender',
        'dob',
        'email',
        'password',
        'google_id',
        'email_verified_at',
        'user_verified_at',
        'citizen_identity_card',
        'student_id_card',
        'street',
        'province_id',
        'district_id',
        'ward_id',
        'address_detail',
        'phone',
        'confirmation_code',
        'confirmation_code_expired_in',
        'remember_token',
        'refresh_token',
        'status',
        'banned_reason',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'avatar' => 'https://anubis.gr/wp-content/uploads/2018/03/no-avatar.png',
        'role_id' => '1',
        'status' => 'active',
    ];

    // protected static function boot()
    // {
    //     parent::boot();

    //     static::creating(function ($model) {
    //         // Thiết lập giá trị mặc định cho trường 'referral_code' khi tạo mới
    //         $model->referral_code = strtoupper(substr(md5(uniqid()), 0, 10));
    //     });
    // }

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
        'confirmation_code',
        'confirmation_code_expired_in',
        'id',
        'province_id',
        'district_id',
        'ward_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'citizen_identity_card' => 'array',
        'student_id_card' => 'array',
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

    public function scopeSearch($query, $search)
    {
        return $query->where('fullname', 'like', '%' . $search . '%')
            ->orWhere('email', 'like', '%' . $search . '%')
            ->orWhere('phone', 'like', '%' . $search . '%');
    }

    public function scopeFilter($query, $status, $role)
    {
        if ($status) {
            $query->where('status', $status);
        } else {
            $query->where('status', '!=', 'delete');
        }

        if ($role) {
            $query->where('role_id', $role);
        }
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function post()
    {
        return $this->hasMany(Post::class);
    }

    public function order()
    {
        return $this->hasMany(LoanOrders::class);
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }

    public function comment()
    {
        return $this->hasMany(Comment::class);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function ward()
    {
        return $this->belongsTo(Ward::class);
    }
}
