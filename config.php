<?php
/*
 * Plik konfiguracyjny Joshorum
 * http://marcin.co
 * Marcin Wieczorek 2011-2013
 * Wersja 1.1
 */
 
define('MYSQL_HOST',''); //Serwer MySQL
define('MYSQL_USER',''); //użytkownik
define('MYSQL_PASS',''); //hasło
define('MYSQL_PORT',3306); //port
define('MYSQL_BASE',''); //baza

define('DIR_NAME',dirname(__FILE__));
define('TBL','eif_');

define('EMAIL_NOREPLY','no-reply@staraprochownia.pl');
define('EMAIL_REGISTER_TITLE','Rejestracja na Staraprochownia.pl');
define('EMAIL_REGISTER_MESSAGE',"Witaj {NICK}!\n\nRejestrowałeś się na forum StaraProchownia.pl\nJeśli nie, zignoruj tę wiadomość.\n\nTwój link aktywacyjny:\n{ACTIVATE}\n\nPozdrawiamy, ekipa Stara Prochownia");

define('EMAIL_ACTIVATION_TITLE','Aktywacja konta na StaraProchownia.pl');
define('EMAIL_ACTIVATION_MESSAGE','');
?>