<?php
namespace Azera\IO;

use Azera\IO\Request;
use Azera\Util\Session;

class Response
{
	
	static function clean()
	{
		ob_clean();
	}

	static function send( $message = null )
	{
		die( $message );
	}

	static function write( $out )
	{
		echo $out;
	}

	static function redirect( $url = null , $flashMessage = null , $flashType = 'warning' )
	{
		if ( is_array($url) ) $url 	= url($url);

		if ( !is_null($flashMessage) )
			Session::setFlash( $flashMessage , 'default' , array( 'class' => $flashType ) );

		ob_clean();
		header('Location: ' . $url);
		die();
	}

	static function header( $key , $value = null )
	{
		if ( $value )
			header("{$key}: {$value}");
		else
			header($key);
	}

	static function reload()
	{
		self::redirect( Request::uri() );
	}

	static function writeFile( $file )
	{
		if ( !file_exists($file) )
			throw new \Exception( __("file '%s' not found",$file) , 404);
		
		$content 	= file_get_contents($file);

		self::write( $content );

	}

	static function sendFile( $file )
	{

		$ext 	= end( explode('.',$file) );
		$name 	= end( explode(DS,$file) );

		$mimes 	= array(
		// 	Format 		MimeType
			'jpg'	=> 'image/jpeg',
			'png'	=> 'image/png',
			'gif'	=> 'image/gif',
			'bmp'	=> 'image/bmp',
			'css'	=> 'text/css',
			'txt'	=> 'text/plain',
			'html'	=> 'text/html',
			'htm'	=> 'text/html',
			'image'	=> 'image/png',
			'js' 	=> 'text/javascript',
			'scss'	=> 'text/css'
		);

		if ( $mime = $mimes[ $ext ] )
			self::header('Content-type' , $mime);
		else
			self::header( 'Content-disposition' , 'attachment;filename=' . $name );

		readfile( $file );
	}

}
?>