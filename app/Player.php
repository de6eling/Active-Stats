<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
  protected $table = 'User_has_Game';
    protected $fillable = ["User_idUser", "number"];
    public $timestamps = false;
}
