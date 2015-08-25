<?php
class SmartUrl
 {
  public $args = array();
  
  function __construct()
   {
	$url_all = trim($_SERVER['REQUEST_URI'], '/');
	if(empty($url_all))
	 {
      $this->args[0] = ''; //Ustawiamy domy�ln� warto��.
	  return;
	 }
	 
    $url_array = explode('/', $url_all); //Rozdzielamy paramtery.
    $this->args[0] = $url_array[0]; //Ustawiamy warto�� 1, mamy pewno�� �e istnieje.
    if(isset($url_array[1])) //Je�li istnieje wi�cej paramter�w...
	 {
	  for($i=1; $i < count($url_array); $i++) //Robimy p�tle by wy�owi� wszystkie parametry.
	   {
		$this->args[$i] = $url_array[$i]; //Ustawiamy warto�� dla odpowiedniej tablicy.
	   }
	 }
	return;
   } }
?>