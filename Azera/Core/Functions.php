<?php
/**
 * Azera Framework v3.1
 * Core Functions
 * @author Masoud Zohabi ( @mdzzohrabi )
 */
import('Registry')->from('@Cache');
import('String')->from('@Util');

use Azera\Util\String;

function useTestKit()
{
	init('@TestKit.TestKit');
}

/**
 *  Find a route in array variable
 *  e.g     eas('_SESSION','Auth.Name')     return      $_SESSION['Auth']['Name'];
 */
function eas( $var , $key , $default = false )
{
	$default 	= ( is_bool( $default ) ? $default === false ? "false" : "true" : $default );
	$var 	= '$' . $var .  '["' . str_replace( '.' , '"]["' , $key ) . '"]';
	return "return ( isset($var) ? $var : $default );";
}

/**
 *  initialize file or files
 *
 *  e.g init('Azera.IO.Request');
 *  or  init('Azera\IO\Request');
 *  or  init('@IO.Request');
 *
 *  for init all files in a folder use it name
 *  e.g init('@IO');
 *
 *  init multiple files or folders at once
 *  e.g init('@Core','@IO');
 *  e.g init(array('@Core','@IO'));
 *
 */
function init( $namespace , $error = true )
{
    
    if ( !is_bool($error) && func_num_args() > 1 )
    {
        return init( func_get_args() );
    }
    
	if ( is_array( $namespace ) )
	{
		foreach ( $namespace as $item )
		{
			init( $item , $error );
		}
		return;
	}
	
	$root     = Base;
	
	$first     = substr( $namespace , 0 , 1 );
	
	switch ( $first )
    {
        case '@':
            $root     = Azera;
            break;
        case '#':
            $root     = APP;
            break;
    }
	
	$namespace 	= strtr( $namespace , array(
			'.'    => DS,
			NS     => DS,
			'@'    => null,
			'#'    => null
		) );

	$path	 	= str_replace( '\\' , DS , $root . DS . $namespace );

	if ( is_dir( $path ) || strpos( $namespace , '*' ) > 0 )
		return inc( glob( $path . DS . '*.php' ) , $error );
	else
		return inc( $path . '.php' , $error );
}

/**
 * Include_Once One or More Files
 */
function inc( $file , $error = true )
{
	if ( is_array( $file ) )
	{
		$out 	= array();
		foreach ($file as $item)
			if ( $error )
				$out[] 	= inc( $item , $error );
			else
				$out[] 	= include_once $file;
		return $out;
	}
	else
		if ( $error === true || file_exists( $file ) )
			return include_once $file;
	return false;
}

/**
 *  return $_SERVER[ $var ];
 */
function server( $var )
{
	return $_SERVER[ $var ];
}

function env( $var )
{
	return server( $var );
}

/**
 *  getInstance from a class
 *  e.g     with('Azera.IO.Request')->uri();
 * 	e.g 	with('@IO.Request')->IP();
 */
function with( $namespace )
{

    $namespace  = strtr( $namespace , array(
        '.' => NS,
        '@' => 'Azera\\'
    ));

	$args 		= array_slice(func_get_args(),1);

	return new $namespace( $args );

}

class ImportClass
{
	public $name 	= null;
	public $path 	= null;
	public $class 	= null;
	public $alias 	= null;

	function from( $path )
	{
		
		$this->path 	= $path;
		
		$this->class 	= strtr($this->path 	. NS . $this->name,array(
				'.' 	=> NS,
				'@'		=> 'Azera' . NS
			));

		init( $this->path . ( $this->name != '*' ? '.' . $this->name : null ) );
		
		return $this;
	}
	function alias( $alias )
	{
		class_alias( $this->class , $alias );
		return $this;
	}
}

/**
 *	Import some class
 *  e.g     import('Request')->from('@IO');
 */
function import( $name )
{

	$object 	= new ImportClass();
	$object->name 	= $name;

	return $object;

}

/**
 * Var_Dump alias
 * @param 	mixed 	$input
 */
function dump( $input )
{
 	var_dump($input);
}

/**
 *  e.g     using('Azera.IO.Request');
 */
function using( $class )
{
	$parts 	= explode( NS , $class );
	$name 	= end( $parts );
	return import( $name )->from( implode(NS,array_slice($parts,0,-1)) );
}

function asset( $mapKey )
{
	return Azera\Routing\Router::asset( $mapKey );
}

function document()
{
	return Process::area();
}
?>