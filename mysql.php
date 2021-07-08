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
		
		$this->mysqli->set_charset("utf8");
		
		$this->SelectAllPlatforms();
		$this->SelectAllGenres();
		$this->SelectAllYears();
	}
	
	public function getGameInfo($id)
	{
		$request = $this->mysqli->query(
		"SELECT
			games.*, 
			group_concat(DISTINCT platforms.Platforms ORDER BY platforms.Generation DESC SEPARATOR ';') as plt_cc, 
			group_concat(DISTINCT genres.Genres ORDER BY genres.Genres SEPARATOR '/') as gen_cc 
		FROM
			games 
				join games_platforms on games_platforms.Game_ID = games.ID 
				join platforms on platforms.ID = games_platforms.Platform_ID 
				join games_genres on games_genres.Game_ID = games.ID 
				join genres on genres.ID = games_genres.Genre_ID 
		WHERE
			games.ID = $id"
		);
		
		return $request->fetch_array(MYSQLI_ASSOC);
	}
	
	public function getPlatformInfo($id)
	{
		$request = $this->mysqli->query(
		"SELECT 
			*
		FROM 
			platforms
		WHERE
			platforms.ID = $id"
		);
		
		return $request->fetch_array(MYSQLI_ASSOC);
	}
	
	public function SelectGameBase($game_genre, $game_platform)
	{
		if ($game_genre <= 1 && $game_platform <= 1)
		{
			$this->sql_games_request = $this->mysqli->query(
			"SELECT
				games.*,
				group_concat(DISTINCT platforms.Platforms ORDER BY platforms.Generation DESC SEPARATOR ';') as plt_cc,
				group_concat(DISTINCT genres.Genres ORDER BY genres.Genres SEPARATOR '/') as gen_cc
			FROM
				games
					join games_platforms on games_platforms.Game_ID = games.ID
					join platforms on platforms.ID = games_platforms.Platform_ID
					join games_genres on games_genres.Game_ID = games.ID
					join genres on genres.ID = games_genres.Genre_ID
			WHERE
				games.Visible > 0
			GROUP BY
				games.ID
			ORDER BY
				CASE
					WHEN games.Name REGEXP '^(A|An|The)[[:space:]]' = 1
					THEN TRIM(SUBSTR(games.Name, INSTR(games.Name ,' ')))
					ELSE games.Name
				END,
				games.Name"
			);
		}
		else if (($game_genre > 1) && ($game_platform <= 1))
		{
			$this->sql_games_request = $this->mysqli->query(
				"SELECT
					games.*,
					group_concat(DISTINCT platforms.Platforms ORDER BY platforms.Generation DESC SEPARATOR ';') as plt_cc,
					group_concat(DISTINCT genres_t.Genres ORDER BY genres_t.Genres SEPARATOR '/') as gen_cc
				FROM
					games
						join games_platforms on games_platforms.Game_ID = games.ID
						join platforms on platforms.ID = games_platforms.Platform_ID
						join games_genres games_genres_t on games_genres_t.Game_ID = games.ID
						join genres genres_t on genres_t.ID = games_genres_t.Genre_ID
						join games_genres games_genres_t2 on games_genres_t2.Game_ID = games.ID
						join genres genres_t2 on genres_t2.ID = games_genres_t2.Genre_ID AND genres_t2.ID = $game_genre
				WHERE
					games.Visible > 0
				GROUP BY
					games.ID
				ORDER BY
					CASE
						WHEN games.Name REGEXP '^(A|An|The)[[:space:]]' = 1
						THEN TRIM(SUBSTR(games.Name, INSTR(games.Name ,' ')))
						ELSE games.Name
					END,
					games.Name"
			);
		}
		else if (($game_platform > 1) && ($game_genre <= 1))
		{
			$this->sql_games_request = $this->mysqli->query(
				"SELECT
					games.*,
					group_concat(DISTINCT platforms_t.Platforms ORDER BY platforms_t.Generation DESC SEPARATOR ';') as plt_cc,
					group_concat(DISTINCT genres.Genres ORDER BY genres.Genres SEPARATOR '/') as gen_cc
				FROM
					games
						join games_platforms games_platforms_t on games_platforms_t.Game_ID = games.ID
						join platforms platforms_t on platforms_t.ID = games_platforms_t.Platform_ID
						join games_platforms games_platforms_t2 on games_platforms_t2.Game_ID = games.ID
						join platforms platforms_t2 on platforms_t2.ID = games_platforms_t2.Platform_ID AND platforms_t2.ID = $game_platform
						join games_genres on games_genres.Game_ID = games.ID
						join genres	on genres.ID = games_genres.Genre_ID
				WHERE
					games.Visible > 0
				GROUP BY
					games.ID
				ORDER BY
					CASE
						WHEN games.Name REGEXP '^(A|An|The)[[:space:]]' = 1
						THEN TRIM(SUBSTR(games.Name, INSTR(games.Name ,' ')))
						ELSE games.Name
					END,
					games.Name"
			);
		}
		else
		{
			$this->sql_games_request = $this->mysqli->query(
				"SELECT
					games.*,
					group_concat(DISTINCT platforms_t.Platforms ORDER BY platforms_t.Generation DESC SEPARATOR ';') as plt_cc,
					group_concat(DISTINCT genres_t.Genres ORDER BY genres_t.Genres SEPARATOR '/') as gen_cc
				FROM
					games
						join games_platforms games_platforms_t on games_platforms_t.Game_ID = games.ID
						join platforms platforms_t on platforms_t.ID = games_platforms_t.Platform_ID
						join games_platforms games_platforms_t2 on games_platforms_t2.Game_ID = games.ID
						join platforms platforms_t2 on platforms_t2.ID = games_platforms_t2.Platform_ID AND platforms_t2.ID = $game_platform
						join games_genres games_genres_t on games_genres_t.Game_ID = games.ID
						join genres genres_t on genres_t.ID = games_genres_t.Genre_ID
						join games_genres games_genres_t2 on games_genres_t2.Game_ID = games.ID
						join genres genres_t2 on genres_t2.ID = games_genres_t2.Genre_ID AND genres_t2.ID = $game_genre
				WHERE
					games.Visible > 0
				GROUP BY
					games.ID
				ORDER BY
					CASE
						WHEN games.Name REGEXP '^(A|An|The)[[:space:]]' = 1
						THEN TRIM(SUBSTR(games.Name, INSTR(games.Name ,' ')))
						ELSE games.Name
					END,
					games.Name"
			);
		}
	}
	
	public function SelectGameSeries($game_series)
	{
		$this->sql_games_request = $this->mysqli->query(
			"SELECT
				games.*,
				group_concat(DISTINCT platforms.Platforms ORDER BY platforms.Sort, platforms.Generation DESC SEPARATOR ';') as plt_cc,
				group_concat(DISTINCT genres.Genres ORDER BY genres.Genres SEPARATOR '/') as gen_cc
			FROM
				games
					join games_platforms on games_platforms.Game_ID = games.ID
					join platforms on platforms.ID = games_platforms.Platform_ID
					join games_genres on games_genres.Game_ID = games.ID
					join genres on genres.ID = games_genres.Genre_ID
			WHERE
				(LOCATE(\"$game_series\", games.Series) and games.Visible > 0) 
			GROUP BY
				games.ID
			ORDER BY
				games.Year"
			);
	}
	
	public function getNextGameDetail()
	{
		return $this->sql_games_request->fetch_array(MYSQLI_ASSOC);
	}
	
	public function getNextPlatform()
	{
		return $this->sql_platforms_request->fetch_array(MYSQLI_ASSOC);
	}
	private function SelectAllPlatforms()
	{
		$this->sql_platforms_request = $this->mysqli->query(
		"SELECT 
			* 
		FROM 
			`platforms`
		ORDER BY 
			Generation, Sort"
		);
	}
	
	public function getNextGenre()
	{
		return $this->sql_genre_request->fetch_array(MYSQLI_ASSOC);
	}
	private function SelectAllGenres()
	{
		$this->sql_genre_request = $this->mysqli->query(
		"SELECT 
			* 
		FROM 
			`genres`
		ORDER BY 
			Sort"
		);
	}
	
	public function getNextYear()
	{
		return $this->sql_year_request->fetch_array(MYSQLI_ASSOC);
	}
	
	private function SelectAllYears()
	{
		$this->sql_year_request = $this->mysqli->query(
		"SELECT
			Year
		FROM
			games
		GROUP BY 
			Year"
		);
	}
	
	private $mysqli;
	private $sql_games_request;
	private $sql_platforms_request;
	private $sql_genre_request;
	private $sql_year_request;
};
?>