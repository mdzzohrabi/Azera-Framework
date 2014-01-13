<?php
namespace Azera\IO;

use Azera\IO\Directory;

class File
{
	
	static function writeAll( $file , $data , $create = true )
	{

		$Directory	 	= implode( DS , array_slice( explode( DS , $file ) , 0 , -1 ) );

		if ( $create ) Directory::create( $Directory );

		file_put_contents( $file , $data );

		return true;
	}

	static function readAll( $file , $default = false )
	{
		if ( file_exists( $file ) )
			return file_get_contents( $file );
		return $default;
	}

	static function size( $file )
	{
		if ( file_exists( $file ) )
			return filesize($file);
		return false;
	}

}
?>