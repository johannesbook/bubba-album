<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');


if (! function_exists('load_lang') ){
	function load_lang($language){
		global $lang, $langcode;
		if( !isset($lang) ){
			if(file_exists(APPPATH."i18n/$language/messages.php")){
				include(APPPATH."i18n/$language/messages.php");
				$langcode = $language;
			}
		}
	}
}

if (! function_exists('t'))
{
	function t($string){
		global $lang;
		if( isset($lang) && isset($lang[$string]) ){
			$string = $lang[$string];
		}else{
			$string = $string;
		}
		$nbr_args = func_num_args();
		if( $nbr_args > 1 ) {
			$args = func_get_args();
			array_shift($args);
			$string = vsprintf( $string, $args );
		}

		return $string;
	}
}

?>
