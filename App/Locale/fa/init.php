<?php
Azera\i18n\Locale::config('fa', array(

	'routePrefix'	=> 'fa',
	'themePrefix'	=> 'fa',
	
	'date' 			=> function( $format = 'Y-m-d' , $timestamp = null )
	{
		return Date( $format , $timestamp );
	},

	'numbers'		=> array(
			'1'		=> '۱',
			'2'		=> '۲',
			'3'		=> '۳',
			'4'		=> '۴',
			'5'		=> '۵',
			'6'		=> '۶',
			'7'		=> '۷',
			'8'		=> '۸',
			'9' 	=> '۹',
			'0'		=> '۰'
		),

	'direction'		=> 'rtl'

));
?>