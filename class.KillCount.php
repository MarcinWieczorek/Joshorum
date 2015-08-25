<?php
/*
 * @Author: Marcin Wieczorek
 * @Date: 01-12-2012
 * @Class: KillCount
 * 
 */
 
class KillCount
 {
  /*
   * killcount - struktura tabeli
    
   */
  
  /*
   * Baza danych
   */
  public $PDO;
  
  public function __construct($PDO)
   {
	$this->PDO = $PDO;
   }
   
  /*
   * Pobieranie rankingu
   * @Param: (Int)$limit
   * @Param: (Boolean)$kills (true=kills, false=deaths)
   * @Return: Array { Array, Int }
   *  [0] - Informacje
   *  [1] - Powodzenie
   *   1 - Udało się
   *   2 - Nie podano limitu
   *   3 - Niepoprawny limit
   *   4 - Nie ma żadnych statów
   *   5 - $kills nie jest booleanem
   */
  public function getTop($limit,$kills=true)
   {
	if($limit <= 0)
	 {
	  $r[1] = 2;
	  return $r;
	 }
	 
	if(!is_numeric($limit))
	 {
	  $r[1] = 3;
	  return $r;
	 }
	 
	if(!is_bool($kills))
	 {
	  $r[1] = 5;
	  return $r;
	 }
	 
	if($kills==true) $wut = 'kills';
	elseif($kills==false) $wut = 'deaths';
	 
	$top = $this->PDO->query("SELECT * FROM `killcount` ORDER BY `$wut` DESC LIMIT 0,$limit");
	
	if($top -> rowCount() == 0)
	 {
	  $r[1] = 4;
	  return $r;
	 }
	 
	$r[0] = $top -> fetchAll();
	$r[1] = 1;
	return $r;
   }
  
  /*
   * @Param: (String)$nick
   * @Return: Array { Array, Int }
   *  [0] - Informacje
   *  [1] - Powodzenie
   *   1 - Udało się
   *   2 - Nie podano nicku
   *   3 - Niepoprawny nick
   *   4 - Taki użytkownik nie istnieje
   */
  public function getUser($nick)
   {
	if(empty($nick))
	 {
	  $r[1] = 2;
	  return $r;
	 }
	 
	if(strstr($nick,' '))
	 {
	  $r[1] = 3;
	  return $r;
	 }
	 
	$user = $this->PDO->query("SELECT * FROM `killcount` WHERE `username`='$nick'");
	
	if($user->rowCount() == 0)
	 {
	  $r[1] = 3;
	  return $r;
	 }
	 
	$r[0] = $user->fetch();
	$r[1] = 1;
	return $r;
   }
  
  /*
   * Sprawdza czy użytkownik jest w bazie
   * @Param: (String)$nick
   * @Return: boolean
   */
  public function existsUser($nick)
   {
	if(empty($nick))
	 {
	  return false;
	 }
	 
	if(strstr($nick,' '))
	 {
	  return false;
	 }
	 
	$user = $this->PDO->query("SELECT `id` FROM `killcount` WHERE `username`='$nick'");
	
	if($user->rowCount() == 0)
	 {
	  return false;
	 }
	 
	return true;
   } 
 }