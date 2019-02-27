<?php

namespace app\models;

use app\Model;

/**
 * Class User
 * @package app\models
 */

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