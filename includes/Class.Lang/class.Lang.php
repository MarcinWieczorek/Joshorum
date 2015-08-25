<?php
/*
 * 
 * 
 * 
 * 
 */
 
class Lang
 {
  /*
   * Jêzyk
   */
  public $lang;
  
  /*
   * Tablica z jêzykiem
   */
  public $parsedLangFile;
  
  public function __construct($language)
   {
    if(empty($language))
	 {
	  return false;
	 }
	 
	if(!file_exists(DIR_NAME.'/libs/Lang/lang_'.$language.'.yml'))
	 {
	  return false;
	 }
	 
	$this->lang = $language;
   }
 }
?>