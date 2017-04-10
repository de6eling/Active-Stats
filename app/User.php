<?php

namespace App;

//use Illuminate\Notifications\Notifiable;
//use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
     protected $table = 'User';
    protected $fillable = [
        'firstName', 'lastName', 'email', 'phone', 'age', 'netId', 'bio'
    ];
    public $timestamps = false;
}
