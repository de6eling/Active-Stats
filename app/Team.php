<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
  protected $table = 'Team';
  protected $fillable = array("Title", "teamid");
  public $timestamps = false;
}
