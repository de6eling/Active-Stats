<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Team_has_Stat extends Model
{
  protected $table = 'Team_has_Stat';
    protected $fillable = ["Team_idTeam", "Game_idPeriod", "Stat_idStat", "value"];
    public $timestamps = false;
}
