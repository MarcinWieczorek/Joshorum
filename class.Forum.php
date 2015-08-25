<?php
/*
 * Marcin Wieczorek
 * 13-09-2012
 * Klasa forum
 * 
 * ALTER TABLE `eif_upfiles` CHANGE COLUMN `blob` `blob` LONGBLOB NOT NULL AFTER `name`;
 * 
 */
 
class JoshorumException extends Exception
 {
 
 }
 
class Forum
 {
  public $your_id;
  public $your_ip;
  public $DirFile;
  private $PDO;
  public $PermHP;
  private $Spyc;
  public $Config;
 
  /*
   * Klasa konstruująca
   * @Param: (PDO)$PDO
   * @Return: none
   */
  public function __construct($PDO,$Spyc=null,$Config=null)
   {
	if(!$PDO)
	 {
	  throw new JoshorumException('Niepoprawne połączenie z bazą',1);
	 }
	 
	if(!$Spyc)
	 {
	  throw new JoshorumException('Niepoprawny obiekt biblioteki Spyc',2);
	 }
	 
	if(!$Config)
	 {
	  throw new JoshorumException('Niepoprawny obiekt klasy Config',3);
	 }
	
	if(defined('TBL')==false || !TBL)
	 {
	  throw new JoshorumException('Niepoprawny prefix tabeli',4);
	 }
	
    $this->your_id = $_SESSION['logged_id'];
	$this->your_ip = $_SERVER['REMOTE_ADDR'];
	$this->DirFile = dirname(__FILE__);
	$this->PDO = $PDO;
	$this->Spyc = $Spyc;
	$this->Config = $Config;
   }
  
  /*
   * Uaktualnianie statusu użytkownika (czyli czy jest na stronie)
   * @Param: none
   * @Return: none
   */
  public function setOnline()
   {  
	$session_id = session_id(); //sesja
	$time = time(); //aktualny czas
	$time_check = $time-600; //czas do skasowania
	
	$online = $this->PDO->query("SELECT * FROM ".TBL."online WHERE `expire`=0 AND `session_id`='$session_id' ORDER BY `id` DESC LIMIT 0,1");
	$online_array = $online->fetch();
	$online_rows = $online -> rowCount();
	
	$userid = $this->PDO->query("SELECT * FROM ".TBL."online WHERE `user_id`='".$this->your_id."' AND `expire`=0");
	$userid_rows = $userid -> rowCount();
	
	if($userid_rows>0)
	 {
	  $userid_array = $userid->fetch();
	  $_SESSION['logged_id'] = $userid_array['user_id'];
	  $this->your_id = $userid_array['user_id'];
	 }
	
	if($online_array['user_id']==0)
	 {
	  $this->PDO->exec("UPDATE ".TBL."online SET user_id='".$this->your_id."' WHERE session_id='$session_id'");
     }
   
	if($online_rows==0 && $userid_rows==0)
	 {
      if($userid_rows==0) { $user_id = $this->your_id; }
	  $this->PDO->exec("INSERT INTO ".TBL."online VALUES(0,'$session_id','$time','".$this->your_ip."','".$this->your_id."','0')");
	 }
	else
 	 {
      $this->PDO->exec("UPDATE ".TBL."online SET time='$time',user_id='".$this->your_id."' WHERE session_id='$session_id'");
	 }
  
	$this->PDO->exec("UPDATE ".TBL."online SET expire='1' WHERE time<$time_check");
   }
  
  /*
   * Generowanie przyjaznego dla mod_rewrite adresu url
   * @Param: (String)$url
   * @Return: (String)$l
   */
  public function genLeave($url)
   {
	$url = str_replace('/','-',$url);
	$l = $this->Config->MAIN_DIR.'leave/'.$url;
	return $l;
   }
  
  /*
   * Opuszczanie strony
   * @Param: (String)$url,(Int)$now=0
   * @Return: (Int)
   *  1 - Udało się, przekierowanie za jakiś czas
   *  2 - udało się, przekierowanie natychmiast
   *  3 - Niepoprawny adres
   *  4 - Nie podano adresu
   */
  public function siteLeave($url,$now=0,$sec=5)
   {
	if(!empty($url))
	 {
	  if(filter_var($url,FILTER_VALIDATE_URL))
	   {
		if($now==1)
		 {
		  $this->Redirect($url);
		  return 2;
		 }
		else
		 {
		  $this->Redirect($url,$sec);
		  return 1;
		 }
	   }
	  else { return 3; }
	 }
	else { return 4; }
   }
  
  /*
   * Przekierowanie
   * @Param: (String)$url, (Int)$sec=0, (Boolean)$echo=true
   * @Return: if($echo===false) then (String)$redirect 
   */
  function Redirect($url,$sec=0,$echo=true)
   {
	$redirect = "<meta http-equiv='refresh' content='$sec; url=$url '>";

	if($echo==true)
	 {
	  echo($redirect);
	 }
	else
	 {
	  return $redirect;
	 }
   }
 
  /*
   * Usuwanie działu
   * @Param: $dzial_id
   * @Return: int
   *  1 - Udało się
   *  2 - Nie podano ID
   *  3 - Niepoprawne ID
   *  4 - Nie ma takiego działu
   *  5 - Dział jest już usunięty
   */
  public function delDzial($dzial_id)
   {
	if($dzial_id <= 0)
	 {
	  return 2;
	 }
   
	if(!is_numeric($dzial_id))
	 {
	  return 3;
	 }
	 
	$dzial = $this->PDO->query("SELECT `deleted` FROM `".TBL."dzial` WHERE `id`=$dzial_id ");
	$dzial_rows = $dzial -> rowCount();
	$dzial_array = $dzial -> fetch();
	
	if($dzial_rows == 0)
	 {
	  return 4;
	 }
	 
	if($dzial_array['deleted']===1)
	 {
	  return 5;
	 }
	
	$this->PDO->exec("UPDATE `".TBL."dzial` SET `deleted`=1 WHERE `id`=$dzial_id");
	return 1;
   }
   
  /*
   * Otwieranie tematu
   * @Date: 06-07-2012
   * @Param: $topic_id
   * @Return: INT
   *  1 - Udało się
   *  2 - Nie podano ID
   *  3 - Niepoprawne ID
   *  4 - Temat Nie istnieje
   *  5 - Temat jest już otwarty
   *  6 - Temat jest usunięty
   */
  public function openTopic($topic_id)
   {
    if($topic_id <= 0)
	 {
	  return 2;
	 }
	 
	if(!is_numeric($topic_id))
	 {
	  return 3;
	 }
	
	$topic = $this->PDO->query("SELECT `deleted`,`lock` FROM `".TBL."tematy` WHERE `id`=$topic_id");
	  
	if($topic->rowCount() == 0)
	 {
	  return 4;
	 }
	
	$topic_array = $topic->fetch();
		
	if($topic_array['deleted']==1)
	 {
	  return 6;
	 }
	 
	if($topic_array['lock']==0)
	 {
	  return 5;
	 }
	 
	$this->PDO->exec("UPDATE `".TBL."tematy` SET `lock`=0 WHERE `id`=$topic_id");
	return 1;
   }

 /*
   * Zamykanie tematu
   * @Date: 06-07-2012
   * @Param: $topic_id
   * @Return: INT
   *  1 - Udało się
   *  2 - Nie podano ID
   *  3 - Niepoprawne ID
   *  4 - Temat Nie istnieje
   *  5 - Temat jest już zamknięty
   *  6 - Temat jest usunięty
   */
  public function closeTopic($topic_id)
   {
    if($topic_id <= 0)
	 {
	  return 2;
	 }
	 
	if(!is_numeric($topic_id))
	 {
	  return 3;
	 }
	
	$topic = $this->PDO->query("SELECT `deleted`,`lock` FROM `".TBL."tematy` WHERE `id`=$topic_id");
	  
	if($topic->rowCount() == 0)
	 {
	  return 4;
	 }
	
	$topic_array = $topic->fetch();
		
	if($topic_array['deleted']==1)
	 {
	  return 6;
	 }
	 
	if($topic_array['lock']==1)
	 {
	  return 5;
	 }
	 
	$this->PDO->exec("UPDATE `".TBL."tematy` SET `lock`=1 WHERE `id`=$topic_id");
	return 1;
   }   

  /*
   * @Author: Marcin (CTRL) Wieczorek
   * @Date: 08-07-2012
   * @Param: $topic_id, $state
   * @Return: INT
   *  1 - Udało się
   *  2 - Nie podano ID
   *  3 - Niepoprawne ID
   *  4 - Nie podano $state
   *  5 - $state nie poprawne
   *  6 - Nie ma takiego tematu
   *  7 - Temat jest usunięty
   *  8 - Temat jest już odpięty
   *  9 - Temat jest już przypięty
   */
  function SetPinned($topic_id,$state)
   {
    if(empty($topic_id))
	 {
	  return 2;
	 }
	 
	if(!is_numeric($topic_id))
	 {
	  return 3;
	 }
	 
	if(!isset($state))
	 {
	  return 4;
	 }
	
	if($state=='0' or $state=='1')
	 {
	  $validState=true;
	 }
	else
	 {
	  $validState=false;
	 }
	 
	if($validState==false)
	 {
	  return 5;
	 }
	
	$topic = $this->PDO->query("SELECT `deleted`,`pinned` FROM `".TBL."tematy` WHERE `id`=$topic_id");
	  
	if($topic->rowCount() == 0)
	 {
	  return 6;
	 }
	
	$topic_array = $topic->fetch();
		
	if($topic_array['deleted']==1)
	 {
	  return 7;
	 }
				
	if($topic_array['pinned']==0 && $state==0)
	 {
	  return 8;
	 }
	 
	if($topic_array['pinned']==1 && $state==1)
	 {
	  return 9;
	 }
	 
	$this->PDO->exec("UPDATE `".TBL."tematy` SET `pinned`=$state WHERE `id`=$topic_id");
	return 1;
   }
   
  /*
   * Rejestracja
   * @Param: $nick,$pass,$passrepeat,$mail,$gadu,$sex,$miasto,$polec,$www
   * @Return: int
   *  1 - Udało się
   *  2 - Nie podano nicku
   *  3 - Nie podano hasła
   *  4 - Nie powtórzono hasła
   *  5 - Nie podano emaila
   *  6 - GaduGadu nie jest liczbą
   *  7 - Niepoprawna płeć
   *  8 - Nick zawiera niedozwolone znaki
   *  9 - Nick jest za długi
   *  10 - Polecający nie istnieje
   *  11 - Strona WWW niepoprawna
   *  12 - Hasła nie są takie same
   *  13 - Niepoprawny email
   *  14 - Rejestrowano się już na ten email
   *  15 - Rejestrowano się już z tego IP
   *  16 - Rejestrowano się już na ten numer GG
   *  17 - Nazwa jest zajęta
   */
  function UserRegister($nick,$pass,$passrepeat,$mail,$gadu,$sex,$miasto,$polec,$www)
   {
	if(!strlen($nick)>0)
	 {
	  return 2;
	 }
	 
	if(!strlen($pass)>0)
	 {
	  return 3;
	 }
	 
	if(!strlen($passrepeat)>0)
	 {
	  return 4;
	 }
	 
	if(!strlen($mail)>0)
	 {
	  return 5;
	 }
	 
	if($gadu>0)
	 {
	  if(!is_numeric($gadu))
	   {
		return 6;
	   }
	 }
	 
	if(!($sex=='male' or $sex=='female'))
	 {
	  return 7;
	 }
	 
	if(strlen($nick)>20)
	 {
	  return 9;
	 }
	
	if(strlen($polec)>0)
	 {
	  $polec = $this->PDO->query("SELECT `id` FROM `".TBL."users` WHERE `nick`='$polec'");
	  
	  if($polec -> rowCount() == 0)
	   {
		return 10;
	   }
	   
	  $polec_array = $polec->fetch();
	  $polec_id = $polec_array['id'];
	  unset($polec_array);
	 }
	 
	if(strlen($www)>0)
	 {
	  if(!filter_var($www, FILTER_VALIDATE_URL))
	   {
		return 11;
	   }
	 }
	 
	if($passrepeat!==$pass)
	 {
	  return 12;
	 }
	 
	if(!filter_var($mail,FILTER_VALIDATE_EMAIL))
	 {
	  return 13;
	 }
	
	
	#zapytanie o usera z nickiem który chcemy zarejestrować
	if($gadu>0) $gadusql = "`gadu`=$gadu OR ";
	$sec = $this->PDO->query("SELECT * FROM `".TBL."users` WHERE $gadusql `nick`='$nick' OR `email`='$mail' OR `ip_rej`='".$this->your_ip."'");
	
	if($sec -> rowCount() > 0)
	 {
	  $sec_array = $sec->fetch();
	  if($sec_array['nick'] === $nick)
	   {
		return 17;
	   }
	  elseif($sec_array['email'] === $mail)
	   {
		return 14;
	   }
	  elseif($sec_array['ip_rej'] === $this->your_ip)
	   {
		return 15;
	   }
	  elseif($sec_array['gadu'] === $gadu)
	   {
		return 16;
	   }
	 }
	
	$Rank = 6; //ranga
	$yRank = 'Unactive'; //yRanga (YAML)
	$acticode = str_shuffle('qwertyuiopasdfghjklzxcvbnm1234567890'); //kod potwierdzający maila
	
	$passS = $this->passPrepare($pass,time());
	
	$this->PDO->exec("INSERT INTO `".TBL."users` VALUES(0,'$nick','$passS','$Rank','$yRank',1,1,'','$mail','$gadu','".$this->your_ip."','".$this->your_ip."','',".time().",'','$sex','$miasto',0,'$acticode','','$polec_id',0,0,0,'$www',1,1)");
						
	$user_id = $this->PDO->lastInsertId();
	
	$actiurl = $this->Config->MAIN_DIR."uzytkownik/aktywuj/".$acticode;
	
	#wysyłanie maila
	$msg = EMAIL_REGISTER_MESSAGE;
	$msg = str_replace('{NICK}',$nick,$msg);
	$msg = str_replace('{ACTIVATE}',$actiurl,$msg);
	$this->sendMail($mail,$msg,EMAIL_REGISTER_TITLE);
	   
	#tworzenie folderu
	if(!is_dir($this->DirName.'files/'.$user_id))
	 {
	  mkdir($this->DirName.'files/'.$user_id,0755);
	 }
	 
	return 1;
   }
   
  /*
   * Pobieranie z określoną prędkością
   * @Param: $local_file, $download_file, $download_rate
   * @Return: (Boolean)
   */
  public function SpeedDownload($local_file,$download_file,$download_rate)
   {
	if(file_exists($local_file) && is_file($local_file))
	 {
      header('Cache-control: private');
      header('Content-Type: application/octet-stream');
      header('Content-Length: '.filesize($local_file));
      header('Content-Disposition: filename='.$download_file);
 
      flush();
 
      $file = fopen($local_file, "r");
 
      while (!feof($file))
	   {
		print fread($file, round($download_rate * 1024));
		flush();
		sleep(1);
	   }
	  fclose($file);
 
	 }
	else
	 {
      return false;
	 }
   }
  
  /*
   * Usuwanie pliku
   * @Param: $file_id
   * @Return: int
   *  1 - Udało się
   *  2 - Nie podano ID
   *  3 - Niepoprawne ID
   *  4 - Plik nie istnieje
   *  5 - Plik został już usunięty
   */
  public function delFile($file_id)
   {
	if($file_id <= 0)
	 {
	  return 2;
	 }
	 
    if(!is_numeric($file_id))
	 {
	  return 3;
	 }
	
	$file = $this->PDO->query("SELECT * FROM `".TBL."upfiles` WHERE `id`=$file_id");
	
	if($file -> rowCount() == 0)
	 {
	  return 4;
	 }
	 
	$file_array = $file -> fetch();
	 
	if($file_array['deleted']==1)
	 {
	  return 5;
	 }
	
	$this->PDO->exec("UPDATE `".TBL."upfiles` SET `deleted`=1 WHERE `id`=$file_id");
	
	$file_path = $this->DirFile.'/files/'.$file_array['user_id'].'/'.$file_array['name'];
	if(file_exists($file_path))
	 {
	  unlink($file_path);
	 }
	
	return 1;
   }
   
  /*
   * Hashowanie i solenie hasła
   * @Param: $String, $TimeRej
   *         (string) (unixtime)
   * @Return: String
   * Zwraca hasło gotowe do zapisania w bazie
   */
  public function passPrepare($String,$TimeRej)
   {
	$String .= substr($TimeRej,5);
	$String = md5($String);
	return $String;
   }
   
  /*
   * Informacje o temacie
   * @Param: $topic_id
   * @Return: Array
   *  [0] - Array 
   *  [1] - Int 
   *    1 - Udało się
   *    2 - Nie podano ID
   *    3 - Niepoprawne ID
   *    4 - Temat nie istnieje
   */
  public function getTopic($topic_id)
   {
	if($topic_id<=0)
	 {
	  $r[1] = 2;
	  return $r;
	 }
	  
	if(!is_numeric($topic_id))
	 {
	  $r[1] = 3;
	  return $r;
	 }
	
	$topic = $this->PDO->query("SELECT * FROM `".TBL."tematy` WHERE `id`=$topic_id");
	$topic_rows = $topic -> rowCount();
		
	if($topic_rows==0)
	 {
	  $r[1] = 4;
	  return $r;
	 }
	 
	$r[0] = $topic -> fetch();
	$r[1] = 1;
	return $r;
   }
   
  /*
   * Pobieranie (domyślnie 10) najnowszych postów
   * @Param: $limit (optional)
   * @Return: Array
   *  [0] - Array
   *  [1] - Int
   *   1 - Udało się
   *   2 - Limit nie jest liczbą
   *   3 - Limit <=0
   *   4 - Nie ma postów
   */
  public function getLastpost($limit=10)
   {
	if(!is_numeric($limit))
	 {
	  $r[1] = 2;
	  return $r;
	 }
	
	if($limit<=0)
	 {
	  $r[1] = 3;
	  return $r;
	 }
	 
	$posts = $this->PDO->query("SELECT * FROM `".TBL."posty` WHERE `deleted`=0 ORDER BY `id` DESC LIMIT 0,$limit");
	$posts_rows = $posts -> rowCount();
	
	if($posts_rows==0)
	 {
	  $r[1] = 4;
	  return $r;
	 }
	 
	$posts_array = $posts -> fetchAll();
	$r[1] = 1;
	$r[0] = $posts_array;
	return $r;
   }
   
  /*
   * Pobieranie postu
   * @Param: $post_id
   * @Return: Array
   *  [0] - Array
   *  [1] - Int
   *   1 - Udało się
   *   2 - Nie podano ID
   *   3 - Niepoprawne ID
   *   4 - post nie istnieje
   */
  public function getPost($post_id)
   {
	if($post_id<=0)
	 {
	  $r[1] = 2;
	  return $r;
	 }
	 
	if(!is_numeric($post_id))
	 {
	  $r[1] = 3;
	  return $r;	  
	 }
	 
	$post = $this->PDO->query("SELECT * FROM `".TBL."posty` WHERE `id`=$post_id ORDER BY `id` DESC LIMIT 0,1");
	$post_rows = $post -> rowCount();
   
    if($post_rows<=0)
	 {
	  $r[1] = 4;
	  return $r;	  
	 }
	 
	$r[0] = $post -> fetch();
	$r[1] = 1;
	return $r;
   }
   
  /*
   * Dodawanie kategorii
   * @Param: $name
   * @Return: int
   *  1 - Udało się
   *  2 - Nie podano nazwy
   *  3 - Nazwa za długa
   */
  public function addCategory($name)
   {
	if(empty($name))
	 {
	  return 2;
	 }
	
	if(strlen($name)>70)
	 {
	  return 3;
	 }
	
	$this->PDO->exec("INSERT INTO `".TBL."kategorie` VALUES(0,'$name');");
	return 1;
   }
   
  /*
   * Pobieranie rangi usera
   * @Param:
   * @Return: Array
   *  [0] - String
   *  [1] - Int
   *   1 - Udało się
   *   2 - Nie podano ID
   *   3 - Niepoprawne ID
   *   4 - Nie ma takiego usera
   */
  public function getUserRank($user_id)
   {
	if($user_id<=0)
	 {
	  $r[1] = 2;
	  return $r;
	 }
	 
	if(!is_numeric($user_id))
	 {
	  $r[1] = 3;
	  return $r;
	 }
	 
	$user = $this->PDO->query("SELECT `yrank` FROM `".TBL."users` WHERE `id`=$user_id");
	$user_rows = $user -> rowCount();
	
	if($user_rows===0)
	 {
	  $r[1] = 4;
	  return $r;
	 }
	 
	$user_array = $user -> fetch();
	$r[0] = $user_array['yrank'];
	$r[1] = 1;
	return $r;
   }
   
  /*
   * Wysyłanie wiadomości na GG
   * @Param: $gadu_numer, $gadu_message
   * @Return: Int
   *  1 - udało się
   *  2 - Plik klasy nie istnieje
   *  3 - Nie podano numeru
   *  4 - Niepoprawny numer
   *  5 - Nie udało się wysłac wiadomości
   *  6 - Nie udało się połączyć
   *  7 - Nie podano treści
   */
  public function ggSend($gadu_numer,$gadu_message)
   {
    $GGfile = DIR_NAME.'/libs/gadugadu/rfGG.class.php';
    
	if(!file_exists($GGfile))
	 {
	  return 2;
	 }
	
	require_once($GGfile);
	$GG = new rfGG(rfGG::VER_77);
	
	if($gadu_numer<=0)
	 {
	  return 3;
	 }
	
	if(empty($gadu_message))
	 {
	  return 7;
	 }
	
	if(!is_numeric($gadu_numer))
	 {
	  return 4;
	 }
	
	if(!$GG->connect('43804091','graniastoslup')) //numer specjalny stworzyłem :3
	 {
	  return 6;
	 }
	
	if($GG->sendMessage($gadu_numer,$gadu_message))
	 {
	  return 1;
	 }
	else
	 {
	  return 5;
	 }
   }
   
  /*
   * Dodawanie posta
   * @Param: (Int)$topic_id, (String)$msg, (Boolean)$starter=0, (Int)$user_id=-1
   * @Return:
   *  [0] - Int - ID dodanego posta
   *  [1] - powodzenie akcji
   *   1 - Udało się
   *   2 - Nie podano ID tematu
   *   3 - Niepoprawne ID
   *   4 - Nie podano wiadomości
   *   5 - Nie ma takiego tematu
   *   6 - Temat jest usunięty
   *   7 - Nie podano użytkownika (nie zalogowany)
   *   8 - Treść wiadomości zawiera niedozwolone znaki
   *   9 - ID użytkownika nie jest liczbą
   *  10 - Temat jest zablokowany
   */
  public function addPost($topic_id,$msg,$starter=0,$user_id= -1)
   {
	if($topic_id <= 0)
	 {
	  $r[1] = 2;
	  return $r;
	 }
	 
	if(!is_numeric($topic_id))
	 {
	  $r[1] = 3;
	  return $r;
	 }
	
	if(strlen($msg) == 0)
	 {
	  $r[1] = 4;
	  return $r;
	 }
	 
	if($user_id === -1)
	 {
	  if($this->your_id > 0)
	   {
		$user_id = $this->your_id;
	   }
	  else
	   {
		$r[1] = 7;
		return $r;
	   }
	 }
	 
	if(!is_numeric($user_id))
	 {
	  $r[1] = 9;
	  return $r;
	 }
	 
	$topic = $this->PDO->query("SELECT `deleted`,`dzial_id`,`lock` FROM `".TBL."tematy` WHERE `id`=$topic_id");
	
	if($topic->rowCount() == 0)
	 {
	  $r[1] = 5;
	  return $r;
	 }
	 
	$topic_array = $topic->fetch();
	
	if($topic_array['deleted'] == 1)
	 {
	  $r[1] = 6;
	  return $r;
	 }
	 
	$dzial = $this->PDO->query("SELECT `counting` FROM `".TBL."dzial` WHERE `id`=".$topic_array['dzial_id']);
	$dzial_array = $dzial -> fetch();
	if($dzial_array['counting'] == 1)
	 {
	  $counted = 1;
	 }
	else
	 {
	  $counted = 0;
	 }
	 
	if($topic_array['lock'] == 1)
	 {
	  $r[1] = 10;
	  return $r;
	 }
	 
	$this->PDO->exec("UPDATE `".TBL."tematy` SET `lastpost_time`=".time()." WHERE `id`=$topic_id");
	$this->PDO->exec("INSERT INTO `".TBL."posty` VALUES(0,".$topic_id.",".$this->your_id.",".$starter.",".time().",'".$msg."',0,".$counted.")");
    
	$r[0] = $this->PDO->lastInsertId();
	$r[1] = 1;
	return $r;
   }
   
  
  /*
   * Przenoszenie tematu
   * @Param: $topic_id, $dzial_id
   * @Return: int
   *  1 - Udało się
   *  2 - Nie podano ID tematu
   *  3 - Niepoprawne ID tematu
   *  4 - Nie podano ID działu
   *  5 - Niepoprawne ID działu
   *  6 - Temat nie istnieje
   *  7 - Temat jest usunięty
   */
  function moveTopic($topic_id,$dzial_id)
   { 
	if(empty($topic_id)) //czy podano id tematu
	 {
	  return 2;
	 }
	
	if(!is_numeric($topic_id)) //poprawność id
	 {
	  return 3;
	 }
	
	if(empty($dzial_id)) //czy podano id działu
	 {
	  return 4;
	 }
	
	if(!is_numeric($dzial_id)) //poprawność i działu
	 {
	  return 5;
	 }
	
	$topic = $this->PDO -> query("SELECT * FROM ".TBL."tematy WHERE `id`=$topic_id ORDER BY `id` DESC LIMIT 0,1");
	
	if($topic->rowCount() == 0) //czy istnieje temat
	 {
	  return 6;
	 }
	
	$topic_array = $topic -> fetch();
	 
	if($topic_array['deleted']==1) //czy temat nie został usunięty
	 {
	  return 7;
	 }
	 
	$this->PDO->exec("UPDATE `".TBL."tematy` SET `dzial_id`=$dzial_id WHERE `id`=".$topic_id);
	return 1;
   }
   
  /*
   * Logowanie
   * @Param: $nick, $pass
   * @Return: Int
   *  1 - Udało się
   *  2 - Nie podano nicku
   *  3 - Nie podano hasła
   *  4 - Nie ma takiego użytkownika
   *  5 - Hasło jest niepoprawne
   */
  function loginUser($nick,$pass)
   {
	if(empty($nick))
     {
	  return 2;
	 }
	 
	if(strlen($pass) == 0)
	 {
	  return 3;
	 }
	 
    $data = date('d.m.Y, H:i');
	$time = time();
	
	#zapytanie i tablica
    $user = $this->PDO->query("SELECT `password`,`md5`,`salt`,`time_rej`,`id` FROM `".TBL."users` WHERE `nick`='$nick'");
    $user_rows = $user -> rowCount();
	
	#sprawdzanie
    if($user_rows == 0)
	 {
	  return 4;
	 }
	
	$user_array = $user -> fetch();
	$user_id = $user_array['id'];
	  
	#hashowanie hasła
	$pass_crypt = crypt(12,$pass);
	$passMd5 = md5($pass);
	$passS = $this->passPrepare($pass,$user_array['time_rej']);
	  
	  #warunki
	  if($user_array['md5']==1 && $user_array['salt']==1)
	   {
		if($user_array['password'] == $passS)
		 {
		  $zgodnoschasel = true;
		 }
	   }
	  elseif($user_array['md5']==1 && $user_array['salt']==0)
	   {
		if($login_array['password'] == $passMd5)
		 {
		  $zgodnoschasel = true;
		  $this->PDO->query("UPDATE `".TBL."users` SET `salt`=1, `password`='$passS' WHERE `id`='$user_id' ");
		 }
	   }
	  else
	   {
		if($user_array['password'] == $pass_crypt)
		 {
		  $zgodnoschasel = true;
		  $this->PDO->query("UPDATE `".TBL."users` SET `md5`=1, `salt`=1, `password`='$passS' WHERE `id`='$user_id' ");
		 }
	   }
	   
	if($zgodnoschasel !== true) //sprawdzanie zgodności haseł
	 {
	  $this->PDO->query("UPDATE `".TBL."users` SET `ip_fail`='".$this->your_ip."' WHERE `nick`='$nick'");
	  return 5;
	 }
	
	$this->PDO->query("UPDATE `".TBL."users` SET `data_last`='$data',`ip_last`='".$this->your_ip."' WHERE `id`='$user_id'");
	
	$_SESSION['logged_id'] = $user_id;
	$this -> your_id = $user_id;
	return 1;
   }
   
  /*
   * Wylogowywanie
   * @Param: $user_id
   * @Return: int
   *  1 - Udało się
   *  2 - Nie zalogowany
   *  3 - Niepoprawne ID
   */
  public function LogOut($user_id=-1)
   {
	if($user_id == -1)
	 {
	  $user_id = $this->your_id;
	 }
	 
	if($user_id == 0)
	 {
	  return 2;
	 }
	 
	if(!is_numeric($user_id))
	 {
	  return 3;
	 }
	 
	$this->your_id = 0;
	$_SESSION['logged_id'] = 0;
	return 1;
   }
   
  /*
   * Pobieranie ikony działu
   * @Param:
   * @Return: Array { String , Int }
   *  [0] - String
   *  [1] - Int 
   *   1 - Udało się
   *   2 - Nie ma domyślnej ikony
   *   3 - Nie ma ikony o takim ID
   *   4 - Niepoprawne ID
   *    - 
   */
  public function getDzialIcon($icon_id=0)
   {
	if(!is_numeric($icon_id))
	 {
	  $r[1] = 4;
	  return $r;
	 }
	
	if(empty($icon_id))
	 {
	  $default = $this->PDO->query("SELECT `iconsrc` FROM `".TBL."dzialicon` WHERE `default`=1 LIMIT 0,1");
	  $default_rows = $default -> rowCount();
	  
	  if($default_rows==0)
	   {
	    $r[1] = 2;
		return $r;
	   }
	   
	  $default_array = $default->fetch();
	  $iconsrc = $default_array['iconsrc'];
	 }
	else
	 {
	  $icon = $this->PDO->query("SELECT `iconsrc` FROM `".TBL."dzialicon` WHERE `id`=$icon_id");
	  $icon_rows = $icon -> rowCount();
	  
	  if($icon_rows == 0)
	   {
		$r[1] = 3;
		return $r;
	   }
	  
	  $icon_array = $icon->fetch();
	  $iconsrc = $icon_array['iconsrc'];
	 }
	 
	$r[0] = $iconsrc;
	$r[1] = 1;
	return $r;
   }
 
  /*
   * Pobieranie uploadu
   * @Param: $user_id, $limit
   * @Return: Array { Array { Int, Array }, Int }
   *  [0] - Array
   *  [1] - Int
   *   1 - Udało się
   *   2 - Nie podano ID
   *   3 - Niepoprawne ID
   *   4 - Nie ma takiego użytkownika
   *   5 - Nie ma żadnych plików
   *   6 - Limit niepoprawny
   */
  public function getUpload($user_id,$limit=0)
   {
	if($user_id<=0)
	 {
	  $r[1] = 2;
	  return $r;
	 }
	 
	if(!is_numeric($user_id))
	 {
	  $r[1] = 3;
	  return $r;
	 }
	 
	if(!is_numeric($limit))
	 {
	  $r[1] = 6;
	  return $r;
	 }
	 
	$user = $this->PDO->query("SELECT `id` FROM `".TBL."users` WHERE `id`=$user_id");
	$user_rows = $user -> rowCount();
	 
	if($user_rows==0)
	 {
	  $r[1] = 4;
	  return $r;
	 }
	
	if($limit>0)
	 {
	  $limit_sql = 'LIMIT 0,'.$limit;
	 }
	
	$files = $this->PDO->query("SELECT * FROM `".TBL."upfiles` WHERE `user_id`=$user_id AND `deleted`=0 ORDER BY `id` DESC $limit_sql");
	$files_rows = $files -> rowCount();
	
	if($files_rows==0)
	 {
	  $r[1] = 5;
	  return $r;
	 }
	 
	$r[0] = $files -> fetchAll();
	$r[1] = 1;
	return $r;
   }
   
  /*
   * Pobieranie informacji o pliku
   * @Param: $file_id
   * @Return: Array { Array, Int }
   *  [0] - Informacje
   *  [1] - Powodzenie akcji
   *   1 - Udało się
   *   2 - Nie podano ID
   *   3 - Niepoprawne ID
   *   4 - Nie ma takiego pliku
   */
  public function getFile($file_id)
   {
	if($file_id<=0)
	 {
	  $r[1] = 2;
	  return $r;
	 }
	 
	if(!is_numeric($file_id))
	 {
	  $r[1] = 3;
	  return $r;
	 }
	 
	$file = $this->PDO->query("SELECT * FROM `".TBL."upfiles` WHERE `id`=$file_id ORDER BY `id` DESC LIMIT 0,1");
	$file_rows = $file -> rowCount();
	
	if($file_rows == 0)
	 {
	  $r[1] = 4;
	  return $r;
	 }
	
	$r[0] = $file -> fetch();
	$r[1] = 1;
	return $r;
   }
   
  /*
   * Dodawanie działu
   * @Param: $kategoria_id, $name, $opis, $adres, $icon_id
   * @Return: Array { Int, Int }
   *  [0] - Id działu
   *  [1] - powodzenie akcji
   *   1  - Udało się
   *   2  - Nie podano ID kategorii
   *   3  - ID kategorii nie jest liczbą
   *   4  - Nie podano nazwy
   *   5  - Nazwa posiada niedozwolone znaki
   *   6  - Adres jest niepoprawny
   *   7  - Opis zawiera niedozwolone znaki
   *   8  - Nie istnieje taka ikona
   *   9  - Nie udało się ustalić domyślnej ikony
   *   10 - Nie istnieje taka kategoria
   *   11 - $counting nie jest booleanem
   *   12 - ID ikony nie jest liczbą
   *   13 - Minimum postów nie jest liczbą
   *   14 - Minimum postów jest mniejsze od zera
   */
  public function addDzial($kategoria_id,$name,$opis,$adres,$icon_id,$counting=1,$minpost=0)
   {
	if($kategoria_id <= 0)
	 {
	  $r[1] = 2;
	  return $r;
	 }
	 
	if(!is_numeric($kategoria_id))
	 {
	  $r[1] = 3;
	  return $r;
	 }
	 
	if(empty($name))
	 {
	  $r[1] = 4;
	  return $r;
	 }
	 
	if(!empty($adres))
	 {
	  if(filter_var($adres,FILTER_VALIDATE_URL))
	   {
		$r[1] = 6;
		return $r;
	   }
	 }
	
	if(!empty($icon_id))
	 {
	  if(is_numeric($icon_id))
	   {
		$icon = $this->PDO->query("SELECT `id` FROM `".TBL."dzialicon` WHERE `id`=$icon_id");
	    
		if($icon->rowCount() == 1)
		 {
		  $trydefault = false;
		 }
		else
		 {
		  $r[1] = 8;
		return $r;
		 }
	   }
	  else
	   {
	    $r[1] = 12;
		return $r;
	   }
	 }
	else
	 {
	  $trydefault = true;
	 }
	 
	if(!($counting==1 or $counting==0))
	 {
	  $r[1] = 11;
	  return $r;
	 }
	
	if($trydefault == true)
	 {
	  $dicon = $this->PDO->query("SELECT `id` FROM `".TBL."dzialicon` WHERE `default`=1");
	  
	  if($dicon->rowCount() == 1)
	   {
	    $dicon_array = $dicon -> fetch();
		$icon_id = $dicon_array['id'];
	   }
	  else
	   {
		$r[1] = 9;
		return $r;
	   }
	 }
	 
	if(!empty($minpost))
	 {
	  if(!is_numeric($minpost))
	   {
		$r[1] = 13;
		return $r;
	   }
	  elseif($minpost < 0)
	   {
		$r[1] = 14;
		return $r;
	   }
	 }
	
	$this->PDO->exec("INSERT INTO `".TBL."dzial` VALUES(0,$kategoria_id,'$name','$opis',0,$icon_id,'$adres',0,$counting,$minpost)");
	$r[0] = $this->PDO->lastInsertId();
	$r[1] = 1;
	return $r;
   }
   
  /*
   * Czyszczenie działu
   * @Param: (Int)$dzial_id
   * @Return: (Int)
   *  1 - Udało się
   *  2 - Nie podano ID
   *  3 - Niepoprawne ID
   *  4 - Nie ma takiego działu
   *  5 - Nie ma tematów
   *  6 - Niewyjaśniony błąd
   */
  public function cleanDzial($dzial_id)
   {
	if($dzial_id <= 0)
	 {
	  return 2;
	 }
   
	if(!is_numeric($dzial_id))
	 {
	  return 3;
	 }
	 
	$dzial = $this->PDO->query("SELECT `deleted` FROM `".TBL."dzial` WHERE `id`=$dzial_id ");
	$dzial_array = $dzial -> fetch();
	
	if($dzial -> rowCount() == 0)
	 {
	  return 4;
	 }
	 
	$tematy = $this->PDO->query("SELECT `id` FROM `".TBL."tematy` WHERE `dzial_id`=$dzial_id AND `deleted`=0");
	$tematy_array = $tematy -> fetchAll();
	
	if($tematy -> rowCount() == 0)
	 {
	  return 5;
	 }
	 
	foreach($tematy_array as $ta)
	 {
	  $this -> delTopic($ta['id']);
	 }
	 
	return 1;
   }
   
  /*
   * Usuwanie tematu
   * @Param: (Int>0)$topic_id
   * @Return: (Int)
   *  1 - Udało się
   *  2 - Nie podano ID
   *  3 - Niepoprawne ID
   *  4 - Nie ma takiego tematu
   *  5 - Temat jest usunięty
   */
  public function delTopic($topic_id)
   {
	if($topic_id <= 0)
	 {
	  return 2;
	 }
	 
	if(!is_numeric($topic_id))
	 {
	  return 3;
	 }
	 
	$topic = $this->PDO->query("SELECT `deleted` FROM `".TBL."tematy` WHERE `id`=$topic_id");
	$topic_array = $topic -> fetch();
	
	if($topic -> rowCount() == 0)
	 {
	  return 4;
	 }
	
	if($topic_array['deleted']==1)
	 {
	  return 5;
	 }
	
	#Usuwanie postów
	$posty = $this->PDO->query("SELECT `id` FROM `".TBL."posty` WHERE `topic_id`=$topic_id AND `deleted`=0");
	
	$this->PDO->exec("
	UPDATE `".TBL."posty` SET `deleted`=1 WHERE `topic_id`=$topic_id;
	UPDATE `".TBL."tematy` SET `deleted`=1 WHERE `id`=$topic_id;
	");
	
	return 1;
   }
   
  /*
   * Usuwanie postów
   * @Param: (Int)$post_id
   * @Return: (Int)
   *  1 - Udało się
   *  2 - Nie podano ID
   *  3 - Niepoprawne ID
   *  4 - Nie ma takiego postu
   *  5 - post jest już usunięty
   */
  public function delPost($post_id)
   {
	if($post_id <= 0)
	 {
	  return 2;
	 }
	 
	if(!is_numeric($post_id))
	 {
	  return 3;
	 }
	 
	$post = $this->PDO->query("SELECT `deleted`,`starter`,`topic_id` FROM `".TBL."posty` WHERE `id`=$post_id");
	
	if($post -> rowCount() == 0) 
	 {
	  return 4;
	 }
	
	$post_array = $post -> fetch();
	
	if($post_array['deleted'] == 1)
	 {
	  return 5;
	 }
	 
	if($post_array['starter'] == 1)
	 {
	  $this->delTopic($post_array['topic_id']);
	 }
	 
	$this->PDO->exec("UPDATE `".TBL."posty` SET `deleted`=1 WHERE `id`=$post_id");
	return 1;
   }
   
  /*
   * Dodawanie pliku
   * @Param: (file)$plik_tmp, (String)$plik_nazwa, (Int)$plik_rozmiar, (Array) $rank
   * @Return: Array { Int, Int }
   * $rank { (Array)allowext, (Int)dirsize, (Int)filesize }
   * [1] 
   *  1 - Udało się
   *  2 - Nie podano pliku
   *  3 - Nie podano nazwy
   *  4 - Nie podano rozmiaru (lub jest równy 0)
   *  5 - Rozszerzenie jest niedozwolone
   *  6 - Nie podano rozszerzeń
   *  7 - Nie podano maksymalnej wielkości folderu
   *  8 - Nie podano maksymalnej wielkości pliku
   *  9 - Plik jest za duży
   *  10 - Plik nie zmieści się w folderze
   *  11 - Plik już istnieje
   */
  public function addFile($plik_tmp,$plik_nazwa,$plik_rozmiar,$rank)
   {
    #przerabianie nazwy pliku
	$plik_nazwa = strtolower($plik_nazwa); //same małe litery
	$plik_nazwa = str_replace(" ","_",$plik_nazwa); //spacje na _
	$plik_nazwa = str_replace("!","",$plik_nazwa); // ! na nic
	$plik_nazwa = str_replace("\\","",$plik_nazwa); // \ na nic
	
    $files_dir = $this->DirFile.'/files/'.$this->your_id;
	$file_path = $files_dir.'/'.$plik_nazwa;
	
	if(!is_dir($files_dir))
	 {
	  mkdir($files_dir);
	 }
	
	if(!$plik_tmp)
	 {
	  $r[1] = 2;
	  return $r;
	 }
	 
	if(empty($plik_nazwa))
	 {
	  $r[1] = 3;
	  return $r;
	 }
	 
	if(!$plik_rozmiar)
	 {
	  $r[1] = 4;
	  return $r;
	 }
	
	if(!$rank['allowext'])
	 {
	  $r[1] = 6;
	  return $r;
	 }
	 
	if($rank['dirsize']<0)
	 {
	  $r[1] = 7;
	  return $r;
	 }
	 
	if($rank['filesize']<0)
	 {
	  $r[1] = 8;
	  return $r;
	 }
	
	if(!is_dir($files_dir))
	 {
	  mkdir($files_dir);
	 }
  
	#informacje o folderze usera
	$dir_waga = filesize($files_dir)/1048576;  //waga folderu
	$dir_waga = round($dir_rozmiar,2);  //waga folderu zaokrąglona
	$dirplik_waga = $dir_waga+$plik_rozmiar; // łączna waga pliku i folderu
    
  	$plik_ext = end(explode('.',$plik_nazwa));
	
	if(!in_array($plik_ext,$rank['allowext']))
	 {
	  $r[1] = 5;
	  return $r;
	 }
	
	if($plik_rozmiar > $rank['filesize'])
	 {
	  $r[1] = 9;
	  return $r;
	 }
	
	if($rank['dirsize'] < $dir_waga+$plik_rozmiar)
	 {
	  $r[1] = 10;
	  return $r;
	 }
				 
	if(file_exists($file_path))
	 {
	  $r[1] = 11;
	  return $r;
	 }
	
	//fizyczne dodawanie pliku
	move_uploaded_file($plik_tmp,$file_path);
	
	//blob
	$fhandle = fopen($file_path,'r');  //otwieranie pliku
	$blob = base64_encode(fread($fhandle,filesize($file_path))); //przerabianie na bloba
	fclose($fhandle); //zamykanie pliku
				
	#dodawanie pliku do bazy
	$this->PDO->exec("INSERT INTO `".TBL."upfiles` VALUES(0,".$this->your_id.",'$plik_nazwa','$blob','$opis',".time().",1,0,0,$plik_rozmiar);");
	$r[0] = $this->PDO->lastInsertId();
	$r[1] = 1;
	return $r;
   }
   
  /*
   * Zmienienie hasła
   * @Param: (Array)$Info { current, new, newr }
   * @Return: (Int)
   *  1 - Udało się
   *  2 - Nie podano ID uzytkownika
   *  3 - Nie podano aktualnego hasła
   *  4 - Nie podano nowego hasła
   *  5 - Nie powtórzono hasła
   *  6 - Hasła nie są takie same
   *  7 - Hasło nie pasuje do aktualnego
   */
  public function changePass($Info)
   {
	if($Info['user_id'] <= 0)
	 {
	  return 2;
	 }
	 
	if($Info['current'] == '')
	 {
	  return 3;
	 }
	 
	if($Info['new'] == '')
	 {
	  return 4;
	 }
	 
	if($Info['newr'] == '')
	 {
	  return 5;
	 }
	
	if($Info['new'] !== $Info['newr'])
	 {
	  return 6;
	 }
	
	
	$user = $this->PDO->query("SELECT * FROM `".TBL."users` WHERE `id`=".$Info['user_id']);
	$user_array = $user -> fetch();
	
	$pass_new = $this->passPrepare($Info['new'],$user_array['time_rej']);
	
	if($user_array['password'] !== $this->passPrepare($Info['current'],$user_array['time_rej']))
	 {
	  return 7;
	 }
	 
	$this->PDO->exec("UPDATE `".TBL."users` SET `password`='".$pass_new."'"." WHERE `id`=".$Info['user_id']);
	return 1;
   }
   
  /*
   * Pobieranie działu
   * @Param: $dzial_id
   * @Return: Array { Array, Int }
   *  1 - Udało się
   *  2 - Nie podano ID
   *  3 - ID nie jest liczbą
   *  4 - Nie ma takiego działu
   */
  public function getDzial($dzial_id)
   {
	if($dzial_id <= 0)
	 {
	  $r[1] = 2;
	  return $r;
	 }
	 
	if(!is_numeric($dzial_id))
	 {
	  $r[1] = 3;
	  return $r;
	 }
	 
	$dzial = $this->PDO->query("SELECT * FROM `".TBL."dzial` WHERE `id`=$dzial_id");
	
	if($dzial -> rowCount() == 0)
	 {
	  $r[1] = 4;
	  return $r;
	 }
	
	$r[0] = $dzial -> fetch();
	$r[1] = 1;
	return $r;
   }
 
  /*
   * Usuwanie shouta
   * @Param: (int)$shout_id
   * @Return: Int
   *  1 - Udało się
   *  2 - Nie podano ID
   *  3 - Niepoprawne ID
   *  4 - Nie ma takiego shouta
   *  5 - Shout jest już usunięty
   */
  public function delShout($shout_id)
   {
	if($shout_id <= 0)
	 {
	  return 2;
	 }
	 
	if(!is_numeric($shout_id))
	 {
	  return 3;
	 }
	 
	$shout = $this->PDO->query("SELECT `deleted` FROM `".TBL."shoutbox` WHERE `id`=$shout_id");
	
	if($shout->rowCount() == 0)
	 {
	  return 4;
	 }
	 
	$shout_array = $shout -> fetch();
	
	if($shout_array['deleted'] == 1)
	 {
	  return 5;
	 }
	 
	$this->PDO->exec("UPDATE `".TBL."shoutbox` SET `deleted`=1 WHERE `id`=$shout_id");
	return 1;
   }
  
  /*
   * Edytowanie shouta
   * @Param: (Int)$shout_id, (String)$msg
   * @Return: Int
   *  1 - Udało się
   *  2 - Nie podano ID
   *  3 - Niepoprawne ID
   *  4 - Nie ma takiego shouta
   *  5 - Shout jest usunięty
   *  6 - Treść jest pusta
   *  7 - Treść zawiera niedozwolone znaki
   */
  public function editShout($shout_id,$msg)
   {
	if($shout_id <= 0)
	 {
	  return 2;
	 }
	 
	if(!is_numeric($shout_id))
	 {
	  return 3;
	 }
	 
	$shout = $this->PDO->query("SELECT `deleted` FROM `".TBL."shoutbox` WHERE `id`=$shout_id");
	
	if($shout->rowCount() == 0)
	 {
	  return 4;
	 }
	 
	$shout_array = $shout -> fetch();
	
	if($shout_array['deleted'] == 1)
	 {
	  return 5;
	 }
	 
	if(strlen($msg) == 0)
	 {
	  return 6;
	 }
	 
	$this->PDO->exec("UPDATE `".TBL."shoutbox` SET `tresc`='$msg' WHERE `id`=$shout_id");
	return 1;
   }
   
  /*
   * Dodawanie shouta
   * @Param: $msg
   * @Return: Array { Int, Int }
   *                  ID   powodzenie
   *  1 - Udało się
   *  2 - Nie podano wiadomości
   *  3 - Wiadomość zawiera niedozwolone znaki
   *  4 - Flood! 5 sekund
   */
  public function addShout($msg)
   {
	if(empty($msg))
	 {
	  $r[1] = 2;
	  return $r;
	 }
	 
	$shout = $this->PDO->query("SELECT `time` FROM `".TBL."shoutbox` WHERE `user_id`=".$this->your_id." ORDER BY `id` DESC LIMIT 1");
    
	if($shout->rowCount() == 0)
	 {
	  $shout_time = 0;
	 }
	else
	 {
	  $sa = $shout->fetch();
	  $shout_time = $sa['time'];
	 }
	
	if(time() - $last_time < 5 && $shout->rowCount() > 0)
	 {
	  $r[1] = 4;
	  return $r;
	 }
	 
	if($this->your_id == 0)
	 {
	  $user_id = 0;
	 }
	else
	 {
	  $user_id = $this->your_id;
	 }
	 
	$this->PDO->exec("INSERT INTO `".TBL."shoutbox` VALUES(0,'$user_id','$msg','".time()."','".$this->your_ip."',0)");
	$r[0] = $this->PDO->lastInsertId();
	$r[1] = 1;
	return $r;
   }
   
  /*
   * Wysyłanie emaila
   * @Param: (String)$email, (String)$msg, (String)$title
   * @Return: (Int)
   *  1 - Udało się
   *  2 - Nie podano emaila
   *  3 - Email niepoprawny
   *  4 - Nie podano treści
   *  5 - Niepoprawna treść
   *  6 - Nie udało się wysłać emaila
   *  7 - Nie podano tytułu
   *  8 - Niepoprawny tytuł
   */
  public function sendMail($email,$msg,$title)
   {
	if(empty($msg))
	 {
	  return 4;
	 }
	 
	if(empty($email))
	 {
	  return 2;
	 }
	 
	if(!filter_var($email,FILTER_VALIDATE_EMAIL))
	 {
	  return 3;
	 }
	 
	if(empty($title))
	 {
	  return 7;
	 }
	 
	$naglowki = "From: ".EMAIL_NOREPLY." \nReply-To: ".EMAIL_NOREPLY." \n"."MIME-Version: 1.0 \nContent-type: text/plain; charset=UTF-8";
 
	if(mail($email,$title,$msg,$naglowki))
	 {
	  return 1;
	 }
	else
	 {
	  return 6;
	 }
   }
   
  /*
   * Pobieranie listy ostrzeżeń
   * @Param: $user_id
   * @Return: Array { Int, Int }
   *  [1] - powodzenie
   *   1 - Udało się
   *   2 - Nie podano ID
   *   3 - Niepoprawne ID
   *   4 - Użytkownik nie istnieje
   */
  public function getWarnlist($user_id)
   {
	if($user_id <= 0)
	 {
	  $r[1] = 2;
	  return $r;
	 }
	 
	if(!is_numeric($user_id))
	 {
	  $r[1] = 3;
	  return $r;
	 }
	
	if($this->User_exists($user_id) == false)
	 {
	  $r[1] = 4;
	  return $r;
	 }
	
	$warnlist = $this->PDO->query("SELECT * FROM `".TBL."warnlist` WHERE `user_id`=$user_id ORDER BY `id` DESC");
	
	$r[0] = $warnlist -> fetchAll();
	$r[1] = 1;
	return $r;
   }
   
  /*
   * Pobieranie informacji o użytkowniku
   * @Param: (Int)$user_id
   * @Result: Array { Array, Int }
   *  1 - Udało się
   *  2 - Nie podano ID
   *  3 - Niepoprawne ID
   *  4 - Taki użytkownik nie istnieje
   */
  public function getUser($user_id)
   {
	if($user_id <= 0)
	 {
	  $r[1] = 2;
	  return $r;
	 }
	 
	if(!is_numeric($user_id))
	 {
	  $r[1] = 3;
	  return $r;
	 }
	 
	$user = $this->PDO->query("SELECT * FROM `".TBL."users` WHERE `id`=$user_id");
	
	if($user->rowCount() == 0)
	 {
	  $r[1] = 4;
	  return $r;
	 }
	 
	$r[0] = $user -> fetch();
	$r[1] = 1;
	return $r;
   }

  /*
   * Czy użytkownik istnieje
   * @Param: (Int)$user_id
   * @Return: boolean
   */   
  public function User_exists($user_id)
   {
	if($user_id <= 0)
	 {
	  return false;
	 }
	 
	if(!is_numeric($user_id))
	 {
	  return false;
	 }
	 
	$user = $this->PDO->query("SELECT `id` FROM `".TBL."users` WHERE `id`=$user_id");
	
	if($user->rowCount() == 0)
	 {
	  return false;
	 }
	 
	return true;
   }
   
  /* 				FUNKCJA NIEDOKOŃCZONA
   * Pokazywanie nicku który ma być wyświetlany, czyli z kolorem i ew. linkiem do profilu
   * @Param: (Int)$user_id, (Boolean)$purl=true
   * @Return: Array { String, Int }
   *  1 - Udało się
   *  2 - Nie podano ID
   *  3 - Niepoprawne ID
   *  4 - Nie ma takiego użytkownika
   *  5 - $purl nie jest booleanem
   */
  public function getDisplayNick($user_id,$purl=true)
   {
	if($user_id <= 0)
	 {
	  $r[1] = 2;
	  return $r;
	 }
	 
	if(!is_bool($purl))
	 {
	  $r[1] = 5;
	  return $r;
	 }
	 
	if(!is_numeric($user_id))
	 {
	  $r[1] = 3;
	  return $r;
	 }
	 
	if($this->User_exists($user_id)==false)
	 {
	  $r[1] = 4;
	  return $r;
	 }
	 
	$user = $this->getUser($user_id);
	$user_array = $user[1];
	
	return $user_array['nick'];
   }
   
  /*
   * Pobieranie listy userów online
   * Return: Array
   */
  public function getOnlineUsers()
   {
	$users = $this->PDO->query("SELECT `user_id` FROM `".TBL."online` WHERE `expire`=0");
	
	if($users -> rowCount() == 0)
	 {
	  return array();
	 }
	else
	 {
	  return $users -> fetchAll();
	 }
   }
   
  /*
   * Zapisywanie logów
   */
  public function saveLog($SU=array())
   {
	if($_POST)
	 {
	  foreach($_POST as $key => $value)
	   {
		if($key=='password') $value=md5($value);
		if($key=='pass') $value=md5($value);
		if($key=='pass_current') $value=md5($value);
		if($key=='pass_newpass') $value=md5($value);
		if($key=='pass_newpassr') $value=md5($value);
		if($key=='passrepeat') $value=md5($value);
		$post_array[] = "$key => $value";
	   }
	  $post_array = implode(",",$post_array);
	 }
  
	/*
	if($_GET)
	 {
	  foreach($_GET as $key => $value)
	   {
		$get_array[] = "$key => $value";
	   }
	  $get_array = implode(",",$get_array);
	 }
	*/

	if($SU)
	 {
	  $SU_array = implode(",",$SU);
	 }

	$this->PDO->exec("INSERT INTO `".TBL."log` VALUES(0,".$this->your_id.",'".$this->your_ip."','".time()."','$SU_array','$post_array');");
   }
   
  /*
   * Generowanie CheckSum w MD5
   * @Param: (String)$file_path
   * @Return: (md5)String
   * 
   */
  public function genCheckSum($file_path)
   {
	if(!file_exists($file_path))
	 {
	  return 0;
	 }
	 
	if(filesize($file_path) == 0)
	 {
	  return 0;
	 }
	 
	$blob = base64_encode(fread(fopen($file_path),filesize($file_path)));
	
	return md5($blob);
   }
   
  /*
   * Dodawanie wiadomości
   * @Param: (Int)$user_id, (String)$title, (String)$msg
   * @Return: Array { Int, Int }
   *  [0] - ID dodanej wiadomości
   *  [1] - Powodzenie akcji
   *   1 - Udało się
   *   2 - Nie podano ID usera
   *   3 - ID usera jest niepoprawne
   *   4 - Nie podano tytułu
   *   5 - Tytuł zawiera niedozwolone znaki
   *   6 - Nie podano wiadomości
   *   7 - Wiadomość zawiera niedozwolone znaki
   *   8 - Nadawca nie został podany ($this->your_id===0)
   *   9 - Nie istnieje użytkownik (odbiorca)
   *   10 - Nie istnieje użytkownik (nadawca)
   *    - 
   */
  public function addMessage($user_id,$title,$msg)
   {
	if($user_id <= 0)
	 {
	  $r[1] = 2;
	  return $r;
	 }
	 
	if(!is_numeric($user_id))
	 {
	  $r[1] = 3;
	  return $r;
	 }
	 
	if(empty($title))
	 {
	  $r[1] = 4;
	  return $r;
	 }
	 
	if(empty($msg))
	 {
	  $r[1] = 6;
	  return $r;
	 }
	 
	if($this->your_id <= 0)
	 {
	  $r[1] = 8;
	  return $r;
	 }
	 
	if(!$this->User_exists($user_id))
	 {
	  $r[1] = 9;
	  return $r;
	 }
	 
	if(!$this->User_exists($this->your_id))
	 {
	  $r[1] = 10;
	  return $r;
	 }
	 
	$this->PDO->exec("INSERT INTO `".TBL."message` VALUES(0,'$your_id','$user_id','$m_title','$m_tresc',0,".time().",0);");
	$r[0] = $this->PDO->lastInsertId();
	$r[1] = 1;
	return $r;
   }
   
  /*
   * Usuwanie wiadomości
   * @Param: $msg_id
   * @Return: Int
   *  1 - Udało się
   *  2 - Nie podano ID
   *  3 - Niepoprawne ID
   *  4 - Nie ma takiej wiadomości
   *  5 - Wiadomość jest już usunięta
   */
  public function delMessage($msg_id)
   {
	if($msg_id <= 0)
	 {
	  return 2;
	 }
	 
	if(!is_numeric($msg_id))
	 {
	  return 3;
	 }
	 
	$msg = $this->PDO->query("SELECT `deleted` FROM `".TBL."message` WHERE `id`=$msg_id");
	 
	if($msg -> rowCount() == 0)
	 {
	  return 4;
	 }
	 
	$msg_array = $msg -> fetch();
	 
	if($msg_array['deleted'] == 1)
	 {
	  return 5;
	 }
	
	$this->PDO->exec("UPDATE `".TBL."message` SET `deleted`=1 WHERE `id`=$msg_id");
	return 1;
   }
   
  /*
   * Pobieranie wiadomośći
   * @Param: (Int)$msg_id
   * @Return: Array { Array, Int }
   *  [0] - Tablica z danymi
   *  [1] - Powodzenie akcji
   *   1 - Udało się
   *   2 - Nie podano ID
   *   3 - Niepoprawne ID
   *   4 - Nie ma takiej wiadomości
   */
  public function getMessage($msg_id)
   {
	if($msg_id <= 0)
	 {
	  $r[1] = 2;
	  return $r;
	 }
	 
	if(!is_numeric($msg_id))
	 {
	  $r[1] = 3;
	  return $r;
	 }
	
	$msg = $this->PDO->query("SELECT * FROM `".TBL."message` WHERE `id`=$msg_id");
	 
	if($msg -> rowCount() == 0)
	 {
	  $r[1] = 4;
	  return $r;
	 }
	 
	$r[0] = $msg -> fetch();
	$r[1] = 1;
	return $r;
   }
   
  /*
   * Wysyłanie aktywacji emailem
   */
  public function sendMailActivation($user_id)
   {
	$user = $this->getUser($user_id);
	
	switch($user[1])
	 {
	 case 1:
	  $user_array = $user[0];
	  $temat = 'Rejstracja na '.$this->Config->SITE_NAME;
	  $text = "Witamy!\n
	  Zarejestrowałeś się na ".$this->Config->SITE_NAME." używając tego adresu email. Jeśli to nieprawda, zignoruj tę wiadomość.\n
	  W celu aktywowania swojego konta proszę kliknąć w poniższy link:\n".$this->Config->MAIN_DIR.'uzytkownik/aktywuj/'.$user_array['acticode'];
	
	  $this->sendMail($user_array['email'],$text,EMAIL_ACTIVATION_TITLE);
	 break;
	 }
   }
   
  /*
   * Zmienianie avatara
   * @Param: (Int)$user_id, (Array { File, String, Int })$avatar, (Array { String, -||- })$allowExt
   *								 plik  nazwa   waga
   * @Return: Int
   *  1 - Udało się
   *  2 - Nie podano ID usera
   *  3 - Niepoprawne ID usera
   *  4 - Nie ma takiego usera
   *  5 - $avatar nie jest tablicą
   *  6 - Nie podano pliku
   *  7 - Nie podano nazwy pliku
   *  8 - Waga pliku jest zerowa
   *  9 - Nie podano dozwolonych rozszerzeń
   *  10 - Rozszerzenie nie jest dozwolone
   *  11 - $allowExt nie jest tablicą
   */
  public function setAvatar($user_id,$avatar,$allowExt)
   {
	$user = $this->getUser($user_id);
	
	switch($user[0])
	 {
	 case 1:
	  $user_array = $user[1];
	 break;
	 case 2:
	  return 2;
	 break;
	 case 3:
	  return 3;
	 break;
	 case 4:
	  return 4;
	 break;
	 }
	 
	if(!is_array($avatar))
	 {
	  return 5;
	 }
	 
	if(empty($avatar['file']))
	 {
	  return 6;
	 }
	 
	if(empty($avatar['name']))
	 {
	  return 7;
	 }
	 
	if(empty($avatar['size']))
	 {
	  return 8;
	 }
	 
	if(empty($allowExt))
	 {
	  return 9;
	 }
	 
	if(!is_array($allowExt))
	 {
	  return 11;
	 }
	 
	$ext = $this->getFileExt($avatar['name']);
	 
	if(!in_array($ext,$allowExt))
	 {
	  echo $ext;
	  return 10;
	 }
	 
	$avDir = $this->DirFile.'/avatar/';
	 
	foreach($allowExt as $aE)
	 {
	  if(file_exists($avDir.$user_id.'.'.$aE))
	   {
		unlink($avDir.$user_id.'.'.$aE);
	   }
	 }
	 
	$this->PDO->exec("UPDATE `".TBL."users` SET `avatar`='".$avatar['name']."' WHERE `id`=".$user_id);
	move_uploaded_file($avatar['file'],$avDir.$user_id.'.'.$ext);
	return 1;
   }
   
  public function getFileExt($name)
   {
	return end(explode('.',$name));
   }
   
  public function ShowUserNick($user_id,$class='',$link=0,$just=0)
   {
	if($user_id <= 0)
	 {
	  return false;
	 }
	
	$user = $this->PDO->query("SELECT * FROM `".TBL."users` WHERE `id`=$user_id");
	
	if($user -> rowCount() == 0)
	 {
	  return false;
	 }

	$user_array = $user -> fetch();
	
	if($just==1)
	 {
	  return $user_array['nick'];
	 }
  
	$rank = $this->PDO->query("SELECT * FROM `".TBL."rangi` WHERE `id`=".$user_array['rank']);
	$rank_array = $rank -> fetch();
  
	#ustalanie znacznika html
	if($link==1)
	 {
	  $z_name = 'a';
	  $z_mid = "href='".$this->Config->MAIN_DIR."user/$user_id'";
	 }
	else
	 {
	  $z_name = 'span';
	  $z_mid = '';
	 }
   
	#ustalanie całości
	$string = "<$z_name $z_mid style='".$rank_array['style']."' class='$class' >".$user_array['nick']."</$z_name>";
   
	return $string;
   }
   
  /*
   * Dodawanie tematu
   * @Param: (Int)$user_id
   * @Param: (String)$title
   * @Param: (Int)$dzial_id
   * @Param: (Boolean)$canlocked
   * @Param: (Boolean)$canbelowmin
   * @Return: Array { Int, Int }
   *  [0] - ID dodanego tematu
   *  [1] - powodzenie
   *   1  - Udało się
   *   2  - Nie podano ID usera
   *   3  - Niepoprawne ID usera
   *   4  - Nie ma takiego usera
   *   5  - Nie podano tytułu
   *   6  - Tytuł jest niepoprawny
   *   7  - Nie podano ID działu
   *   8  - Niepoprawne ID działu
   *   9  - Nie ma takiego działu
   *   10 - Dział jest zablokowany (i nie ma uprawnień do pisania)
   *   11 - Dział jest usunięty
   *   12 - Tytuł jest za długi (50 znaków)
   *   13 - Nie podano $canlocked
   *   14 - $canlocked nie jest booleanem
   *   15 - Nie wystarczającej ilości postów (i nie ma uprawnień do zignorowania)
   */
  public function addTopic($user_id,$title,$dzial_id,$canlocked,$canbelowmin=false)
   {
	$user = $this->getUser($user_id);
	 
	switch($user[1])
	 {
	 case 1:
	  $user_array = $user[0];
	 break;
	 case 2:
	  $r[1] = 2;
	  return $r;
	 break;
	 case 3:
	  $r[1] = 3;
	  return $r;
	 break;
	 case 4:
	  $r[1] = 4;
	  return $r;
	 break;
	 }
	 
	if(empty($title))
	 {
	  $r[1] = 5;
	  return $r;
	 }
	 
	if(strlen($title) > 50)
	 {
	  $r[1] = 12;
	  return $r;
	 }
	 
	$dzial = $this->getDzial($dzial_id);
	 
	switch($dzial[1])
	 {
	 case 1:
	  $dzial_array = $dzial[0];
	 break;
	 case 2:
	  $r[1] = 7;
	  return $r;
	 break;
	 case 3:
	  $r[1] = 8;
	  return $r;
	 break;
	 case 4:
	  $r[1] = 9;
	  return $r;
	 break;
	 }
	 
	if(!isset($canlocked))
	 {
	  $r[1] = 13;
	  return $r;
	 }
	 
	if(!is_bool($canlocked))
	 {
	  $r[1] = 14;
	  return $r;
	 }
	 
	if($dzial_array['deleted'] == 1)
	 {
	  $r[1] = 11;
	  return $r;
	 }
	 
	if($dzial_array['lock'] == 1 && $canlocked==false)
	 {
	  $r[1] = 10;
	  return $r;
	 }
	 
	if(!($canbelowmin===false or $canbelowmin===true))
	 {
	  
	 }
	 
	if($canbelowmin==false)
	 {
	  $posts = $this->PDO->query("SELECT `id` FROM `".TBL."posty` WHERE `user_id`=$user_id AND `deleted`=0 AND `counted`=1");
	  if($dzial_array['minpost']<$posts->rowCount())
	   {
	   
	   }
	 }
	 
	$this->PDO->exec("INSERT INTO `".TBL."tematy` VALUES(0,$dzial_id,$user_id,".time().",'$title',0,0,0,".time().")");
	$r[0] = $this->PDO->lastInsertId();
	$r[1] = 1;
	return $r;
   }
   
  /*
   * Dodawanie warnów
   * @Param: (Int)$user_id
   * @Param: (Int)$admin_id
   * @Param: (String)$reason
   * @Param: (Boolean)$type
   * @Param: (Int)$procent
   * @Return: Int
   *  1 - Udało się
   *  2 - Nie podano ID usera
   *  3 - Niepoprawne ID usera
   *  4 - Nie istnieje taki user
   *  5 - Nie podano ID admina
   *  6 - Niepoprawne ID admina
   *  7 - Nie istnieje konto admina
   *  8 - Nie podano powodu
   *  9 - Powód zawiera niedozwolone znaki
   *  10 - Niepoprawny typ (0/1)
   *  11 - Nie podano procentów
   *  12 - Procenty nie są liczbą 
   *  13 - Nie podano IP
   *  14 - Niepoprawne IP 
   */
  public function addWarn($user_id,$admin_id,$admin_ip,$reason,$type,$procent)
   {
	$user = $this->getUser($user_id);
	
	switch($user[1])
	 {
	 case 2: //nie ma id
	  return 2;
	 break;
	 case 3: //niepoprawne id
	  return 3;
	 break;
	 case 4: //nie istnieje
	  return 4;
	 break;
	 }
	
	$admin = $this->getUser($admin_id);
	
	switch($admin[1])
	 {
	 case 2: //nie ma id
	  return 5;
	 break;
	 case 3: //niepoprawne id
	  return 6;
	 break;
	 case 4: //nie istnieje
	  return 7;
	 break;
	 }
	 
	if(empty($reason))
	 {
	  return 8;
	 }
	 
	if(!($type==0 or $type==1))
	 {
	  return 10;
	 }
	 
	if(empty($procent))
	 {
	  return 11;
	 }
	 
	if(!is_numeric($procent))
	 {
	  return 12;
	 }
	 
	if($type==0)
	 {
	  $procent_all = $user[0]['warn'] - $procent;
	 }
	else
	 {
	  $procent_all = $procent + $user[0]['warn'];
	 }
	 
	$this->PDO->exec("INSERT INTO `".TBL."warnlist` VALUES(0,'$admin_ip',$user_id,".time().",'$reason',$procent,$type,$admin_id)");
	$this->PDO->exec("UPDATE `".TBL."users` SET `warn`=$procent_all WHERE `id`=$user_id");
	return 1;
   }
   
  public function toBits($value)
   {
    //liczenie ile liter to jednostka, np: GB=2, B=1
	for($i=0;$i<strlen($value);$i++)
	 {
	  if(!is_numeric($value{$i})) //jeśli nie jest liczbą czyli jest literą
	   {
		$jedc++; //dodajemy
	   }
	 }
	 
	$jedn = substr($value,-$jedc); //jednostka, np: GB
	
	$value = substr($value,0,-$jedc); //sama wartość bez jednostki
	
	$pows['kB'] = 10; // kilo
	$pows['MB']  = 20; // mega
	$pows['GB'] = 30; // giga
	$pows['TB'] = 40; // tera
	$pows['PB'] = 50; // peta
	$pows['EB'] = 60; // eksa
	$pows['ZB'] = 70; // zetta
	$pows['YB'] = 80; // jotta
	
	if($jedn=='B') //bajt
	 {
	  $bits = $value;
	 }
	elseif($jedn=='b') //bit
	 {
	  $bits = $value / 8;
	 }
	else
	 {
	  $bits = pow(2,$pows[$jedn]) * $value; //potęgowanie
	 }
	 
	return $bits; //zwracanie
   }
   
  public function ShowRating($file_id)
   {
	#zapytanie o sumę
	$suma = $this->PDO->query("SELECT SUM(vote) FROM `".TBL."filevote` WHERE `file_id`=$file_id");
	
	#zapytanie o głosy na ten plik
	$vote = $this->PDO->query("SELECT `id` FROM `".TBL."filevote` WHERE `file_id`=$file_id");
	$vote_rows = $vote -> rowCount();
  
	#przeciwko dzieleniu przez 0
	if($vote_rows == 0) $vote_rows=1; //żeby nie było dzielenia przez 0
	$suma = $suma->fetchColumn(0);
  
	#obliczanie średniej
	$srednia = $suma/$vote_rows;
  
	#Generowanie gwiazdek
	for($a=1;$a<6;$a++)
 	 {
	  if($srednia<$a)
	   {
		$class = "grey";
	   }
	  else
	   {
		$class = "gold";
	   }
	  $rate = $a;
	  
	  $star[] = '<div id="ratestar_'.$file_id.'_'.$rate.'" class="'.$class.'" onclick="FileVote('.$file_id.','.$rate.')" alt="'.$rate.'" ></div>'."\n";
	 }
	$rate_stars = implode('',$star);
  
	return $rate_stars;
   }
   
  /*
   * Pobieranie kategorii
   * @Param: $category_id
   * @Return: Array { Array, Int }
   *  1 - Udało się
   *  2 - Nie podano ID
   *  3 - ID nie jest liczbą
   *  4 - Nie ma takiej kategorii
   */
  public function getCategory($category_id)
   {
	if($category_id <= 0)
	 {
	  $r[1] = 2;
	  return $r;
	 }
	 
	if(!is_numeric($category_id))
	 {
	  $r[1] = 3;
	  return $r;
	 }
	 
	$kategoria = $this->PDO->query("SELECT * FROM `".TBL."kategorie` WHERE `id`=$category_id");
	
	if($kategoria -> rowCount() == 0)
	 {
	  $r[1] = 4;
	  return $r;
	 }
	
	$r[0] = $kategoria -> fetch();
	$r[1] = 1;
	return $r;
   }
   
  /*
   * Edycja działu
   * @Param: (Int)$dzial_id
   * @Param: (Int)$category_id
   * @Param: (String)$nazwa
   * @Param: (String)$opis
   * @Param: (Int)$lock
   * @Param: (Int)$icon_id
   * @Param: (String)$url
   * @Param: (Int)$deleted
   * @Param: (Int)$counting
   * @Param: (Int)$minpost
   * @Return: int
   *  1  - Udało się
   *  2  - Nie podano ID kategorii
   *  3  - ID kategorii nie jest liczbą
   *  4  - Nie podano nazwy
   *  5  - Nazwa posiada niedozwolone znaki
   *  6  - Adres jest niepoprawny
   *  7  - Opis zawiera niedozwolone znaki
   *  8  - Nie istnieje taka ikona
   *  9  - Nie udało się ustalić domyślnej ikony
   *  10 - Nie istnieje taka kategoria
   *  11 - $counting nie jest booleanem
   *  12 - ID ikony nie jest liczbą
   *  13 - Minimum postów nie jest liczbą
   *  14 - Minimum postów jest mniejsze od zera
   */
  public function updateDzial($kategoria_id,$name,$opis,$adres,$icon_id,$deleted,$counting,$minpost)
   {
	if($kategoria_id <= 0)
	 {
	  return 2;
	 }
	 
	if(!is_numeric($kategoria_id))
	 {
	  return 3;
	 }
	 
	if(empty($name))
	 {
	  return 4;
	 }
	 
	if(!empty($adres))
	 {
	  if(filter_var($adres,FILTER_VALIDATE_URL))
	   {
		return 6;
	   }
	 }
	
	if(!empty($icon_id))
	 {
	  if(is_numeric($icon_id))
	   {
		$icon = $this->PDO->query("SELECT `id` FROM `".TBL."dzialicon` WHERE `id`=$icon_id");
	    
		if($icon->rowCount() == 1)
		 {
		  $trydefault = false;
		 }
		else
		 {
		  return 8;
		 }
	   }
	  else
	   {
	    return 12;
	   }
	 }
	else
	 {
	  $trydefault = true;
	 }
	 
	if(!($counting==1 or $counting==0))
	 {
	  return 11;
	 }
	
	if($trydefault == true)
	 {
	  $dicon = $this->PDO->query("SELECT `id` FROM `".TBL."dzialicon` WHERE `default`=1");
	  
	  if($dicon->rowCount() == 1)
	   {
	    $dicon_array = $dicon -> fetch();
		$icon_id = $dicon_array['id'];
	   }
	  else
	   {
		return 9;
	   }
	 }
	 
	if(!empty($minpost))
	 {
	  if(!is_numeric($minpost))
	   {
		return 13;
	   }
	  elseif($minpost < 0)
	   {
		return 14;
	   }
	 }
	
	$this->PDO->exec("UPDATE `".TBL."dzial` SET `id_kategoria`=$category_id, `nazwa`='$nazwa', `opis`='$opis', `lock`=$lock, `icon_id`=$icon_id `url`='$url', `deleted`=$deleted, `counting`=$counting, `minpost`=$minpost WHERE `id`=$dzial_id");
	return 1;
   }
   
  /*
   * Pobieranie raportu
   * @Param: (Int)$report_id
   * @Return: Array { Array, Int }
   *  [0] - Informacje
   *  [1] - Powodzenie
   *   1 - Udało się
   *   2 - Nie podano ID
   *   3 - Niepoprawne ID
   *   4 - Nie istnieje taki raport
   */
  public function getReport($report_id)
   {
	if($report_id <= 0)
	 {
	  $r[1] = 2;
	  return $r;
	 }
	
	if(!is_numeric($report_id))
	 {
	  $r[1] = 3;
	  return $r;
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
   
  /*
   * Pobieranie avatara
   */
  public function getAvatar($user_id)
   {
	$UserRank = $this -> getUserRank($user_id);
	$P = new PermissionsHP($this->Spyc);
	$P->setUserRank($UserRank[0]);
	
	#ustalanie pliku
	$avatar_dir = 'avatar/';
	$img_tocheck = $avatar_dir.$user_id;
  
	$allowExt = $P->getRank('AvatarExts');
	if(!$allowExt) $allowExt = array();
	
	foreach($allowExt as $aE)
	 {
	  if(file_exists(dirname(__FILE__).'/'.$img_tocheck.'.'.$aE))
       {
		$img = $this->Config->MAIN_DIR.$img_tocheck.'.'.$aE;
	   }
	 }
  
	if(empty($img))
	 {
      $img = $this->Config->MAIN_DIR.$avatar_dir.'default.png';
	 }
	return $img;
   }
   
  public function isOnline($user_id)
   {
    if($user_id <= 0)
	 {
	  return 0;
	 }
	
	if(!is_numeric($user_id))
	 {
	  return 0;
	 }
	 
    $Online = $this->PDO->query("SELECT `expire` FROM `eif_online` WHERE `user_id`=$user_id AND `expire`=0");
	return $Online->rowCount();
   }
   
  /*
   * Captcha
   * @Return: Array
   *  [0] - Obrazek
   *  [1] - Tekst z obrazka
   */
  public function genCaptcha()
   {
	$pool = '0123456789abcdefghijklmnopqrstuvwxyz';
	$img_width = 120;
	$img_height = 30;
	
	for($i = 0; $i < 7; $i++)
	 {
	  $str .= substr($pool, mt_rand(0, strlen($pool) -1), 1);
	 }
	
	$im = imagecreate($img_width, $img_height);
	$bg_color = imagecolorallocate($im,163,163,163);
	$font_color = imagecolorallocate($im,252,252,252);
	$grid_color = imagecolorallocate($im,31,0,0);
	$border_color = imagecolorallocate ($im, 174, 174, 174);
	imagefill($im,1,1,$bg_color);
	for($i=0; $i<1600; $i++)
	 {
	  $rand1 = rand(0,$img_width);
	  $rand2 = rand(0,$img_height);
	  imageline($im, $rand1, $rand2, $rand1, $rand2, $grid_color);
	 }

	$x = rand(5, $img_width/(7/2));
	imagerectangle($im, 0, 0, $img_width-1, $img_height-1, $border_color);

	for($a=0; $a < 7; $a++)
	{
	  imagestring($im, 5, $x, rand(6 , $img_height/3), substr($str, $a, 1), $font_color);
	  $x += (5*2);
	 }
	 
	$r[0] = $im;
	$r[1] = $str;

	imagedestroy($im);
	return $r;
   }
 }
?>