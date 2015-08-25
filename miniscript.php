<?php
# miniscript.php
# EIF FORUM
# 24-01-2012
# Marcin Wieczorek

#sesja
session_start();

include(dirname(__FILE__).'/config.php');
include(DIR_NAME.'/libs/Spyc/class.Spyc.php');
include(DIR_NAME.'/libs/Config/class.Config.php');
include(DIR_NAME.'/class.PermissionsHP.php');

#Plik konfiguracyjny
include('funkcje.php');
include('class.Forum.php');
#połączenie z bazą
MysqlConnect();
$PDO = getPDO();

$your_ip = $_SERVER['REMOTE_ADDR']; //ip przeglądającego
$your_id = $_SESSION['logged_id']; //id przeglądającego

$Spyc = new Spyc();
$PermHP = new PermissionsHP($Spyc);
$Config = new Config($PDO);
define('MAIN_DIR',$Config->MAIN_DIR);

$forum = new Forum($PDO,$Spyc,$Config);
$forum->setOnline();

if($your_id > 0)
 {
  $UserRank = $forum->getUserRank($your_id);
 }
else
 {
  $UserRank[0] = $Config->UNLOGGED_RANK;
 }
$PermHP -> setUserRank($UserRank[0]);

$forum->PermHP = $PermHP;

#zapytanie odpowiadające za polskie znaki
mysql_query("SET CHARSET utf8");
mysql_query("SET NAMES `utf8` COLLATE `utf8_polish_ci`");

#zapisywanie loga
$forum->saveLog();

switch($_GET['a'])
 {
 case 'captcha':
  $cap = $forum->genCaptcha();
  $_SESSION['captcha'] = $cap[1];
  header("Content-type: image/gif");
  imagegif($cap[0]);
 break;
 case 'shout':
  if($PermHP->hasPermission('eif.shoutbox.write'))
   {
	$forum->addShout($_POST['tresc']);
   }
  else { echo 'no-permissions'; }
 break;
 case 'userlogin':
  if($PermHP->hasPermission('eif.log.in'))
   {
	switch($forum->loginUser($_POST['nick'],$_POST['password']))
	 {
	 case 1:
	  echo('logged');
	 break;
	 }
   }
 break;
 case 'passrecoveryemailsend':
  $nick = $PDO->query("SELECT `email` FROM `".TBL."users` WHERE `nick`='".$_GET['nick']."'");
  if($nick->rowCount()==1)
   {
    echo 'success';
   }
  else
   {
    echo 'no-user';
   }
 break;
 case 'upfilename':
  $f = $PDO->query("SELECT `name` FROM `".TBL."upfiles` WHERE `id`=".(int)$_GET['id']);
  if($f->rowCount()==1)
   {
	$fa = $f->fetch();
	echo $fa['name'];
   }
 break;
 case 'captchacheck':
  if($_SESSION['captcha']==$_GET['val']) echo 'true'; else echo 'false';
 break;
 case 'getfilerate':
  $file_id = $_GET['id'];
  $suma = $this->PDO->query("SELECT SUM(vote) FROM `".TBL."filevote` WHERE `file_id`=$file_id");
  $vote = $this->PDO->query("SELECT `id` FROM `".TBL."filevote` WHERE `file_id`=$file_id");
  $vote_rows = $vote -> rowCount();
  if($vote_rows == 0) $vote_rows=1; //żeby nie było dzielenia przez 0
  $suma = $suma->fetchColumn(0);
  echo($suma/$vote_rows);
 break;
 case 'onlinestats':
  $implode = array();
  $onlineUsers = $forum->getOnlineUsers();
  echo('Liczba użytkowników online w ciągu ostatnich 10 minut: '.count($onlineUsers).' <br/>');
  foreach($onlineUsers as $online_array)
   {
	if($online_array['user_id'] > 0)
	 {
      $implode[] = ShowUserNick($online_array['user_id'],'',1);
	 }
   }
  
  echo(implode(',&nbsp;&nbsp;',$implode));
 break;
 case 'filevote':
  $file_id = $_GET['file_id'];
  $file_rate = $_GET['rate'];
  FileVote($file_id,$file_rate);
 break;
 case 'delfile':
  $file = $forum->getFile($_GET['id']);
  if($PermHP->hasPermission('eif.file.delete.other') or ($PermHP->hasPermission('eif.file.delete.mine') && $your_id==$file[0]['user_id']))
   {
	switch($forum->delFile($_GET['id']))
	 {
	 case 1:
      echo('1');
	 break;
	 }
   }
  else { echo(error_message('nopermissoins')); }
 break;
 case 'logout':
  $forum->LogOut();
 break;
 case 'passmatch':
  $pass1 = $_GET['pass1'];
  $pass2 = $_GET['pass2'];
  if($pass1==$pass2)
   {
    echo '<font color=green>Hasło jest takie samo.</font>';
   }
  else
   {
    echo '<font color=red>Hasło musi być takie jak wyżej!</font>';
   }
 break;
 case 'emailvalidate':
  if(filter_var($_GET['email'],FILTER_VALIDATE_EMAIL))
   {
    echo("<font color=green>Email poprawny.</font>");
   }
  else
   {
    echo("<font color=red>Podaj poprawny email</font>");
   }
 break;
 case 'isfreenick':
  $nick = $PDO->query("SELECT `id` FROM `".TBL."users` WHERE `nick`='".$_GET['nick']."'");
  if($nick->rowCount()==0)
   {
    echo '1';
   }
  else
   {
    echo '0';
   }
 break;
 case 'sbrefresh':
  ShowShoutbox();
 break;
 }
?>