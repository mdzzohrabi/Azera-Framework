<?php
namespace Azera\Util;

class Session {
	
	public static function read( $key )
	{
		return eval(eas('_SESSION' , $key));
	}

	public static function write( $key , $value = null )
	{
		return eval('return $_SESSION["' . str_replace( '.' , '"]["' , $key ) . '"] = $value;');
	}

	public static function setFlash( $message = null , $element = 'flash-message' , $attrs = array() )
	{
		self::write('Sys.Flash',array(
				'message'	=> $message,
				'element'	=> $element,
				'attrs'		=> $attrs
			));
	}

	public static function getFlash()
	{
		$flash = self::read('Sys.Flash');
		self::delete('Sys.Flash');
		return $flash;
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