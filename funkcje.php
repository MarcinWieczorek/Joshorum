<?php
#slashe w GET i POST
if(isset($_GET)) foreach($_GET as $key => $val)
 {
  $_GET[$key] = addslashes($_GET[$key]);
  $_GET[$key] = htmlspecialchars($_GET[$key]);
 }
if(isset($SU)) foreach($SU as $key => $val)
 {
  $SU[$key] = addslashes($SU[$key]);
  $SU[$key] = htmlspecialchars($SU[$key]);
 }
if(isset($_POST)) foreach($_POST as $key => $val)
 {
  $_POST[$key] = addslashes($_POST[$key]);
  $_POST[$key] = htmlspecialchars($_POST[$key]);
 }
 
#łączenie z bazą Mysql
function MysqlConnect()
 {
  #łączenie
  $conn = mysql_connect(MYSQL_HOST.':'.MYSQL_PORT,MYSQL_USER,MYSQL_PASS); //host, user, hasło
  mysql_select_db(MYSQL_BASE); //wybieranie bazy
  return $conn;
 }
 
#Wiadomość błędu
function error_message($str)
 {
  $str = "<div class='error'>$str</div>";
  return $str;
 }

#Wiadomość informacji
function answer_message($str)
 {
  $str = "<div id='answer'>$str</div>";
  return $str;
 }
 
#aktywowanie konta
function UserActivate($user_acticode)
 {
  if($user_acticode)
   {
	#zapytanie
	$user_query = mysql_query("SELECT * FROM eif_users WHERE acticode='$user_acticode'");
	$user_rows = mysql_num_rows($user_query);
  
	#sprawdzanie czy user istnieje
	if($user_rows)
	 {
      #tablica z userem
	  $user_array = mysql_fetch_assoc($user_query);
	  $user_id = $user_array['id'];
	  
	  #ranga usera (tablica)
	  $user_rank = CheckUserRank($user_id);
	  
	  #sprawdzanie czy już nie aktywowano
	  if($user_rank['id']==6)
	   {
		#zapytanie
		$activate_query = mysql_query("UPDATE eif_users SET `rank`=2, `yrank`='User' WHERE `id`=$user_id ") or die(mysql_error());
		
		#wiadomość
		echo(answer_message("Konto zostało pomyślnie aktywowane. Możesz się już zalogować."));
	   }
	  else { echo(error_message("Twoje konto nie jest <strong>nieaktywne</strong>.")); }
	 }
	else { echo(error_message("Konto z takim kodem nie istnieje.")); }
   }
  else { echo(error_message("Nie podałeś kodu")); }
 }
  
