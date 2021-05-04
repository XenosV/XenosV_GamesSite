<?php

define("GAME_PATH", "files/games/");
define("FIRST_LOAD", 20);
include_once('mysql.php');

error_reporting(E_ERROR | E_PARSE);

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
				$platform_generation = $platform_detail['Generation'];
					
				// Add platform's colors to global buffer
				$this->AddPlatformColor($platform_name_short, $platform_color);
				$this->AddPlatformId($platform_name_short, $platform_id);

				$json_sorter .= "{\"v\":$vis,\"p\":$platform_id,\"g\":$genre_select,\"name\":\"$platform_name_short\",\"bg\":\"#$platform_color\",\"gn\":$platform_generation},";
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
				echo "<div class='GameView'>";
					echo "<div>";
						echo "<p class='Name'>$selected_game_name</p>";
					echo "</div>";
					echo "<div class='GameDetail'>";
						echo "<div class='CoverContaner'>";
							$icon_path = GAME_PATH.NameForFile($selected_game_name).'_'.$this->game_info['ID'].'/cover.jpg';
							echo "<img class='Cover' src=\"$icon_path\"></img>";
						echo "</div>";
						echo "<div class='Description'>";
							echo "<div style='display:flex;justify-content:space-between;height:100%;'>";
								echo "<div style='display:flex;flex-direction:column;justify-content:space-between;'>";
									$text = $this->game_info['Developer'];
									echo "<p class='Text'><b>Разработчик:</b> &nbsp; $text</p>";
									$text = $this->game_info['plt_cc'];
									$kw_platforms = preg_split("[;]", $text);
									echo "<div style='display:flex;align-items:center;flex-wrap:wrap;'>";
									echo "<p class='Text'><b>Платформы:</b> &nbsp;</p>";
									foreach ($kw_platforms as $key => $value)
									{
										$color = $this->getPlatformColor($value);
										$platform_id = $this->getPlatformId($value);
										echo "<a href='index.php?visible=$vis&platform=$platform_id' class='GameViewPlatforms_tst' style='background:#$color'><p style='margin:0'>$value</p></a>";
									}
									echo "</div>";
									$text = $this->game_info['gen_cc'];
									echo "<p class='Text'><b>Жанры:</b> &nbsp; $text</p>";
									$text = $this->game_info['Series'];
									echo "<p class='Text'><b>Серия:</b> &nbsp; $text</p>";
									$text = $this->game_info['Year'];
									echo "<p class='Text'><b>Год выпуска:</b> &nbsp; $text</p>";
									$text = 'files\img\rating\rating'.$this->game_info['Rating'].'.png';
									echo "<p class='Text'><b>Рейтинг:</b> &nbsp; <img src=\"$text\"></img></p>";
								echo "</div>";
							echo "</div>";
						echo "</div>";
						echo "<div class='Completed'>";
							$text = 'files\img\completed\large\completed'.$this->game_info['Completed'].'.png';
							echo "<img src=\"$text\" style='width:120px;'></img>";
						echo "</div>";
					echo "</div>";
					
					// Gallery TEST
					echo "<div>";
						$gallery_path = GAME_PATH.NameForFile($selected_game_name)."_".$this->game_info['ID'].'/media/';
						$gallery_files = scandir($gallery_path);
						$gallery_index = 0;
						foreach($gallery_files as &$folder)
						{
							if (($folder != ".") && ($folder != ".."))
							{
								echo "<div>";
									echo '<p style="margin:10px;text-align:center;font-weight:700;font-size:20px;">'.substr(str_replace("__", " / ", $folder), 3).'</p>';
									$gallery_images_path = GAME_PATH.NameForFile($selected_game_name)."_".$this->game_info['ID'].'/media/'.$folder.'/';
									
									// Find videos
									$videos = preg_grep('~\.(mp4)$~', scandir($gallery_images_path));
									// Find images
									$images = preg_grep('~\.(jpeg|jpg|png)$~', scandir($gallery_images_path));
									$size = getimagesize($gallery_images_path.current($images));
									if ($size[0] < 2200)
									{
										$size[0] /= 2;
										$size[1] /= 2;
									}
									else
									{
										$size[0] /= 4;
										$size[1] /= 4;
									}
									
									$json_gallery = "";
									foreach($videos as &$video)
									{
										$video_name = $gallery_images_path.$video.'#t=0.0';
										$json_gallery = "{\"gallery\":[{\"type\":\"video\",\"src\":\"$video_name\"}";
									}
									foreach($images as &$image)
									{
										$image_name = $gallery_images_path.$image;
										$json_gallery .= ",{\"type\":\"image\",\"src\":\"$image_name\"}";
									}
									if ($json_gallery != "")
										$json_gallery .= "]}";
								
									$video_name = $gallery_images_path.current($videos).'#t=0.0';
									echo "<div id=\"gallery".$gallery_index."\" style='display:flex;align-items:center;justify-content:center;width:100%;' data-index='0' data-src='$json_gallery'>";
										echo "<div style='margin-left:auto;margin-right:10px;'>";
											echo "<div style='width:30px;height:25px;background:#A0A0A0' onclick=\"SetGallery(this, -1)\"></div>";
										echo "</div>";
										echo "<div style=\"max-width:".$size[0]."px;\">";
											echo "<video style=\"width:100%;\" controls preload=\"auto\" autoplay muted><source src=\"$video_name\"></source></video>";
										echo "</div>";
										echo "<div style='margin-left:10px;margin-right:auto;'>";
											echo "<div style='width:30px;height:25px;background:#A0A0A0' onclick=\"SetGallery(this, 1)\"></div>";
										echo "</div>";
										echo "<script type=\"text/javascript\">GalleryPreload($gallery_index);</script>";
									echo "</div>";
									$gallery_index++;
								echo "</div>";
							}
						}
					echo "</div>";
				echo "</div>";
			}
			else //////////////////////////// No Game selected //////////////////////////////////
			{
				if ($this->getSortPlatform() > 1)
				{
					echo "<div class='PlatformView'>";
						$platform_name = $this->getSortPlatformNameFull();
						
						echo "<div>";
							echo "<a href=''><img src=\"files/img/platforms_jpg/".NameForFile($platform_name).".jpg\" height='250px'></img></a>";
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
				$this->DrawGamesView();
			}
		echo "</div>"; // <div class='SiteMain'>
	}
	
	public function DrawGamesView()
	{
		$vis = $this->getVisible();
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
							$platform_id = $this->getPlatformId($kw_platforms[$i]);
							$pl = $kw_platforms[$i];
								$json_game .= "{\"p\":\"$pl\",\"c\":\"#$color\",\"id\":$platform_id},";
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
							$platform_id = $this->getPlatformId($value);
							echo "<a href='index.php?visible=$vis&platform=$platform_id' class='GameViewPlatforms' style='background:#$color'><p style='margin:0'>$value</p></a>";
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
	private function AddPlatformId($name, $id)
	{
		$this->platform_id_array[$name] = $id;
	}
	private function getPlatformId($name)
	{
		return $this->platform_id_array[$name];
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
		echo "<script type=\"text/javascript\">InitVar($this->visible);</script>";
		echo "<script type=\"text/javascript\">
			function GalleryPreload(id)
			{
				var gallery = document.getElementById(\"gallery\" + id);
				if (gallery.dataset.src != '')
				{
					var data = JSON.parse(gallery.dataset.src);
					var img_next = new Image();
					img_next.src = data.gallery[1].src;
					var img_pre = new Image();
					img_pre.src = data.gallery[data.gallery.length - 1].src;
				}
			}
			</script>";
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
	private $platform_id_array;
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
document.getElementById("BtnPlatforms").addEventListener("click", ShowSortPlatforms, false);
document.getElementById("BtnGenres").addEventListener("click", ShowSortGenres, false);

function SetGallery(button, increment)
{
	var gallery = button.parentNode.parentNode.children[1];
	var data = JSON.parse(button.parentNode.parentNode.dataset.src);
	var index = (Number(button.parentNode.parentNode.dataset.index) + Number(increment)) % data.gallery.length;
	button.parentNode.parentNode.dataset.index = index;
	
	// TO DO ADD PRELOAD NEXT IMAGE !!!!!
	/////////////////////////////////////
	
	var elm = null;
	if (data.gallery[index].type == "video")
	{
		elm = document.createElement("video");
		elm.setAttribute("style", "width:100%");
		elm.setAttribute("autoplay", "autoplay");
		elm.muted = true;
		elm.setAttribute("controls", "controls");
		
		var elm2 = document.createElement("source");
		elm2.setAttribute("src", data.gallery[index].src);
		elm.appendChild(elm2);
	}
	else
	{
		elm = document.createElement("img");
		elm.setAttribute("style", "width:100%");
		elm.setAttribute("src", data.gallery[index].src);
	}
	if (elm)
	{
		gallery.appendChild(elm);
		gallery.removeChild(gallery.firstChild);
	}
}
</script>


<?php
//$c = ob_get_contents();
//file_put_contents($cache_file, $c);
?>
