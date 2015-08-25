<?php
class SmartUrl
 {
  public $args = array();
  
  function __construct()
   {
	$url_all = trim($_SERVER['REQUEST_URI'], '/');
	if(empty($url_all))
	 {
      $this->args[0] = ''; //Ustawiamy domyœln¹ wartoœæ.
	  return;
	 }
	 
    $url_array = explode('/', $url_all); //Rozdzielamy paramtery.
    $this->args[0] = $url_array[0]; //Ustawiamy wartoœæ 1, mamy pewnoœæ ¿e istnieje.
    if(isset($url_array[1])) //Jeœli istnieje wiêcej paramterów...
	 {
	  for($i=1; $i < count($url_array); $i++) //Robimy pêtle by wy³owiæ wszystkie parametry.
	   {
		$this->args[$i] = $url_array[$i]; //Ustawiamy wartoœæ dla odpowiedniej tablicy.
	   }
	 }
	return;
   } }
?>