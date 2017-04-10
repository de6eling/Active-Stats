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
use DateTime;

class uploadXML extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uploadXML {file}';

    /**
     * The consoluse App\User;
ew command instance.
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
        $xml = simplexml_load_file($this->argument('file')) or die("Error: Cannot create object");
          $game = $xml->venue[0];
          $venue = Venue::firstOrNew([
            "location" => $game['location'],
            "stadium" => $game['stadium']
          ]);
          $venue->save();
          $formattedStart = date('Y-m-d h:i:s', strtotime($game['date'].' '.$game['start']));
          $formattedEnd = date('Y-m-d h:i:s', strtotime($game['date'].' '.$game['end']));
          $formattedDuration = $game['duration'];
          $time = strtotime($game['date']);
          $newformat = date('Y-m-d',$time);
          $newGame = Game::firstOrNew(
            [
              "gameid" => $game['gameid'],
              "date" => $newformat,
              "startTime" => $formattedStart,
              "endTime" => $formattedEnd,
              "duration" => $formattedDuration,
              "weather" => $game['weather'],
              "attend" => $game['attend'],
              "Venue_idVenue" => $venue->idVenue
            ]
          );
          $newGame->save();
          // Add new team 1
          $team1 = $xml->team[0];
          $addTeam1 = Team::firstOrNew(
            [
              "Title" => $team1['name'],
              "teamid" => $team1['id'],
              "abb" => $team1['abb']
            ]
          );
          $addTeam1->save();

          // Add new team 2
          $team2 = $xml->team[1];
          $addTeam2 = Team::firstOrNew(
            [
              "Title" => $team2['name'],
              "teamid" => $team2['id'],
              "abb" => $team2['abb']
            ]
          );
          $addTeam2->save();

          // Add users for first team
          foreach($team1->children() as $user) {
            if ($user->getName() == "player") {
              $b = $user['name'];
                $firstName = "";
                $lastName = "";
                  if (sizeof(explode(',', $b)) > 1) {
                    $firstName = explode(',', $b)[1];
                    $lastName = explode(',', $b)[0];
                  }
                  else {
                    $firstName = $b;
                  }
                $user1 = User::firstOrNew([
                  "firstName" => $firstName,
                  "lastName" => $lastName
                ]);
                $user1->save();
                $id = 0;
                if ($user1->save()) {
                  if ($user1->id == null) {
                    $id = $user1->idUser;
                  }
                  else {
                    $id = $user1->id;
                  }
                }

                echo "User added: ".$firstName." ".$lastName."\n";
                $teamplayer1 = Team_has_User::firstOrNew([
                  "Team_idTeam" => $addTeam1->idTeam,
                  "User_idUser" => $id
                ]);
                $teamplayer1->save();

                $user1Game = User_has_Game::firstOrNew([
                  "User_idUser" => $id,
                  "Game_idGame" => $newGame->idGame,
                  "Game_Venue_idVenue" => $newGame->Venue_idVenue
                ]);
                $user1Game->save();

                $player1 = Player::firstOrNew([
                  "User_idUser" => $id,
                  "number" => $user['uni']
                ]);
                $player1->save();

                echo "Team_has_User added: ".$addTeam1."\n";
            }
          }

          // Add users for second team
          foreach($team2->children() as $user) {
            if ($user->getName() == "player") {
              $b = $user['name'];
                  if (sizeof(explode(',', $b)) > 1) {
                    $firstName = explode(',', $b)[1];
                    $lastName = explode(',', $b)[0];
                  }
                  else {
                    $firstName = $b;
                  }
                  $user2 = User::firstOrNew([
                    "firstName" => $firstName,
                    "lastName" => $lastName
                  ]);
                  $id = 0;
                  if ($user2->save()) {
                    if ($user2->id == null) {
                      $id = $user2->idUser;
                    }
                    else {
                      $id = $user2->id;
                    }
                  }

                echo "User added: ".$user2."\n";
                $teamplayer2 = Team_has_User::firstOrNew([
                  "Team_idTeam" => $addTeam2->idTeam,
                  "User_idUser" => $id
                ]);
                $teamplayer2->save();

                $user2Game = User_has_Game::firstOrNew([
                  "User_idUser" => $id,
                  "Game_idGame" => $newGame->idGame,
                  "Game_Venue_idVenue" => $newGame->Venue_idVenue
                ]);
                $user2Game->save();

                $player2 = Player::firstOrNew([
                  "User_idUser" => $id,
                  "number" => $user['uni']
                ]);
                $player2->save();

                echo "Team_has_User added: ".$firstName." ".$lastName."!\n";
            }
          }


          // Test code
          /*foreach($xml->children() as $game) {
            if ($game->getName() == "venue")
            foreach($game->attributes() as $a => $b) {
              echo $a,'="',$b,"\"\n";
            }
            foreach($game->children() as $team) {
              echo "-------",$team->getName(),"-------\n";
              foreach($team->attributes() as $c => $d) {
                echo $c,'="',$d,"\"\n";
              }
              foreach($team->children() as $stats) {
                echo '-------',$stats->getName(),"-------\n";
                foreach($stats->attributes() as $e => $f) {
                  echo $e,'="',$f,"\"\n";
                }
              }
            }
          }*/
    }
}
