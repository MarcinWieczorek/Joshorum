<?php
/////////////////////////////////////////////
//               Joshorum  			       //
//             www.marcin.co  		       //
//            Marcin Wieczorek             //
//           Michał Sokołowski             //
//            Maciej Wysocki               //
//             12-2011 - 2013              //
/////////////////////////////////////////////

$microtime_start = microtime(true);
session_start();
header("Content-Type: text/html; charset=utf-8");

include(dirname(__FILE__).'/config.php');
include(DIR_NAME.'/libs/Bbcode/BbCode.php');
include(DIR_NAME.'/libs/SmartUrl/class.SmartUrl.php');
include(DIR_NAME.'/libs/Config/class.Config.php');
include(DIR_NAME.'/libs/Spyc/class.Spyc.php');
include(DIR_NAME.'/funkcje.php');
include(DIR_NAME.'/class.PermissionsHP.php');
include(DIR_NAME.'/class.Forum.php');

#połączenie z bazą
$conn = MysqlConnect();
if(!$conn) { echo('Przepraszamy, nie udało się połączyć z bazą danych przez co system jest niezdatny do użytkowania.'); exit; }

#zapytanie odpowiadające za polskie znaki
mysql_query("SET CHARSET utf8");
mysql_query("SET NAMES `utf8` COLLATE `utf8_polish_ci`");

$PDO = getPDO();

$Config = new Config($PDO);
define('MAIN_DIR',$Config->MAIN_DIR); //usunąć gdy nie będzie funkcje.php

$data = date('d.m.Y, H:i');

$your_ip = $_SERVER['REMOTE_ADDR']; //ip przeglądającego
$your_id = $_SESSION['logged_id']; //id przeglądającego

#Tworzenie obiektów klas
$Spyc = new Spyc();
$PermHP = new PermissionsHP($Spyc);
$bb = new BbCode();

try
 {
  $forum = new Forum($PDO,$Spyc,$Config);
 }
catch(JoshorumException $e)
 {
  $exception_tpl = getTemplate('exception-box');
  $exception_tpl = str_replace('{CODE}',$e->getCode(),$exception_tpl);
  $exception_tpl = str_replace('{MESSAGE}',$e->getMessage(),$exception_tpl);
  echo $exception_tpl;
  exit;
 }
  
$SU = new SmartUrl;

$SU = $SU -> args;

if($your_id > 0)
 {
  $UserRank = $forum -> getUserRank($your_id);
 }
else
 {
  $UserRank[0] = $Config->UNLOGGED_RANK;
 }
$PermHP -> setUserRank($UserRank[0]);

$forum->PermHP = $PermHP;

#zapisywanie logów
$forum->saveLog($SU);

#Informacje o przeglądającym
if($your_id > 0)
 {
  $your_query = $PDO -> query("SELECT * FROM `".TBL."users` WHERE `id`=$your_id ORDER BY `id` DESC LIMIT 0,1");
  $your_array = $your_query -> fetch();
 }
  
#globalne rangi
$globalranks['inactive'] = 6;
$globalranks['banned'] = 3;

#zarządzania userami online
$forum->setOnline(); //aktualizacja online

if($SU[0]=='temat' && $SU[1]=='pokaz' && !empty($SU[2]))
 {
  $topic = $forum -> getTopic($SU[2]);
  $customtitle = $topic[0]['temat'].' - ';
 }
?>
<!DOCTYPE html>
<html>
 <head>
  <title><?php echo $customtitle.$Config->PAGE_TITLE; ?></title>
  <link rel="stylesheet" href="<?php echo $Config->MAIN_DIR; ?>css/<?php echo $Config->CSS_TEMPLATE; ?>/background.css" />
  <link rel="stylesheet" href="<?php echo $Config->MAIN_DIR; ?>css/<?php echo $Config->CSS_TEMPLATE; ?>/text.css" />
  <link rel="stylesheet" href="<?php echo $Config->MAIN_DIR; ?>css/<?php echo $Config->CSS_TEMPLATE; ?>/style.css" />
  <script src="<?php echo $Config->MAIN_DIR; ?>jscripts/ContextMenu.js"></script>
  <script src="<?php echo $Config->MAIN_DIR; ?>jscripts/quicklogin.js"></script>
  <script src="<?php echo $Config->MAIN_DIR; ?>javascript.js"></script>
  <script src="<?php echo $Config->MAIN_DIR; ?>jscripts/datehour.js"></script>
  <script src="http://code.jquery.com/jquery-latest.min.js" type="text/javascript"></script>
  <script src="http://ciasteczka.eu/cookiesEU-latest.min.js"></script>
  <script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.17/jquery-ui.min.js"></script>
  <link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.17/themes/base/jquery-ui.css" media="all" />
  <link rel="icon" type="image/png" href="<?php echo $Config->MAIN_DIR; ?>icon/favicon.ico"/>
  <META NAME="google-site-verification" CONTENT="<?php echo $Config->GOOGLE_SITE_VERIFICATION; ?>"/>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta name="description" content="<?php echo $Config->META_DESCRIPTION; ?>">
  <meta name="keywords" content="<?php echo $Config->META_KEYWORDS; ?>">
  <meta name="author" content="<?php echo $Config->META_AUTHOR; ?>">
  <script>
  jQuery(document).ready(function(){
		jQuery.fn.cookiesEU();
	});
  </script>
  <?php if(!empty($Config->GOOGLEANALYTICS_ACCOUNTID)): ?>
  <script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', '<?php echo $Config->GOOGLEANALYTICS_ACCOUNTID; ?>']);
  _gaq.push(['_trackPageview']);
  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
  </script>
  <?php endif; ?>
 </head>
 <body>
  <?php
   if(!empty($Config->FACEBOOK_FANPAGE) && ($your_array['showfb']==1 or $your_id==0)):
  ?>
  <div id="fb-root"></div>
  <div id="like-box">
   <div class="outside">
	<div class="inside">
	 <div class="fb-like-box" data-href="<?php echo $Config->FACEBOOK_FANPAGE ?>" data-width="292" data-height="305" data-show-faces="true" data-stream="false" data-header="false"></div>
	 <br/>
	 <input type="button" value="Schowaj" onclick="$('#like-box').hide('slow');" />
	</div>
	</div>
   <div class="belt">facebook</div>
  </div>
  <?php
   endif;
  ?>
  <div id='dialog'></div>
  <div id='refresh'><img src='<?php echo $Config->MAIN_DIR; ?>icon/loader.gif'  alt='Ładowanie...'></div><script>$('#refresh').hide();</script>
  <header class="mainhead">
   <div>
    <h1>
	 <a href="<?php echo $Config->MAIN_DIR; ?>"><?php echo $Config->SITE_NAME; ?></a>
	</h1>
	<?php
	#ikonka wiadomości.
	if($your_id > 0):
	$pm = $PDO -> query("SELECT `id` FROM `".TBL."message` WHERE to_id='$your_id' and odebrano=0 ");
	$pm_rows = $pm -> rowCount();
	  
	if($pm_rows > 0)
	 {
	  $hl = ' highlight';
	 }
	?>
	<div class="pmcont">
     <a href="<?php echo $Config->MAIN_DIR; ?>wiadomosci" class="pm<?php echo $hl; ?>"><?php echo $pm_rows; ?></a>
	</div>
	<?php
	endif;
	?>
   </div>
  </header>
  <nav id="upnav">
   <?php
	if($your_array['floatnav']==1 or $your_id==0):
   ?>
   <script>
	$(window).scroll(function() {
		if($(this).scrollTop() > 61) {
			$('#upnav').addClass("fly");
		}
		else {
			$('#upnav').removeClass("fly");
		}
	});
   </script>
   <?php
	endif;
   ?>
   <span id='clock'><script>displayTime();</script></span>
<?php
if($your_id==0): //jeśli nie zalogowano
?>
  <ul id="general">
   <li><a href="#<?php if($your_array['floatnav']==1) echo 'upnav'; ?>" onclick="quickLogin()">Zaloguj</a></li>
   <li><a href="<?php echo $Config->MAIN_DIR; ?>rejestracja">Zarejestruj</a></li>
  </ul>
  
  <form action="<?php echo $Config->MAIN_DIR; ?>login" method="post" onkeypress="{if (event.keyCode==13)javascript:login()}" >
   <ul id="login" >
    <li><input type="text" id="nick" name="nick" placeholder="nazwa" /></li>
    <li><input type="password" id="pass" name="pass" placeholder="hasło" /></li>
    <li><input type="button" onclick="javascript:login()" value="Zaloguj" /></li>
    <li><a href="<?php echo $Config->MAIN_DIR; ?>rejestracja">Zarejestruj</a></li>
   </ul>
  </form>
<?php
 else:
?>
   <ul style="margin-top: 0px !important;" >
    <li>Witaj <?php echo $forum->ShowUserNick($your_id,'nav_nick',1); ?></li>
    <li><a href="<?php echo $Config->MAIN_DIR; ?>usercp" class="nav_link">Panel</a></li>
    <li><a href="<?php echo $Config->MAIN_DIR; ?>upload" class="nav_link">Folder</a></li>
    <li><a href="#" onclick="LogOut();" class="nav_link">Wyloguj</a></li> 
   </ul>
<?php
endif;
?>
  </nav>
  <section>
