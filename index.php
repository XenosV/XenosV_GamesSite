<?php

define("GAME_PATH", "files/games/");
define("FIRST_LOAD", 20);
include_once('mysql.php');

function NameForFile($name)
{
	$name = str_replace("&", "_", $name);
	return str_replace(array(":", "#", "%", "/", "*", "\""), "", $name);
}

function EscapingCharacters($name)
{
	return str_replace("'", "%39", $name);
}

class MainHtml
{
	private function CreateMainGalery()
	{
		$vis = $this->getVisible();
		$genre_select = $this->getSortGenre();
		$platform_select = $this->getSortPlatform();
		echo "<div class='SiteMain'>";
			// Filters and Sorters
			echo "<div class='SortersContaner'>";
				echo "<div class=\"Btn\" id=\"BtnPlatforms\">Платформы <i class='down'></i></div>";
				echo "<div class=\"Btn\" id=\"BtnGenres\">Жанры <i class='down'></i></div>";
				echo "<div class=\"Btn\">Годы <i class='down'></i></div>";
				echo "<div class=\"Btn\">Рейтинги <i class='down'></i></div>";
				echo "<div class=\"Btn\">Прохождение <i class='down'></i></div>";
			echo "</div>";

			$json_sorter = "{\"pl\":[";
			while ($platform_detail = $this->getNextPlatform())
			{
				$platform_name_short = $platform_detail['Platforms'];
				$platform_name = $platform_detail['Name'];
				$platform_color = $platform_detail['Color'];
				$platform_id = $platform_detail['ID'];
					
				// Add platform's colors to global buffer
				$this->AddPlatformColor($platform_name_short, $platform_color);

				$json_sorter .= "{\"v\":$vis,\"p\":$platform_id,\"g\":$genre_select,\"name\":\"$platform_name_short\",\"bg\":\"#$platform_color\"},";
			}
			$json_sorter = rtrim($json_sorter, ",");
			$json_sorter .= "],\"gn\":[";
			while ($genre_detail = $this->getNextGenre())
			{
				$genre_name_short = EscapingCharacters($genre_detail['Genres']);
				$genre_name = EscapingCharacters($genre_detail['Name']);
				$genre_color = $genre_detail['Color'];
				$genre_id = $genre_detail['ID'];

				$json_sorter .= "{\"v\":$vis,\"p\":$platform_select,\"g\":$genre_id,\"name\":\"$genre_name_short\",\"bg\":\"#$genre_color\"},";
			}
			$json_sorter = rtrim($json_sorter, ",");
			$json_sorter .= "]}";

			echo "<div class='ButtonContaner' data-src='$json_sorter' id=\"btnShow\"></div>";

			if ($this->sort_id != 0) // Game selected
			{
				$selected_game_name = $this->getSelectedGameName();
				echo "<div>";
					echo "<p>$selected_game_name</p>";
				echo "</div>";
				echo "<div>";
					$video_path = GAME_PATH.NameForFile($selected_game_name)."_".$this->game_info['ID'].'/video/';
					$video_files = scandir($video_path);
					
					if (count($video_files) > 2)
					{
						for ($i = 0; $i < count($video_files); $i++)
						{
							if (($video_files[$i] != ".") && ($video_files[$i] != ".."))
							{
								$video_width = mb_substr($video_files[$i], 3, -4);
								if (!is_numeric($video_width))
									$video_width = "960";
								
								$video_name = $video_path.$video_files[$i].'#t=0.0';
								echo "<div style=\"display:block; margin: 0 auto; max-width:".$video_width."px;\">";
								echo "<video style=\"display:block; margin: 0 auto; width:100%;\" controls=\"controls\" preload=\"auto\"><source src=\"$video_name\"></video>";
								echo "</div>";
							}
						}
					}
				echo "</div>";
				
				echo "<div class='GamePicture'>";					
					$pic_path = GAME_PATH.NameForFile($selected_game_name)."_".$this->game_info['ID'].'/pic/';
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
								echo "<div class='ImagesForGalery'><img src=\"$pic_name_small\" width='$size[0]' height = $size[1] /></div>";
							}
						}
					}
				echo "</div>";
			}
			else //////////////////////////// No Game selected //////////////////////////////////
			{
				if ($this->getSortPlatform() > 1)
				{
					echo "<div class='PlatformView'>";
						$platform_name = $this->getSortPlatformNameFull();
						
						echo "<div>";
							echo "<a href=''><img src=\"files/img/platforms_jpg/".$platform_name.".jpg\" height='250px'></img></a>";
						echo "</div>";
						echo "<div class=PlatformAbout>";
							echo "<p>Платформа: $platform_name</p>";
							$str = $this->getSortPlatformDeveloper();
							echo "<p>Разработчик: $str </p>";
							$str = $this->getSortPlatformYears();
							echo "<p>Годы: $str </p>";
							$str = $this->getSortPlatformGeneration();
							echo "<p>Поколение: $str </p>";
						echo "</div>";
					echo "</div>";
				}
				
				$this->games_base->SelectGameBase($this->getSortGenre(), $this->getSortPlatform());
				
				// Games
				echo "<div class='SiteView'>";
					$index = 0;
					while($game_details = $this->games_base->getNextGameDetail())
					{
						$name = $game_details['Name'];
						$id = $game_details['ID'];
						$platforms = $game_details['plt_cc'];
						$kw_platforms = preg_split("[;]", $platforms);
						$genres = $game_details['gen_cc'];
						$year = $game_details['Year'];
						$complete = $game_details['Completed'];
						$rating = $game_details['Rating'];
						
						if ($index >= FIRST_LOAD)
						{
							$style = 0;
							if ($this->sort_platform > 1)
							{
								if (!file_exists(GAME_PATH.NameForFile($name)."_".$id."/cover_".iconv("UTF-8", "ASCII//TRANSLIT", $this->getSortPlatformName()).".jpg"))
								{
									$style = 1;
								}
								else
								{
									$style = 2;
								}
							}
							// id - game id
							// st - game cover style: 0 - cover.jpg (no margin); 1 - cover_platform_digital + cover.jpg (margin-top and margin-bottom it platform.css); 2 - cover_platform.jpg (no margin)
							// pl - array of game platforms: p - platform name, c - color name
							// gn - game ganres
							// cm - game complete
							// rt - game rating
							// yr - game year
							$json_game = "{\"id\":$id,\"st\":$style,\"pl\":[";
								for ($i = 0; $i < count($kw_platforms); $i++)
								{
									$color = $this->getPlatformColor($kw_platforms[$i]);
									$pl = $kw_platforms[$i];
										$json_game .= "{\"p\":\"$pl\",\"c\":\"#$color\"},";
								}
								$json_game = rtrim($json_game, ",");
								$gn = EscapingCharacters($genres);
								$json_game .= "],\"gn\":\"$gn\",\"cm\":$complete,\"rt\":$rating,\"yr\":$year}";
							echo "<div class='GV' data-src='$json_game'>$name</div>";
						}
						else
						{
							if (($complete > 1) || ($this->visible))
							{
								echo "<div class='GV' data-src=0 style=''>";
								$index++;
							}
							else
								echo "<div class='GV' data-src=0 style='display:none;'>";
								$cover_name = "/cover.jpg";
								$style = "style=\"margin-top:0px;margin-bottom:0px;\"";
								if ($this->sort_platform > 1)
								{
									if (!file_exists(GAME_PATH.NameForFile($name)."_".$id."/cover_".iconv("UTF-8", "ASCII//TRANSLIT", $this->getSortPlatformName()).".jpg"))
									{
										$icon_path_digital = "files/img/cover_digital/cover_".iconv("UTF-8", "ASCII//TRANSLIT", $this->getSortPlatformName()).".png";
										$style = "style=\"height:205px;\"";
										echo "<a href='index.php?visible=$vis&id=$id'><img class='GameViewImageDigital' src=\"$icon_path_digital\"></img></a>";
									}
									else
									{
										$cover_name = "/cover_".iconv("UTF-8", "ASCII//TRANSLIT", $this->getSortPlatformName()).".jpg";
									}
								}

								$icon_path = GAME_PATH.NameForFile($name)."_".$id.$cover_name;
								echo "<a href = 'index.php?visible=$vis&id=$id'><img class='GameViewImage' $style src=\"$icon_path\" loading=\"lazy\"></img></a>";
								
								echo "<a href = 'index.php?visible=$vis&id=$id' class='GameViewName'>$name</a>";
								echo "<div class='GameViewPlatformsContainer'>";
								foreach ($kw_platforms as $key => $value)
								{
									$color = $this->getPlatformColor($value);
									echo "<p class='GameViewPlatforms' style='background: #$color'>$value</p>";
								}
								echo "</div>";
								echo "<p class='GameViewGenres'>$genres</p>";
								$rating = 'files\img\rating\rating'.$game_details['Rating'].'.png';
								$complete = 'files\img\completed\completed'.$game_details['Completed'].'.png';
								echo "<div class=GameViewSortContainer>";
									echo "<img src=\"$complete\" width=20px height=20px/><img src=\"$rating\" width=100px height=20px style='margin: 0 0 0 30px'/><p class='GameViewYear'>$year</p>";
								echo "</div>";
							echo "</div>";
						}
					}
				echo "</div>"; // <div class='SiteView'>
			}
		echo "</div>"; // <div class='SiteMain'>
	}
	
	public function MainHtml()
	{
		ini_set('session.cookie_samesite', 'None');
		session_start();
		
		$this->games_base = new GamesBase('localhost', 'XenosV', '5uy$_H3X%a?ykwE', 'mygames');
		
		if (isset($_GET['id']) && is_numeric($_GET['id']))
			$this->sort_id = $_GET['id'];
		else
			$this->sort_id = 0;
		
		if (isset($_GET['genre']) && is_numeric($_GET['genre']))
			$this->sort_genre = $_GET['genre'];
		else
			$this->sort_genre = 0;
		
		if (isset($_GET['platform']) && is_numeric($_GET['platform']))
		{
			$this->sort_platform = $_GET['platform'];
			$this->sort_platform_info = $this->games_base->getPlatformInfo($this->sort_platform);
		}
		else
			$this->sort_platform = 0;
		
		if (isset($_GET['visible']) && is_numeric($_GET['visible']))
			$this->visible = $_GET['visible'];
		else
			$this->visible = 0;
		
		if ($this->sort_id != 0)
			$this->game_info = $this->games_base->getGameInfo($this->sort_id);
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
	
	private function getNextPlatform()
	{
		return $this->games_base->getNextPlatform();
	}
	private function getNextGenre()
	{
		return $this->games_base->getNextGenre();
	}
	private function getNextYear()
	{
		return $this->games_base->getNextYear();
	}
	private function getSortPlatformName()
	{
		return $this->sort_platform_info['Platforms'];
	}
	private function getSortPlatformNameFull()
	{
		return $this->sort_platform_info['Name'];
	}
	private function getSortPlatformDeveloper()
	{
		return $this->sort_platform_info['Developer'];
	}
	private function getSortPlatformYears()
	{
		return $this->sort_platform_info['Year'];
	}
	private function getSortPlatformGeneration()
	{
		return $this->sort_platform_info['Generation'];
	}
	private function AddPlatformColor($name, $color)
	{
		$this->platform_color_array[$name] = $color;
	}
	private function getPlatformColor($name)
	{
		return $this->platform_color_array[$name];
	}
	private function getVisible()
	{
		return $this->visible;
	}
	private function getSelectedGameName()
	{
		return $this->game_info['Name'];
	}
	
	public function CreateHead()
	{
		echo '<head><meta charset="utf-8"/>';
		
		if ($this->sort_id != 0)
			$title_name = $this->getSelectedGameName();
		else if ($this->sort_platform > 1)
			$title_name = $this->sort_platform_info['Name'];
		else
			$title_name = "XenosV Games";
			
		echo "<title>$title_name</title>";
		
		echo '<link rel="preconnect" href="https://fonts.gstatic.com">';
		echo '<link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">';
		echo '<link rel="stylesheet" type="text/css" href="files/css/site.css">';
		echo '<style> article, aside, details, figcaption, figure, footer,header, hgroup, menu, nav, section { display: block; } </style>';
		$css_file = 'files/css/'.$this->sort_platform_info['Platforms'].'.css';
		$css_file = iconv("UTF-8", "ASCII//TRANSLIT", $css_file);
		if (($this->sort_platform > 1) && (file_exists($css_file)))
		{
			echo "<link rel='stylesheet' type='text/css' href='".$css_file."'>";
		}
		echo "<script src='files/js/site.js'></script>";
		echo"<script type=\"text/javascript\">InitVar($this->visible);</script>";
		echo '</head>';
	}
	
	public function CreateBody()
	{
		$vis = $this->getVisible();
		$pl_name = iconv("UTF-8", "ASCII//TRANSLIT", $this->getSortPlatformName());
		echo "<body onload='GenerateGameView(\"$pl_name\")'>";	
			echo "<div class='SiteBase'>";
				$this->CreateMainGalery();
			echo"</div>";
		echo "</body>";
	}
	
	private $platform_color_array;
	private $sort_name;
	private $sort_id;
	private $sort_genre;
	private $sort_series;
	private $sort_platform;
	private $sort_platform_info;
	private $games_base;
	private $visible;
	private $game_info;
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

echo "<!DOCTYPE html><html>";
	$main_html->CreateHead();
	$main_html->CreateBody();
echo "</html>";
?>
<script>
document.getElementById ("BtnPlatforms").addEventListener ("click", ShowSortPlatforms, false);
document.getElementById ("BtnGenres").addEventListener ("click", ShowSortGenres, false);
</script>


<?php
//$c = ob_get_contents();
//file_put_contents($cache_file, $c);
?>
