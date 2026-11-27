<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable,HasRoles,HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'password',
        'status',
        'phone',
        'image',
        'device_token',
        'org_id',
        'bio',
        'country',
        'language',
        'bank_code',
        'bank_name',
        'account_number',
        'account_name',
        'payout_currency',
        'payout_verified_at',
        'paystack_recipient_code',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        // Never let a new payout field leak through an existing API response.
        'account_number',
        'paystack_recipient_code',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'payout_verified_at' => 'datetime',
        'account_number' => 'encrypted',
    ];
    protected $appends = ['followers','imagePath'];

    public function getFollowersAttribute()
    {
        $appuser = AppUser::get();          
        $followers= array();
        foreach ($appuser as $user) {                
            if(in_array($this->attributes['id'],array_filter(explode(',',$user->following)))){
                array_push($followers,$user->id);
            } 
        }  
        return $followers;      
    }
    
    public function getImagePathAttribute()
    {
        return url('images/upload') . '/'.$this->attributes['image'];
    }

    public function hasPayoutDetails(): bool
    {
        return filled($this->account_number)
            && filled($this->bank_code)
            && filled($this->bank_name)
            && filled($this->account_name);
    }

    public function maskedAccountNumber(): string
    {
        $number = (string) ($this->account_number ?? '');

        if ($number === '') {
            return '—';
        }

        return str_repeat('•', max(0, strlen($number) - 4)).substr($number, -4);
    }
}
