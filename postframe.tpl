<article id="$post_id">
 <div class="author">
  {$UserNick}
  <br/>
  {if $UserTitle_display == 1}
   <small>
	<b>
	 {$UserTitle}
    </b>
   </small>
   <br/>
  {/if}
  
  {if $UserAvatar_display == 1}
   <img src="'.$user_avatar.'" class="avatar" alt=""/>
  {/if}
  
  <time>
   {$hour}<br/>
   {$day} {$month} {$year}
  </time>
  
  Posty: {$UserPostCount}<br/>
  
  Ostrze¿enia<br/>
  <div class='warn_cont'>
   <a href='{$MAIN_DIR}warnlist/$user_id'>
    <div class='warn_pasek' style='width:{$UserWarn}%;'></div>
   </a>
  </div>
  
  $odznaczenia_html<br/>
  
  <br/>
  <div class="miniicons">
   <a href="{MAIN_DIR}upload/{$UserId}"><img src="'.MAIN_DIR.'icon/folder.png"/></a>';
   <a href="{MAIN_DIR}ggnapisz/$UserGadu"><img src="http://www.gadu-gadu.pl/users/status.asp?id='.$user_array['gadu'].'"/></a>';
   <a href="{MAIN_DIR}$www"target="_blank"><img src="'.MAIN_DIR.'icon/www.png"/></a>';
   <a href="{MAIN_DIR}napiszmaila/'.$user_id.'"><img src="'.MAIN_DIR.'icon/mail.png"/></a>';
  </div>
 </div>
 <p class="post-text">
  $post_tresc_bbcoded
  <br/><br/><br/>-------------------------------------------------------<br/>
  $bbsygnatura
 </p>
 <div class="post_footer">
  <a style="display:none;">a</a>
  <a href='".MAIN_DIR."post/edytuj/$post_id'>Edytuj</a>";
  <a href='".MAIN_DIR."post/usun/$post_id'>Usuñ</a>";
  <div class="post_id">
   <a href="MAIN_DIR post/s/ $post_id"># $post_id</a>
  </div>
 </div>
</article>