<?php
namespace Azera\i18n;

class Locale
{

	private static $locales 	= array();
	
	static function loadLocales()
	{

	}

	static function current( $key )
	{
		$defaultLang 	= Config::get( 'defaultLanguage' );
		return self::$locales[ $defaultLang ][ $key ];
	}

	static function config( $name , $config = array() )
	{

		self::$locales[$name] 	= array_merge( array(
				'routePrefix'	=> $name,
				'themePrefix' 	=> null,
				'number'		=> null,
				'date'			=> null,
				'direction'		=> 'ltr'
			) , $config );

	}

}
?>