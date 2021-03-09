<?php

define("GAME_PATH", "files/games/");
include_once('mysql.php');

function NameForFile($name)
{
	return str_replace(":", "", $name);
}

class MainHtml
{
	public function CreateMainGalery()
	{
		echo "<div class='SiteMain'>";
			if ($this->sort_id != 0)
			{
				$game_info = $this->games_base->getGameInfo($this->sort_id);
				
				echo "<div>";
					$video_path = GAME_PATH.NameForFile($game_info['Name'])."_".$game_info['ID'].'/video/';
					$video_files = scandir($video_path);
					
					if (count($video_files) > 2)
					{
						for ($i = 0; $i < count($video_files); $i++)
						{
							if (($video_files[$i] != ".") && ($video_files[$i] != ".."))
							{
								$video_name = $video_path.$video_files[$i].'#t=0.0';
								echo "<video style=\"display:block; margin: 0 auto; width: 1080px;\" controls=\"controls\" preload=\"auto\"><source src=\"$video_name\"></video>";
							}
						}
					}
				echo "</div>";
				
				echo "<div class='GamePicture'>";					
					$pic_path = GAME_PATH.NameForFile($game_info['Name'])."_".$game_info['ID'].'/pic/';
					$pic_files = scandir($pic_path);
					
					if (count($pic_files) > 3)
					{
						for ($i = 0; $i < count($pic_files); $i++)
						{
							if (($pic_files[$i] != ".") && ($pic_files[$i] != "..") && ($pic_files[$i] != "small"))
							{
								$pic_name = $pic_path.$pic_files[$i];
								$pic_name_small = $pic_path.'small/'.$pic_files[$i];
								$size = getimagesize($pic_name_small);
								$size[0] /= 2;
								$size[1] /= 2;
								echo "<div class='ImagesForGalery' width='$size[0]' height = $size[1]><img src=\"$pic_name_small\" width='$size[0]' height = $size[1] /></div>";
							}
						}
					}
				echo "</div>";
			}
			else
			{
				$this->games_base->SelectGameBase($this->getSortGenre(), $this->getSortPlatform());
					
				while($game_details = $this->games_base->getNextGameDetail())
				{
					echo "<div class='GameView'>";
						$name = $game_details['Name'];
						$id = $game_details['ID'];
						$icon_path = GAME_PATH.NameForFile($game_details['Name'])."_".$game_details['ID']."/cover.png";
						$platforms = $game_details['plt_cc'];
						$kw_platforms = preg_split("[/]", $platforms);
						echo "<a href = 'index.php?id=$id' class='GameImage'><img class='GameImage' src=\"$icon_path\" data-src='$icon_path'></img></a>";
						echo "<a href = 'index.php?id=$id' class='GameName'>$name</a>";
						echo "<div class='PlatformsViewContainer'>";
						foreach ($kw_platforms as $key => $value)
						{
							$color = $this->getPlatformColor($value);
							echo "<p class='PlatformsView' style='background: #$color'>$value</p>";
						}
						echo "</div>";
					echo "</div>";
				}
			}
		echo "</div>"; // <div class='SiteMain'>
	}
	
	public function MainHtml()
	{
		if (isset($_GET['id']))
			$this->sort_id = $_GET['id'];
		else
			$this->sort_id = 0;
		
		if (isset($_GET['genre']))
			$this->sort_genre = $_GET['genre'];
		else
			$this->sort_genre = 0;
		
		if (isset($_GET['platform']))
			$this->sort_platform = $_GET['platform'];
		else
			$this->sort_platform = 0;
		
		$this->games_base = new GamesBase('localhost', 'XenosV', '5uy$_H3X%a?ykwE', 'mygames');
	}
	
	public function getSortId(){ return $this->sort_id; }
	public function getSortIdSelf(){ return htmlspecialchars($this->sort_id); }
	public function getSortIdFile(){ return NameForFile($this->getSortIdSelf()); }
	
	public function getSortGenre(){ return $this->sort_genre; }
	public function getSortGenreSelf(){ return $this->sort_genre; }
	public function getSortGenreFile(){ return $this->sort_genre; }
	
	public function getSortPlatform(){ return $this->sort_platform; }
	public function getSortPlatformSelf(){ return $this->sort_platform; }
	public function getSortPlatformFile(){ return $this->sort_platform; }
	
	// tmp //
	public function getNextPlatform()
	{
		return $this->games_base->getNextPlatform();
	}
	public function getNextGenre()
	{
		return $this->games_base->getNextGenre();
	}
	public function getNextYear()
	{
		return $this->games_base->getNextYear();
	}
	/////////
	
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
	private $games_base;
};
	
$main_html = new MainHtml();
	
$cache_file = 'cache/';
	$cache_file .= '_'.htmlspecialchars($main_html->getSortIdFile());
	$cache_file .= '_'.htmlspecialchars($main_html->getSortGenreFile());
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
		
		echo "<div class='SiteBase'>";
			echo "<div class='LeftSorter'>";
				echo "<div>";
					echo "<p class='TextSort'>Сортировка по платформам</p>";
				echo "</div>";
				echo "<div class='ButtonContaner'>";
					while ($platform_detail = $main_html->getNextPlatform())
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
					while ($genre_details = $main_html->getNextGenre())
					{
						$genre_name_short = $genre_details['Genres'];
						$genre_name = $genre_details['Name'];
						$genre_color = $genre_details['Color'];
						$genre_id = $genre_details['ID'];;
						
						echo "<a href='index.php?genre=$genre_id' class='ButtonSort' style='background: #$genre_color;'>$genre_name_short</a>";
					}
				echo "</div>";
			echo "</div>"; // <div class='LeftSorter'>
			
			$main_html->CreateMainGalery();
			
			echo "<div class='RightSorter'>";
				echo "<div>";
					echo "<p class='TextSort'>Сортировка по годам</p>";
				echo "</div>";
				echo "<div class='ButtonContaner'>";
					echo "<a class='ButtonSort'>All</a>";
					while ($year_detail = $main_html->getNextYear())
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
