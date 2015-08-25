<?php
/*
 * @Author: Marcin Wieczorek (CTRL)
 * @Date: 07-01-2013
 * 
 * 
 */
 
class Report
 {
  /*
   * Obiekt bazy danych
   */
  public $PDO;
  
  /*
   * Identyfikator raportu
   */
  public $report_id;
  
  /*
   * Konstruowanie klasy
   * @Param: (PDO)$PDO
   */
  public function __construct($PDO)
   {
	$this->PDO = $PDO;
   }
   
  /*
   * Pobieranie raportu
   * @Param: (Int)$report_id
   * @Return: Array { Array, Int }
   *  [0] - Informacje
   *  [1] - Powodzenie
   *   1 - Uda³o siê
   *   2 - Nie podano ID
   *   3 - Niepoprawne ID
   *   4 - Nie istnieje taki raport
   */
  public function Get($report_id=-1)
   {
	switch($this->checkID($report_id))
	 {
	 case 2:
	  $r[1] = 2;
	  return $r;
	 break;
	 case 3:
	  $r[1] = 3;
	  return $r;
	 break;
	 }
	 
	$report = $PDO->query("SELECT * FROM `".TBL."report` WHERE `id`=".$report_id);
	 
	if($report->rowCount() == 0)
	 {
	  $r[1] = 4;
	  return $r;
	 }
	 
	$r[0] = $report -> fetch();
	$r[1] = 1;
	return $r;
   }
   
  public function Delete($report_id=-1)
   {
	switch($this->checkID($report_id))
	 {
	 case 2:
	  $r[1] = 2;
	  return $r;
	 break;
	 case 3:
	  $r[1] = 3;
	  return $r;
	 break;
	 }
	 
	
   }
   
  public function Add() {}
  public function Close() {}
  public function Open() {}
  
  /*
   * Sprawdzanie czy ID jest poprawne
   * @Param: (Int)$report_id
   * @Return: (Int)
   *  1 - Uda³o siê
   *  2 - Nie podano ID
   *  3 - Niepoprawne ID
   */
  public function checkID($report_id)
   {
	if($this->report_id == 0 && $report_id <= 0)
	 {
	  return 2;
	 }
	elseif($report_id <= 0)
	 {
	  $report_id = $this->report_id;
	 }
	 
	if(!is_numeric($report_id))
	 {
	  return 3;
	 }
	 
	return 1;
   }
 }
?>