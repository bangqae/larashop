<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'password',
        'first_name', 'last_name', 'email', 'phone', 'password', 'company', 'address1', 'address2', 'province_id', 'city_id', 'postcode',
    ];

    protected $appends = ['user_full_name'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function products() // Jamak
    {
        return $this->hasMany('App\Model\Product'); // Relasi one to many, tapi di model Product : one to one
    }

    /**
	 * Add full_name custom attribute to order object
	 *
	 * @return boolean
	 */
	public function getUserFullNameAttribute()
	{
		return "{$this->first_name} {$this->last_name}";
	}
}
