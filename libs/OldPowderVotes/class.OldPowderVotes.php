<?php
/*
 * 
 * 
 * 
 * 
 */
 
class OldPowderVotes
 {
  /*
   * Obiekt bazy danych
   */
  private $PDO;
   
  public function __construct($PDO)
   {
	$this->PDO = $PDO;
   }
   
  public function addVote() {}
  public function delVote($vote_id) {}
  
  /*
   * @Param: (Int)$vote_id
   * @Return: Array { Array, Int }
   *  [0] - Informacje
   *  [1] - Powodzenie akcji
   *   1 - Uda³o siê
   *   2 - Nie podano ID
   *   3 - Niepoprawne ID
   *   4 - Nie istnieje taki g³os
   */
  public function getVote($vote_id)
   {
	if($vote_id <= 0)
	 {
	  $r[1] = 2;
	  return $r;
	 }
	 
	if(!is_numeric($vote_id))
	 {
	  $r[1] = 3;
	  return $r;
	 }
	 
	$vote = $this->PDO->query("SELECT * FROM `opv_votes` WHERE `id`=$vote_id");
	
	if($vote -> rowCount() == 0)
	 {
	  $r[1] = 4;
	  return $r;
	 }
	 
	$r[0] = $vote -> fetch();
	$r[1] = 1;
	return $r;
   }
   
  public function addVbase($topic_id,) {}
  public function delVbase($Vbase_id) {}
  public function getVbase($Vbase_id) {}
  public function hasVoted($user_id,$Vbase_id) {}
  public function hasVbase($topic_id) {}
 }
?>