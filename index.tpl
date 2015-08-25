<!DOCTYPE html>
<html>
 <head>
  <title>{$title}</title>
  <link rel="stylesheet" href="{$MAIN_DIR}style.css" />
  <script src="{$MAIN_DIR}jscripts/quicklogin.js"></script>
  <script src="{$MAIN_DIR}javascript.js"></script>
  <script src="{$MAIN_DIR}jscripts/datehour.js"></script>
  <script src="{$MAIN_DIR}jquery.js"></script>
  <script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.17/jquery-ui.min.js"></script>
  <link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.17/themes/base/jquery-ui.css" media="all" />
  <script src="{$MAIN_DIR}jscripts/jquery.animate-colors-min.js"></script>
  <script src="{$MAIN_DIR}jscripts/jquery.cookie.js"></script>
  <script src="{$MAIN_DIR}jscripts/light.js" ></script>
  <script src="http://znajomek.unixstorm.org/EvilFox/admin.js"></script>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
 </head>
 <body>
  <div id='dialog'></div>
  <div id='refresh'><img src='{$MAIN_DIR}icon/loader.gif' /></div>
  <header>
   <div>
    <h1>
     <a href="{$MAIN_DIR}"/>{$logo_link}</a>
	</h1>
	{if $msgcount_show==1}
	<a href='{$MAIN_DIR}wiadomosci' class='pm {$pm_highlight}'>{$pm_newmessagescount}</a>
	{/if}
   </div>
</header>
<nav>
 <div>
   <span id='clock'><script>displayTime();</script></span>
    {if $welcome_show==1}
	<ul style="margin-top: 0px !important;" >
	 <li>Witaj <a href='{$MAIN_DIR}user/{$welcome_userid}' style='{$welcome_userstyle}' class='nav_nick' >{$welcome_nick}</a></li>
	 <li><a href="{$MAIN_DIR}usercp" class="nav_link">Panel</a></li>
	 <li><a href="{$MAIN_DIR}upload" class="nav_link">Folder</a></li>
	 <li><a href="#" onclick="LogOut();" class="nav_link">Wyloguj</a></li> 
    </ul>
	{/if}
   </div> 
  </nav>
  <section style="width: 980px;" >
   {if $answer==1}
    <div id='answer'>
	 {$answer_string}
	</div>
   {/if}
   
   {if $error==1}
    <div class='error'>
	 {$error_string}
	</div>
   {/if}
  </section>
  <footer>
   Copyright &copy; by <a href="{$MAIN_DIR}info">{$webpage_name}</a> 2011-2012
   <a href="javascript:scrollToTop()" class="scroll" style="border-bottom: 0px !important;" ></a>
   
   {if $adminlink_show==1}
    <span>
	 <a href="{$MAIN_DIR}admin" style="margin-right: 15px;">
	  Panel Administratora
	 </a>
	</span>
   {/if}
   
   &nbsp;&nbsp;&nbsp;&nbsp;
   Powered by <i><a href="http://marcin.co">Joshorum</a></i>
  </footer>
 </head>
</html>