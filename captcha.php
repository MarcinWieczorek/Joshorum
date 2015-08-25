<?
#CAPTCHA.php

#zaczynanie sesji      
session_start();

#znaki z których ma byæ losowanie
$pool = '0123456789abcdefghijklmnopqrstuvwxyz';

#rozmiary obrazka
$img_width = 120;
$img_height = 30;

#pocz¹tkowy tekst
$str = '';

#losowanie tekstu
for($i = 0; $i < 7; $i++)
 {
  $str .= substr($pool, mt_rand(0, strlen($pool) -1), 1);
 }
 
#zamiana zmiennej
$string = $str;

#zapisanie tekstu w sesji
$_SESSION['captcha'] = $string;

#tworzenie obrazka
$im = imagecreate($img_width, $img_height);

#kolor, czcionka itp.
$bg_color = imagecolorallocate($im,163,163,163);
$font_color = imagecolorallocate($im,252,252,252);
$grid_color = imagecolorallocate($im,31,0,0);
$border_color = imagecolorallocate ($im, 174, 174, 174);

#wype³nianie obrazka kolorem
imagefill($im,1,1,$bg_color);

#dodawanie szumu
for($i=0; $i<1600; $i++)
 {
  $rand1 = rand(0,$img_width);
  $rand2 = rand(0,$img_height);
  imageline($im, $rand1, $rand2, $rand1, $rand2, $grid_color);
 }

#losowanie szerokoœci na jakiej ma byæ tekst
$x = rand(5, $img_width/(7/2));

#prostok¹t
imagerectangle($im, 0, 0, $img_width-1, $img_height-1, $border_color);

#pisanie tekstu
for($a=0; $a < 7; $a++)
 {
 
  imagestring($im, 5, $x, rand(6 , $img_height/3), substr($string, $a, 1), $font_color);
  $x += (5*2); #odstêp
 }

#nag³ówek
header("Content-type: image/gif");

#pokazywanie obrazka
imagegif($im);

#niszczenie obrazka
imagedestroy($im);
?>