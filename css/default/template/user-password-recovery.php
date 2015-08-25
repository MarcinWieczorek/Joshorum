<script>
$(window).ready(function(){
	$("#codeform").hide();
	
	$("#recovery_dalej").click(function(){
		$.get(MAIN_DIR+"miniscript.php?a=passrecoveryemailsend&nick="+$("#recovery_nick").val(),function(data){
			if(data == "no-user") {
				$("div.fileedit_cont").addClass("redborder");
				$("div.fileedit_cont").effect("shake",{ times: 3 },20);
			}
			else if(data == "success") {
				$("div.fileedit_cont").removeClass("redborder");
				$("#codeform").show('blind',{ direction: "vertical" });
				$("#first").hide('blind',{ direction: "vertical" });
			}
		});
	});
});
</script>
<header>
 <h2>
  Resetowanie hasła
 </h2>
</header>
<div class="fileedit_cont">
 <div id="first">
  <input type="text" id="recovery_nick" placeholder="Wpisz nazwę użytkownika" />
  <input type="button" id="recovery_dalej" value="Dalej" />
 </div>
<div id="codeform">
 Dostałeś emaila z kodem, użyj go jako hasło aby się zalogować
</div>
</div>