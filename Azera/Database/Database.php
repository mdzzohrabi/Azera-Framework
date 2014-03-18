<?php
namespace Azera\Database;

use Azera\Core\Config;
use Azera\Database\Model;
use Azera\Database\Engine;

class Database
{

	public static $engine 	= null;

	/**
	 * Return current database engine
	 * @return Azera\Database\Engine
	 */
	static function &engine()
	{
		return self::$engine;
	}
	
	/**
	 * initilize Database
	 */
	static function init()
	{
		$config 	= Config::get('Database');					# Get Database User Configuration
		$engine 	= $config['engine'];						# Database Engine

		//$_engine 	= strtolower( $engine );

		$class 		= 'Azera\Database\Engines\\' .$engine;		# Engine Class Name
		

		init('@Database.Engines.' . $engine );					# Load Database Engine File

		if ( class_exists( $class ) )
			return self::$engine = new $class();
		else
			throw new Exception( "Database $engine Engine not found" );

	}

	static function createModel( $table , $alias = null )
	{
		if ( !$alias ) $alias = $table;

		$model 	= new Model();

		$model->table 	= $table;

		$model->alias 	= $alias;

		$model->__construct();

		return $model;
	}

}
?>