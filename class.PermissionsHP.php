<?php
/*
 * Marcin Wieczorek
 * 13-09-2012
 * http://marcin.co
 * Wszelkie prawa zastrzeżone!
 */
class PermissionsHP
 {
  public $Spyc;
  public $UserRank = '';
  public $Permissions;
  
  public function __construct($Spyc)
   {
    $this->Spyc = $Spyc;
	$this->Permissions = $Spyc->LoadFile('permissions.yml');
   }
  
  public function getRanks()
   {
	return $this->Permissions['ranks'];
   }
  
  public function setUserRank($UserRank)
   {
	$this->UserRank = $UserRank;
	return true;
   }
  
  public function hasPermission($permission)
   {
    $ranks = $this->Permissions['ranks'];
	$rank = $ranks[$this->UserRank];
	$rankPermissions = $rank['Permissions'];
	$exp = explode('.',$permission);
	$last = $exp[count($exp)-1];
	$glast = $exp[count($exp)-2];
	$perm_star = str_replace($last,'',$permission);
	$perm_star .= '*';
	$step = $exp[0];
	for($i=1;$i<count($exp);$i++)
	 {
	  if($star==false)
	   {
		$perm_star = $step.'.*';
		if(@in_array($perm_star,$rankPermissions))
		 {
		  $star=true;
		  break;
		 }
		$step .= '.'.$exp[$i];
	   }
	 }
	
	if(@in_array($permission,$rankPermissions) || $star==true)
	 {
	  return true;
	 }
	return false;
   }
   
  public function getRank($wat=-1)
   {
	if($wat==-1)
	 {
	  $all=true;
	 }
	 
	if(!$this->UserRank)
	 {
	  return false;
	 }
	 
	$ranks = $this->Permissions['ranks'];
	$rank = $ranks[$this->UserRank];
	
	if($all==true)
	 {
	  return $rank;
	 }
	 
	return $rank[$wat];
   }
 }
?>