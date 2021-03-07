<?php

define("GAME_PATH", "files/games/");

function NameForFile($name)
{
	return str_replace(":", "", $name);
}

class MainHtml
{
	public function MainHtml()
	{
		if (isset($_GET['name']))
			$this->sort_name = $_GET['name'];
		else
			$this->sort_name = 'none';
		
		if (isset($_GET['id']))
			$this->sort_id = $_GET['id'];
		else
			$this->sort_id = -1;
		
		if (isset($_GET['genre']))
			$this->sort_genre = $_GET['genre'];
		else
			$this->sort_genre = 0;
		
		if (isset($_GET['series']))
			$this->sort_series = $_GET['series'];
		else
			$this->sort_series = 'none';
		
		if (isset($_GET['platform']))
			$this->sort_platform = $_GET['platform'];
		else
			$this->sort_platform = 0;
		
		if (isset($_GET['yearb']))
			$this->sort_year_begin = $_GET['yearb'];
		else
			$this->sort_year_begin = -1;
		if (isset($_GET['yeare']))
			$this->sort_year_begin = $_GET['yeare'];
		else
			$this->sort_year_end = -1;
	}
	
	public function getSortName(){ return $this->sort_name; }
	public function getSortNameSelf(){ return htmlspecialchars($this->sort_name); }
	public function getSortNameFile(){ return NameForFile($this->getSortNameSelf()); }
	
	public function getSortId(){ return $this->sort_id; }
	public function getSortIdSelf(){ return htmlspecialchars($this->sort_id); }
	public function getSortIdFile(){ return NameForFile($this->getSortIdSelf()); }
	
	public function getSortGenre(){ return $this->sort_genre; }
	public function getSortGenreSelf(){ return $this->sort_genre; }
	public function getSortGenreFile(){ return $this->sort_genre; }
	
	public function getSortSeries(){ return $this->sort_series; }
	public function getSortSeriesSelf(){ return htmlspecialchars($this->sort_series); }
	public function getSortSeriesFile(){ return NameForFile($this->getSortSeriesSelf()); }
	
	public function getSortPlatform(){ return $this->sort_platform; }
	public function getSortPlatformSelf(){ return $this->sort_platform; }
	public function getSortPlatformFile(){ return $this->sort_platform; }
	
	public function getSortYearBegin(){ return $this->sort_year_begin; }
	public function getSortYearBeginSelf(){ return htmlspecialchars($this->sort_year_begin); }
	public function getSortYearBeginFile(){ return NameForFile($this->getSortYearBeginSelf()); }
	
	public function getSortYearEnd(){ return $this->sort_year_end; }
	public function getSortYearEndSelf(){ return htmlspecialchars($this->sort_year_end); }
	public function getSortYearEndFile(){ return NameForFile($this->getSortYearEndSelf()); }
	
	private $sort_name;
	private $sort_id;
	private $sort_genre;
	private $sort_series;
	private $sort_platform;
	private $sort_year_begin;
	private $sort_year_end;
};

include_once('mysql.php');
	
$main_html = new MainHtml();
	
$cache_file = 'cache/';
	$cache_file .= '_'.htmlspecialchars($main_html->getSortNameFile());
	$cache_file .= '_'.htmlspecialchars($main_html->getSortIdFile());
	$cache_file .= '_'.htmlspecialchars($main_html->getSortGenreFile());
	$cache_file .= '_'.htmlspecialchars($main_html->getSortSeriesFile());
	$cache_file .= '_'.htmlspecialchars($main_html->getSortPlatformFile());
	$cache_file .= '_'.htmlspecialchars($main_html->getSortYearBeginFile());
	$cache_file .= '_'.htmlspecialchars($main_html->getSortYearEndFile());
	$cache_file .= '_.cache';

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
		<link rel="stylesheet" type="text/css" href="files/css/site.css">
		<style>
			article, aside, details, figcaption, figure, footer,header,
			hgroup, menu, nav, section { display: block; }
		</style>
	</head>
	<body>
	<?php
		echo "<div class = 'site_main'>";
			$games_base = new GamesBase('localhost', 'XenosV', '5uy$_H3X%a?ykwE', 'mygames');
			
			$games_base->SelectGameBase($main_html->getSortGenre(), $main_html->getSortPlatform());
			
			while($game_details = $games_base->getNextGameDetail())
			{
				echo "<div class = 'game_view'>";
					$name = NameForFile($game_details['Name']);
					$icon_path = GAME_PATH.$name."_".$game_details['ID']."/cover.png";
					echo "<img class = 'game_image' src = \"$icon_path\" data-src = '$icon_path'></img>";
					echo "<a href = 'index.php' class = 'game_name'>$name</a>";
				echo "</div>";
			}
		echo "</div>"; // class='site_main'
	?>
	</body>
</html>


<?php
//$c = ob_get_contents();
//file_put_contents($cache_file, $c);
?>
