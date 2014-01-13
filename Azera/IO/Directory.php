<?php
namespace Azera\IO;

use Exception;

class Directory
{
	
	static function create( $dir )
	{
		$parts 	= explode( DS , $dir );

		$path 	= null;

		try
		{
			foreach ( $parts as $part )
			{
				$path 	.= $part . DS;
				file_exists( $path ) or mkdir( $path );
			}
		}catch( Exception $e)
		{
			return false;
		}

		return $dir;

	}

}
?>