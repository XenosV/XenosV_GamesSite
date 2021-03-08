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
	
	public function AddPlatformColor($name, $color)
	{
		$this->platform_color_array[$name] = $color;
	}
	public function getPlatformColor($name)
	{
		return $this->platform_color_array[$name];
	}
	
	private $platform_color_array;
	private $sort_name;
	private $sort_id;
	private $sort_genre;
	private $sort_series;
	private $sort_platform;
};

include_once('mysql.php');
	
$main_html = new MainHtml();
	
$cache_file = 'cache/';
	$cache_file .= '_'.htmlspecialchars($main_html->getSortNameFile());
	$cache_file .= '_'.htmlspecialchars($main_html->getSortIdFile());
	$cache_file .= '_'.htmlspecialchars($main_html->getSortGenreFile());
	$cache_file .= '_'.htmlspecialchars($main_html->getSortSeriesFile());
	$cache_file .= '_'.htmlspecialchars($main_html->getSortPlatformFile());
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
		<link rel="preconnect" href="https://fonts.gstatic.com">
		<link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">
		<link rel="stylesheet" type="text/css" href="files/css/site.css">
		<style>
			article, aside, details, figcaption, figure, footer,header,
			hgroup, menu, nav, section { display: block; }
		</style>
	</head>
	<body>
	<?php
	
		$games_base = new GamesBase('localhost', 'XenosV', '5uy$_H3X%a?ykwE', 'mygames');
		
		echo "<div class='SiteBase'>";
			echo "<div class='LeftSorter'>";
				echo "<div>";
					echo "<p class='TextSort'>Сортировка по платформам</p>";
				echo "</div>";
				echo "<div class='ButtonContaner'>";
					while ($platform_detail = $games_base->getNextPlatform())
					{
						$platform_name_short = $platform_detail['Platforms'];
						$platform_name = $platform_detail['Name'];
						$platform_color = $platform_detail['Color'];
						$platform_id = $platform_detail['ID'];;
						
						$main_html->AddPlatformColor($platform_name_short, $platform_color);
						echo "<a href='index.php?platform=$platform_id' class='ButtonSort' style='background: #$platform_color;'>$platform_name_short</a>";
					}
				echo "</div>";
				echo "<div>";
					echo "<p class='TextSort'>Сортировка по жанрам</p>";
				echo "</div>";
				echo "<div class='ButtonContaner'>";
					while ($genre_details = $games_base->getNextGenre())
					{
						$genre_name_short = $genre_details['Genres'];
						$genre_name = $genre_details['Name'];
						$genre_color = $genre_details['Color'];
						$genre_id = $genre_details['ID'];;
						
						echo "<a href='index.php?genre=$genre_id' class='ButtonSort' style='background: #$genre_color;'>$genre_name_short</a>";
					}
				echo "</div>";
			echo "</div>"; // <div class='LeftSorter'>
			
			echo "<div class='SiteMain'>";
				$games_base->SelectGameBase($main_html->getSortGenre(), $main_html->getSortPlatform());
				
				while($game_details = $games_base->getNextGameDetail())
				{
					echo "<div class='GameView'>";
						$name = NameForFile($game_details['Name']);
						$icon_path = GAME_PATH.$name."_".$game_details['ID']."/cover.png";
						$platforms = $game_details['plt_cc'];
						$kw_platforms = preg_split("[/]", $platforms);
						echo "<img class='GameImage' src = \"$icon_path\" data-src = '$icon_path'></img>";
						echo "<a href = 'index.php' class='GameName'>$name</a>";
						echo "<div class='PlatformsViewContainer'>";
						foreach ($kw_platforms as $key => $value)
						{
							$color = $main_html->getPlatformColor($value);
							echo "<p class='PlatformsView' style='background: #$color'>$value</p>";
						}
						echo "</div>";
					echo "</div>";
				}
			echo "</div>"; // <div class='SiteMain'>
			
			echo "<div class='RightSorter'>";
				echo "<div>";
					echo "<p class='TextSort'>Сортировка по годам</p>";
				echo "</div>";
				echo "<div class='ButtonContaner'>";
					echo "<a class='ButtonSort'>All</a>";
					while ($year_detail = $games_base->getNextYear())
					{
						$year_name = $year_detail['Year'];
						
						echo "<a class='ButtonSort'>$year_name</a>";
					}
				echo "</div>";
			echo "</div>"; // <div class='RightSorter'>
		echo"</div>";
	?>
	</body>
</html>


<?php
//$c = ob_get_contents();
//file_put_contents($cache_file, $c);
?>