#pokazywanie danych usera
function ShowUserData($user_id)
 {
  global $your_id;
  global $PDO;
  
  if(!empty($user_id))
   {
    if(is_numeric($user_id))
	 {
	  #zapytanie o usera
	  $user_query = mysql_query("SELECT * FROM eif_users WHERE `id`=$user_id");
	  
	  #sprawdzanie czy user istnieje
	  $user_rows = mysql_num_rows($user_query);
	  
	  if($user_rows) //czy user istnieje
	   {
		$user_array = mysql_fetch_assoc($user_query);
		
		#zapytanie o rangę (nazwa)
		$rank_query = mysql_query("SELECT * FROM eif_rangi WHERE `id`=".$user_array['rank']);
		$rank_array = mysql_fetch_assoc($rank_query);
		$rank_name = $rank_array['name'];
  
		#zapytanie o pliki usera
		$files = $PDO->query("SELECT id FROM eif_upfiles WHERE `user_id`=$user_id AND `deleted`=0");
		
		#ilość postów usera
		$posty = $PDO->query("SELECT id FROM `eif_posty` WHERE `user_id`=$user_id AND `counted`=1 AND `deleted`=0");
		
		#przetwarzanie nicku usera
		$user_nick_parsed = ShowUserNick($user_id,'',0);
		
		if($user_array['gadu']>0)
		 {
		  $gaduTR = '<tr><td>Gadu-Gadu</td><td>'.$user_array['gadu'].'</td></tr>';
		 }
		 
		#wyświetlanie
		echo('
		Przeglądasz profil użytkownika '.ShowUserNick($user_id,'',0).'<br/>
		<table style="width: 500px;" class="table" >
		 <tr><th>Informacje</th><th style="width:200px;"></th></tr>
		 <tr><td>Nazwa</td><td>'.ShowUserNick($user_id,'',0).'</td></tr>
		 <tr><td>Ranga</td><td>'.$rank_array['name'].'</td></tr>
		 <tr><td>Ilość postów</td><td>'.$posty->rowCount().'</td></tr>
		 <tr><td>Ilość dodanych plików</td><td>'.$files->rowCount().'</td></tr>
		 <tr><td>Ostrzeżenia</td><td>'.$user_array['warn'].'</td></tr>
		 <tr><td>Adres email</td><td>'.$user_array['email'].'</td></tr>
		 '.$gaduTR.'
		 <tr>
		  <td><a href="'.MAIN_DIR.'wiadomosci/napisz/'.$user_id.'" class="pm">Napisz prywatną wiadomość</a></td>
		  <td><a href="'.MAIN_DIR.'upload/'.$user_id.'">Zobacz folder</a></td>
		 </tr>
		');
		
		#pokazywanie opcji warnowania, tylko dla moderatorów
		if(hasPermission($your_id,"warnuser",1)==true)
		 {
		  echo("<tr><td><a href='".MAIN_DIR."uzytkownik/warnuj/$user_id'>Warnuj</a></td><td></td></tr>");
		 }
		 
		#Kończenie tabeli
		echo("</table>");
	   }
	  else
	   {
	    echo(error_message("Taki użytkownik nie istnieje!"));
	   }
	 }
	else
	 {
	  echo(error_message("Podałeś nieprawidłowy identyfikator!"));
	 }
   }
  else { echo(error_message("Nie podałeś identyfikatora!")); }
 }
 
#pokazywanie shoutów w shoutboxie
function ShowShoutbox()
 {
  /* Global Var: */
	global $your_id;
	global $BbCode;
	
  #zapytanie odpowiadające za polskie znaki
  mysql_query("SET CHARSET utf8");
  mysql_query("SET NAMES `utf8` COLLATE `utf8_polish_ci`");
  
  #zapytanie o shouty
  $shout_query = mysql_query("SELECT * FROM `eif_shoutbox` WHERE `deleted`=0 ORDER BY id DESC LIMIT 0,30 ") or die(mysql_error());
	
  #ilość shoutów
  $ilesb = mysql_num_rows($shout_query);
	
  #pętla wyświetlająca shouty
  while($shout_array = mysql_fetch_assoc($shout_query))
   {
	#informacje o autorze
	$user_id = $shout_array['user_id'];
	$shout_time = $shout_array['time'];
    $data = date('d.m.Y, H:i',$shout_time);
	
	#pobieranie informacji o shoutcie
	$shout_tresc = $shout_array['tresc'];
	
	#zamiana urla na link
	$shout_tresc = preg_replace('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.%-]*(\?\S+)?)?)?)@', '<a href="$1" target="_blank">$1</a>',$shout_tresc);
	$shout_tresc = str_replace(':D','<img src="'.MAIN_DIR.'icon/emot_D.png" />',$shout_tresc);
	
	
	if(preg_match('/\[[a-zA-Z0-9]\]/',$shout_tresc,$found))
	 {
	  $nick = $found[0];
	  $nick = str_replace('[','',$nick);
	  $nick = str_replace(']','',$nick);
	  $user_query = mysql_query("SELECT `id` FROM `eif_users` WHERE `nick`='$nick'");
	  $user_rows = mysql_num_rows($user_query);
	  if($user_rows>0)
	   {
	    echo 'user';
	    $user_array = mysql_fetch_assoc($user_query);
		preg_replace('/\[[a-zA-Z0-9]\]/',ShowUserNick($user_array['id'],'',1),$shout_tresc);
	   }
	 }
	
	//$BbCode = new BbCode();
	//$BbCode->parse($shout_tresc, false);
	//$tresc_bbc = $BbCode->getHtml();
	
	#przerabianie nicku usera
	$user_nick_parsed = ShowUserNick($user_id,'',1);
 	
	#usuwanie
	if(hasPermission($your_id,'shout_delete',1))
	 {
	  $usun = '<a onclick="ShoutDelete();"><img src="icon/icon_delete.png" /></a>';
	 }
	else $usun = '';
	
	#wyświetlanie shouta
	echo("<span class='sbdate'>".$usun.$data."</span>");
	echo("$user_nick_parsed: ");
	echo($shout_tresc);
	echo("<hr/>\n");
   }
	 
  #na wypadek gdyby nie było shoutów
  if($ilesb==0)
   {
	echo('W shoutboxie nie ma żadnych shoutów.');
   }
 }


#głosowanie na plik
function FileVote($file_id,$file_rate)
 {
  /* Global Vars */
  
  global $your_id;
  global $PermHP;
  
  if(!empty($your_id)) //czy zalogowano
   {
	if($file_id) //czy jest id
     {
	  if(is_numeric($file_id)) //poprawny id
       {
		if($file_rate) //czy jest rate
	     {
		  if(is_numeric($file_rate)) //poprawny rate
		   {			
			#informacje o głosującym
			$time = time(); //aktualny czas
			$user_ip = $_SERVER['REMOTE_ADDR'];
			
			#zapytanie o głosowanie z tego ip
			$vote_query = mysql_query("SELECT * FROM eif_filevote WHERE user_id='$your_id' AND file_id='$file_id'");
			$vote_rows = mysql_num_rows($vote_query);
			
			if(!$vote_rows) //sprawdzanie czy już głosowano
			 {
			  if($file_rate<=5 && $file_rate>0)
			   {
			    if($PermHP->hasPermission('eif.file.rate'))
				 {
				  #zapytanie dodające głos
				  $newVote_query = mysql_query("INSERT INTO eif_filevote VALUES(0,'$user_id','$file_id','$user_ip','$time','$file_rate');") or die(mysql_error());
			  
				  #znak dla javascriptu
				  echo("NO_ERROR");
				 }
				else { echo("ERROR_7"); } //nie ma uprawnień
			   }
			 }
			else { echo("ERROR_6"); } //już dodano
		   }
		  else { echo("ERROR_5"); } //zly rate
	     }
		else { echo("ERROR_4"); } //nie ma rate
	   }
	  else { echo("ERROR_3"); } //zły id
	 }
	else { echo("ERROR_2"); } //nie ma id
   }
  else { echo("ERROR_1"); } //nie zalogowano
 }

function TopicsList($forum_id,$pinned=0)
 {
  if(is_numeric($forum_id))
   {
	#informacje o forum
	$forum_query = mysql_query("SELECT * FROM `eif_dzial` WHERE `id`='$forum_id'") or die(mysql_error());
	$forum_rows = mysql_num_rows($forum_query);
	
	if($forum_rows)
	 {
	  $forum_array = mysql_fetch_assoc($forum_query);
	  $forumnazwa = $forum_array['nazwa'];
	  
	  #czy pokazać przypięte
	  if($pinned==1)
	   {
		$pinned_query = 'AND pinned=1';
	   }
	  else
	   {
		$pinned_query = 'AND pinned=0';
	   }
	  
	  #informacje o tematach w tym forum
	  $tematy_query = mysql_query("SELECT * FROM eif_tematy WHERE dzial_id='$forum_id' AND `deleted`=0 $pinned_query ORDER BY lastpost_time DESC");
	  $tematy_rows = mysql_num_rows($tematy_query);
   
	  #pętla listy tematów
	  while($temat_array = mysql_fetch_assoc($tematy_query))
	   {
		$temat_id = $temat_array['id'];
		$temat_nazwa = $temat_array['temat'];
		$user_id = $temat_array['user_id'];
		$posty_query = mysql_query("SELECT * FROM eif_posty WHERE topic_id='$temat_id' AND deleted=0");
		$posty_rows = mysql_num_rows($posty_query);
		
		if($pinned==1)
		 {
		  $pinned_html = '<img src="'.MAIN_DIR.'icon/icon_attach.png" class="pinned_topic"></div>';
		 }
		else
		 {
		  $pinned_html = '';
		 }
		 
		if($temat_array['lock']==1)
		 {
		  $lock_html = '<img src="'.MAIN_DIR.'icon/icon_close.png" class="pinned_topic">';
		 }
		else
		 {
		  $lock_html = '';
		 }
		
		#nick usera
		$user_nick_parsed = ShowUserNick($user_id,'user_nick',1);
		
		#odmiana liczby tematów
		if($posty_rows==1) { $ilepostytext='post'; }
		if($posty_rows==0) { $ilepostytext='brak postów'; $posty_rows=''; }
		if($posty_rows>1 && $posty_rows<5) { $ilepostytext='posty'; }
		if($posty_rows>4) { $ilepostytext='postów'; }
	
		#wyświetlanie
		echo("
			<li>
			<aside>$posty_rows $ilepostytext</aside>
			$pinned_html
			$lock_html
			<a href='".MAIN_DIR."temat/pokaz/$temat_id'>$temat_nazwa</a>
			Autor: $user_nick_parsed
			</li>
		");
	   }
     }
   }
 }

#panel użytkownika
function UserCP($panel,$avatar_get)
 {
  global $your_id;
  global $forum;
  
  if(hasPermission($your_id,'usercp_show',1) && $your_id>0) //jeśli zalogowano i są uprawnienia
   {
    switch($panel)
	 {
	 case 'signchange':
	  if(!empty($_POST['sygnatura']))
	   {
		#update
		mysql_query("UPDATE eif_users SET sygnatura='".$_POST['sygnatura']."' WHERE id=$your_id");
	  
		#wiadomość
		echo('<header><h2>Sygnatura została zmieniona</h2><a class="button" href="'.MAIN_DIR.'usercp" >Wróć<a></header>');
	   }
	 break;
	 case '':
	  $user_query = mysql_query("SELECT * FROM `eif_users` WHERE `id`=$your_id");
	  $user_array = mysql_fetch_assoc($user_query);
	
	  #zapytania
	  $user_files_query = mysql_query("SELECT * FROM `eif_upfiles` WHERE `user_id`=$your_id");
	  $user_posty_query = mysql_query("SELECT * FROM `eif_posty` WHERE `user_id`=$your_id");
	   
	  #przetwarzanie rang
	  $rank_array = CheckUserRank($your_id);
	   
	  #wyświetlanie profilu
	   echo('
	    <h2>Witamy w panelu.</h2><br/><p/>
		<div style="float: left; width: 128px; height: 128px;">
		 <img src="'.ShowUserAvatar($your_id).'" width=120 height=120 />
		</div>
		<table width="372" border="0">
		 <tr><td>Nazwa:</td><td>'.ShowUserNick($your_id,'',0).'</td></tr>
		 <tr><td>Data rejestracji:</td><td>'.date('d.m.Y, H:i',$user_array['time_rej']).'</td></tr>
		 <tr><td>Plików dodał:</td><td>'.mysql_num_rows($user_files_query).'</td></tr>
		 <tr><td>Postów napisał:</td><td>'.mysql_num_rows($user_posty_query).'</td></tr>
		 <tr><td>Ranga:</td><td>'.$rank_array['name'].'</td></tr>
		 <tr><td>Strona WWW:</td><td><a href="'.$forum->genLeave($user_array['www']).'" target="_blank" >'.$user_array['www'].'</a></td></tr>
		</table><br/>');
		
	   echo('
	   <hr/>
	  <br/><br/>
	  <b>Zmień avatar</b>
	  <br/><small>Tylko PNG lub GIF, najlepiej 100x100</small>
	  <form enctype="multipart/form-data" action="'.MAIN_DIR.'zmien-avatar" method="post">
	  <input type="hidden" name="MAX_FILE_SIZE" value="999999999999999999999999999999999999999999999999" /> 
	  <input name="avatar" value=1 type="hidden" /> 
	  <input name="plik" type="file" /> 
	  <input type="submit" class="submit" value="Dodaj" /> 
	  </form>
	  <br/><br/>
	  
	  Zmienianie sygnatury:<br/>
	  <form action="'.MAIN_DIR.'usercp/signchange" method="post">
	   <textarea class="textarea" style="height:200px;" name="sygnatura">'.$user_array['sygnatura'].'</textarea>
	   <input type=submit class="longinput" value="Zmień sygnaturę" />
	  </form>
	   ');
	  break;
	 }
   }
  else { echo(error_message('Nie masz uprawnień!')); }
 }

#pokazywanie avatara
function ShowUserAvatar($user_id)
 {
  global $forum;
  global $Spyc;
  $UserRank = $forum -> getUserRank($user_id);
  $P = new PermissionsHP($Spyc);
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
	  $img = MAIN_DIR.$img_tocheck.'.'.$aE;
	 }
   }
  
  if(empty($img))
   {
    $img = MAIN_DIR.$avatar_dir.'default.png';
   }
   
  #zwracanie
  return $img;
 }


function hasPermission($user_id,$permission_name,$expected_value)
 {
  /*
	@Author: Marcin Wieczorek
	@Date: 01-07-2012
	@Function: hasPermission
	@Parameters: user_id, permission_name, expected_value
	@Returns: true/false
  */
  if(!empty($permission_name))
   {
	if(!empty($expected_value))
	 {
	  if(!empty($user_id)) //if user is logged
	   {
		$user_query = mysql_query("SELECT * FROM eif_users WHERE `id`=$user_id");
		$user_rows = mysql_num_rows($user_query);
		
		if(!empty($user_rows)) //user exist
		 {
		  $user_array = mysql_fetch_assoc($user_query);
		  $user_rank = $user_array['rank'];
		 }
		else { die('User does not exist'); }
	   }
	  else //unlogged user
	   {
		$user_rank = 9;
	   }
	 }	 
	
	//check permission
	$rank_query = mysql_query("SELECT * FROM eif_rangi WHERE `id`=$user_rank");
	$rank_rows = mysql_num_rows($rank_query);
		
	if(!empty($rank_rows))
	 {
	  $rank_array = mysql_fetch_assoc($rank_query); //array with rank
		  
	  if($rank_array[$permission_name] == $expected_value) //permission if
	   {
		return TRUE; //returning true, it works
	   }
	  else { return FALSE; }
	 }
	else { die('This rank doesn\'t exist'); }
   }
  else { die('Please add permission name'); }
 }
 
#pokazywanie nicku usera
function ShowUserNick($user_id,$class='',$link=1,$just=0)
 {
  if($user_id>0)
   {
	$user_query = mysql_query("SELECT * FROM `eif_users` WHERE `id`=$user_id") or die(mysql_error());
	$user_array = mysql_fetch_assoc($user_query);
	$user_rank = $user_array['rank'];
	$user_nick = $user_array['nick'];
  
	if($just==1)
	 {
	  return $user_array['nick'];
	 }
  
	$rank_query = mysql_query("SELECT * FROM eif_rangi WHERE id='$user_rank'");
	$rank_array = mysql_fetch_assoc($rank_query);
	$rank_style = $rank_array['style'];
  
	#ustalanie znacznika html
	if($link==1)
	 {
	  $z_name = 'a';
	  $z_mid = "href='".MAIN_DIR."user/$user_id'";
	 }
	else
	 {
	  $z_name = 'span';
	  $z_mid = '';
	 }
   
	#ustalanie całości
	$string = "<$z_name $z_mid style='$rank_style' class='$class' >$user_nick</$z_name>";
   
	return $string;
   }
 }

#sprawdzanie rangi usera ZWRACA TABLICĘ
function CheckUserRank($user_id)
 {
  $user_query = mysql_query("SELECT * FROM eif_users WHERE `id`=$user_id");
  $user_array = mysql_fetch_assoc($user_query);
  $rank_id = $user_array['rank'];
  
  $rank_query = mysql_query("SELECT * FROM eif_rangi WHERE id='$rank_id'");
  $rank_array = mysql_fetch_assoc($rank_query);
  
  return $rank_array;
 }

#edytowanie pliku
function FileEdit($file_id)
 {
  global $your_id;
  
  if($your_id)
   {
    if($file_id)
	 {
	  if(is_numeric($file_id))
	   {
		#zapytanie o plik
		$file_query = mysql_query("SELECT * FROM eif_upfiles WHERE `id`=$file_id");
		$file_rows = mysql_num_rows($file_query);
	
		if($file_rows)
		 {
		  #tablica
		  $file_array = mysql_fetch_assoc($file_query);
		  $user_id = $file_array['user_id'];
		  $file_name = $file_array['name'];
		  $file_opis = $file_array['opis'];
		  $file_lock = $file_array['lock'];
	  
		  if(hasPermission($your_id,"fileedit",2)==true or (hasPermission($your_id,"fileedit",1)==true && $your_id==$user_id))
		   {
			if(hasPermission($your_id,"fileedit",2)==true or $file_lock==0)
			 {
			  if($_POST)
			   {
				#pobieranie z formularza
				$pFile_name = $_POST['pfile_name'];
				$pFile_opis = $_POST['pfile_opis'];
				$pFile_visible = $_POST['pfile_visible'];
				$pFile_lock = $_POST['pfile_lock'];
		
				if($pFile_visible==1 or $pFile_visible==0)
				 {
				  if($pFile_lock==1 or $pFile_lock==0)
				   {
				    if(hasPermission($your_id,"fileedit_name",1)==true)
					 {
					  $update_query = mysql_query("UPDATE `eif_upfiles` SET `name`='$pFile_name' WHERE `id`=$file_id") or die(mysql_error());
				     }
					
					if(hasPermission($your_id,"fileedit_opis",1))
					 {
					  $update_query = mysql_query("UPDATE `eif_upfiles` SET `opis`='$pFile_opis' WHERE `id`=$file_id") or die(mysql_error());
				     }
					 
					if(hasPermission($your_id,"fileedit_visible",1))
					 {
				      $update_query = mysql_query("UPDATE `eif_upfiles` SET `visible`=$pFile_visible WHERE `id`=$file_id") or die(mysql_error());
					 }
					
					if(hasPermission($your_id,"fileedit_lock",1))
					 {
					  $update_query = mysql_query("UPDATE `eif_upfiles` SET `lock`=$pFile_lock WHERE `id`=$file_id") or die(mysql_error());
					 }
					
				    #wiadomość
					echo(answer_message("Właściwości pliku zostały zmienione"));
				   }
				 }
			   }
			  else
			   {
			    #selected do formularza
				if($file_array['visible']==1) $vsw = "selected='selected'";
				if($file_array['visible']==0) $vsu = "selected='selected'";
				
				if($file_lock==1) $lsw = "selected='selected'";
				if($file_lock==0) $lsu = "selected='selected'";
				
				#disabled do formularza
				if(hasPermission($your_id,"fileedit_name",1)==false) $dis_name = "disabled=disabled";
				if(hasPermission($your_id,"fileedit_opis",1)==false) $dis_opis = "disabled=disabled";
				if(hasPermission($your_id,"fileedit_visible",1)==false) $dis_visible = "disabled=disabled";
				if(hasPermission($your_id,"fileedit_lock",1)==false) $dis_lock = "disabled=disabled";
				
				#formularze
				echo("
				<div class='fileedit_cont'>
				 Edycja pliku
				 <form action='' method='post'>
				  <small>Nazwa</small>
				  <input type='text' name='pfile_name' value='$file_name' placeholder='Nazwa' $dis_name /><br/>
				  <small>Opis</small>
				  <textarea name='pfile_opis' class='fileedit_ta' $dis_opis />$file_opis</textarea><br/>
				  <small>Widoczność</small>
				  <div class='select'>
				   <select name='pfile_visible' $dis_visible >
				    <option value='1' $vsw>Widoczny</option>
				    <option value='0' $vsu>Ukryty</option>
				   </select>
				  </div>
				  
				  <small>Blokada</small>
				  <div class='select'>
				   <select name='pfile_lock' $dis_lock>
				    <option value='1' $lsw>Zablokowany</option>
				    <option value='0' $lsu>Odblokowany</option>
				   </select>
				  </div>
				  <input type='submit' value='Zapisz' />
				 </form>
				</div>
				");
			   }
			 }
			else { echo(error_message("Ten plik jest zablokowany")); }
		   }
		  else { echo(error_message("Nie masz uprawnień")); }
		 }
		else { echo(error_message("Taki plik nie istnieje")); }
	   }
	  else { echo(error_message("ID pliku jest niepoprawne")); }
	 }
	else { echo(error_message("Podaj ID pliku")); }
   }
  else { echo(error_message("Zaloguj się")); }
 }

#ramka na post
function PostFrame($user_id,$tresc,$time)
 {
  global $your_id;
  $data = date('d.m.Y, H:i',$time);
  $bb = new BbCode();
  
  #informacje o userze
  $user_query = mysql_query("SELECT * FROM `eif_users` WHERE `id`=$user_id") or die(mysql_error());
  $user_array = mysql_fetch_assoc($user_query);
  $user_nick = $user_array['nick'];
  $user_title = $user_array['title'];
	  
  #ostrzeżenia
  if(hasPermission($user_id,"showwarnsinpost",1))
	   {
		$user_warn = $user_array['warn'];
		
		$warns_html = 
		"
		 Ostrzeżenia<br/>
		 <div class='warn_cont'>
		  <a href='".MAIN_DIR."warnlist/$user_id'>
		   <div class='warn_pasek' style='width: $user_warn%;'></div>
		  </a>
		 </div>
		";
	   }
	  else { $warns_html = ''; }
	   
	  #odznaczenia
	  $rank_array = CheckUserRank($user_id);
	  $rank_odznaczenia = $rank_array['odznaczenia'];
	  if($rank_odznaczenia)
	  {
	    $odznaczenia_query = mysql_query("SELECT * FROM eif_odznaczenia WHERE id='$rank_odznaczenia'");
		$odznaczenia_rows = mysql_num_rows($odznaczenia_query);
		
		if($odznaczenia_rows)
		 {
		  $odznaczenia_array = mysql_fetch_assoc($odznaczenia_query);
		  $odznaczenia_src = $odznaczenia_array['src'];
		  $odznaczenia_html = "<img src='".MAIN_DIR."$odznaczenia_src' alt='' />";
		 }
	   }
	  else { $odznaczenia_html=""; }
	  
	  #ilość postów
	  $ileposty_query = mysql_query("SELECT * FROM `eif_posty` WHERE topic_id='$temat_id' AND `deleted`=0 AND `counted`=1");
	  $ileposty = mysql_num_rows($ileposty_query);

	  #dzien
	  $dzien = substr($data,0,2);
	  $dzien = str_replace('.','',$dzien);  
	  
	  #przetwarzanie liczby miesiąca na nazwę (02 -> luty)
	  $miesiac = substr($data,3,3);
	  $miesiac = str_replace('.','',$miesiac);
 	  
	  #przerabianie miesiąca na polską nazwę
	  if($miesiac==1) { $miesiac = 'styczeń'; }
	  elseif($miesiac==2) { $miesiac = 'luty'; }
	  elseif($miesiac==3) { $miesiac= 'marzec'; }
	  elseif($miesiac==4) { $miesiac= 'kwiecień'; }
	  elseif($miesiac==5) { $miesiac= 'maj'; }
	  elseif($miesiac==6) { $miesiac= 'czerwiec'; }
	  elseif($miesiac==7) { $miesiac= 'lipiec'; }
	  elseif($miesiac==8) { $miesiac= 'sierpień'; }
	  elseif($miesiac==9) { $miesiac= 'wrzesień'; }
	  elseif($miesiac==10) { $miesiac= 'październik'; }
	  elseif($miesiac==11) { $miesiac= 'listopad'; }
	  elseif($miesiac==12) { $miesiac= 'grudzień'; }

	  #przerabianie na bbcode
	  $bb->parse($tresc, false);
	
	  echo('
   	   <article>
		<div class="author">
		 '.ShowUserNick($user_id,'',1).'
		 <br/>
		 <small>
		  <b>'.$user_title.'</b>
		 </small>
		 <br/>
		 <img src="'.ShowUserAvatar($user_id).'" width=128 height=128 alt="" />
		 <time>
		  '.substr($data,11).'<br />
		  '.$dzien.' '.$miesiac.' '.str_replace('.','',substr($data,5,5)).'
		 </time>
		'.$warns_html.'
		'.$odznaczenia_html.'
		Posty: '.$ileposty.'
		</div>
		<p class="post-text">
		 '.$bb->getHtml().'
		</p>
		<div class="post_footer">
		 '.$edit.'
		 '.$delete.'
		 <div style="text-align:right;margin-top:-16px;margin-right: 10px;">
		  <a href="#'.$item_id.'" id="'.$item_id.'">'.$item_id.'</a>
		 </div> 
		</div>
	   </article>
  ');
 }

#przekierowanie
function Redirect($url,$sec=0,$echo=1)
 {
  #generowanie przekierowania
  $redirect = "<meta http-equiv='refresh' content='$sec; url=$url '> ";

  if($echo==1)
   {
	echo($redirect);
   }
  else
   {
	return $redirect;
   }
 }
 
#pokazywanie posta
function ShowPost($post_id)
 {
  /* Global Vars */
	global $forum;
	global $your_id;
	global $PDO;
	
  #tworzenie obiektu do bbcode
  $bb = new BbCode(); //tworzenie obiektu
  
  if($post_id)
   {
    if(is_numeric($post_id))
	 {
	  #zapytanie o post
	  $post_query = mysql_query("SELECT * FROM eif_posty WHERE id='$post_id'");
	  $post_rows = mysql_num_rows($post_query);
	  
	  if($post_rows)
	   {
	    #tablica z postem
		$post_array = mysql_fetch_assoc($post_query);
		
		#informacje o poście
		$post_tresc = $post_array['tresc']; //tresc posta
		$user_id = $post_array['user_id']; //autor posta
		$time = $post_array['time']; //data stworzenia posta
		$data = date('d.m.Y, H:i',$time);
		  
		#informacje o userze
		$user_query = mysql_query("SELECT * FROM eif_users WHERE `id`=$user_id");
		$user_array = mysql_fetch_assoc($user_query);
		$user_title = $user_array['title'];
	  
		#ostrzeżenia
		if(hasPermission($user_id,"showwarnsinpost",1)==true)
	     {
		  $user_warn = $user_array['warn'];
		
		  $warns_html = 
		  "
		   Ostrzeżenia<br/>
		   <div class='warn_cont'>
		    <a href='".MAIN_DIR."warnlist/$user_id'>
		    <div class='warn_pasek' style='width: $user_warn%;'></div>
		    </a>
		   </div>
		  ";
		 }
	  else { $warns_html = ''; }
	   
	  #odznaczenia
	  $rank_array = CheckUserRank($user_id);
	  $rank_odznaczenia = $rank_array['odznaczenia'];
	  
	  $user_posts = $PDO->query("SELECT `id` FROM `eif_posty` WHERE `user_id`=$user_id");
	  
	  if($rank_odznaczenia)
	   {
	    $odznaczenia_query = mysql_query("SELECT * FROM eif_odznaczenia WHERE id='$rank_odznaczenia'");
		$odznaczenia_rows = mysql_num_rows($odznaczenia_query);
		
		if($odznaczenia_rows)
		 {
		  $odznaczenia_array = mysql_fetch_assoc($odznaczenia_query);
		  $odznaczenia_src = $odznaczenia_array['src'];
		  $odznaczenia_html = "<img src='".MAIN_DIR."$odznaczenia_src' alt='' />";
		 }
	   }
	  else { $odznaczenia_html=""; }
	  
	  #zapytanie o temat
	  $topic_query = mysql_query("SELECT * FROM eif_tematy WHERE id='$temat_id'") or die(mysql_error());
	  $topic_rows = mysql_num_rows($topic_query);
	  if(!empty($topic_rows)) $topic_array = mysql_fetch_assoc($topic_query);
	  
	  #ilość postów
	  $ileposty_query = mysql_query("SELECT * FROM `eif_posty` WHERE `topic_id`='$temat_id' ");
	  $ileposty = mysql_num_rows($ileposty_query);
	
	  #ustawienia edytowania
	  $edit='';
	  if(hasPermission($your_id,"post_edit",2) or ($user_id==$your_id && hasPermission($your_id,"post_edit",1)==true))
	   {
		$edit = "<a href='".MAIN_DIR."post/edytuj/$post_id'>Edytuj</a>";
	   }
 
	  #ustawienia usuwania posta
	  if(hasPermission($your_id,"post_delete",2) or ($user_id==$your_id && hasPermission($your_id,"post_delete",1)==true))
	   {
		$delete = "<a href='".MAIN_DIR."post/usun/$post_id'>Usuń</a>";
	   }
	  else
	   {
		$delete='';
	   }
	   
	  #sygnatura z bbcode
	  $bb->parse($user_array['sygnatura'],false);
	  $bbsygnatura = $bb->getHtml();
	
	  #ustawianie ścieżki avatara
	  $user_avatar = $forum->getAvatar($user_id);
	
	  #dzien
	  $dzien = substr($data,0,2);
	  $dzien = str_replace('.','',$dzien);  
	  
	  #przetwarzanie liczby miesiąca na nazwę (02 -> luty)
	  $miesiac = substr($data,3,3);
	  $miesiac = str_replace('.','',$miesiac);
 	
	  #przerabianie miesiąca na polską nazwę
	  if($miesiac=='1') { $miesiac = 'styczeń'; }
	  elseif($miesiac=='2') { $miesiac = 'luty'; }
	  elseif($miesiac=='3') { $miesiac= 'marzec'; }
	  elseif($miesiac=='4') { $miesiac= 'kwiecień'; }
	  elseif($miesiac=='5') { $miesiac= 'maj'; }
	  elseif($miesiac=='6') { $miesiac= 'czerwiec'; }
	  elseif($miesiac=='7') { $miesiac= 'lipiec'; }
	  elseif($miesiac=='8') { $miesiac= 'sierpień'; }
	  elseif($miesiac=='9') { $miesiac= 'wrzesień'; }
	  elseif($miesiac=='10') { $miesiac= 'październik'; }
	  elseif($miesiac=='11') { $miesiac= 'listopad'; }
	  elseif($miesiac=='12') { $miesiac= 'grudzień'; }

	  #rok
	  $rok = substr($data,5,5);
	  $rok = str_replace('.','',$rok);

	  #godzina
	  $time = substr($data,11);

	  #przerabianie na bbcode
	  $post_tresc = html_entity_decode($post_tresc);
	  $bb->parse($post_tresc, false);
	  $post_tresc_bbcoded = $bb->getHtml();
	
	  #przerabianie nicku
	  $user_nick = ShowUserNick($user_id,'',1);
	 
	  #link do folderu
	  $miniicons .= '<a href="'.MAIN_DIR.'upload/'.$user_id.'"><img src="'.MAIN_DIR.'icon/famfamfam/folder.png" /></a>';
	 
	  #Status gadugadu
	  if($user_array['gadu']>0)
	   {
		$miniicons .= '<a href="'.MAIN_DIR.'ggnapisz/'.$user_array['gadu'].'"><img src="http://www.gadu-gadu.pl/users/status.asp?id='.$user_array['gadu'].'" /></a>';
	   }
	   
	  #Status gadugadu
	  if(strlen($user_array['www'])>0)
	   {
	    $www = $user_array['www'];
		$www = $forum->genLeave($www);
		$miniicons .= '<a href="'.$www.'" target="_blank"><img src="'.MAIN_DIR.'icon/famfamfam/world.png" /></a>';
	   }
	   
	  #wiadomosc
	  /*
	  if(strlen($user_array['email'])>0)
	   {
		$miniicons .= '<a href="'.MAIN_DIR.'napiszmaila/'.$user_id.'"><img src="'.MAIN_DIR.'icon/mail.png" /></a>';
	   }
	  */
	  $miniicons .= '<a href="'.MAIN_DIR.'wiadomosci/napisz/'.$user_id.'"><img src="'.MAIN_DIR.'icon/famfamfam/email.png" /></a>';
	  	
	  #wyświetlanie
	  echo('
	   <article id="'.$post_id.'" >
		<div class="author">
		 '.$user_nick.'
		 <br/>
		 <small>
		  <b>'.$user_title.'</b>
		 </small>
		 <br/>
		 <div style="width: 128px; height: 128px; position: relative;margin: 0 auto;">
		  <img src="'.$user_avatar.'" width=128 height=128 alt="" />
		  <img src="'.MAIN_DIR.'icon/userstatus_'.$forum->isOnline($user_id).'.png" style="position: absolute; left: 0; top: 0;" />
		 </div>
		 <time>
		  '.$time.'<br />
		  '.$dzien.' '.$miesiac.' '.$rok.'
		 </time>
		 Posty: '.$user_posts->rowCount().'<br/>
		 '.$warns_html.'
		 '.$odznaczenia_html.'<br/>
		 <br/>
		 <div class="miniicons">
		  '.$miniicons.'
		 </div>
		</div>
		<p class="post-text">
		 '.$post_tresc_bbcoded.'
		<br/><br/><br/>-------------------------------------------------------<br/>
		 '.$bbsygnatura.'
		</p>
		<div class="post_footer">
		<a style="display:none;" >a</a>
		 '.$edit.'
		 '.$delete.'
		 <div style="text-align:right;float:right;margin-right: 10px;">
		  <a href="'.MAIN_DIR.'post/s/'.$post_id.'">#'.$post_id.'</a>
		 </div>
		</div>
		<div id="rpgive_'.$post_id.'"></div>
	   </article>
		');
       }
	 }
   }
 }
 
#kody - pokazywanie
function SourcebinShow($sb_id)
 {
  /*
   @Author: Marcin (CTRL) Wieczorek
   @Date: 06-07-2012
   @Function: SourcebinShow
   @Requied Permissions: source_show
   @Parameters: $sb_id
   @Idea by: Michał (Toaspzoo) Łabno
  
   Global Vars*/
	
	global $your_id;
	
  if(!empty($sb_id))
   {
	if(is_numeric($sb_id))
	 {
	  if(hasPermission($your_id,'sourcebin_show',1))
	   {
		$sb_query = mysql_query("SELECT * FROM sourcebin_kody WHERE `id`=$sb_id");
		$sb_rows = mysql_num_rows($sb_query);
		
		if(!empty($sb_rows))
		 {
		  $sb_array = mysql_fetch_assoc($sb_query);
		  $sb_array['source'] = htmlspecialchars_decode($sb_array['source']);
		  echo('Ten SourceBin został wrzucony przez użytkownika '.ShowUserNick($sb_array['user_id'],'',1).' dnia '.date('d.m.Y, H:i',$sb_array['time']).'<hr/>');
		  
		  echo('<div style="padding:20px;">');
		  
		  /* GESHI */
		  include(DIR_NAME.'/libs/geshi/geshi.php'); 
		  $geshi =& new GeSHi($sb_array['source'],'php');
		  $geshi->set_header_type(GESHI_HEADER_PRE);
		  $geshi->enable_classes();		 
		  $geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS, 5);		
		  $geshi->set_overall_style('color: #000066; border: 1px solid #d0d0d0; background-color: #f0f0f0;', true);
		  $geshi->set_line_style('font: normal normal 95% \'Courier New\', Courier, monospace; color: #003030;', 'font-weight: bold; color: #006060;', true);
		  $geshi->set_code_style('color: #000020;', 'color: #000020;');
		  $geshi->set_link_styles(GESHI_LINK, 'color: #000060;');
		  $geshi->set_link_styles(GESHI_HOVER, 'background-color: #f0f000;');	
		  echo('<style>');
		  echo $geshi->get_stylesheet();
		  echo('</style>');
		  echo $geshi->parse_code();
		  /* /GESHI */
		  
		  echo('</div>');
		 }
		else { echo(error_message('Taki sourcebin nie istnieje!')); }
	   }
	  else { echo(error_message('Nie masz uprawnień!')); }
	 }
	else { echo(error_message('ID sourcebina jest niepoprawne!')); }
   }
  else { echo(error_message('Podaj ID sourcebina!')); } 
 }
 
function getPDO()
 { 
  try
   {
	$PDO = new PDO('mysql:host='.MYSQL_HOST.';port='.MYSQL_PORT.';dbname='.MYSQL_BASE.'',MYSQL_USER,MYSQL_PASS,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
    $PDO->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
   }
  catch(PDOException $e)
   {
	echo 'Połączenie nie mogło zostać utworzone: ' . $e->getMessage();
   }
  return $PDO;
 }
 
function getTemplate($str)
 {
  global $Config;
  return file_get_contents(dirname(__FILE__).'/css/'.$Config->CSS_TEMPLATE.'/template/'.$str.'.php');
 }
?>