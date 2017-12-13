<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use App\Game;
use App\Venue;
use App\Team;
use App\User;
use App\Team_has_User;
use App\User_has_Game;
use App\Player;
use App\Game_has_Period;
use App\Stat;
use App\Player_has_Stat;
use App\Team_has_Stat;
use App\Team_has_Game;
use App\Coach;
use App\Season;

class queryRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queryRecords {statId} {timeLength} {teamOrPlayer} {onlyBYU}';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->argument('teamOrPlayer') == 'player') {
          if ($this->argument('onlyBYU') == "BYU") {
            if ($this->argument('timeLength') == 'Quarter') {

            }
            else {
              $players = DB::table('User')->join('Player', 'User.idUser', '=', 'Player.User_idUser')
              ->join('Team_has_User', 'User.idUser', '=', 'Team_has_User.User_idUser')
              ->join('Team', 'Team_has_User.Team_idTeam', '=', 'Team.idTeam')
              ->select('User.idUser', 'Player.idPlayer')->where('Team.idTeam', '=', 1)->get();

              $playerStats = array();

              foreach ($players as $p) {
                // Sort by career //
                if ($this->argument('timeLength') == 'Career') {
                  echo "PlayerId: ".$p->idPlayer."\n";
                  $p->idPlayer;
                  $playerCareerStats = DB::table('Player_has_Stat')
                  ->join('Player', 'Player_has_Stat.Player_idPlayer', '=', 'Player.idPlayer')
                  ->select('Player_has_Stat.value', 'Game_idPeriod')
                  ->where('Player_has_Stat.Stat_idStat', '=', $this->argument('statId'))->where('Player_has_Stat.Player_idPlayer', '=', $p->idPlayer)->get();
                  $careerTotal = 0;
                  if (count($playerCareerStats) > 0) {
                    foreach ($playerCareerStats as $careerStat) {
                      $careerTotal += $careerStat->value;
                    }
                    $userInfo = User::where('idUser', '=', $p->idUser)->first();
                    echo $p->idUser."\n";
                      $statPlayer = array('firstName'=> $userInfo->firstName, 'lastName'=>$userInfo->lastName, 'value'=> $careerTotal);
                    array_push($playerStats, $statPlayer);
                  }
                }
                // Sort by season //
                else if ($this->argument('timeLength') == 'Season') {
                  echo $p->idPlayer."\n";
                  $season = DB::table('Season')->select('idSeason', 'Year')->get();
                  foreach ($season as $s) {
                    $playerSeasonStats = DB::table('Player_has_Stat')
                    ->join('Player', 'Player_has_Stat.Player_idPlayer', '=', 'Player.idPlayer')
                    ->join('Game_has_Period', 'Player_has_Stat.Game_idPeriod', '=', 'Game_has_Period.idGame_has_Period')
                    ->join('Game', 'Game_has_Period.Game_idGame', '=', 'Game.idGame')
                    ->join('Season', 'Game.Season_idSeason', '=', 'Season.idSeason')
                    ->select('Player_has_Stat.value', 'Player_has_Stat.Game_idPeriod')
                    ->where('Player_has_Stat.Stat_idStat', '=', $this->argument('statId'))
                    ->where('Player_has_Stat.Player_idPlayer', '=', $p->idPlayer)
                    ->where('Season.idSeason', '=', $s->idSeason)->get();
                    $seasonTotal = 0;
                    if (count($playerSeasonStats) > 0) {
                      foreach($playerSeasonStats as $seasonStat) {
                        $seasonTotal += $seasonStat->value;
                      }
                      $userInfo = User::where('idUser', '=', $p->idUser)->first();
                      $statPlayer = array('firstName'=> $userInfo->firstName, 'lastName'=>$userInfo->lastName, 'value'=> $seasonTotal);
                      array_push($playerStats, $statPlayer);
                    }
                  }
                }
                // Sort by season //
                else if ($this->argument('timeLength') == 'Game') {
                  echo $p->idPlayer."\n";
                  $game = DB::table('Game')->select('idGame')->get();
                  foreach ($game as $g) {
                      $playerGameStats = DB::table('Player_has_Stat')
                      ->join('Player', 'Player_has_Stat.Player_idPlayer', '=', 'Player.idPlayer')
                      ->join('Game_has_Period', 'Player_has_Stat.Game_idPeriod', '=', 'Game_has_Period.idGame_has_Period')
                      ->join('Game', 'Game_has_Period.Game_idGame', '=', 'Game.idGame')
                      ->join('Season', 'Game.Season_idSeason', '=', 'Season.idSeason')
                      ->select('Player_has_Stat.value', 'Player_has_Stat.Game_idPeriod')
                      ->where('Player_has_Stat.Stat_idStat', '=', $this->argument('statId'))
                      ->where('Player_has_Stat.Player_idPlayer', '=', $p->idPlayer)
                      ->where('Game.idGame', '=', $g->idGame)->get();
                      $gameTotal = 0;
                      if (count($playerGameStats) > 0) {
                        foreach($playerGameStats as $gameStat) {
                          $gameTotal += $gameStat->value;
                        }
                        $userInfo = User::where('idUser', '=', $p->idUser)->first();
                        $statPlayer = array('firstName'=> $userInfo->firstName, 'lastName'=>$userInfo->lastName, 'value'=> $gameTotal);
                        array_push($playerStats, $statPlayer);
                      }
                  }
                }
                else if ($this->argument('timeLength') == "1stHalf") {
                  $game = DB::table('Game')->select('idGame')->get();
                  echo $p->idPlayer."\n";
                  foreach ($game as $g) {
                      $playerGameStats = DB::table('Player_has_Stat')
                      ->join('Player', 'Player_has_Stat.Player_idPlayer', '=', 'Player.idPlayer')
                      ->join('Game_has_Period', 'Player_has_Stat.Game_idPeriod', '=', 'Game_has_Period.idGame_has_Period')
                      ->join('Game', 'Game_has_Period.Game_idGame', '=', 'Game.idGame')
                      ->join('Season', 'Game.Season_idSeason', '=', 'Season.idSeason')
                      ->select('Player_has_Stat.value', 'Player_has_Stat.Game_idPeriod')
                      ->where('Player_has_Stat.Stat_idStat', '=', $this->argument('statId'))
                      ->where('Player_has_Stat.Player_idPlayer', '=', $p->idPlayer)
                      ->where('Game.idGame', '=', $g->idGame)
                      ->where('Game_has_Period.Period', '=', 1)->where('Game_has_Period.Period', '=', 2)->get();
                      $halfTotal = 0;
                      if (count($playerGameStats) > 0) {
                        foreach($playerGameStats as $gameStat) {
                          $halfTotal += $gameStat->value;
                        }
                        $userInfo = User::where('idUser', '=', $p->idUser)->first();
                        $statPlayer = array('firstName'=> $userInfo->firstName, 'lastName'=>$userInfo->lastName, 'value'=> $halfTotal);
                        array_push($playerStats, $statPlayer);
                      }
                  }
                }
                else if ($this->argument('timeLength') == "2ndHalf") {
                  $game = DB::table('Game')->select('idGame')->get();
                  echo $p->idPlayer."\n";
                  foreach ($game as $g) {
                      $playerGameStats = DB::table('Player_has_Stat')
                      ->join('Player', 'Player_has_Stat.Player_idPlayer', '=', 'Player.idPlayer')
                      ->join('Game_has_Period', 'Player_has_Stat.Game_idPeriod', '=', 'Game_has_Period.idGame_has_Period')
                      ->join('Game', 'Game_has_Period.Game_idGame', '=', 'Game.idGame')
                      ->join('Season', 'Game.Season_idSeason', '=', 'Season.idSeason')
                      ->select('Player_has_Stat.value', 'Player_has_Stat.Game_idPeriod')
                      ->where('Player_has_Stat.Stat_idStat', '=', $this->argument('statId'))
                      ->where('Player_has_Stat.Player_idPlayer', '=', $p->idPlayer)
                      ->where('Game.idGame', '=', $g->idGame)
                      ->where('Game_has_Period.Period', '=', 3)->where('Game_has_Period.Period', '=', 4)->get();
                      $halfTotal = 0;
                      if (count($playerGameStats) > 0) {
                        foreach($playerGameStats as $gameStat) {
                          $halfTotal += $gameStat->value;
                        }
                        $userInfo = User::where('idUser', '=', $p->idUser)->first();
                        $statPlayer = array('firstName'=> $userInfo->firstName, 'lastName'=>$userInfo->lastName, 'value'=> $halfTotal);
                        array_push($playerStats, $statPlayer);
                      }
                  }
                }
              }

              usort($playerStats, function($a, $b) {
                if($a['value']==$b['value']) return 0;
                return $a['value'] < $b['value']?1:-1;
              });
              foreach ($playerStats as $record) {
                echo $record['firstName']." ".$record['lastName'].", ".$record['value']."\n";
              }
            }
          }
        }
    }
}
