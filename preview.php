<?php
	header("Content-type: image/jpeg");
	$source=$_GET['src'];
	$width = $_GET['resolution'];

	$size = getimagesize($source);
	$format = strtolower(substr($size['mime'], strpos($size['mime'], '/')+1));
	$icfunc = "imagecreatefrom".$format;
	$img = $icfunc($source);
	
	$img_new = imagescale($img, $width, -1, IMG_BICUBIC);
	imagejpeg($img_new, NULL, 80);
	//imagejpeg($img_new, $source.'.tmpimg', 90);

	imagedestroy($img_new);
	imagedestroy($img);
?>