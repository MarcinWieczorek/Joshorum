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
   * Język
   */
  public $lang;
  
  /*
   * Tablica z językiem
   */
  public $parsedLangFile;
  
  /*
   * Parser YAML na tablicę PHP
   */
  public $Parser;
  
  public function __construct($language,$Parser)
   {
    $this->Parser = $Parser;
	
    if(empty($language))
	 {
	  return false;
	 }
	 
	if(!file_exists(DIR_NAME.'/libs/Language/langs/lang_'.$language.'.yml'))
	 {
	  return false;
	 }
	 
	if(!file_exists(DIR_NAME.'/libs/Language/parsed/lang_'.$language.'.yml'))
	 {
	  $Parser -> parse();
	 }
	 
	$this->lang = $language;
   }
 }
?>