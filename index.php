<?php

class MainHtml
{
	public function MainHtml()
	{
		if (isset($_GET['name']))
			$this->sort_name = str_replace(":", "", $_GET['name']);
		else
			$this->sort_name = 'none';
		
		if (isset($_GET['id']))
			$this->sort_id = str_replace(":", "", $_GET['id']);
		else
			$this->sort_id = 'none';
		
		if (isset($_GET['genre']))
			$this->sort_genre = str_replace(":", "", $_GET['genre']);
		else
			$this->sort_genre = 'none';
		
		if (isset($_GET['series']))
			$this->sort_series = str_replace(":", "", $_GET['series']);
		else
			$this->sort_series = 'none';
		
		if (isset($_GET['platform']))
			$this->sort_platform = str_replace(":", "", $_GET['platform']);
		else
			$this->sort_platform = 'none';
		
		if (isset($_GET['year']))
			$this->sort_year = str_replace(":", "", $_GET['year']);
		else
			$this->sort_year = 'none';
	}
	
	public function getSortName()
	{
		return $this->sort_name;
	}
	public function getSortId()
	{
		return $this->sort_id;
	}
	public function getSortGenre()
	{
		return $this->sort_genre;
	}
	public function getSortSeries()
	{
		return $this->sort_series;
	}
	public function getSortPlatform()
	{
		return $this->sort_platform;
	}
	public function getSortYear()
	{
		return $this->sort_year;
	}
	
	private $sort_name;
	private $sort_id;
	private $sort_genre;
	private $sort_series;
	private $sort_platform;
	private $sort_year;
};

	include_once('mysql.php');
	
	$main_html = new MainHtml();
	
	$cache_file = 'cache/';
	$cache_file .= '_'.htmlspecialchars($main_html->getSortName());
	$cache_file .= '_'.htmlspecialchars($main_html->getSortId());
	$cache_file .= '_'.htmlspecialchars($main_html->getSortGenre());
	$cache_file .= '_'.htmlspecialchars($main_html->getSortSeries());
	$cache_file .= '_'.htmlspecialchars($main_html->getSortPlatform());
	$cache_file .= '_'.htmlspecialchars($main_html->getSortYear());
	$cache_file .= '_.cache';
	
	echo $cache_file;

	if(file_exists($cache_file))
	{
		$c = file_get_contents($cache_file);
		echo $c;
		exit;
	}

	ob_start();
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title>XenosV Games</title>
		<style>
			article, aside, details, figcaption, figure, footer,header,
			hgroup, menu, nav, section { display: block; }
		</style>
	</head>
	<body>
	<?php
		
		$games_base = new GamesBase('localhost', 'XenosV', '5uy$_H3X%a?ykwE', 'mygames');
		
		$games_base->ProcessingRequest($main_html->getSortGenre());

		while($game_details = $games_base->getNextGameDetail())
		{
			printf("<br/>%s %s %s", $game_details[0], $game_details[1], $game_details[2]);
			
		}
	?>
	</body>
</html>


<?php
$c = ob_get_contents();
file_put_contents($cache_file, $c);
?>
