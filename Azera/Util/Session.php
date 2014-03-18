<?php
namespace Azera\Util;

use Azera\View\View;

class Session {

	public static function start()
	{
		session_start();
	}
	
	public static function read( $key )
	{
		return eval(eas('_SESSION' , $key));
	}

	public static function write( $key , $value = null )
	{
		return eval('return $_SESSION["' . str_replace( '.' , '"]["' , $key ) . '"] = $value;');
	}

	public static function setFlash( $message = null , $element = 'default' , $attrs = array() )
	{
		self::write('App.Flash',array(
				'message'	=> $message,
				'element'	=> $element,
				'attrs'		=> $attrs
			));
	}

	public static function getFlash()
	{
		$flash = self::read('App.Flash');
		self::delete('App.Flash');
		return $flash;
	}

	public static function flash()
	{

		if ( $flash 	= self::getFlash() )
		{
			return View::make('Element.Message.' . $flash['element'])->set([ 'message'	=> $flash['message'] , 'attrs'	=> $flash['attrs'] ])->render();
		}

		return null;

	}

	public static function destroy()
	{
		session_destroy();
	}

	public static function delete($key)
	{
		eval('unset($_SESSION["' . str_replace( '.' , '"]["' , $key ) . '"]);');
	}

}
?>