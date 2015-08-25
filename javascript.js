/* Javascript.js */
MAIN_DIR = 'http://staraprochownia.pl/';

/* FORUM */

/* FACEBOOK */
(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/pl_PL/all.js#xfbml=1";
  fjs.parentNode.insertBefore(js, fjs);
  }(document, 'script', 'facebook-jssdk'));


function CaptchaCheck(val)
 {
  $.get(MAIN_DIR+'miniscript.php?a=captchacheck&val='+val, function(data) {
  if(data=='true') $('#captchacheck').html('<font color=green>Kod poprawny</font>');
  else $('#captchacheck').html('<font color=red>Kod <b>nie</b>poprawny</font>');
  });
 }

function delFile(id)
 {
  $.get(MAIN_DIR+'miniscript.php?a=delfile&id='+id, function(data) {
	if(data=='1') {
		//$('#fileid_'+id).hide();
		$('#fileid_'+id).slideUp('slow');
		//$('#fileid_'+id).fadeOut('slow');
		//$('#fileid_'+id).hide('blind', { direction: "vertical" },300);
	}
  });
 }
 
function AutoSbRefresh()
 {
  ShoutboxRefresh();
  timeout();
 }

/* Shoutbox */
function ShoutboxRefresh()
 {
  $("#shoutbox").load(MAIN_DIR+"miniscript.php?a=sbrefresh");
  showOnlineStats();
  $("#refresh").show('blind', { direction: "vertical" },300);
 }
 
function showOnlineStats()
 {
  $("#onlinestats").load(MAIN_DIR+"miniscript.php?a=onlinestats");
 }
 
//chowanie ładowania
function HideRefresh()
 {
  $("#refresh").hide('blind', { direction: "vertical" },300);
 }
 
function timeout()
 {
  mytime=setTimeout('AutoSbRefresh()',10000);
  mytime=setTimeout('HideRefresh()',1000*3);
 }
 
//powrót do góry strony
function scrollToTop()
 {
  $('html, body').animate({scrollTop:0}, 'slow');
 }

//rozwijanie i zwijanie
function collapse(a,b,hgroup_id) {
	var kategoria = $("#"+a);
	var hgroup = $("#"+hgroup_id);
  
	if(!kategoria) return true;
  
	if(kategoria.css("display")=="none") {
		kategoria.toggle('blind', { direction: "vertical" },1000);
		$("#"+b).toggleClass("minus");
		$("#"+b).toggleClass("plus");
		hgroup.css("borderRadius","5px 5px 0 0");
		cookie_name = "collapse_"+a+"_"+b+"_"+hgroup;
		$.cookie(cookie_name, 1);
	}
	else {
		kategoria.toggle('blind', { direction: "vertical" },1000);
		$("#"+b).toggleClass("minus");
		$("#"+b).toggleClass("plus");
		setTimeout(function(){hgroup.css("borderRadius","5px");},1000);
	}
	return true;
}

//Pokazywanie OlBox
function OlBoxShow(id)
 {
  $(id).show('slow');
 }

//dodawanie shouta
function AddNewShout() {
	sbinput = $("#shoutboxinput");
	sberror = $('#sberror');
	$.post(MAIN_DIR+"miniscript.php?a=shout", { tresc: sbinput.val() },function(data) {
		if(data=='error1') {
			sberror.html('Poczekaj 5 sekund przed wysłaniem posta'); sberror.show();
		}
	});
  
  sbinput.val('');
  ShoutboxRefresh(); //odświeżanie shoutboxa
 }

function login()
 {
  vnick = document.getElementById("nick").value;
  vpass = document.getElementById("pass").value;
  $.post(MAIN_DIR+"miniscript.php?a=userlogin", { nick: vnick, password: vpass, ajax: 1 },function(data)
   {
	window.location="";
   });
 }
 
function SmoothGet(id,url)
 {
  input = $('#'+id);
  window.location=MAIN_DIR+url+'/'+input.val();
 }
function SendNewsletter()
 {
  $("#SendNewsletter").load(MAIN_DIR+"miniscript.php?a=SendNewsletter");
 }
 

function EmailValidate(email)
 {
  $("#emailvalid").load(MAIN_DIR+"miniscript.php?a=emailvalidate&email="+email);
 }

//sprawdzanie czy hasło pasuje
function PasswordMatch() {
	pass1 = $("#pass").val();
	pass2 = $("#passrepeat").val();
  
	if(pass1==pass2) $("#passmatch").html("<font color=green>Hasła są zgodne</font>"); else $("#passmatch").html("<font color=red>Hasła nie są zgodne</font>");
}
 
//pobieranie pliku
function file_download(file)
 {
  $("#download").load(MAIN_DIR+"miniscript.php?a=download&file="+file);
 }
 

//sprawdzanie czy nick wolny
function IsFreeNick(nick,b)
 {
  //b=0 - nick
  //b=1 - polecanie
  $.get(MAIN_DIR+"miniscript.php?a=isfreenick&nick="+nick, function(data)
   {
    if(b==0)
	 {
	  if(data==1) $('#nickfree').html('<font color=green><small>Nick jest dostępny.</small></font>');
	  else if(data==0) $('#nickfree').html('<font color=red><small>Nick jest niestety zajęty.</small></font>');
	 }
    if(b==1)
	 {
	  if(data==1) $('#poleccheck').html('<font color=red><small>Użytkownik nie istnieje!</small></font>');
	  else if(data==0) $('#poleccheck').html('<font color=green><small>Użytkownik poprawny</small></font>');
	 }
   });
 }
 
