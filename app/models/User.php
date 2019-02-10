<?php

namespace app\models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';

    public static $loginRules = [
        'required' => [
            ['name', 'password', 'csrf']
        ],
    ];

}