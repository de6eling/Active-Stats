<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Team_has_User extends Model
{
  protected $table = 'Team_has_User';
    protected $fillable = ["Team_idTeam", "User_idUser"];
    public $timestamps = false;
}
