<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $table = 'Game';
    protected $fillable = array("gameid", "weather", "startTime", "endTime", "date", "Venue_idVenue", "attend", "duration", "neutralSite", "nightGame", "postSeason", "Season_idSeason");
    protected $dateformat = "m/d/Y";
    public $timestamps = false;
}
