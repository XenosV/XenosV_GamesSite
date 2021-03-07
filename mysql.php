<?php
class GamesBase
{
	public function GamesBase($host, $username, $password, $database)
	{
		// Connect to database
		$this->mysqli = new mysqli($host, $username, $password, $database);
		if ($this->mysqli->connect_errno) 
		{
			die('mysql ошибка соединения с базой данных');
		}
	}
	
	public function ProcessingRequest($game_genre)
	{
		if ($game_genre != 'none' && $game_genre != 'all')
		{
			$this->sql_games_request = $this->mysqli->query("select games.*, 
															group_concat(DISTINCT platforms.Platforms ORDER BY platforms.Generation DESC) as plt_cc, 
															group_concat(DISTINCT genres.Genres ORDER BY genres.Genres SEPARATOR \"\/\") as gen_cc
													from games 
													join games_platforms on games_platforms.Game_ID = games.ID 
													join platforms on platforms.ID = games_platforms.Platform_ID 
													join games_genres on games_genres.Game_ID = games.ID 
													join genres	on genres.ID = games_genres.Genre_ID
													WHERE games.Visible > 0
													group by games.ID 
													Having LOCATE(\"$game_genre\", gen_cc) > 0 
													ORDER BY games.Series, games.Sort");
		}
		else
		{
			$this->sql_games_request = $this->mysqli->query("SELECT games.*, group_concat(DISTINCT platforms.Platforms ORDER BY platforms.Generation DESC) as plt_cc, group_concat(DISTINCT genres.Genres ORDER BY genres.Genres SEPARATOR '/') as gen_cc from games join games_platforms on games_platforms.Game_ID = games.ID join platforms on platforms.ID = games_platforms.Platform_ID join games_genres on games_genres.Game_ID = games.ID join genres	on genres.ID = games_genres.Genre_ID WHERE games.Visible > 0 group by games.ID ORDER BY CASE WHEN games.Series REGEXP '^(A|An|The)[[:space:]]' = 1 THEN TRIM(SUBSTR(games.Series , INSTR(games.Series ,' '))) ELSE games.Series	END, games.Sort");
		}
	}
	
	public function getNextGameDetail()
	{
		return $this->sql_games_request->fetch_array(MYSQLI_NUM);
	}
	
	private $mysqli;
	private $sql_games_request;
};
?>