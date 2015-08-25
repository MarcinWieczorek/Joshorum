<?php
include(dirname(__FILE__).'/config.php');
include(dirname(__FILE__).'/libs/SmartUrl/class.SmartUrl.php');

$SU = new SmartUrl();
$SU = $SU->args;

switch($SU[0])
 {
 case 'step':
  switch($SU[1])
   {
   case 1:
	
   break;
   }
 break;
 case '':
  ?>
Witaj w instalatorze Joshorum!
  <?php
 break;
 }
?>