<?php
namespace Azera\Database;

use Azera\Core\Config;
use Azera\Database\Model;
use Azera\Util\Set;

abstract class Engine
{
	
	/**
	 * inserted row id
	 * @var integer
	 */
	public $insertID = null;

	/**
	 * number of affected rows in last query
	 * @var integer
	 */
	public $affected = 0;

	/**
	 * engine connection object
	 * @var object
	 */
	protected $connection = null;

	/**
	 * last query result
	 * @var object
	 */
	public $result = null;

	/**
	 * quoted identifiers
	 * @var string
	 */
	public $startQuote = null;
	public $endQuote = null;

	public $startString	= null;
	public $endString 	= null;

	public $useAlias = false;
	public $alias 	 = null;
	public $tablePrefix = null;
	public $config 		= array();
	public $_logs		= array();

	protected $_schemes 	= array();

	protected $sqlCondOps	= array('LIKE','DISLIKE','REGEXP','NOT','IN','NOT IN','!=','=');

	abstract function connect();
	abstract function fetch( $result );
	abstract function query( $sql );
	abstract function getScheme( Model $model );
	abstract function createTable( $table , $scheme , Model $model );
	abstract function setEncoding( $enc );

	function __construct()
	{
		$this->connect();
		$this->tablePrefix 	= ( isset($this->config['prefix']) ? $this->config['prefix'] : null );
		$this->config 		= $this->config();
		$this->getScheme();
	}

	function exists( $table )
	{
		return array_key_exists( $table , $this->_schemes );
	}

	function config()
	{
		return Config::get('Database');
	}

	function connected()
	{
		return !is_null($this->connection);
	}

	function fetchArray( $result = false )
	{
		$rows 	= array();
		while ( $row = $this->fetch( $result ) )
		{
			$rows[] = $row;
		}
		return $rows;
	}

	function renderStatement( $type = 'select' , $options = array() , Model $model = null )
	{

	}

	function find( Model $model , $options = array() )
	{
		$options	= Set::extend(array(
				'table'		=> $model->fullTableName(),
				'alias'		=> $model->alias(),
				'fields'	=> array('*'),
				'conditions'=> array(),
				'sort'		=> null,
				'group'		=> null,
				'limit'		=> null
			),$options);
		return $options;
	}

	function delete( Model $model , $conditions = array() )
	{
	
	}

}
?>