function pasStr() {
	password = $("#pass").val();
	var desc = new Array();
	desc[0] = "Bardzo słabe";
	desc[1] = "Weak";
	desc[2] = "Better";
	desc[3] = "Medium";
	desc[4] = "Strong";
	desc[5] = "Strongest";

  var score   = 0;

  //if password bigger than 6 give 1 point
  if (password.length > 6) score++;
  
  //if password has both lower and uppercase characters give 1 point	
  if ( ( password.match(/[a-z]/) ) && ( password.match(/[A-Z]/) ) ) score++;

	//if password has at least one number give 1 point
	if (password.match(/\d+/)) score++;

	//if password has at least one special caracther give 1 point
	if ( password.match(/.[!,@,#,$,%,^,&,*,?,_,~,-,(,)]/) )	score++;

	//if password bigger than 12 give another 1 point
	if (password.length > 12) score++;

	$("#passtr_desc").html(desc[score]);
	document.getElementById("passtr").className = "str" + score;
 }

//Dodawanie tagu
function wrapText(elementID, openTag, closeTag) {
    var textArea = $('#' + elementID);
    var len = textArea.val().length;
    var start = textArea[0].selectionStart;
    var end = textArea[0].selectionEnd;
    var selectedText = textArea.val().substring(start, end);
    var replacement = openTag + selectedText + closeTag;
    textArea.val(textArea.val().substring(0, start) + replacement + textArea.val().substring(end, len));
}
 
//dialog od jquery ui
function dialog(str,tit)
 {
  dial = document.getElementById("dialog"); //przypisanie
  dial.title = tit; //zmiana tytułu
  $("#dialog").html(str); //wpisywanie wartości
  $("#dialog").dialog();
 }
 
//wylogowywanie
function LogOut()
 {
  $("#dialog").load(MAIN_DIR+"miniscript.php?a=logout");
  dialog('Zostałeś wylogowany.<meta http-equiv="refresh" content="2; url='+MAIN_DIR+'">','Wylogowywanie');
 }
 
function FileVote(file_id,rate) {
	$.get(MAIN_DIR+"miniscript.php?a=filevote&file_id="+file_id+"&rate="+rate, function(returnstr) {
		switch(returnstr) {
			case 'ERROR_1':
				dialogstr="Aby oddać głos musisz się zalogować.";
			break;
			case 'ERROR_2':
				dialogstr="Nie podano identyfikatora pliku!";
			break;
			case 'ERROR_3':
				dialogstr="Identyfikator pliku jest niepoprawny!";
			break;
			case 'ERROR_4':
				dialogstr="Nie podano oceny!";
			break;
			case 'ERROR_5':
				dialogstr="Ocena jest niepoprawna!";
			break;
			case 'ERROR_6':
				dialogstr="Już głosowałeś na ten plik!";
			break;
			case 'ERROR_7':
				dialogstr="Nie masz uprawnień!";
			break;
			case 'NO_ERROR':
				srednia = $.load(MAIN_DIR+"miniscript.php?a=getfilerate&id="+file_id);
				alert(srednia);
				for(i=1;$i<=srednia;i++) {
					alert(i);
					if($("#ratestar_"+file_id+"_"+i).hasClass('grey')) {
						$("#ratestar_"+file_id+"_"+i).removeClass('grey');
						$("#ratestar_"+file_id+"_"+i).addClass('gold');
					}
				}
				dialogstr="Zagłosowałeś pomyślnie, dałeś "+rate+" gwiazdek.";
			break;
			default:
				dialogstr="Niewyjaśniony błąd. "+returnstr;
			break;
		}
	});
	dialog(dialogstr,"Głosowanie"); //dialog
}
 
function MinusDownloadTimer(DownloadTimer_time)
 {
  if(DownloadTimer_time>0)
   {
	setTimeout(function(){MinusDownloadTimer(DownloadTimer_time)},1000);
	DownloadTimer_time--;
	$("#download_timer").html(DownloadTimer_time);
   }
  else
   {
	$("#adrespliku").toggle();
	$("#timer").toggle("fast");
   }
 }

function StartDownloadTimer(sec)
 {
  var DownloadTimer_time = sec;
  if(sec>0)
   {
	setTimeout(function(){MinusDownloadTimer(DownloadTimer_time)},1000);
   }
  else
   {
	setTimeout(function(){MinusDownloadTimer(DownloadTimer_time)},0);
   }
 }

/* ZNAJOMEK */
/*
Designed for EvilFox forum
http://forum.cezary.pl
autor: toaspzoo
release date: 7-07-2012

- At first create button with onclick="activateAdmin" attribute
- Second step is to add CLASS attribute to your html tag within is content to change: class="userid_YOUR_USER_ID"
- Thirdly you'll have to load users into function using addUserToList(user_id)

- You can edit added text above below "add content" signed comment
 PLEASE ADD!!!!  finishAddingUsersIntoList() function in the end of executing script 

*/



var userlist;
var i=0;
var activated=0;

function addUserToList(uid) {
	userlist+=","+uid;
}
	
	
function finishAddingUsersIntoList() {
	userlist = userlist.split(',');
}
	
	
function activateAdmin() {
	if(activated==0) {
		for(i=1;i<userlist.length;i++) {
			uval=document.getElementById('userid_'+userlist[i]).innerHTML;
			$('#userid_'+userlist[i]).html(uval+'<td class="adminactions" style="display:none;"><a href="/uzytkownik/edytuj/'+userlist[i]+'">edycja</a></td>');	
		}
			
		$('td.adminactions').fadeIn(1000);
		activated=1;	
	}
	else {
		$('td.adminactions').fadeOut(1000);
		$('td.adminactions').remove();
		activated=0;
	}
}
var once=0;