<?php
switch($SU[0])
 {
 case 'konfiguracja':
  if($PermHP->hasPermission('eif.config.show'))
   {
	foreach($Config as $conf)
	 {
	  if(!$conf instanceof PDO)
	   {
		if(!$PermHP->hasPermission('eif.config.noedit.'.$conf))
		 {
		  $disabled='disabled="disabled"';
		 }
		else { $disabled=''; }
		
		echo($conf.': <input type="text" value="'.$Config->$conf.'" name="'.$conf.'" $disabled d/><br/>');
	   }
	 }
   }
 break;
 case 'dzial':
  $dzial_id = $SU[2];
  switch($SU[1])
   {
   case 'wyczysc':
	if($PermHP->hasPermission('eif.dzial.clean'))
	 {
	  switch($forum->cleanDzial($dzial_id))
	   {
	   case 1:
		echo(answer_message('Dział został wyczyszczony'));
	   break;
	   case 2:
		echo(error_message('Nie podano ID'));
	   break;
	   case 3:
		echo(error_message('Niepoprawne ID'));
	   break;
	   case 4:
		echo(error_message('Nie ma takiego działu'));
	   break;
	   case 5:
		echo(error_message('W tym dziale nie ma tematów'));
	   break;
	   case 6:
		echo(error_message('Niewyjaśniony błąd'));
	   break;
	   }
	 }
	else { echo(error_message('Nie masz uprawnień!')); }
   break;
   case 'pokaz':
	if($PermHP->hasPermission('eif.dzial.show'))
	 {
      $dzial = $forum->getDzial($dzial_id);
      switch($dzial[1])
	   {
	   case 1:
		$dzial_array = $dzial[0];
		$tematy = $PDO->query("SELECT * FROM `".TBL."tematy` WHERE `dzial_id`=$dzial_id AND `deleted`=0");
	
		if($PermHP->hasPermission('eif.topic.add') or ($PermHP->hasPermission('eif.topic.add.locked') && $dzial_array['lock']==1))
		 {
	      $nowytemat = '<a href="'.$Config->MAIN_DIR.'nowytemat/'.$dzial_id.'" class="button" >Nowy Temat</a>';
		 }
	
		echo('
		 <hgroup>
		  '.$nowytemat.'
		  <h2>'.$dzial_array['nazwa'].'</h2>
		 </hgroup>
		 <ul class="item">
		');
		
		#Funkcja pokazująca tematy
		TopicsList($dzial_id,1); //przypięte
	  
		$tematy = $PDO->query("SELECT * FROM `".TBL."tematy` WHERE `pinned`=0 AND `dzial_id`=$dzial_id AND `deleted`=0 ORDER BY `lastpost_time` DESC");
	  
		foreach($tematy->fetchAll() as $temat_array)
		 {
		$user_id = $temat_array['user_id'];
		$posty = $PDO->query("SELECT `id` FROM `".TBL."posty` WHERE `topic_id`=".$temat_array['id']." AND `deleted`=0");
		$posty_rows = $posty->rowCount();
		
		#odmiana liczby tematów
		if($posty_rows==1) { $ilepostytext='post'; }
		if($posty_rows==0) { $ilepostytext='brak postów'; $posty_rows=''; }
		if($posty_rows>1 && $posty_rows<5) { $ilepostytext='posty'; }
		if($posty_rows>4) { $ilepostytext='postów'; }
	
		if($temat_array['lock']==1)
		 {
		  $lock_html = '<img src="'.$Config->MAIN_DIR.'icon/icon_close.png" class="pinned_topic">';
		 }
		else
		 {
		  $lock_html = '';
		 }
	
		#wyświetlanie
		echo('
		 <li>
		  <aside>'.$posty_rows.' '.$ilepostytext.'</aside>
		  '.$lock_html.'
		  <a href="'.$Config->MAIN_DIR.'temat/pokaz/'.$temat_array['id'].'">'.$temat_array['temat'].'</a>
		  <div class="autor">
		   Autor: '.$forum->ShowUserNick($user_id,'a',1).'
		  </div>
		 </li>
		');
	   }
			
	  #wyświetlanie wiadomości jeśli nie ma żadnych tematów
	  if($tematy -> rowCount() == 0) 
	   {
	    echo('
		 <li>
		  W tym dziale nie ma żadnych tematów
		 </li>
		');
	   }
	
		echo('</ul>');
	 
		$modforum = $PDO->query("SELECT `id` FROM `".TBL."modforum` WHERE `dzial_id`=".$dzial_id);
	
		if($modforum -> rowCount() > 0)
	     {
		  $modforum_txt = ('<span style="font-size:14px;color: #5b83ad;font-weight:normal;">&nbsp;&nbsp;Moderatorzy:&nbsp;');
  
		  #pętla pokazująca moderatorów forum
		  foreach($modforum->fetchAll() as $modforum_array)
		   {
			$modforum_txt .= $forum->ShowUserNick($modforum_array['user_id'],'modforum_nick',1);
	       }
		 
		  $modforum_txt .= '</span><br/><br/>';
		
		  echo($modforum_txt);
		 }
	 
		#narzędzia dla moderatora - usuwanie działu
		$options = array();
	  
		if($PermHP->hasPermission('eif.dzial.clean'))
	     {
		  $options['dzial_clean'] = '<img src="'.$Config->MAIN_DIR.'icon/icon_clean.png" class="opticon"  alt="Wyczyść dział"></span><a href="'.$Config->MAIN_DIR.'dzial/wyczysc/'.$dzial_id.'">Wyczyść dział</a>';
	     }
	   
		if($PermHP->hasPermission('eif.dzial.remove'))
	     {
		  $options['dzial_remove'] = '<img src="'.$Config->MAIN_DIR.'icon/icon_remove.png" class="opticon"  alt="Usuń dział"></span><a href="'.$Config->MAIN_DIR.'usundzial/'.$dzial_id.'">Usuń dział</a>';
	     }
   
		foreach($options as $key => $val)
	     {
		  echo('<span class="dzial_option">'.$options[$key].'</span>');
	     }
	   break;
	   }
	 }
	else { echo(error_message('Nie masz uprawnień')); }
   break;
   case 'dodaj':
	if($PermHP->hasPermission('eif.dzial.add'))
	 {
	  if(isset($_POST['nazwa']))
	   {
		$add = $forum->addDzial($_POST['kategoria_id'],$_POST['nazwa'],$_POST['opis'],$_POST['url'],$_POST['icon_id'],$_POST['count']);
		switch($add[1])
		 {
	     case 1:
	      echo('<header><h2>Dział został dodany!</h2><a href="'.$Config->MAIN_DIR.'admin" class="button">Powrót</a></header>');
		 break;
		 case 2:
	      echo(error_message('Nie podano ID kategorii'));
		 break;
		 case 3:
	      echo(error_message('Niepoprawne ID kategorii'));
		 break;
	     case 4:
	      echo(error_message('Nie podano nazwy'));
	     break;
	     case 5:
	      echo(error_message('W nazwie występują niedozwolone znaki'));
	     break;
	     case 6:
	      echo(error_message('Adres jest niepoprawny'));
	     break;
	     case 7:
	      echo(error_message('Opis zawiera niedozwolone znaki'));
	     break;
	     case 8:
	      echo(error_message('Nie istnieje taka ikona'));
	     break;
	     case 9:
	      echo(error_message('Nie udało się ustalić domyślnej ikony'));
	     break;
	     case 10:
	      echo(error_message('Nie istnieje taka kategoria'));
	     break;
	     case 11:
	      echo(error_message('Niepoprawnie ustalone liczenie'));
	     break;
	     case 12:
	      echo(error_message('ID ikony jest niepoprawne'));
	     break;
	     }
	   }
	  else
	   {
	    echo('
		 <header><h2>Dodawanie nowego działu</h2></header>
		 <form action="" method="post">
	      <div class="select">
		   <select name="kategoria_id">
		');
		  
		$kategorie = $PDO -> query("SELECT * FROM `".TBL."kategorie` ORDER BY `id` DESC");
		  
		foreach($kategorie->fetchAll() as $kA)
		 {
		  echo('<option value="'.$kA['id'].'" >'.$kA['nazwa'].'</option>');
		 }
		  
		echo('
		   </select>
		  </div><br/>
		  <input type="text" name="nazwa" placeholder="Nazwa" /><br/>
		  <input type="text" name="url" placeholder="Url" /><br/>
		  <textarea class="fileedit_ta" name="opis"></textarea><br/>
		  <div class="select">
		   <select name="icon_id" >
		');
		  
		$icons = $PDO -> query("SELECT * FROM `".TBL."dzialicon` ORDER BY `id` DESC");
		  
		foreach($icons->fetchAll() as $iA)
		 {
		  if($iA['default']==1) $def='selected="selected"'; else $def='';
		   echo('<option '.$def.' value="'.$iA['id'].'" >'.$iA['name'].'</option>');
		 } 
		  
		echo('
		   </select>
		  </div><br/>
	 	  <div class="select">
		   <select name="count">
		    <option value="1">Licz posty</option>
			<option value="0">Nie licz postów</option>
		   </select>
		  </div><br/>
		  <input type="submit" value="Dodaj" /><br/>
	     </form>
		');
	   }
	 }
	else { echo(error_message('Nie masz uprawnień!')); }
   break;
   case 'usun':
	if($PermHP->hasPermission('eif.dzial.remove'))
	 {
	  switch($forum->delDzial($SU[1]))
	   {
	   case 1:
		echo(answer_message('Dział został pomyślnie usunięty'));
	   break;
	   case 2:
		echo(error_message('Nie podano ID działu'));
	   break;
	   case 3:
		echo(error_message('ID działu jest niepoprawne'));
	   break;
	   case 4:
		echo(error_message('Nie ma takiego działu'));
	   break;
	   case 5:
		echo(error_message('Dział jest usunięty'));
	   break;
	   }
	 }
	else { echo(error_message('Nie masz uprawnień')); }
   break;
   case 'edytuj':
	if($PermHP->hasPermission('eif.dzial.edit'))
	 {
	  if(empty($_POST['dzialedit_submit']))
	   {
	    $getDzial = $forum->getDzial($SU[2]);
	    switch($getDzial[1])
		 {
		 case 1:
		  $dzial_array = $getDzial[0];
		  $selected = 'selected="selected"';
		  
		  if($dzial_array['lock']==1)
		   {
			$lock_selected[1] = $selected;
		   }
		  else
		   {
			$lock_selected[0] = $selected;
		   }
		  
		  if($dzial_array['deleted']==1)
		   {
			$deleted_selected[1] = $selected;
		   }
		  else
		   {
			$deleted_selected[0] = $selected;
		   }
		  
		  if($dzial_array['counting']==1)
		   {
			$counting_selected[1] = $selected;
		   }
		  else
		   {
			$counting_selected[0] = $selected;
		   }
		?>
		<form action="" method="post">
		 <div class="fileedit_cont">
		 Kategoria:<br/>
		 <div class="select">
		  <select name="category_id">
		   <?php
			$categories = $PDO->query("SELECT `id`,`nazwa` FROM `".TBL."kategorie`");
		   
			foreach($categories->fetchAll() as $category)
			 {
			  if($dzial_array['id_kategoria'] == $category['id'])
			   {
				$kategoria_selected = $selected;
			   }
			  else { $kategoria_selected = ''; }
			  
			  echo('<option '.$kategoria_selected.' value="'.$category['id'].'">'.$category['nazwa'].'</option>'."\n");
			 }
		   ?>
		  </select>
		 </div><br/>
		 Nazwa:<br/>
		 <input type="text" name="nazwa" value="<?php echo $dzial_array['nazwa']; ?>" /><br/>
		 Adres:<br/>
		 <input type="text" name="url" value="<?php echo $dzial_array['url']; ?>" /><br/>
		 Minimum postów:<br/>
		 <input type="text" name="minpost" value="<?php echo $dzial_array['minpost']; ?>" /><br/>
		 Liczenie postów:<br/>
		 <div class="select">
		  <select name="counting">
		   <option <?php echo($counting_selected[1]); ?> value="1">Tak</option>
		   <option <?php echo($counting_selected[0]); ?> value="0">Nie</option>
		  </select>
		 </div><br/>
		 Usunięty:<br/>
		 <div class="select">
		  <select name="deleted">
		   <option <?php echo($deleted_selected[1]); ?> value="1">Tak</option>
		   <option <?php echo($deleted_selected[0]); ?> value="0">Nie</option>
		  </select>
		 </div><br/>
		 Zablokowany:<br/>
		 <div class="select">
		  <select name="lock">
		   <option <?php echo($lock_selected[1]); ?> value="1">Tak</option>
		   <option <?php echo($lock_selected[0]); ?> value="0">Nie</option>
		  </select>
		 </div><br/>
		 Opis:<br/>
		 <textarea class="fileedit_ta" name="opis"><?php echo $dzial_array['opis']; ?></textarea>
		 <input type="submit" name="dzialedit_submit" value="Zatwierdź edycję" />
		 </div>
		</form>
		<?php
		 break;
		 case 2:
		  echo(error_message('Nie podano ID'));
		 break;
		 case 3:
		  echo(error_message('Niepoprawne ID'));
		 break;
		 case 4:
		  echo(error_message('Nie ma takiego działu'));
		 break;
		 }
	   }
	  else
	   {
	    $update = updateDzial(
		$_POST['category_id'],
		$_POST['nazwa'],
		$_POST['opis'],
		$_POST['url'],
		$_POST['icon_id'],
		$_POST['deleted'],
		$_POST['counting'],
		$_POST['minpost']
		);
		switch($update)
		 {
		 case 1:
		  echo(answer_message('Ustawienia działu zostały zapisane'));
		 break;
		 case 2:
		  echo(error_message('Nie podano ID kategorii'));
		 break;
		 case 3:
		  echo(error_message('ID kategorii nie jest liczbą'));
		 break;
		 case 4:
		  echo(error_message('Nie podano nazwy'));
		 break;
		 case 5:
		  echo(error_message('Nazwa posiada niedozwolone znaki'));
		 break;
		 case 6:
		  echo(error_message('Adres jest niepoprawny'));
		 break;
		 case 7:
		  echo(error_message('Opis zawiera niedozwolone znaki'));
		 break;
		 case 8:
		  echo(error_message('Nie istnieje taka ikona'));
		 break;
		 case 9:
		  echo(error_message('Nie udało się ustalić domyślnej ikony'));
		 break;
		 case 10:
		  echo(error_message('Nie istnieje taka kategoria'));
		 break;
		 case 11:
		  echo(error_message('Sposób liczenia postów jest nieporawny'));
		 break;
		 case 12:
		  echo(error_message('ID ikony nie jest liczbą'));
		 break;
		 case 13:
		  echo(error_message('Minimum postów nie jest liczbą'));
		 break;
		 case 14:
		  echo(error_message('Minimum postów jest mniejsze od zera'));
		 break;
		 }
	   }
	 }
	else { echo(error_message('Nie masz uprawnień')); }
   break;
   }
 break;
 case 'ranga':
  switch($SU[1])
   {
   case 'lista':
    if($PermHP->hasPermission('eif.rank.list'))
	 {
	  $rangi = $PermHP->getRanks();
	  echo '<pre>';
	  print_r($rangi);
	 }
	else { echo(error_message('Nie masz uprawnień')); }
   break;
   }
 break;
 case 'leave':
  $url = str_replace('-','/',$SU[1]);
  switch($forum -> siteLeave($url))
   {
   case 1:
    echo(answer_message('Za 5 sekund zostaniesz przekierowany na adres: <b>'.$url.'</b><br>Wróć do nas!'));
   break;
   case 2:
    echo(answer_message('Zostajesz przekierowany'));
   break;
   case 3:
    echo(error_message('Niepoprawny adres'));
   break;
   case 4:
    echo(error_message('Nie podano adresu'));
   break;
   }
 break;
 case 'rejestracja':
  if(!empty($_POST['nick']))
   {
	if($_POST['captcha'] == $_SESSION['captcha'])
	 {
	  $register = $forum->UserRegister(
	   $_POST['nick'],
	   $_POST['pass'],
	   $_POST['passrepeat'],
	   $_POST['mail'],
	   $_POST['gadu'],
	   $_POST['sex'],
	   $_POST['miasto'],
	   $_POST['polec'],
	   $_POST['www']
	  );
	  
	  switch($register)
	   {
	   case 1:
		echo(answer_message('Twoje konto zostało założone'));
	   break;
	   case 2:
		echo(error_message('Nie podano nicku'));
	   break;
	   case 3:
		echo(error_message('Nie podano hasła'));
	   break;
	   case 4:
		echo(error_message('Nie powtórzono hasła'));
	   break;
	   case 5:
		echo(error_message('Nie podano emaila'));
	   break;
	   case 6:
		echo(error_message('GaduGadu jest niepoprawne'));
	   break;
	   case 7:
		echo(error_message('Niepoprawna płeć'));
	   break;
	   case 8:
		echo(error_message('Nick zawiera niedozwolone znaki'));
	   break;
	   case 9:
		echo(error_message('Nick jest za długi'));
	   break;
	   case 10:
		echo(error_message('Polecający użytkownik nie istnieje'));
	   break;
	   case 11:
		echo(error_message('Strona www jest niepoprawna'));
	   break;
	   case 12:
		echo(error_message('Hasła nie są takie same'));
	   break;
	   case 13:
		echo(error_message('Niepoprawny email'));
	   break;
	   case 14:
		echo(error_message('Rejestrowano się już na ten email'));
	   break;
	   case 15:
		echo(error_message('Rejestrowano się już na ten adres IP'));
	   break;
	   case 16:
		echo(error_message('Rejestrowano się już na ten numer GG'));
	   break;
	   case 17:
		echo(error_message('Nazwa jest zajęta'));
	   break;
	   }
	 }
	else { echo(error_message('Captcha jest niepoprawna')); }
   }
  else
   {
	?>
	 <table width="680" border="0" align="center" >
	  <form id="registerform" action="<?php echo $Config->MAIN_DIR; ?>rejestracja" method="post">
	   <tr>
	    <td>Nick</td>
	    <td>
		 <input placeholder="Nick" OnKeyUp="IsFreeNick(this.value,0);" autocomplete="off" type="text" id="reg_nick" name="nick" value="" />
		</td>
		<td>
		 <div id="nickfree" >...</div>
		</td>
	   </tr>
	   <tr>
		<td>Hasło</td>
		<td><input placeholder="Hasło" OnKeyDown="PasswordMatch();pasStr();" type="password" id="pass" name="pass" /></td>
		<td>
		 <div id="passtr" class="str0"></div>
		 <span class="passtr_desc"></span>
		</td>
	   </tr>
	   <tr>
		<td>Powtórz hasło</td>
		<td><input placeholder="hasło" OnKeyUp="PasswordMatch();" type="password" id="passrepeat" name="passrepeat" /></td>
		<td id="passmatch"></td>
	   </tr>
	   <tr>
		<td>E-mail</td>
		<td>
		 <input placeholder="email" type="text" id="mail" name="mail" value="" OnKeyUp="EmailValidate(this.value);" />
		</td>
		<td>
		 <div id="emailvalid">Będzie potrzebny do aktywowania konta.</div>
		</td>
	   </tr>
	   <tr>
	    <td>Gadu-Gadu</td>
		<td>
		 <input placeholder="GaduGadu" onkeypress="if(event.keyCode>57 || event.keyCode<48){return false;}" type="text" id="gadu" name="gadu" value="" />
		</td>
	   </tr>
	   <tr>
		<td>Płeć</td>
		<td>
		 <div class="select">
		  <select id="sex" name="sex">
		   <option value="male">Mężczyzna</option>
		   <option value="female">Kobieta</option>
		  </select>
		 </div>
		</td>
	   </tr>
	   <tr>
		<td>Miasto</td>
		<td>
		 <input placeholder="Miasto" type="text" id="miasto" name="miasto" value="" />
		</td>
	   </tr>
	   <tr>
		<td>Captcha</td>
		<td>
		 <img src="<?php echo $Config->MAIN_DIR; ?>captcha.php" alt="Captcha" style="vertical-align: middle" />
		 <input name="captcha" OnKeyUp="CaptchaCheck(this.value)" style="width: 100px; height: 20px; vertical-align: middle;" type="text" />
		</td>
		<td id="captchacheck">Przepisz kod</td>
	   </tr>
	   <tr>
		<td>Polecający</td>
		<td><input type="text" name="polec" value="<?php echo $SU[1]; ?>" OnKeyUp="IsFreeNick(this.value,1)" /></td>
		<td>
		 <div id="poleccheck" ></div>
		</td>
	   </tr>
	   <tr>
		<td>Strona WWW</td>
		<td><input type="text" name="www" /></td>
	   </tr>
	   <tr>
		<td>Akceptacja <a href="<?php echo $Config->MAIN_DIR; ?>regulamin">Regulaminu</a></td>
		<td><input type="checkbox" id="regulaminaccept" /></td>
	   </tr>
	  </table>
	  <script>
		$('#registerform').submit(function() {
			if(!$("#regulaminaccept").is("input:checked")) {
			alert("Akceptuj regulamin");
			return false;
		}
		return true;
		});
	  </script>
	  <input id="registerbutton" placeholder="" onclick="checkRegister()" type="submit" value="Zarejestruj" /><input placeholder="" type="reset" value="Resetuj" />
	 </form>
	<?php
   }
 break;
 case 'dodajplik':
  if($PermHP->hasPermission('eif.file.add'))
   {
    if(isset($_FILES['plik']))
	 {
	  $plik_tmp = $_FILES['plik']['tmp_name'];  //plik
	  $plik_nazwa = $_FILES['plik']['name'];  //nazwa
	  $plik_rozmiar = $_FILES['plik']['size']; //rozmiar
	  
	  $rank = array();
	  $rank['allowext'] = $PermHP->getRank('AllowExt');
	  $rank['filesize'] = $forum->toBits($PermHP->getRank('FileSize'));
	  $rank['dirsize'] = $forum->toBits($PermHP->getRank('DirSize'));
	  $addfile = $forum->addFile($plik_tmp,$plik_nazwa,$plik_rozmiar,$rank);
	  
	  switch($addfile[1])
	   {
	   case 1:
		echo('<header><h2>Pomyślnie dodano plik</h2><a class="button" href="'.$Config->MAIN_DIR.'file/'.$addfile[0].'">Link do pliku</a></header>');
	   break;
	   case 2:
		echo(error_message('Nie podano pliku'));
	   break;
	   case 3:
		echo(error_message('Nie podano nazwy pliku'));
	   break;
	   case 4:
		echo(error_message('Waga pliku jest zerowa'));
	   break;
	   case 5:
		echo(error_message('Rozszerzenie jest niedozwolone'));
	   break;
	   case 6:
		echo(error_message('Nie podano dozwolonych rozszerzeń'));
	   break;
	   case 7:
		echo(error_message('Nie podano maksymalnej wielkości folderu'));
	   break;
	   case 8:
		echo(error_message('Nie podano maksymalnej wielkości pliku'));
	   break;
	   case 9:
		echo(error_message('Plik jest za duży'));
	   break;
	   case 10:
		echo(error_message('Plik nie zmieści się w folderze'));
	   break;
	   case 11:
		echo(error_message('Pik już istnieje'));
	   break;
	   case 12:
		echo(error_message('Nazwa pliku zawiera niedozwolone znaki'));
	   break;
	   }
	 }
	else
	 {
	  echo('
	   <form enctype="multipart/form-data" action="'.$Config->MAIN_DIR.'dodajplik" method="post">
		<input type="hidden" name="MAX_FILE_SIZE" value="999999999999999999999999999999999999999999999999" /> 
		<input name="plik" type="file" /> 
		<input type="submit" class="submit" value="Dodaj" /> 
	   </form>
	  ');
	 }
   }
  else { echo(error_message('Nie masz uprawnień')); }
 break;
 case 'sourcebin':
  switch($SU[1])
   {
   case 'dodaj':
	if(empty($_POST['source']))
	 {
	  #formularze
	  echo('
	   <form method="post" action="">
		<textarea class="textarea" style="height:300px;width:100% !important;" name="source" ></textarea>
		<input type=submit class="longinput" value="Dodaj SourceBina">
	   </form>
	  ');
	 }
	else
	 {
	  #dodawanie
	  $_POST['source'] = htmlspecialchars_decode($_POST['source']);
	  $PDO->exec("INSERT INTO `sourcebin_kody` VALUES(0,'".$_POST['source']."',$your_id,".time().",0);");
	 
	  #wiadomość
	  echo('<header><h2>Twój Sourcebin został dodany</h2><a class="button" href="'.$Config->MAIN_DIR.'sourcebin/s/'.$PDO->lastinsertid().'" >Przekieruj</a></header>');
	 }
   break;
   case 's';
	#wywoływanie funkcji
	SourcebinShow($SU[2]);
	echo('<br><br><a href="'.$Config->MAIN_DIR.'sourcebin/dodaj">Dodaj nowego sourcebina</a>');
   break;
   default:
	if($PermHP->hasPermission('eif.sourcebin.cp'))
     {
	  echo('<a class="button" href="'.$Config->MAIN_DIR.'sourcebin/dodaj">Dodaj nowego sourcebina</a><br/>Kliknij na ID sourcebina aby przekierować do niego');
	  $sourcebins = $PDO->query("SELECT * FROM `sourcebin_kody` WHERE `deleted`=0");
	   
	  echo('
	   <table class="table" style="width:500px;margin:0 auto;" >
	    <tr>
	     <th style="width:25px;" >ID</th>
	     <th>Autor</th>
	     <th>Data</th>
		</tr>
	  ');
	
	  foreach($sourcebins->fetchAll() as $sb_array)
	   {
	    echo('
	     <tr>
	  	  <td><a href="'.$Config->MAIN_DIR.'sourcebin/s/'.$sb_array['id'].'">'.$sb_array['id'].'</td>
	  	  <td>'.ShowUserNick($sb_array['user_id'],'',1).'</td>
		  <td>'.date('d.m.Y, H:i',$sb_array['time']).'</td>
		 </tr>
		');
	   }
	 
	  echo('</table>');
	 }
	else { echo(error_message('Nie masz uprawnień!')); }
   break;
   }
 break;
 case 'dodajkategorie':
  if($PermHP->hasPermission('eif.category.add'))
   {
	if(isset($_POST['name']))
	 {
	  switch($forum->addCategory($_POST['name']))
	   {
	   case 1:
	    echo(answer_message('Kategoria została dodana!'));
	   break;
	   case 2:
	    echo(error_message('Nie podano nazwy!'));
	   break;
	   case 3:
	    echo(error_message('Za długa nazwa!'));
	   break;
	   }
	 }
	else
	 {
	  echo('
	   <header><h2>Dodawanie nowej kategorii</h2></header>
	   <form action="" method="post">
		<input type="text" name="name" placeholder="Nazwa" style="border-radius: 5px 0px 0px 5px;" />
		<input type="submit" value="Dodaj" style="border-radius: 0px 5px 5px 0px;margin-left: -11px;width:80px;height:34px;" />
	   </form>
	  ');
	 }
   }
  else { echo(error_message('Nie masz uprawnień!')); }
 break;
 case 'ggnapisz':
  if($PermHP->hasPermission('eif.gadu.send'))
   {
	if($SU[1] > 0)
	 {
	  if(!empty($_POST['message']))
	   {
	    $_POST['message'] = ''.$_POST['message'];
	    switch($forum->ggSend($SU[1],$_POST['message']))
		 {
		 case 1:
		  echo(answer_message('Wiadomość została wysłana'));
		 break;
		 case 2:
		  echo(error_message('Plik klasy nie istnieje!'));
		 break;
		 case 3:
		  echo(error_message('Nie podano numeru'));
		 break;
		 case 4:
		  echo(error_message('Niepoprawny numer'));
		 break;
		 case 5:
		  echo(error_message('Nie udało się wysłać wiadomośći'));
		 break;
		 case 6:
		  echo(error_message('Nie udało się połączyć'));
		 break;
		 case 7:
		  echo(error_message('Nie podano treści'));
		 break;
		 }
	   }
	  else
	   {
		echo('
		<form action="" method="post">
	    <textarea class="textarea" name="message" style="min-height:200px;" placeholder="Tutaj wpisz wiadomość..." ></textarea>
	    <br/><input class="longinput" type="submit" />
		</form>
		');
       }
	 }
	else
	 {
	  echo(error_message('Podaj numer GG!'));
	  echo('<br><input type="text" id="gg" /><input type="button" onclick="SmoothGet(\'gg\',\'ggnapisz\')" value="Przekieruj" >');
	 }
   }
  else { echo(error_message('Nie masz uprawnień')); }
 break;
 case 'haslo': //zmiana hasła
  switch($SU[1])
   {
   case 'zmien':
	if($PermHP->hasPermission('eif.user.cp.password.change'))
	 {
      if(!empty($_POST))
	   {
		$Info = array( 
		'user_id' => $your_id,
		'current' => $_POST['pass_current'],
		'new' => $_POST['pass_newpass'],
		'newr' => $_POST['pass_newpassr']
		);
	
		switch($forum->changePass($Info))
		{
		case 1:
		echo(answer_message('Twoje hasło zostało zmienione'));
		break;
		case 2:
		echo(error_message('Nie podałeś identyfikatora użytkownika'));
		break;
		case 3:
		echo(error_message('Nie podano aktualnego hasła'));
		break;
		case 4:
		echo(error_message('Nie podano nowego hasła'));
		break;
		case 5:
		echo(error_message('Nie powtórzono nowego hasła'));
		break;
		case 6:
		echo(error_message('Hasła nie są takie same'));
		break;
		case 7:
		echo(error_message('Hasło nie pasuje do aktualnego'));
		break;
		}
	   }
	  else
	   {
		echo('
		<form action="" method="post">
	    <div class="fileedit_cont">
	     <input type="password" name="pass_current" placeholder="Aktualne hasło" />
	     <input type="password" name="pass_newpass" placeholder="Nowe hasło" />
	     <input type="password" name="pass_newpassr" placeholder="Powtórz hasło" />
	     <input type="submit" value="Zmień hasło" />
	    </div>
		</form>
		');
	   }
	 }
	else { echo(error_message('Nie masz uprawnień')); } 
   break;
   default:
	echo(error_message('Nie istnieje taka strona'));
   break;
   case 'przypomnij':
	if($PermHP->hasPermission('eif.user.password.recovery'))
	 {
	  if(isset($_POST['recovery_submit']))
	   {
		(int)$code = $_POST['recovery_code'];
		$usercode = $PDO->query("SELECT `id` FROM `".TBL."users` WHERE `passrecoverycode`=".$code);
	    $userid = $usercode->fetchColumn(0);
	   
	    if($userid > 0)
		 {
		  $usertime = $PDO->query("SELECT `time_rej` FROM `".TBL."users` WHERE `id`=".$userid);
		  $Npass = $forum->passPrepare(rand(0,999999999999),$usertime->fetchColumn(0));
		  $PDO->exec("UPDATE `".TBL."users` SET `password`='', `passrecoverycode`=0 WHERE `id`=".$userid);
		  echo(answer_message('Twoje hasło zostało zresetowane'));
		 }
		else { echo(error_message('Niepoprawny kod')); }
	   }
	  else
	   {
		echo getTemplate('user-password-recovery');
	   }
	 }
	else { echo(error_message('Nie masz uprawnień')); } 
   break;
   }
 break;
 case 'wiadomosci':
  switch($SU[1])
   {
   case '':
	#nagłówek
    echo('
	 <header>
	  <a href="'.$Config->MAIN_DIR.'wiadomosci/wyslane" style="margin-left: 5px;" class="button">Wysłane</a>
	  <a href="'.$Config->MAIN_DIR.'wiadomosci/napisz" style="margin-left: 5px;" class="button">Napisz wiadomość</a>
	  <h2>Wiadomości</h2><span>Tutaj możesz odbierać i pisać wiadomości.</span><br/>
	 </header>
	');
		
	$messages = $PDO->query("SELECT * FROM `".TBL."message` WHERE `to_id`=$your_id ORDER BY id DESC LIMIT 0,20");
	  
	  #tabela
	  echo("
	   <table class='table pms' >
	    <tr>
		 <th style='width: 400px;' >Temat</th>
		 <th style='width: 190px;' >Od</th>
		 <th style='width: 110px;' >Data</th>
		</tr>
	  ");
	  
	if($messages->rowCount() > 0)
	 {
	  foreach($messages->fetchAll() as $message_array)
	   {
		if($message_array['deleted']==0)
		 {
		  #ustawianie stylu (odebrane czy nie)
		  if($message_array['odebrano']==0)
		   {
			$newmsg = 'newmsg';
		   }
		  else
		   {
			$newmsg='';
		   }
	  
		  #wyświetlanie
		  echo('
		   <tr class="'.$newmsg.'" >
		   <td><a href="'.$Config->MAIN_DIR.'wiadomosci/czytaj/'.$message_array['id'].'" width="100%" height="32px">'.$message_array['tytul'].'</a></td>
		   <td>'.ShowUserNick($message_array['user_id'],'',1).'</td>
		   <td style="width: 110px;">'.date('d.m.Y, H:i',$message_array['time']).'</td>
		   </tr>
		  ');
		 }
	   }
	 }
	else
	 {
	  echo('
	   <tr class="newmsg" >
	    <td>Nie ma żadnych wiadomości</td>
	    <td></td>
	    <td></td>
	   </tr>
	  ');
	 }
	echo('</table>'); //koniec tabeli
   break;
   case 'napisz':
	if($your_id>0)
	 {
      if($PermHP->hasPermission('eif.message.add'))
	   {
		echo("
		 <header>
	      <a href='".$Config->MAIN_DIR."wiadomosci/wyslane' style='margin-left: 5px;' class='button'>Wysłane</a>
		  <a href='".$Config->MAIN_DIR."wiadomosci' style='margin-left: 5px;' class='button'>Odebrane</a>
		  <h2>Wiadomości</h2><span>Napisz nową wiadomość</span><br/>
		 </header>
		");
	  
		if($_POST)
		 {
		  $m_title = $_POST['title'];
		  $user_nick = $_POST['do_nick'];
		  $m_tresc = $_POST['tresc'];
		  $user_id = (int)$SU[2];
		
		  #zapytanie o usera
		  if(!empty($user_nick))
		   {
			$user = $PDO->query("SELECT * FROM `".TBL."users` WHERE `nick`='$user_nick'");
		   }
		  else
		   {
			$user = $PDO->query("SELECT * FROM `".TBL."users` WHERE `id`=$user_id");
		   }
		
		  if($user->rowCount() == 1)
		   {
			#tablica usera
			$user_array = $user->fetch();
			$user_id = $user_array['id'];
		  
			if($user_id!==$your_id)
			 {			
			  if(empty($m_title)) { $m_title = 'Brak tytułu'; }
			
			  #dodawanie
			  $PDO->query("INSERT INTO `".TBL."message` VALUES(0,'$your_id','$user_id','$m_title','$m_tresc',0,".time().",0);");
		   
			  #wysyłanie maila do odbiorcy
			  $temat = 'Nowa prywatna wiadomosc - '.$Config->SITE_NAME;
			  $text = "Witaj!\n\nDostałeś nową prywatną wiadomość na naszym forum! Nadawcą jest użytkownik: ".$your_array['nick']."\nAby odebrać wiadomość wejdź na nasze forum: ".$Config->MAIN_DIR."\nI kliknij ikonkę wiadomości która pokaże się po zalogowaniu.\n\nPozdrawiamy, ekipa ".$Config->SITE_NAME;
		      $forum->sendMail($user_array['email'],$text,$temat);
			
			  echo(answer_message("Wiadomość została wysłana."));
			 }
			else { echo(error_message('Nie możesz wysłać wiadomości do siebie!')); }
		   }
		  else { echo(error_message('Taki użytkownik nie istnieje')); }
		 }
		else
		 {
	      if(!empty($SU[2]))
		   {
			$do_input = 'Piszesz wiadomość do użytkownika: '.$forum->ShowUserNick($SU[2],'',1).'<br/>';
		   }
		  else
		   {
			$do_input = '<input type="text" name="do_nick" class="longinput" placeholder="Nick użytkownika" value="'.$do_nick.'" />';
		   }
		
		  echo('
		   <form action="" method="post">
			'.$do_input.'
			<input type="text" name="title" class="longinput" placeholder="Tytuł wiadomości" />
			<textarea name="tresc" style="min-height: 300px;" class="textarea" ></textarea>
			<input type="submit" value="Wyślij wiadomość" />
		   </form>
		  ');
		 }
	   }
	  else { echo(error_message('Nie masz uprawnień')); }
	 }
	else { echo(error_message('Zaloguj się')); }
   break;
   case 'czytaj':
	$msg = $forum->getMessage($SU[2]);
	
	switch($msg[1])
	 {
	 case 1:
	  $msg_array = $msg[0];
	  
	  if($msg_array['deleted']==0)
	   {
		echo('<header><h2>'.$msg_array['tytul'].'</h2><a href="'.$Config->MAIN_DIR.'wiadomosci/napisz/'.$msg_array['user_id'].'" class="button">Odpisz</a></header>');
		//echo(PostFrame($message_array['user_id'],$message_array['tresc'],$message_array['time']));
			
		echo($msg_array['tresc']);
		
		#ustawianie statusu wiadomości odebranej
		if($your_id==$msg_array['to_id'])
		 {
		  $PDO->exec("UPDATE `".TBL."message` SET `odebrano`=1 WHERE `id`=".$SU[2]);
		 }
	   }
	  else { echo(error_message('Ta wiadomość jest usunięta')); }
	 break;
	 case 2:
	  echo(error_message('Nie podano ID'));
	 break;
	 case 3:
	  echo(error_message('Niepoprawne ID'));
	 break;
	 case 4:
	  echo(error_message('Taka wiadomość nie istnieje'));
	 break;
	 }
   break;
   case 'wyslane':
	if($PermHP->hasPermission('eif.message.sent'))
	 {
	  $messages = $PDO->query("SELECT * FROM `".TBL."message` WHERE `user_id`=$your_id ORDER BY `id` DESC");
	  
	  #tabela
	  echo("
	   <table class='table pms' >
	    <tr>
		 <th style='width: 400px;' >Temat</th>
		 <th style='width: 190px;' >Do</th>
		 <th style='width: 110px;' >Data</th>
		</tr>
	  ");
	  
	  foreach($messages->fetchAll() as $message_array)
	   {
		if($message_array['deleted']==0)
		 {
		  #ustawianie stylu (odebrane czy nie)
		  if($message_array['odebrano']==0)
		   {
			$newmsg = 'newmsg';
		   }
		  else
		   {
			$newmsg='';
		   }
	  
		  #wyświetlanie
		  echo('
		   <tr class="'.$newmsg.'" >
		   <td><a href="'.$Config->MAIN_DIR.'wiadomosci/czytaj/'.$message_array['id'].'" width="100%" height="32px">'.$message_array['tytul'].'</a></td>
		   <td>'.ShowUserNick($message_array['to_id'],'',1).'</td>
		   <td style="width: 110px;">'.date('d.m.Y, H:i',$message_array['time']).'</td>
		   </tr>
		  ');
		 }
	   }
	   
	  echo('</table>');
	 }
   break;
   default:
	echo(error_message('Taka strona nie istnieje'));
   break;
   }
 break;
 //-------
 case 'napiszmaila': //zamykanie tematu
  if(!empty($_POST['wiadomosc']))
   {
    $user = $forum->getUser($SU[1]);
	
	$wiadomosc = "Dostałeś emaila z forum ".$Config->SITE_NAME." od użytkownka ".$your_array['nick']." \n\n\n".$_POST['wiadomosc'];
	
    switch($forum->sendMail($user[0]['email'],$wiadomosc,'Wiadomosc na '.$Config->SITE_NAME))
	 {
	 case 1:
	  echo(answer_message('Twoja wiadomość została wysłana'));
	 break;
	 }
   }
  else
   {
    if(!empty($SU[1]))
	 {
	  echo('
	   Piszesz emaila do użytkownika '.$forum->ShowUserNick($SU[1]).'<br>
	   <form action="" method="post">
		<textarea class="textarea" name="wiadomosc" placeholder="Treść emaila" style="min-height:300px;" ></textarea>
		<input class="longinput" type="submit" value="Wyślij emaila" />
	   </form>
	  ');
	 }
	else { echo(error_message('Podaj ID użytkownika')); }
   }
 break;
 //-------
 case 'temat':
  switch($SU[1])
   {
   case 'pokaz':
	$topic_id = $SU[2];
	$topic = $forum -> getTopic($topic_id);
	
	switch($topic[1])
	 {
	 case 1:
      $topic_array = $topic[0];
	
	  $dzial = $forum -> getDzial($topic_array['dzial_id']);
      $dzial_array = $dzial[0];
   
	  switch($dzial[1])
	   {
	   case 1:
		if($topic_array['deleted'] == 0)
		 {
		  $posty = $PDO->query("SELECT `id` FROM `".TBL."posty` WHERE `topic_id`=$topic_id and `deleted`=0 ORDER BY `id` ASC"); //zapytanie o posty
		  $posty_array = $posty -> fetchAll();
	
	  	  if($topic_array['pinned']==1)
		   {
			$pinned_icon = '<img class="pinned_topic" src="'.$Config->MAIN_DIR.'icon/icon_attach.png" alt="Ten temat jest przypięty" style="float:right;" >';
		   }
		  else { $pinned_icon=''; }
		 
		  $kategoria = $forum->getCategory($dzial_array['id_kategoria']);
		  $kategoria_array = $kategoria[0];
		 
		  #wyświetlanie nagłówka
		  echo('
		   <header style="height:10px;padding-top:3px;color:#28292a;">
		    <a href="'.$Config->MAIN_DIR.'">'.$Config->SITE_NAME.'</a> -> 
		    <a href="'.$Config->MAIN_DIR.'kategoria/pokaz/'.$kategoria_array['id'].'">'.$kategoria_array['nazwa'].'</a> -> 
		    <a href="'.$Config->MAIN_DIR.'dzial/pokaz/'.$topic_array['dzial_id'].'">'.$dzial_array['nazwa'].'</a> -> 
		    <a href="'.$Config->MAIN_DIR.'temat/pokaz/'.$topic_id.'">'.$topic_array['temat'].'</a>
		   </header>
		 
		   <header>
		    <h2>'.$topic_array['temat'].'</h2>
		    '.$pinned_icon.'
		   </header>
		  ');
  
		  #pętla wyświetlająca post
		  foreach($posty_array as $post_array)
		   {
		    ShowPost($post_array['id']);
	       }
	   
	      #możliwość dopisania odpowiedzi jeśli jestem zalogowany i temat nie jest zamknięty
	      if(($PermHP->hasPermission('eif.post.add.opened') && $topic_array['lock']==0) or $PermHP->hasPermission('eif.post.add.closed'))
		   {
		    if($your_id > 0)
	         {
			  ?>
			  <br>
			  <h2>Odpowiedz</h2>
			  <br>
			  Możesz używać BBcode! <strong onclick="wrapText(\'tresc_post\',\'[b]\',\'[/b]\')">[b]</strong>&nbsp;
			  <i onclick="wrapText(\'tresc_post\',\'[i]\',\'[/i]\')">[i]</i>&nbsp;
			  <u onclick="wrapText(\'tresc_post\',\'[u]\',\'[/u]\')">[u]</u>
			  <span onclick="wrapText(\'tresc_post\',\'[img]\',\'[/img]\')">[img]obraz.png[/img]</span>&nbsp;
			  [url=google.com]google[/url]
			  <br><br>
			  <form action="<?php echo $Config->MAIN_DIR; ?>post/dodaj/<?php echo $topic_id; ?>" method="post" >
			    <textarea class="textarea" cols="119" rows="10" id="tresc_post" name="tresc_post" ></textarea>
			    <br><input class="longinput" type="submit" value="Napisz post" />
			   </form>
			 <?php
			 }
			else { echo(answer_message('Jeśli chcesz napisać odpowiedź musisz się <a href="'.$Config->MAIN_DIR.'login">zalogować</a>.')); }
		   }
	   
		  #narzędzia dla moderatora - zamykanie tematu
		  $options = array(); //domyślne opcje
	      if($PermHP->hasPermission('eif.topic.lock.other') or ($PermHP->hasPermission('eif.topic.lock.mine') && $user_id==$your_id))
	       {
		    if($topic_array['lock']==0)
		     {
	 	      $options['topic_lock'] = '<img src="'.$Config->MAIN_DIR.'icon/icon_close.png"  alt="Zamknij temat"></span><a href="'.$Config->MAIN_DIR.'temat/zamknij/'.$topic_id.'">Zamknij temat</a>';
 		     }
		   }
	   
		  #narzędzia dla moderatora - otwieranie tematu
		  if($PermHP->hasPermission('eif.topic.open.other') or ($PermHP->hasPermission('eif.topic.open.mine') && $user_id==$your_id))
	       {
			if($topic_array['lock']==1)
		     {
	 		  $options['topic_open'] = '<img src="'.$Config->MAIN_DIR.'icon/icon_open.png"  alt="Otwórz temat"></span><a href="'.$Config->MAIN_DIR.'temat/otworz/'.$topic_id.'">Otworz temat</a>';
		     }
		   }
	   
		  #narzędzia dla moderatora - przenoszenie tematu
		  if($PermHP->hasPermission('eif.topic.move'))
	       {
			$options['topic_move'] = '<img src="'.$Config->MAIN_DIR.'icon/icon_move.png"  alt="Przenieś temat"></span><a href="'.$Config->MAIN_DIR.'temat/przenies/'.$topic_id.'">Przenieś temat</a>';
		   }
	   
		  #narzędzia dla moderatora - przypinanie
		  if($PermHP->hasPermission('eif.topic.setpin'))
	       {
		    if($topic_array['pinned']==0)
		     {
			  $options['topic_setpin1'] = '<img src="'.$Config->MAIN_DIR.'icon/icon_attach.png"  alt="Przypnij temat"></span><a href="'.$Config->MAIN_DIR.'temat/przypnij/'.$topic_id.'/1">Przypnij temat</a>';
		     }
		    elseif($topic_array['pinned']==1)
		     {
		 	  $options['topic_setpin0'] = '<img src="'.$Config->MAIN_DIR.'icon/icon_detach.png"  alt="Odepnij temat"></span><a href="'.$Config->MAIN_DIR.'temat/przypnij/'.$topic_id.'/0">Przypnij temat</a>';
		     }
		   }
		
		  foreach($options as $key => $val)
		   {
		    echo('<span style="margin-left:20px; position:relative; top:4px; right:5px;">'.$options[$key].'</div>');
		   }
	     }
		else { echo(error_message('Ten temat jest usunięty')); }
	   break;
	   }
     break;
     case 2:
      echo(error_message('Nie podano ID'));
     break;
     case 3:
      echo(error_message('Niepoprawne ID'));
     break;
     case 4:
 	  echo(error_message('Temat nie istnieje'));
     break;
     }
   break;
   case 'przenies':
	if($PermHP->hasPermission('eif.topic.move'))
	 {
	  if(!empty($_POST['dzial_id']))
	   {
	    switch($forum->moveTopic($SU[2],$_POST['dzial_id']))
	     {
	     case 1:
		  echo('<header><h2>Temat został przeniesiony</h2><a href="'.$Config->MAIN_DIR.'temat/pokaz/'.$SU[2].'" class="button">Powrót</a></header>');
	     break;
	   case 2:
		echo(error_message('Nie podano ID tematu'));
	   break;
	   case 3:
		echo(error_message('Niepoprawne ID tematu'));
	   break;
	   case 4:
		echo(error_message('Nie podano ID działu'));
	   break;
	   case 5:
		echo(error_message('Niepoprawne ID działu'));
	   break;
	   case 6:
		echo(error_message('Temat nie istnieje!'));
	   break;
	   case 7:
		echo(error_message('Temat jest usunięty!'));
	   break;
	   }
	 }
	else
	 {
	  echo('
	  <header>
	   <h2>
		Przenoszenie tematu
	   </h2>
	  </header>
	  Wybierz dział:<br>
	  <form action="" method="post">
	   <div class="select">
	    <select name="dzial_id">');
		
	  #zapytanie o działy
	  $dzialy = $PDO->query("SELECT * FROM `".TBL."dzial` WHERE `url`='' ORDER BY id DESC");
	  $dzialy_rows = $dzialy -> rowCount();
	  
	  foreach($dzialy -> fetchAll() as $dzialy_array)
	   {
		echo('<option value="'.$dzialy_array['id'].'" >'.$dzialy_array['nazwa'].'</option>');
	   }
	
	  echo('
	    </select>
	   </div>
	   <input type="submit" value="Przenieś" />
	  </form>
	  ');
	  }
	 }
	else { echo(error_message('Nie masz uprawnień')); }
   break;
   case 'przypnij': //przypinanie/odpinanie tematu
	$state = $SU[3];
	if(($PermHP->hasPermission('eif.topic.setpin.true') && $state==1) OR ($PermHP->hasPermission('eif.topic.setpin.false') && $state==0))
	 {
	  switch($forum->SetPinned($SU[2],$state))
	   {
	   case 1:
        switch($state)
	     {
	     case 0:
		  echo(answer_message('Temat został odpięty'));
	     break;
         case 1:
		  echo(answer_message('Temat został przypięty'));
	     break;
	     }
       break;
       case 2:
		echo(error_message('Nie podano ID'));
       break;
       case 3:
		echo(error_message('Niepoprawne ID'));
       break;
       case 4:
		echo(error_message('Nie podano stanu'));
       break;
	   case 5:
        echo(error_message('Niepoprawny stan'));
	   break;
       case 6:
		echo(error_message('Nie ma takiego tematu'));
       break;
       case 7:
		echo(error_message('Temat jest już przypięty'));
       break;
       case 8:
		echo(error_message('Temat jest już odpięty'));
       break;
       case 9:
		echo(error_message('Temat jest usunięty'));
	   break;
	   }
	 }
	else { echo(error_message('Nie masz uprawnień')); } 
   break;
   case 'zamknij':
	$topic_id = $SU[2];
	$topic = $forum->getTopic($topic_id);
	if($topic[1]==1) $topic_array = $topic[0];
	if($PermHP->hasPermission('eif.topic.lock.other') or ($PermHP->hasPermission('eif.topic.lock.mine') && $topic_array['user_id']==$your_id))
	 {
	  switch($forum->closeTopic($topic_id))
	   {
	   case 1:
		echo('<header><h2>Temat został zamknięty!</h2><a class="button" href="'.$Config->MAIN_DIR.'temat/pokaz/'.$topic_id.'">Powrót</a></header>');
	   break;
	   case 2:
		echo(error_message('Nie podano ID'));
	   break;
	   case 3:
		echo(error_message('Niepoprawne ID'));
	   break;
	   case 4:
		echo(error_message('Temat nie istnieje!'));
	   break;
	   case 5:
		echo(error_message('Temat jest już zamknięty!'));
	   break;
	   case 6:
		echo(error_message('Temat jest usunięty!'));
	   break;
	   }
	 }
	else { echo(error_message('Nie masz uprawnień')); }
   break;
   case 'otworz':
	$topic_id = $SU[2];
	$topic = $forum->getTopic($topic_id);
	if($topic_array[1]==1) $topic_array = $topic[0];
	if($PermHP->hasPermission('eif.topic.open.other') or ($PermHP->hasPermission('eif.topic.open.mine') && $topic_array['user_id']==$your_id))
	 {
	  switch($forum->openTopic($topic_id))
	   {
	   case 1:
		echo('
		 <header>
		  <h2>
		   Temat został otwarty!
		  </h2>
		  <a class="button" href="'.$Config->MAIN_DIR.'temat/pokaz/'.$topic_id.'">Powrót</a>
		 </header>');
	   break;
	   case 2:
		echo(error_message('Nie podano ID'));
	   break;
	   case 3:
		echo(error_message('Niepoprawne ID'));
	   break;
	   case 4:
		echo(error_message('Temat nie istnieje!'));
	   break;
	   case 5:
		echo(error_message('Temat jest już otwarty!'));
	   break;
	   case 6:
		echo(error_message('Temat jest usunięty!'));
	   break;
	   }
	 }
	else { echo(error_message('Nie masz uprawnień')); }
   break;
   }
 break;
 //-------
 case 'kategoria':
  switch($SU[1])
   {
   case 'pokaz':
	echo('Strona w produkcji');
   break;
   case 'edytuj':
    
   break;
   }
 break;
 case 'login': //logowanie
  if(!empty($_POST))
   {
    if($PermHP->hasPermission('eif.log.in'))
	 {
	  switch($forum->loginUser($_POST['nick'],$_POST['pass']))
	   {
	   case 1:
	    echo(answer_message('Pomyślnie zalogowano'));
		$forum->Redirect($Config->MAIN_DIR,3);
	   break;
	   case 2:
	    echo(error_message('Nie podano nicku'));
	   break;
	   case 3:
	    echo(error_message('Nie podano hasła'));
	   break;
	   case 4:
	    echo(error_message('Nie ma takiego użytkownika'));
	   break;
	   case 5:
	    echo(error_message('Hasło jest niepoprawne'));
	   break;
	   }
	 }
	else
	 {
	  echo(error_message('Nie masz uprawnień'));
	 }
   }
  else
   {
	echo('
	<center>
	 <table border="0" align="center" >
	  <form action="'.$Config->MAIN_DIR.'login" method="post">
	   <tr>
		<td><input placeholder="Nick" type="text" id="nick" name="nick" value="" /></td>
	   </tr>
	   <tr>
		<td><input placeholder="Hasło" type="password" id="pass" name="pass" value="" /></td>
	   </tr>
	   <tr>
	    <td><input type="submit" value="Zaloguj" /></td>
	   </tr>
	  </form>
	 </table>
	</center>
	');
   }
 break;
 //-------
 case 'log':
  switch($SU[1])
   {
   case 'filtruj':
	if($PermHP->hasPermission('eif.log.clear.filter'))
	 {
	  $logclear_mt = microtime(true);
	  //$clear = $PDO->prepare("
	  $sql = ("
	  DELETE from ".TBL."log WHERE get_array LIKE 'a => isfreenick,nick => %' AND post_array='';
	  DELETE from ".TBL."log WHERE get_array LIKE 'a => emailvalidate,email => %' AND post_array='';
	  DELETE from ".TBL."log WHERE get_array LIKE '%images/loadingAnimation.gif';	
	  DELETE from ".TBL."log WHERE get_array='' AND post_array='';
	  DELETE from ".TBL."log WHERE get_array='a => sbrefresh' AND post_array='';
	  DELETE from ".TBL."log WHERE get_array='a => onlinestats' AND post_array='';
	  DELETE from ".TBL."log WHERE get_array='a => shout' AND post_array='' ;
	  DELETE FROM ".TBL."log WHERE get_array LIKE 'p => temat,topic_id =>%' AND user_id=1 AND post_array='';
	  DELETE FROM ".TBL."log WHERE get_array='p => lastpost' AND post_array='';
	  DELETE FROM ".TBL."log WHERE get_array LIKE 'a => collapse,col_id => %' AND post_array='';
	  ");
	  
	  //$crc = $clear->execute();
	  $crc = $PDO->exec($sql);
	  
	  echo(answer_message('Logi zostały wyczyszczone.'));
	  echo('
	  Informacje:<br/>
	  <b>Czas czyszczenia:</b> '.round(microtime(true)-$logclear_mt,7).'<br/>
	  <b>Wyczyszczono logów:</b> '.$crc.'
	  ');
	 }
	else { echo(error_message('Nie masz uprawnień!')); }
   break;
   case 'pokaz':
	if($PermHP->hasPermission('eif.log.show'))
	 {
	  echo('
	   <header>
	    <h2>Przeglądasz logi.</h2>
	    <a href="'.$Config->MAIN_DIR.'log/filtruj" class="button">Filtruj</a>
	   </header>
	   <table class="table" style="max-width:100%;">
		<tr>
		 <th>Użytkownik</th>
		 <th style="width:150px;">Data</th>
		 <th>Adres IP</th>
		 <th style="width:20%;">GET</th>
		 <th style="width:20%;">POST</th>
		</tr>
	  ');
	 
	  $logs = $PDO->query("SELECT * FROM `eif_log` ORDER BY `id` DESC LIMIT 0,500");
	  
	  foreach($logs as $log_array)
	   {
	    if(!empty($log_array['post_array']))
		 {
		  $post = substr($log_array['post_array'],0,20).'...';
		 }
		else { $post = ''; }
		 
		echo('
		 <tr>
		  <td>'.$forum->ShowUserNick($log_array['user_id'],'',1).'</td>
		  <td>'.date('d.m.Y, H:i',$log_array['time']).'</td>
		  <td>'.$log_array['user_ip'].'</td>
		  <td>'.$log_array['get_array'].'</td>
		  <td>'.$post.'</td>
		 </tr>
		');
	   }
	   
	  echo('</table>');
	 }
	else { echo(error_message('Nie masz uprawnień!')); }
   break;
   default:
	echo(error_message('Taka strona nie istnieje'));
   break;
   }
 break;
 case 'admin': //Panel administratora
  if($PermHP->hasPermission('eif.admincp.enter'))
   {
    switch($SU[1])
	 {
	 case '':
	  echo('
	   Lista użytkowników: <a href="'.$Config->MAIN_DIR.'lista-userow">Zobacz listę użytkowników</a><br/><hr/>
	   Dodawanie kategorii (forum): <a href="'.$Config->MAIN_DIR.'dodajkategorie">Dodaj</a><br/><hr/>
	   Dodawanie działu (forum): <a href="'.$Config->MAIN_DIR.'dzial/dodaj">Dodaj</a><br/><hr/>	
	   Rejestrowanie logów: <a href="'.$Config->MAIN_DIR.'log/pokaz">Pokaż</a><br/><hr/>	
	   Ustawienia forum: <a href="'.$Config->MAIN_DIR.'admin/ustawienia">Pokaż</a><br/><hr/>	
	  ');
	 break;
	 case 'ustawienia':
	  if(isset($_POST['settings_submit']))
	   {
	   
	   }
	  else
	   {
		eval(__DIR__ .'/css/'.$Config->CSS_TEMPLATE.'/template/admin-settings-form.php');
	   }
	 break;
	 }
   }
  else { echo(error_message('Nie masz uprawnień!')); }
 break;
 //-------
 case 'post': //POSTY
  $post_id = $SU[2];
  if($post_id>0)
   {
    $getPost = $forum->getPost($post_id);
   }
  
  switch($SU[1])
   {
   case 'edytuj':
	switch($getPost[1])
	 {
	 case 1:
	  $post_array = $getPost[0];
	  $getTopic = $forum->getTopic($post_array['topic_id']);
	  $getDzial = $forum->getDzial($getTopic[0]['dzial_id']);
	  
	  if($PermHP->hasPermission('eif.post.edit.other') OR ($PermHP->hasPermission('eif.post.edit.mine') && $your_id==$post_array['user_id']))
	   {
		if($post_array['deleted'] == 0)
		 {
		  if($getDzial[0]['lock'] == 0)
		   {
			if($getTopic[0]['lock'] == 0)
			 {
			  if(!empty($_POST['tresc_post']))
			   {
				$_POST['tresc_post'] = strip_tags($_POST['tresc_post']);
				$tytul = strip_tags($_POST['tytul']);
			
				if(!empty($tytul))
				 {
				  if($PermHP->hasPermission('eif.topic.edit.title'))
				   {
					$PDO->exec("UPDATE `eif_tematy` SET `temat`='$tytul' WHERE `id`=".$post_array['topic_id']);
				   }
				 }
				$PDO->exec("UPDATE ".TBL."posty SET `tresc`='".$_POST['tresc_post']."' WHERE `id`=$post_id");
				echo(answer_message('Edytowanie zakończone pomyślnie.'));
				Redirect($Config->MAIN_DIR.'post/s/'.$post_id);
			   }
			  else
			   {
				if($post_array['starter']==1 && $PermHP->hasPermission('eif.topic.edit.title'))
				 {
				  $tytul_input = '<input type="text" class="longinput" name="tytul" placeholder="Tytuł" value="'.$getTopic[0]['temat'].'" />';
				 }
				 
				echo('
				 <form action="'.$Config->MAIN_DIR.'post/edytuj/'.$post_id.'" method="post">
				  '.$tytul_input.'
				  <textarea class="textarea" id="tresc_post" style="height:400px;" name="tresc_post" >'.$getPost[0]['tresc'].'</textarea>
				  <br/><input type="submit" value="Zapisz" />
				 </form>
				');
			   }
			 }
			else { echo(error_message('Ten temat jest zablokowany')); }
		   }
		  else { echo(error_message('Ten dział jest zablokowany')); }
		 }
		else { echo(error_message('Ten post jest usunięty')); }
	   }
	  else { echo(error_message('Nie masz uprawnień')); }
	 }
   break;
   case 'usun':	
	if($PermHP->hasPermission('eif.post.delete.other') or ($PermHP->hasPermission('eif.post.delete.other') && $your_id===$getPost[0]['user_id']))
	 {
	  switch($del = $forum->delPost($post_id))
	   {
	   case 1:
		echo('<header><h2>Post został usunięty</h2><a href="'.$Config->MAIN_DIR.'temat/pokaz/'.$getPost[0]['topic_id'].'" class="button">Powrót</a></head>');
	   break;
       case 2:
		echo(error_message('Nie podano ID postu'));
       break;
       case 3:
		echo(error_message('Niepoprawne ID postu'));
       break;
       case 4:
		echo(error_message('Nie ma takiego postu'));
       break;
       case 5:
		echo(error_message('Post jest już usunięty'));
       break;
       }
	 }
	else { echo(error_message('Nie masz uprawnień')); }
   break;
   case 'dodaj': //dodawanie posta
	$topic_id = $SU[2];
	$getTopic = $forum->getTopic($topic_id);
	if($PermHP->hasPermission('eif.post.add.locked') OR ($getTopic[0]['lock']==0 && $PermHP->hasPermission('eif.post.add.opened')))
	 {
	  $addpost = $forum->addPost($topic_id,$_POST['tresc_post']);
 
	  switch($addpost[1])
	   {
	   case 1:
		echo('
		 <header>
	 	  <h2>
		   Twój post został dodany!
		  </h2>
		  <a href="'.$Config->MAIN_DIR.'post/s/'.$addpost[0].'" class="button">Przekieruj</a>
		 </header>
		');
	   break;
	   case 2:
		echo(error_message('Nie podano ID tematu'));
       break;
	   case 3:
		echo(error_message('ID tematu jest niepoprawne'));
	   break;
	   case 4:
		echo(error_message('Nie podano wiadomości'));
	   break;
	   case 5:
		echo(error_message('Nie ma takiego tematu'));
	   break;
	   case 6:
		echo(error_message('Temat jest usunięty'));
	   break;
	   case 7:
		echo(error_message('Nie jesteś zalogowany'));
	   break;
	   case 8:
		echo(error_message('Treść zawiera niedozwolone znaki'));
	   break;
	   case 9:
		echo(error_message('ID użytkownika nie jest liczbą'));
	   break;
	   case 10:
		echo(error_message('Temat jest zablokowany'));
	   break;
	   }
	 }
	else { echo(error_message('Nie masz uprawnień')); }
   break;
   case 's': //przekierowywanie do tematu /post/ID
	if($PermHP->hasPermission('eif.topic.show.bypost'))
	 {
      $post = $forum->getPost($SU[2]);
	
	  switch($post[1])
	   {
	   case 1:
		$post_array = $post[0];
		if($post_array['deleted']==0)
		 {
		  $forum->Redirect($Config->MAIN_DIR.'temat/pokaz/'.$post_array['topic_id'].'#'.$post_array['id']);
		 }
		else { echo(error_message('Ten post jest usunięty')); }
	   break;
	   case 2:
		echo(error_message('Nie podano ID'));
	   break;
	   case 3:
		echo(error_message('Niepoprawne ID'));
	   break;
	   case 4:
		echo(error_message('Taki post nie istnieje'));
	   break;
	   }
	 }
	else { echo(error_message('Nie masz uprawnień')); }
   break;
   }
 break;
 //-------
 case 'nowytemat': //formularze z nowym tematem
  if($PermHP->hasPermission('eif.topic.add'))
   {
	if($_POST)
	 {
	  $title = $_POST['title']; //tytuł tematu
	  $tresc = $_POST['tresc_post']; //treść z formularza
	  (bool)$canlocked = $PermHP->hasPermission('eif.topic.add.locked');
	  if(!$canlocked) $canlocked = false;
	  $add = $forum->addTopic($your_id,$title,$SU[1],$canlocked);
	 
	  switch($add[1])
	   {
	   case 1:
	    $forum->addPost($add[0],$tresc,1,$your_id);
	    echo('<header><h2>Temat został dodany</h2><a class="button" href="'.$Config->MAIN_DIR.'temat/pokaz/'.$add[0].'">Przekieruj</a></header>');
	   break;
	   case 2:
	    echo(error_message('Nie podano ID użytkownika'));
	   break;
	   case 3:
	    echo(error_message('Niepoprawne ID użytkownika'));
	   break;
	   case 4:
	    echo(error_message('Nie ma takiego użytkownika'));
	   break;
	   case 5:
	    echo(error_message('Nie podano tytułu'));
	   break;
	   case 6:
	    echo(error_message('Tytuł jest niepoprawny'));
	   break;
	   case 7:
	    echo(error_message('Nie podano ID działu'));
	   break;
	   case 8:
	    echo(error_message('Niepoprawne ID działu'));
	   break;
	   case 9:
	    echo(error_message('Nie ma takiego działu'));
	   break;
	   case 10:
	    echo(error_message('Dział jest zablokowany'));
	   break;
	   case 11:
	    echo(error_message('Dział jest usunięty'));
	   break;
	   case 12:
	    echo(error_message('Tytuł jest za długi'));
	   break;
	   case 13:
	    echo(error_message('Nie podano czy są uprawnienia'));
	   break;
	   case 14:
	    echo(error_message('Uprawnienia nie są poprawne'));
	   break;
	   }
	 }
	else
	 {
	  if(is_numeric($SU[1]))
       {
		echo("
		<form action='".$Config->MAIN_DIR."nowytemat/".$SU[1]."' method='post'>
		<input placeholder='Tytuł' type='text' name='title' class='longinput' maxlength='100' />
		<textarea cols='119' rows='10' name='tresc_post' class='textarea' ></textarea>
		<br><input type='submit' value='Napisz temat' class='longinput' />
		</form>
		");
	   }
	  else { echo(error_message("Identyfikator jest niepoprawny")); }
	 }
   }
  else { echo(error_message('Nie masz uprawnień')); }
 break;
 //-------
 case 'warnlist': //lista warnów jakiegoś usera
  if($PermHP->hasPermission('eif.warn.list.show'))
   {
	$user_id = $SU[1];
	$warnlist = $forum -> getWarnlist($user_id);
	
	switch($warnlist[1])
	 {
	 case 1:
	  $warnlist_array = $warnlist[0];
	  
	  echo('
	   <table class="table">
	    <tr>
		 <th>Przyczyna</th>
		 <th>Data</th>
		 <th>%</th>
		 <th>Przez</th>
		</tr>'
		.answer_message('Oglądasz listę ostrzeżeń użytkownika '.$forum->ShowUserNick($user_id,'',1))
	  );
		
	  if(count($warnlist_array) == 0)
	   {
		echo('
		 <tr>
		  <td>Ten użytkownik nie był jeszcze karany</td>
		  <td></td>
		  <td></td>
		 </tr>
		');
	   }
	
	  #pętla wyświetlająca
	  foreach($warnlist_array as $warn_array)
	   {
		#kolorowanie na zielono jeśli odjęto lub czerwono jeśli dodano ostrzeżenie
		if($warn_array['type']==0)
		 {
		  $kolor = '<font color="green">';
		 }
		else
		 {
		  $kolor = '<font color="red">';
		 }
	  
		  #wyświetlanie tabeli
		  echo('
		   <tr>
		    <td>'.$kolor.$warn_array['reason'].'</font></td>
			<td style="width: 140px;" >'.date('d.m.Y, H:i',$warn_array['time']).'</td>
			<td>'.$warn_array['procent'].'</td>
			<td style="width: 130px;" >'.$forum->ShowUserNick($warn_array['admin_id'],'',1).'</td>
		   </tr>
		  ');
		 }
		#html kończący tabele
		echo('</table>');
	 break;
	 case 2:
	  echo(error_message('Nie podano ID'));
	 break;
	 case 3:
	  echo(error_message('Niepoprawne ID'));
	 break;
	 case 4:
	  echo(error_message('Nie ma takiego użytkownika'));
	 break;
	 }
   }
  else { echo(error_message('Nie masz uprawnień')); }
 break;
 //--------
 case 'usercp': // PANEL USERA
  UserCP($SU[1],$_POST['avatar']);
 break;
 case 'zmien-avatar':
  if($PermHP->hasPermission('eif.avatar.change.mine'))
   {
	if(!empty($_POST))
	 {
	  $avatar = array();
	  $avatar['file'] = $_FILES['plik']['tmp_name'];  //plik
	  $avatar['name'] = $_FILES['plik']['name'];  //nazwa
	  $avatar['size'] = $_FILES['plik']['size']; //rozmiar
	  $av = $forum->setAvatar($your_id,$avatar,$PermHP->getRank('AvatarExts'));
	  
	  switch($av)
	   {
	   case 1:
	    echo(answer_message('Avatar został zmieniony'));
	   break;
	   case 2:
	    echo(error_message('Nie podano identyfikatora użytkownika'));
	   break;
	   case 3:
	    echo(error_message('Niepoprawny identyfikator użytkownika'));
	   break;
	   case 4:
	    echo(error_message('Nie istnieje taki użytkownik'));
	   break;
	   case 5:
	    echo(error_message('Dane pliku są niepoprawne'));
	   break;
	   case 6:
	    echo(error_message('Nie podano pliku'));
	   break;
	   case 7:
	    echo(error_message('Nie podano nazwy pliku'));
	   break;
	   case 8:
	    echo(error_message('Waga jest zerowa'));
	   break;
	   case 9:
	    echo(error_message('Nie podano dozwolonych rozszerzeń'));
	   break;
	   case 10:
	    echo(error_message('Rozszerzenie nie jest dozwolone'));
	   break;
	   case 11:
	    echo(error_message('Rozszerzenia są niepoprawne'));
	   break;
	   }
	 }
   }
  else { echo(error_message('Nie masz uprawnień')); }
 break;
 //--------
 case 'user': //podglądanie usera
  //ShowUserData($SU[1]);
  $user_id = $SU[1];
  $getUser = $forum->getUser($user_id);
  
  switch($getUser[1])
   {
   case 1:
	switch($SU[2])
	 {
	 case '':
	  if($PermHP->hasPermission('eif.user.show'))
	   {
		echo(
		'<header>
		 <h2>
		 &nbsp;
		 </h2>
		  <a href="'.MAIN_DIR.'user/'.$user_id.'/polecony" class="button">Polecony przez</a>
		</header>'
		);
		
		$user_array = $getUser[0];
		$rank = $PDO->query("SELECT * FROM eif_rangi WHERE `id`=".$user_array['rank']);
		$rank_array = $rank->fetch();
		$rank_name = $rank_array['name'];
  
		#zapytanie o pliki usera
		$files = $PDO->query("SELECT id FROM eif_upfiles WHERE `user_id`=$user_id AND `deleted`=0");
		
		#ilość postów usera
		$posty = $PDO->query("SELECT id FROM `eif_posty` WHERE `user_id`=$user_id AND `counted`=1 AND `deleted`=0");
		
		#przetwarzanie nicku usera
		$user_nick_parsed = $forum->ShowUserNick($user_id,'',0);
		
		if($user_array['gadu']>0)
		 {
		  $gaduTR = '<tr><td>Gadu-Gadu</td><td>'.$user_array['gadu'].'</td></tr>';
		 }
		 
		#wyświetlanie
		echo('
		Przeglądasz profil użytkownika '.$forum->ShowUserNick($user_id,'',0).'<br/>
		<table style="width: 500px;" class="table" >
		 <tr><th>Informacje</th><th style="width:200px;"></th></tr>
		 <tr><td>Nazwa</td><td>'.$forum->ShowUserNick($user_id,'',0).'</td></tr>
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
		if($PermHP->hasPermission('eif.user.warn'))
		 {
		  echo("<tr><td><a href='".MAIN_DIR."uzytkownik/warnuj/$user_id'>Warnuj</a></td><td></td></tr>");
		 }
		 
		echo('</table>');
	   }
	  else { echo(error_message('Nie masz uprawnień!')); }
	 break;
	 case 'polecony':
	  $polecili = $PDO->query("SELECT `id`,`rank` FROM `eif_users` WHERE `polec`=$user_id");
	  
	  if($polecili->rowCount()==0)
	   {
		$brak_polecaczy = (file_get_contents(dirname(__FILE__).'/css/'.$Config->CSS_TEMPLATE.'/template/user-polecony-nousers.php'));
	   }
	  
	  echo(
	   '<table class="table">
	     <tr>
		  <th style="width:50%;">Użytkownik</th>
		  <th>Ranga</th>
	     </tr>
		 '.$brak_polecaczy
	  );
	  
	  foreach($polecili->fetchAll() as $polecacz)
	   {
		$rank = $PDO->query("SELECT `name` FROM `eif_rangi` WHERE `id`=".$polecacz['rank']);
		$rank_array = $rank->fetch();
		echo(
		 '<tr>
		   <td>'.$forum->ShowUserNick($polecacz['id'],'',0).'</td>
		   <td>'.$rank_array['name'].'</td>
		  </tr>'
		);
	   }
	   
	  echo('</table>');
	 break;
	 }
   break;
   case 2:
	echo(error_message('Nie podano ID użytkownika'));
   break;
   case 3:
	echo(error_message('Niepoprawne ID użytkownika'));
   break;
   case 4:
	echo(error_message('Taki użytkownik nie istnieje'));
   break;
   }
 break;
 //--------
 break;
 //--------
 case 'lastpost': //ostatnie posty
  $lastpost = $forum -> getLastpost();
  
  switch($lastpost[1])
   {
   case 1:
	#nagłówek
	echo("<hgroup><h2>10 ostatnich postów</h2></hgroup><ul class='item' >");
  
	#pętla wyświetlająca
	foreach($lastpost[0] as $lastpost_array)
	 {
      #zmienne
      $tresc = $lastpost_array['tresc'];
      $user_id = $lastpost_array['user_id'];
      $temat = $lastpost_array['tytul'];
      $temat_id = $lastpost_array['topic_id'];
	  $post_id = $lastpost_array['id'];
	
	  $tresc = substr($tresc,0,100); //skracanie treści do 100 znaków
	  $bb->parse($tresc, false); //bbcode
	  $tresc = $bb->getHtml(); //bbcode
	
	  #wyświetlanie
      echo('
	   <li>
		<aside>
		 '.$forum->ShowUserNick($user_id,'',1).'
		</aside>
		<a href="'.$Config->MAIN_DIR.'post/s/'.$post_id.'" style="font-size:12px;" >Przejdź do tematu</a><br>
		'.$tresc.'
	   </li>');
	 }
	echo('</ul>');
   break;
   case 2:
    echo(error_message('Limit nie jest liczbą'));
   break;
   case 3:
    echo(error_message('Zerowy lub ujemny limit'));
   break;
   case 4:
    echo(error_message('Nie ma żadnych postów'));
   break;
   }
 break;
 //--------
 case 'file': //pokazywanie pliku
  $file = $forum -> getFile($SU[1]);
  switch($file[1])
   {
   case 1:
	$file_array = $file[0];
	$user = $forum->getUser($file_array['user_id']);
	$user_array = $user[1];
		
	if($file_array['lock']==0)
	 {
	  if($file_array['deleted']==0)
	   {
		if($PermHP->hasPermission('eif.file.get'))
		 {
		  #generowanie linka
		  $direct_link = $Config->MAIN_DIR.'files/'.$file_array['user_id'].'/'.$file_array['name'];
		  $file_link = $Config->MAIN_DIR.'file/'.$file_array['id'];
		  
		  #kolor nicku autora pliku
		  $user_nick_parsed = $forum->ShowUserNick($file_array['user_id']);
		  
		  #zapytanie o rangę usera
		  
		  $rank_filewait = $rank_array['filewait'];
		  
		  if(hasPermission($your_id,'filefast',1))
		   {
			$filefast = '<a href="'.$direct_link.'" target="_blank"><img src="'.$Config->MAIN_DIR.'icon/icon_next.png" width=24 height=24 /></a>';
		   }
		  
		  #pokazuj zalogujsie
		  if($your_id==0)
		   {
		    $sekundy = 10; 
		    $zalogujsie= "<div id='zalogujsie' style='border: 1px #000 dotted;width:350px;height:30px;text-align:center;margin:0 auto;' >Nie chcesz czekać? <a href='".$Config->MAIN_DIR."login'>zaloguj się</a> lub <a href='".$Config->MAIN_DIR."rejestracja'>zarejestruj</a></div>";
		   }
		  $sekundy = $rank_filewait; // ustalanie sekund
		   
		  #przekierowanie
		  echo("
		   <h2 style='text-align: center;'>Pobieranie pliku</h2>
		   <br/><br/>Żądasz pliku: <strong>".$file_array['name']."</strong>. 
		   Został on wrzucony przez użytkownika ".$user_nick_parsed."<br/><br/>
		   Link do pliku: <input type='text' style='width: 500px;' value='".$file_link."' />
		   $filefast
		   
		   <br/><br/><br/>
		   <div id='timer'>
		    <div style='border: 1px #000 dotted;width:350px;height:70px;text-align:center;margin:0 auto;'>
		     <script>StartDownloadTimer('$sekundy');</script>
		     <strong style='font-size: 20px;'>Link do pliku pojawi się za:</strong><br/><br/>
		     <span id='download_timer' style='color:red;font-size:15px;'>$sekundy</span>
		    </div>
			$zalogujsie
		   </div>
		   
		   <div id='adrespliku'>
		    <strong style='font-size:17px;'>Adres pliku (Do pobrania)</strong><br/>
		    <input type='text' style='width:500px;text-align:center;' value='".$direct_link."' />
		   </div>
		  ");
		 }
	   }
	 }
   break;
   case 2:
	echo(error_message('Nie podano ID'));
   break;
   case 3:
	echo(error_message('Niepoprawne ID'));
   break;
   case 4:
	echo(error_message('Nie ma takiego pliku'));
   break;
  }
 break;
 //--------
 case 'regulamin': //regulamin forum
  echo "
   <h2>Regulamin</h2><br><br>
   1. Forum
    <ul style='margin-left: 30px;'>
	 <li>Posty na forum muszą mieć jakiś sens i przesłanie</li>
	 <li>Każde wypowiedzenie ma być poprawne stylistycznie oraz ortograficznie</li>
	 <li>Zakazane jest wyzywanie użytkowników</li>
	 <li>Post naruszający regulamin należy zgłosić</li>
	 <li>Administrator ma prawo usunąć post lub zbanować użytkownika bez podania przyczyny</li>
     <li>Użytkownik ma obowiązek zgłosić błąd w systemie</li>
     <li>Po osiągnięciu 100% ostrzeżeń użytkownik zostaje zbanowany do odwołania</li>
     <li>Zabrania się posiadania wielu kont, takowe konta będą od razu banowane.</li>
     <li>Post musi zawierać przynajmniej dwa zdania.</li>
	 <li>Zakazane jest edytowanie notatek dodanych przez administrację</li>
	 <li>Administrator zastrzega sobie prawo do treści umieszczonych na forum przez autora, które nie są oznaczone prawami autorskimi</li>
	 <li>Odkopywanie tematów sprzed więcej niż miesiąc karane jest 10% warna</li>
	</ul>
				<br><br>
   2. Uploader
    <ul style='margin-left: 30px;'>
     <li>Każdy użytkownik ma określoną ilość miejsca która jest stała dla danej grupy.</li>
	 <li>Zabrania się jakiegokolwiek kombinowania w celu uszkodzenia systemu</li>
	 <li>Wszelkie treści pornograficzne i rasistowskie będą usuwane</li>
	 <li>Niedozwolone jest zamieszczanie materiałów objętych prawami autorskimi</li>
	 <li>Każdy plik może zostać skasowany przez administratora bez podania przyczyny</li>
	 <li>Pliki są własnością administatora systemu</li>
	</ul>
				<br><br>
   3. Shoutbox
    <ul style='margin-left: 30px;'>
     <li>Nie wolno wysyłać bez sensownych shoutów.</li>
     <li>Obrażanie użytkowników będzie karane</li>
     <li>Całkowicie zabronione jest używanie wulgaryzmów</li>
	</ul>
   ";
 break;
 case 'upload':
  $user_id = $SU[1];
  
  if(!isset($user_id))
   {
	if($your_id>0)
	 {
	  $user_id = $your_id;
	 }
	else { echo(error_message('Zaloguj się')); }
   }
  
  if($user_id !== $your_id)
   {
	echo('Przeglądasz folder użytkownika: '.$forum->showUserNick($user_id,'',1));
   }
  
  if($PermHP->hasPermission('eif.file.add') && $user_id==$your_id)
   {
	echo('
	 <div class="fileedit_cont" style="width: 100%">
	  <b>Wysyłanie nowego pliku</b>
	  <form enctype="multipart/form-data" action="'.$Config->MAIN_DIR.'dodajplik" method="POST"> 
	   <div class="photo">
		<img class="photo-preview" src="'.$Config->MAIN_DIR.'icon/dodajplik.png" />
		<input name="plik" type="file" id="plik" /> 
	   </div>
		<input type="hidden" name="MAX_FILE_SIZE" value="99999999999999999999999999999999999999999999999999999999999999999" /> 
		<input type="submit" class="submit" value="Wyślij plik" /> 
	   </form>
	  </div>
	');
   }
   
  if(($PermHP->hasPermission('eif.file.getlist.mine') && $user_id==$your_id) or $PermHP->hasPermission('eif.file.getlist.other'))
   {
	$Upload = $forum -> getUpload($user_id,100);
	
	switch($Upload[1])
	 {
	 case 1:
	  echo('<table class="table" id="upload">
	  <tr>
	   <th style="width: 300px;">Plik</th>
	   <th>Ocena</th>
	   <th>Akcje</th>
	   <th style="width: 130px;">Data dodania</th></tr>');  
		
	  #ustawianie uprawnień
	  if($PermHP->hasPermission('eif.file.edit.other') or ($PermHP->hasPermission('eif.file.edit.mine') && $user_id==$your_id))
	   {
		$uperm['edit'] = true;
	   }
	  else { $uperm['edit'] = false; }
	   
	  if($PermHP->hasPermission('eif.file.delete.other') or ($PermHP->hasPermission('eif.file.delete.mine') && $user_id==$your_id))
	   {
		$uperm['delete'] = true;
	   }
	  else { $uperm['delete'] = false; }
		
	  if($PermHP->hasPermission('eif.file.download.fast'))
	   {
		$uperm['fast'] = true;
	   }
	  else { $uperm['fast'] = false; }
		
	  foreach($Upload[0] as $files)
	   {
	    $file_link = $Config->MAIN_DIR.'file/'.$files['id'];
		$direct_link = $Config->MAIN_DIR.'files/'.$user_id.'/'.$files['name'];
		
		#generowanie gwiazdek
		$rate_stars = $forum->ShowRating($files['id']);
		$star = array();
		$akcjeString = '';		
		$akcje = array();
		
		#akcja edycji
		if($uperm['edit']==true)
		 {
		  $akcje[] = "
		   <a href='".$Config->MAIN_DIR."fileedit/".$files['id']."'>
		    <img src='".$Config->MAIN_DIR."icon/icon_edit.png' class='fileakcjeicon'  alt='Edytuj plik'>
		   </a>";
		 }
		 
		if($uperm['delete']==true)
		 {
		  $akcje[] = "
		   <a onclick='javascript:delFile(\"".$files['id']."\")'>
		    <img src='".$Config->MAIN_DIR."icon/icon_remove.png' class='fileakcjeicon'  alt='Usuń plik'>
		   </a>";
		 }
		  
		if($uperm['fast']==true)
		 {
		  $akcje[] = '
		   <a href="'.$direct_link.'" target="_blank">
		    <img src="'.$Config->MAIN_DIR.'icon/icon_next.png" class="fileakcjeicon"  alt="Odnośnik bezpośredni">
		   </a>';
		 }
				
		foreach($akcje as $akk)
		 {
		  $akcjeString .= $akk;
		 }
				
		echo('
		 <tr id="fileid_'.$files['id'].'">
		  <td><a href="'.$file_link.'">'.$files['name'].'</a></td>
		  <td class="ratestars">'.$rate_stars.'</td>
		  <td>'.$akcjeString.'</td>
		  <td>'.date('d.m.Y, H:i',$files['time']).'</td>
		 </tr>');
	   }
	  echo('</table>');
	 break;
	 case 2:
	  echo(error_message('Nie podano ID użytkownika'));
	 break;
	 case 3:
	  echo(error_message('ID jest niepoprawne'));
	 break;
	 case 4:
	  echo(error_message('Nie ma takiego użytkownika'));
	 break;
	 case 5:
	  echo(error_message('Nie ma żadnych plików'));
	 break;
	 case 6:
	  echo(error_message('Niepoprawny limit'));
	 break;
	 }
   }
  else { echo(error_message('Nie masz uprawnień do przeglądania listy plików!')); }
 break;
 case 'killcount':
  switch($SU[1])
   {
   case 'droncraft':
	$PDO_KillCount = new PDO('mysql:host=sql.mwcezary.nazwa.pl;port=3307;dbname=mwcezary_4','mwcezary_4','z0WF4dZW2ozx9IFRgjSvV1uOHD9TKOXa',array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
    $PDO_KillCount->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
   break;
   }
   
  if($PDO_KillCount)
   {
	include(DIR_NAME.'/class.KillCount.php');
	$KC = new KillCount($PDO_KillCount);
	$top = $KC->getTop(25);
	
	switch($top[1])
	 {
	 case 1:
	  echo(answer_message('Oglądasz top 25 zabójstw'));
	  
	  echo('
	  <table class="table" style="width:60%;margin:0 auto;"> 
	   <tr>
	    <th style="width:25px;">Lp.</th>
	    <th>Nick</th>
	    <th style="width:20%;">Zabicia</th>
	    <th style="width:20%;">Śmierci</th>
	    <th style="width:20%;"">Średnia</th>
	   </tr>
	  ');
	  
	  $lp = 0;
	  foreach($top[0] as $tA)
	   {
	    $kdr = 1;
		$lp++;
		if($tA['deaths'] > 0)
		 {
		  $kdr = round($tA['kills']/$tA['deaths'],1);
		 }
		else
		 {
		  $kdr = $tA['kills'];
		 }
	  
		echo('<tr><td>'.$lp.'</td><td>'.$tA['username'].'</td>'.'<td>'.$tA['kills'].'</td>'.'<td>'.$tA['deaths'].'</td>'.'<td>'.$kdr.'</td></tr>');
	   }
	  
	  echo('</table>');
	 break;
	 case 2:
	  echo(error_message('Nie podano limitu'));
	 break;
	 case 3:
	  echo(error_message('Niepoprawny limit'));
	 break;
	 case 4:
	  echo(error_message('Nie ma żadnych statystyk'));
	 break;
	 case 5:
	  echo(error_message('Źle podano zabicia/dedy'));
	 break;
	 }
   }
  else
   {
	echo(error_message('Podałeś niepoprawny serwer!'));
   }
 break;
 case 'h':
  echo(
  answer_message('Dostałeś bana na serwerze hardcore?').
  'Wyślij sms o treści <b>AP.FH</b> kosztem <b>2.46zł</b> na numer <b>72068</b><br/>
  Poczekaj chwilę i wyślij otrzymany kod administratorowi <font color=red>CTRL</font><br/><br/>
  Kontakt do CTRL:<br/>
  GaduGadu: 10779916<br/>
  Skype: evilinside99<br/>
  Email: marcin@cezary.pl<br/>
  Wiadomość na forum'
  );
 break;
 case 'sendactivation': //edytowanie posta
  if($your_id>0)
   {
	switch($SU[1])
	 {
	 case 'gadu':
	  if($PermHP->hasPermission('eif.gadugadu.activation.send'))
	   {
		$user = $PDO -> query("SELECT * FROM `".TBL."users` WHERE `id`=$your_id");
		$user_array = $user -> fetch();
	
		if($user_array['gadu']>0)
		 {
		  $gadu_message = "
		  Witaj ".$user_array['nick']."!\n
		  Zarejestrowałeś się na forum pod adresem: ".$Config->MAIN_DIR."\n
		  W celu aktywowania konta musisz kliknąć w poniższy link:\n
		  ".$Config->MAIN_DIR."uzytkownik/aktywuj/".$user_array['acticode'];
		  
		  $forum->ggSend($user_array['gadu'],$gadu_message);
		  
		  echo(answer_message('Link aktywacyjny został wysłany'));
		 }
		else { echo(error_message('Nie podałeś numeru GaduGadu')); }
	   }
	  else { echo(error_message('Nie masz uprawnień!')); }
     break;
	 case 'mail':
	  if($PermHP->hasPermission('eif.mail.activation.send'))
	   {
		$forum->sendMailActivation($your_id);
		echo(answer_message('Email z linkiem został wysłany'));
	   }
	 break;
 	 default:
	  echo(error_message('Błąd, nie podano metody lub jest ona niepoprawna!'));
	 break;
	 }
   } else { echo(error_message('Zaloguj się!')); }
 break;
 //--------
 case 'infovip': //info o vipie
  echo "
  Co możesz zyskać dzięki randze VIP?
  <ul style='margin-left: 30px;'>
   <li>Fioletowy nick</li>
   <li>Możliwość edytowania właściwości swoich plików (Nazwa, opis, widoczność itp.)</li>
   <li>Możliwość usunięcia swojego pliku z uploadera</li>
   <li>Przycisk FileFast*</li>
   <li>Tytuł pod nickiem (Np. przy postach)</li>
   <li>Większy limit rozmiaru pliku oraz folderu</li>
   <li>Większy limit wiadomości</li>
   <li>Większa szansa na pozytywne rozpatrzenie różnego typu podań</li>
   <li>Możliwość założenia swojego działu, jeśli będzie on miał sens</li>
   <li>Możliwość założenia działu swojego serwera, jeśli będzie on popularny</li>
  </ul>
  
  <br><br><br>
  Co należy robić aby zostać <b>VIP</b>em?
  
  <ul style='margin-left: 30px;'>
   <li>Wpłacić jakąś (nawet symboliczną) dotację</li>
   <li>Uczestniczyć w życiu na forum, udzielać się</li>
   <li>Mieć czystą kartotekę</li>
   <li>Pomagać w utrzymaniu porządku</li>
   <li>Być lubianym przez użytkowników</li>
   <li>Reklamować nas gdzie tylko się da</li>
   <li>Włożyć coś od siebie, jakąś swoją twórczość</li>
  </ul>
  
  <br><br><br>
  <b>* - FileFast:</b> Przycisk dzięki któremu możesz przenieść się do strony z plikiem za pomocą jednego kliknięcia w uploaderze (niebieska strzałka). Nie trzeba czekać paru sekund aby zobaczyć plik.
  ";
 break;
 //--------
 case 'usunplik': //usuwanie pliku
  if($PermHP->hasPermission('eif.file.delete.other') or ($PermHP->hasPermission('eif.file.delete.mine') && $your_id==$user_id))
   {
	switch($forum->delFile($SU[1]))
	 {
	 case 1:
      echo(answer_message('Plik został usunięty!'));
	 break;
	 case 2:
      echo(error_message('Nie podano ID'));
	 break;
	 case 3:
      echo(error_message('Niepoprawne ID'));
	 break;
	 case 4:
	  echo(error_message('Nie ma takiego pliku'));
	 break;
	 case 5:
      echo(error_message('Plik jest zablokowany'));
	 break;
	 case 6:
      echo(error_message('Plik jest usunięty'));
	 break;
	 case 7:
      echo(error_message('Plik fizycznie nie istnieje'));
	 break;
	 }
   }
  else { echo(error_message('Nie masz uprawnień!')); }
 break;
 //--------
 case 'usuntemat':
  $topic_id = $SU[1];
  $topic = $forum->getTopic($topic_id);
  switch($topic[1])
   {
   case 1:
	if($PermHP->hasPermission('eif.topic.delete.mine'))
	 {
      switch($forum -> delTopic($topic_id))
	   {
	   case 1:
	    echo(answer_message('Temat został usunięty!'));
	   break;
	   case 2:
	    echo(error_message('Nie podano ID'));
	   break;
	   case 3:
	    echo(error_message('Niepoprawne ID'));
	   break;
	   case 4:
	    echo(error_message('Taki temat nie istnieje'));
	   break;
	   case 5:
	    echo(error_message('Temat jest usunięty'));
	   break;
	   }
	 }
	else { echo(error_message('Nie masz uprawnień')); }
   break;
   case 2:
    echo(error_message('Nie podano ID'));
   break;
   case 3:
    echo(error_message('Niepoprawne ID'));
   break;
   case 4:
    echo(error_message('Nie ma takiego tematu'));
   break;
   }
 break;
 //--------
 case 'lista-userow':
  $users = $PDO->query("SELECT * FROM `".TBL."users` ORDER BY id ASC");
	  
  #tabela html
  echo('
    <input type="button" onclick="activateAdmin()" value="admintools">
	<table class="table">
	 <tr>
	  <th width=20>ID</th>
	  <th>Nazwa</th>
	  <th width=150>Ranga</th>
	  <th width=20>Posty</th>
	  <th>Pliki</th>
	  <th>Ostrzeżenia</th>
	 </tr>
  ');
	  
  if(!empty($users_rows))
   {
	echo('<tr><td></td><td>Nie ma żadnych użytkowników</td><td></td><td></td><td></td><td></td></tr>');
   }
   
  foreach($users->fetchAll() as $users_array)
   {
	$user_id = $users_array['id'];

	$rank_array = CheckUserRank($user_id);
	  
	$posty = $PDO->query("SELECT * FROM `".TBL."posty` WHERE `user_id`=$user_id AND `deleted`=0");
	$pliki = $PDO->query("SELECT * FROM `".TBL."upfiles` WHERE `user_id`=$user_id");
	
	#wyświetlanie
	echo('
	 <tr id="userid_'.$user_id.'">
	  <td>'.$user_id.'</td>
	  <td>'.$forum->ShowUserNick($users_array['id'],'',1).'</td>
	  <td>'.$rank_array['name'].'</td>
	  <td>'.$posty->rowCount().'</td>
	  <td>'.$pliki->rowCount().'<a href="'.$Config->MAIN_DIR.'upload/'.$user_id.'" style="margin-left: 20px;" >(Folder)</a></td>
	  <td><a href="'.$Config->MAIN_DIR.'warnlist/'.$user_id.'">'.$users_array['warn'].' %</a></td>
	 </tr>
	 <script>addUserToList('.$user_id.')</script>
	');
   }
	   
  echo('
   <script>finishAddingUsersIntoList()</script>
   </table>
  ');
 break;
 //--------
 case 'uzytkownik':
  switch($SU[1])
   {
   case 'aktywuj':
	$acticode = $SU[2];
	if($PermHP->hasPermission('eif.user.activate'))
	 {
	  if(!empty($acticode))
	   {
		$user = $PDO->query("SELECT `id` FROM `eif_users` WHERE `acticode`='$acticode'");
		$user_id = $user->fetchColumn(0);
		
		if($user->rowCount() == 1)
		 {
		  $user_rank = CheckUserRank($user_id);
	  
		  if($user_rank['id']==6)
		   {
			$activate_query = $PDO->exec("UPDATE `eif_users` SET `rank`=2, `yrank`='User' WHERE `id`=$user_id ") or die(mysql_error());
		
			echo(answer_message("Konto zostało pomyślnie aktywowane. Możesz się już zalogować."));
		   }
		  else { echo(error_message("Twoje konto nie jest <strong>nieaktywne</strong>.")); }
		 }
		else { echo(error_message("Konto z takim kodem nie istnieje.")); }
	   }
      else { echo(error_message("Nie podałeś kodu")); }
     }
    else { echo(error_message("Nie masz uprawnień")); }
   break;
   case 'edytuj':
    $user_id = $SU[2];
	
	if($PermHP->hasPermission('eif.user.edit'))
	 {
	  $User = $forum->getUser($user_id);
	  
	  switch($User[1])
	   {
	   case 1:
		if($_POST)
	     {
		  $user_nick = $_POST['user_nick']; //nick usera
		  $user_rank = $_POST['user_rank']; //ranga
		  $user_title = $_POST['user_title']; //tytuł usera
		  $user_email = $_POST['user_email']; //email usera
		  $user_gadugadu = $_POST['user_gadugadu']; //gg usera
		  $user_sex = $_POST['user_sex']; //płeć
		  $user_miasto = $_POST['user_miasto']; //miasto pochodzenia usera
		  $user_rankexpire = $_POST['user_rankexpire']; //czas do wygaśnięcia rangi
		  $user_afterexpire = $_POST['user_afterexpire']; //ranga po wygaśnięciu
		
		  $PDO->exec("UPDATE `".TBL."users` SET `nick`='$user_nick', `rank`=$user_rank `title`='$user_title', `email`='$user_email', `gadu`=$user_gadugadu, `sex`='$user_sex', `miasto`='$user_miasto' WHERE `id`=$user_id");
			
		  #wiadomość
		  echo('<header><h2>Właściwości użytkownika zostały zaktualizowane.</h2><a class="button" href="'.$Config->MAIN_DIR.'uzytkownik/edytuj/'.$user_id.'">Powrót</a></header>');
		 }
	    else
	     {
		  $user_array = $User[0];
		  
		  #zmienne
		  $user_nick = $user_array['nick'];
		  $user_rank = $user_array['rank'];
		  $user_visible = $user_array['visible'];
		  $user_title = $user_array['title'];
		  $user_email = $user_array['email'];
		  $user_gadugadu = $user_array['gadu'];
		  $user_sex = $user_array['sex'];
		  $user_miasto = $user_array['miasto'];
			
		  $ranks = $PDO->query("SELECT * FROM `eif_rangi` ORDER BY id DESC");
		
		  foreach($ranks->fetchAll() as $ranks_array)
		   {
			if($user_rank==$ranks_array['id'])
			 {
			  $rank_selected = 'selected=selected';
			 }
			else
			 {
			  $rank_selected = '';
			 }
			   
			$rank_options .= '<option value="'.$ranks_array['id'].'" '.$rank_selected.' >'.$ranks_array['name'].'</option>';
		   }
			 
		  //Ranga po wygaśnięciu
		  foreach($ranks->fetchAll() as $ranks_array)
		   {
			if($user_array['afterexpire']==$ranks_array['id'])
			 {
			  $rank_selected = 'selected=selected';
			 }
			else
			 {
			  $rank_selected = '';
			 }
			   
			$expire_rank_options .= '<option value="'.$ranks_array['id'].'" '.$rank_selected.' >'.$ranks_array['name'].'</option>';
		   }
			
		  #zaznaczanie płci
		  if($user_sex=='male')
		   {
			$sex_male='selected=selected';
		   }
		  else
		   {
			$sex_female='selected=selected'; //płeć
		   }
			
		  #pokazywanie formularzy
		  echo(
		   '
			<form action="'.$Config->MAIN_DIR.'uzytkownik/edytuj/'.$user_id.'" method="post">
			 <table class="table" style="width: 400px;">
			  <tr><td>Nick</td><td><input type="text" value="'.$user_nick.'" name="user_nick" /></td></tr>
			  <tr><td>Ranga</td><td><div class="select"><select name="user_rank" style="width: 256px">'.$rank_options.'<select></div></td></tr>
			  <tr><td>Tytuł</td><td><input type="text" value="'.$user_title.'" name="user_title" /></td></tr>
			  <tr><td>Adres email</td><td><input type="text" value="'.$user_email.'" name="user_email" /></td></tr>
			  <tr><td>Gadu-Gadu</td><td><input type="text" value="'.$user_gadugadu.'" name="user_gadugadu" /></td></tr>
			  <tr><td>Płeć</td><td><div class="select"><select name="user_sex"><option '.$sex_female.' value="female" >Kobieta</option><option '.$sex_male.' value="male" >Mężczyzna</option></select></div></td></tr>
			  <tr><td>Miasto pochodzenia</td><td><input type="text" value="'.$user_miasto.'" name="user_miasto" /></td></tr>
			  <tr><td>Ranga po wygaśnięciu</td><td><div class="select"><select name="user_afterexpire" style="width: 256px">'.$expire_rank_options.'<select></div></td></tr>
			  <tr><td><input type="submit" value="Zapisz"></td><td><input type="reset" value="Resetuj"></td></tr>
			  </table>
			</form>
		   ');
	     }
	   break;
	   case 2:
	    echo(error_message('Nie podano ID użytkownika'));
	   break;
	   case 3:
	    echo(error_message('Niepoprawne ID użytkownika'));
	   break;
	   case 4:
	    echo(error_message('Taki użytkownik nie istnieje'));
	   break;
	   }
	 }
	else { echo(error_message('Nie masz uprawnień')); }
   break;
   case 'warnuj':
	if($PermHP->hasPermission('eif.warn'))
	 {
	  if(!empty($_POST))
	   {
		switch($forum->addWarn($SU[2],$your_id,$your_ip,$_POST['reason'],$_POST['type'],$_POST['procent']))
		 {
		 case 1:
		  echo(answer_message('Użytkownik dostał ostrzeżenie!'));
		 break;
		 case 2:
		  echo(error_message('Nie podano ID użytkownika'));
	     break;
	     case 3:
		  echo(error_message('Niepoprawne ID użytkownika'));
	     break;
	     case 4:
		  echo(error_message('Nie istnieje taki użytkownik'));
	     break;
	     case 5:
	      echo(error_message('Nie podano ID admina'));
	     break;
	     case 6:
		  echo(error_message('Niepoprawne ID admina'));
	     break;
	     case 7:
		  echo(error_message('Nie istnieje taki administrator'));
	     break;
	     case 8:
		  echo(error_message('Nie podano powodu'));
	     break;
	     case 9:
		  echo(error_message('Powód zawiera niedozwolone znaki'));
	     break;
	     case 10:
	  	  echo(error_message('Niepoprawny typ'));
	     break;
	     case 11:
		  echo(error_message('Nie podano procentów'));
	     break;
	     case 12:
		  echo(error_message('Procenty nie są liczbą'));
	     break;
	     case 13:
		  echo(error_message('Nie podano adresu IP'));
	     break;
	     case 14:
		  echo(error_message('Niepoprawny adres IP'));
	     break;
	     }
	   }
	  else
	   {
	    echo('
	     <form action="" method="post">
	      Warnujesz użytkownika: '.$forum->ShowUserNick($SU[1]).'<br/>
		  <textarea class="fileedit_ta" name="reason" ></textarea><br/>
		  <input type="text" name="procent" placeholder="0%" /><br/>
		  <div class="select">
		   <select name="type">
			<option value="1">Dodaj</option>
			<option value="0">Odejmij</option>
		   </select>
		  </div><br/>
		  <input type="submit" value="Warnuj" />
	     </form>
	    ');
	   }
     }
    else { echo(error_message('Nie masz uprawnień')); }
   break;
   }
 break;
 case 'fileedit': //edytowanie pliku
  FileEdit($SU[1]);
 break;
 case '':
?>
    <header>
	 <h2>:)</h2>
	 <a class="button" style="margin-left: 5px;" href="<?php echo $Config->MAIN_DIR; ?>lastpost">Ostatnie posty</a>
	 <a class="button" style="margin-left: 5px;" href="<?php echo $Config->MAIN_DIR; ?>infovip">Ranga VIP</a>
	</header>
	<ul id="contextMenu">
	 <li><a id="go">Przejdź</a></li>
	 <li><a id="edit">Edytuj</a></li>
	 <li><a id="delete">Usuń</a></li>
	</ul>
<?php
if($your_array['rank']==6):
?>
	<header>
	 <h2>Wyślij link aktywacyjny wciskając przycisk</h2>
	 <a class='button' style='margin-left: 5px;' href='<?php echo $Config->MAIN_DIR; ?>sendactivation/gadu'>GaduGadu</a>
	 <a class='button' style='margin-left: 5px;' href='<?php echo $Config->MAIN_DIR; ?>sendactivation/mail'>E-Mail</a>
	</header>
<?php
endif;
  
  //Strona główna
  $kategorie = $PDO -> query("SELECT * FROM `".TBL."kategorie` ORDER BY id ASC");
  
  #pętla wyświetlająca kategorie
  foreach($kategorie -> fetchAll() as $kategorie_array)
   {
	#wyświetlanie nagłówka
	echo('
	<div class="itemhead hgroup" id="hgroup_'.$kategorie_array['id'].'" >
	 <h2>'.$kategorie_array['nazwa'].'</h2>
	 <div class="collapse minus" id="collapse_'.$kategorie_array['id'].'" onclick="collapse(\''.$kategorie_array['id'].'\',\'collapse_'.$kategorie_array['id'].'\',\'hgroup_'.$kategorie_array['id'].'\')" ></div>
	</div>
	<ul class="item" id="'.$kategorie_array['id'].'" >');
    
	#zapytanie o fora
	$dzial = $PDO -> query("SELECT * FROM `".TBL."dzial` WHERE `id_kategoria`=".$kategorie_array['id']." AND `deleted`=0 ORDER BY id ASC");
	$dzial_array = $dzial -> fetchAll();
	
	#pętla wyświetlająca fora
    foreach($dzial_array as $dzialA)
     {
	  #ustawienia
      $dzial_id = $dzialA['id'];
	  $dzialicon = $forum->getDzialIcon($dzialA['icon_id']);
	  $dzial_icon = '<img src="'.$dzialicon[0].'" class="dzial_icon"  alt="Ikonka działu">';
	  
	  if(!empty($dzialA['url']))
	   {
		#wyświetlanie
		echo('
		<li>
		 '.$dzial_icon.'
		 <a href="'.$dzialA['url'].'">'.$dzialA['nazwa'].'</a>
		 '.$dzialA['opis'].'
		</li>
		');
	   }
	  else
	   {
	    $tematy_query = $PDO -> query("SELECT `id` FROM `".TBL."tematy` WHERE `dzial_id`=$dzial_id AND `deleted`=0 ORDER BY `id` DESC");
		$tematy_rows = $tematy_query -> rowCount();
	
		#odmiana liczby tematów
		if($tematy_rows==1) { $fora_iletext='temat'; }
		if($tematy_rows==0) { $fora_iletext='brak tematów'; $tematy_rows=''; }
		if($tematy_rows>1 && $tematy_rows<5) { $fora_iletext='tematy'; }
		if($tematy_rows>4) { $fora_iletext='tematów'; }
	  
		echo('
		<li>
		 '.$dzial_icon.'
		 <aside> '.$tematy_rows.' '.$fora_iletext.'</aside>
		 <a href="'.$Config->MAIN_DIR.'dzial/pokaz/'.$dzial_id.'">'.$dzialA['nazwa'].'</a>
		 '.$dzialA['opis'].'
		</li>');
	   }
	 }
	#kończenie htmla
	echo('</ul>');
   }
  
  // SHOUTBOX
    echo('
	<div class="itemhead hgroup" id="hgroup_sb" >
	 <h2>Shoutbox</h2>
	 <div class="collapse minus" id="collapse_sb" onclick="collapse(\'sb\',\'collapse_sb\',\'hgroup_sb\')" ></div>
	</div>
	<ul class="item shoutbox" id="sb" >
	');
  
  if($PermHP->hasPermission('eif.shoutbox.write'))
   {
    echo('
	<li>
	 <input id="shoutboxinput" maxlength="200" onkeypress="{if (event.keyCode==13)AddNewShout()}" placeholder="Tutaj wpisz treść" type="text" />
	 <input type="button" onclick="AddNewShout();" id="sbsubmit" value="ok" />
	 </li>
	');
   }
   
  if($PermHP->hasPermission('eif.shoutbox.show'))
   {
    echo('
	 <li>
	 <div id="sberror"></div>
	 <div id="shoutbox">
	 <script>
	 AutoSbRefresh();
	 $("#shoutbox").tinyscrollbar();
	 </script>
	 </div>
	 </li>
	');
   }
  else
   {
	echo('<li><h3>Zaloguj się aby zobaczyć czat</h3></li>');
   }
   
  echo('</ul>');

  #statystyki
  echo("\n
  <div class='itemhead hgroup' id='hgroup_stats' >
   <h2>Statystyki</h2>
   <div class='collapse minus' id='collapse_stats' onclick='collapse(\"stats\",\"collapse_stats\",\"hgroup_stats\")' ></div>
  </div>
  <ul class='item stats' id='stats'>\n
  <li>\n");
  
  #zapytanie o userów
  $user = $PDO -> query("SELECT `id` FROM `".TBL."users` ORDER BY `id` DESC");
  $user_rows = $user -> rowCount();
  if($user_rows>0) $user_array = $user -> fetch();
  $user_id = $user_array['id']; //id najnowszego usera
  
  #zapytanie o tematy i posty
  $temat = $PDO -> query("SELECT `id` FROM `".TBL."tematy`");
  $post = $PDO -> query("SELECT `id` FROM `".TBL."posty`");
  
  $postmost = $PDO->query("SELECT COUNT(`id`) AS `user_id`, `id` FROM `eif_posty` GROUP BY `user_id` ORDER BY `id` DESC");
  $postmost_user = $postmost->fetch();
  //print_r($postmost_user);
  //<br/>Najwięcej postów napisał użytkownik: '.$forum->ShowUserNick($postmost_user['user_id'],'',1).'
	
  
  //wyświetlanie
  echo('
    Mamy już <b>'.$user_rows.'</b> użytkowników, a najnowszy to:&nbsp;&nbsp;'.$forum->ShowUserNick($user_array['id'],'stats_nick',1).'
    <br>Nasi użytkownicy napisali już <b>'.$post->rowCount().'</b> postów w <b>'.$temat->rowCount().'</b> tematach.<br><br><br>
    <div id="onlinestats">
     <script>showOnlineStats();</script>
	</div>
   </li>
  </ul>');
 break;
 //------
 default:
  echo('404 Strona nie została znaleziona');
 break;
 }

if(!$PermHP->hasPermission('eif.admincp.button'))
 {
  $admincp_display = 'none';
 }

?>
  </section>
  <footer>
   Copyright &copy; by <a href="<?php echo $Config->MAIN_DIR; ?>"><?php echo $Config->SITE_NAME; ?></a> 2011-2013
   <a href="javascript:scrollToTop()" class="scroll" style="border-bottom: 0px !important;" ></a>
   <span><a href="<?php echo $Config->MAIN_DIR; ?>admin" style="margin-right: 15px;display:<?php echo $admincp_display; ?>;">Panel Administratora</a></span>
   &nbsp;&nbsp;&nbsp;&nbsp;Powered by <i><a href="http://marcin.co/">Joshorum</a></i>
  </footer>
 </body>
</html>