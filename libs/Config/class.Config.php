<?php
/*
 * Klasa Config
 * Konfiguracja strony w bazie danych
 * Marcin Wieczorek 2013
 */
 
class Config
 {
  /*
   * Baza danych
   */
  public $PDO;
   
  public function __construct($PDO)
   {
    $this->PDO = $PDO;
   
    $con = $PDO->query("SELECT * FROM `".TBL."config`");
	
	foreach($con->fetchAll() as $row)
	 {
	  $this->$row['key'] = $row['value'];
	 }
   }
 }
?>