<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{

    /**
     * Define table name.
     */
    protected $table = 'member';

    /**
     * Define prumary key.
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'full_name',
        'phone_number',
        'phone_number_verify_status',
        'email_address',
        'email_address_verify_status',
        'current_address',
        'shirt_size',
        'position',
        'account_status',
        'api_token',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'api_token',
    ];
}
