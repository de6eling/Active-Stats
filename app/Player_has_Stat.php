<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Player_has_Stat extends Model
{
  protected $table = 'Player_has_Stat';
    protected $fillable = ["Player_idPlayer", "Player_User_idUser", "Game_idPeriod", "Stat_idStat", "value"];
    public $timestamps = false;
}
