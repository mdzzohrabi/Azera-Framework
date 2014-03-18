<?php
/**
 * Azera Framework
 * Database Modeling class
 * @author  Masoud Zohrabi ( @mdzzohrabi )
 * @package Azera\Database\Model
 * @version 1.3
 **/
namespace Azera\Database;

//init('@Database.Model.BehaviorCollection');
//init('@Database.Model.Behavior');

use Azera\Core\Object;
use Azera\Util\String;
use Azera\Util\Set;
use Azera\Events\Manager as Events;
use Azera\Database\Model\BehaviorCollection;
use Azera\Database\Model\Behavior;

use Azera\Debug\Exceptions;

class Model extends Object {
	
	public $table 		= null;
	public $userTable 	= null;		/* user defined table (by system) */
	public $alias 		= null;
	public $joins		= array();
	public $adapter 	= null;
	public $scheme 		= array();
	public $insertID	= null;
	public $affected 	= 0;
	public $recursive 	= 0;
	public $pk		 	= null;
	public $displayField	 = null;
	public $result 		= null;
	public $id 			= null;
	public $linkDeep 	= 1;
	public $parent 		= null;
	public $createTable	= false;	// Create table if not exists

	public $fields 		= array();
	public $procedures 	= array();
	public $sort 		= null;

	public $controller 	= null;

	public $hasOne 				= array();
	public $hasMany 			= array();
	public $belongsTo 			= array();
	public $hasAndBelongsTo 	= array();
	public $actsAs 				= array();
	public $behavior 			= null;

	public $regexCalls			= array(
			'/find(.*)By(.*)/'		=> '_findBy',
			'/delete(.*)By(.*)/'	=> '_deleteBy',
			'/set(.*)Where(.*)/'	=> '_setWhere'
		);

	public $findMethods 		= array(
			/* 'method'	=> 'callable' */
		);

	public $relations 	= array();

	/** Make Instance Of Model Object **/
	public static function model()
	{
		return new static();
	}

	public function startup()
	{
		# user statup code
	}

	public function afterCreateTable()
	{
		# User Code Here
	}

	private function createTable()
	{
		if ( !$this->createTable || empty( $this->scheme ) ) return false;

		$this->table 	= String::plural( String::underscore($this->alias) );

		if ( !empty($this->userTable) )	
			$this->table 	= $this->userTable;
		/*
		array(
			'field' 	=> array(
				'type'	=> 'integer',
				'size'	=> 11,
				'primary',
				'autoId'
			)
		)
		*/

		$this->adapter->createTable( $this->table , $this->scheme , $this );

		$this->afterCreateTable();

	}

	public function __construct( $controller = null , Model $model = null , $deep = false )
	{
		parent::__construct();

		$this->parent 	  = &$model;
		$this->controller = &$controller;

		if ( $deep !== false )
			$this->linkDeep = $deep;

		$name 			= String::toListArray( $this->toString() , NS )->last();

		$this->adapter 	= &$this->adapter();
		$this->alias 	= ( isset($this->alias) ? $this->alias : $name );
		$this->userTable= $this->table;
		$this->table 	= ( isset($this->table) ? $this->table : $this->determineTable() );
		
		/* create table from scheme when table not found */
		if ( !$this->getScheme() )
		{
			$this->createTable();
		}

		if ( $this->linkDeep >= 0 )
			$this->loadRelations();

		// Initialize Behaviors
		$this->behavior 	= new BehaviorCollection( $this );

		$this->behavior->init(  $this->actsAs );

		$this->startup();
	}

	private static $_object 	= null;
	/**
	 * Create a Custom iQuery
	 */
	public function go()
	{
		return new Model\iQuery( $this );
	}

	// Create Static alias for this model
	public function asStatic( $name )
	{
		eval('
			class ' . $name . ' extends Azera\Database\Model\StaticModel
			{
			}
			');
		$name::setModel($this);
		return $this;
	}

	/**
	 * Load Model Relation Models
	 */
	private function loadRelations()
	{
		foreach ( $this->hasOne as $model => $args )
		{
			if ( !is_array($args) ) $model 	= $args;
			$this->loadAssociation( $model , $args , Model\RELATION::ONE );
		}

		foreach ( $this->hasMany as $model => $args )
		{
			if ( !is_array($args) ) $model 	= $args;
			$this->loadAssociation( $model , $args , Model\RELATION::MANY );
		}

		foreach ( $this->belongsTo as $model => $args )
		{
			if ( !is_array($args) ) $model 	= $args;
			$this->loadAssociation( $model , $args , Model\RELATION::BELONG );
		}
	}

	/**
	 * Find Model Table Name if it not set
	 */
	public function determineTable()
	{
		$names 	= array(
				// module_aliases
				String::lower( $this->module ) . '_' . String::plural( String::underscore($this->alias) ),
				// alias
				String::underscore($this->alias),
				// aliases
				String::plural(String::underscore($this->alias))
			);

		foreach ( $names as $name )
			if ( $this->adapter->exists( $name ) )
				return $name;

		return $this->alias;
	}

	/**
	 * Load Model Assosiaction
	 * @param 	String 		$model
	 * @param 	Array 		$options 	Assocation options
	 * @param 	RELATION 	$type 		Relation Type
	 * @return 	void
	 */
	function &loadAssociation( $model , $options = array() , $type = Model\RELATION::ONE )
	{

		// If it loaded past
		if ( isset($this->relations[$model]) ) return;

		// Convert Options to Array
		$options	= (array)$options;

		// Model Name
		$_model = $model;

		// Model Class Name
		$class 	= $model;

		if ( isset( $options['class'] ) )
		{
			$class 	= $options['class'];
			$model 	= new $class( $this->controller , $this , $this->linkDeep - 1 );
		}
		else
		{
			$route 	= String::dispatch( $model , 'model' );
			$route 	= Set::extend(  $this->defaultRoute , array_filter($route)  );
			$class 	= String::className( $route );
			$model 	= new $class( $this->controller , $this , $this->linkDeep - 1 );
			//$model 	= App\ClassRegistry::init( implode('.',$route) , 'Model' , $this->controller , $this ,  $this->linkDeep - 1 );
		}

		if ( !$model )
		{
			throw new Exceptions\NotFound( "Load model association failed $_model" );
			
		}

		// Default Options
		$options 	= Set::extend(array(
				'primaryKey'	=> a_b($model->alias) . '_id',
				'linkType'		=> $type,
				'object'		=> $model,
				'class'			=> $class
			),$options);

		$this->relations[ $_model ] = $options;

		$this->{$model->alias}	 	= $model;

		return $this->{$model->alias};

	}

	function fieldExists( $field )
	{
		return isset( $this->scheme[$field] );
	}

	function fieldComment( $field )
	{
		if ( $field = $this->field( $field ) )
			return $field['Comment'];
		return false;
	}

	function fieldDefault( $field )
	{
		if ( $field = $this->field( $field ) )
			return $field['Default'];
		return false;		
	}

	function field( $field )
	{
		return $this->scheme[ $field ];
	}

	function fieldType( $field )
	{
		if ( !isset($this->scheme[$field]) ) return false;

		$column = $this->scheme[$field];

		preg_match( '/(.*)\((.*)\)/' , $column['Type'] , $out);

		if ( empty($out) )
			$type 	= $column['Type'];
		else
			list( $a , $type , $limit ) = $out;

		foreach ( $this->adapter->columns as $fieldType => $column )
			if ( $column['name'] == $type )
				return $fieldType;

		return 'string';
	}

	function name( $name  , $type = 'field' , $alias = true )
	{
		if ( is_object($name) )
		{
			return $this->name($name->fullTableName(),'table') . ($alias ? $this->adapter->alias . $this->name($name->alias(),'table') : null);
		}

		if ( strpos( $name , ' ' ) !== false )
			return $this->name( current(explode( ' ' , $name )) );

		if ( strpos($name , '.') !== false )
		{
			
			$parts 	= explode('.', $name);
			foreach ( $parts as $i => $part )
				$parts[$i] = $this->name($part , 'table');
			return implode('.' , $parts);

		}else if ( $type != 'table' && $alias ){
			
			$name 	= $this->alias() . '.' . $name;
			return $this->name( $name );

		}

		if ( $name == '@' ) $name = $this->alias();

		return $this->adapter->startQuote . 
				($type == 'table' ? $this->adapter->tablePrefix : '') . $name 
			. $this->adapter->endQuote;
	}

	function value( $value , $field = null )
	{

		if ( !$field )
		{
			return $this->adapter->startString . $value . $this->adapter->endString;
		}

		$type 	= $this->fieldType( $field );

		$value 	= $this->adapter->escape($value);

		if ( $type == 'integer' || $type == 'float' || $type == 'boolean' )
			return $value;

		return $this->adapter->startString . $value . $this->adapter->endString;
	}

	function &adapter()
	{
		if ( $engine = &Database::engine() )
			return $engine;

		throw new Exceptions\NotFound('Database Engine not set');
	}

	function fullTableName()
	{
		return $this->table;
	}

	function execute( $sql )
	{
		return $this->result = $this->adapter->query( $sql );
	}

	function alias()
	{
		return $this->alias;
	}

	protected function getScheme()
	{

		$scheme 	= $this->adapter->getScheme( $this );

		if ( !$scheme ) return false;

		foreach ( (array)$scheme as $column )
		{
			$this->scheme[ $column['Field'] ] 	= $column;

			if ( $column['Key'] == 'PRI' )
				$this->pk 	= $column['Field'];

		}

		if ( empty($this->displayField) )
		foreach ( array('title','name','caption','text') as $name )
			if ( isset( $this->scheme[ $name ] ) )
			{
				$this->displayField 	= $name;
				break;
			}

		return true;
	}

	public function scheme()
	{
		return $this->scheme;
	}

	/**
	 * SELECT Method
	 * find('all');
	 * find('first');
	 * find('list');
	 * find('count');
	 * find('tree');
	 */
	public function find( $type = 'all' , $options  = array() )
	{

		// LIMIT 1 for first find type
		if ( $type == 'first' ) $options['limit'] = 1;

		// Default Options
		$options 	= array_merge(array(
				'alias'		=> true,
				'contain'	=> []
			),$options);

		// Add FindType to Options for Events
		$options['findType']	= $type;

		// Append Model default Sort
		if ( !isset($options['sort']) && $this->sort )
			$options['sort']	= $this->sort;

		// Model beforeFind Event
		$options 	= $this->beforeFind( $options );

		// Other FindMethods in $this->findMethods
		// can Add By addFindMethod( 'name', [] )
		if ( isset( $this->findMethods[ $type ] ) )
			return $this->{ $this->findMethods[ $type ] }( $options );

		extract( $options );

		// fetch results
		if ( !$results 	= $this->adapter->find( $this , $options ) ) return false;

		// FindType 	: All
		if ( $type == 'all' )
		{
			$result = array();

			while ( $row = $results->read() )
			{
				$result[] 	= $this->_associations( $row , $contain , $alias );
			}

			return $this->afterFind($result,$options);
		}

		// FindType 	: List
		if ( $type == 'list' )
		{
			$result 	= array();
			while ( $row 	= $results->read() )
			{
				$result[ $row[ $this->pk ] ] 	= $row[ $this->displayField ];
			}
			return $result;
		}

		// FindType 	: First
		if ( $type == 'first' )
		{
			return $this->afterFind($this->_associations( $results->read() , $contain , $alias ),$options);
		}

		// FindType 	: Count
		if ( $type == 'count' )
		{
			return $results->count;
		}

	}


	/**
	 * Fetch Query Associations and append them to query result
	 * @param 	Array 	$row 		Result Row
	 * @param 	Array 	$contains 	Contains
	 * @param 	Boolean $alias 		Row has Alias ?
	 * @return 	Array 	Result
	 */
	private function _associations( $row , $contains = array() , $alias = true )
	{
		
		if (empty($row)) return $row;


		if ( $alias )
			$_result 	= array(
					$this->alias 	=> $row
				);
		else
			$_result 	= $row;

		foreach ( (array)$contains as $link => $args )
		{

			if ( !is_array($args) )
			{
				$link 	= $args;
				$args 	= array();
			}

			$obj 		= $this->relations[$link]['object'];
			$primaryKey = $this->relations[$link]['primaryKey'];
			$linkType 	= $this->relations[$link]['linkType'];

			//echo $primaryKey;

			if ( ($linkType == Model\RELATION::ONE || $linkType == Model\RELATION::BELONG) && isset( $row[$primaryKey] ) )
			{
				$args['conditions'][ $obj->pk ] 	= $row[ $primaryKey ];
				$_result 	+= (array)$obj->find( 'first' , $args );
			}

			if ( $linkType == Model\RELATION::MANY )
			{
				$args['conditions'][ $primaryKey ] 	= $row[ $this->pk ];
				$_result[$obj->alias] 	= $obj->find( 'all' , $args );
			}
		}
		return $_result;
	}

	/**
	 * Alias for find('all' , []);
	 * @param 	Array 	$options
	 * @return 	Result
	 */
	public function findAll( $options = array() )
	{
		return $this->find( 'all' , $options );
	}

	/**
	 * alias for find('all',[]);
	 */
	public function all( $options = [] )
	{
		return $this->find( 'all' , $options );
	}

	/**
	 * Alias for find('count', [])
	 * @param 	Array 	$options
	 */
	public function count( $options = array() )
	{
		return $this->find( 'count' , $options );
	}

	/**
	 * Alias for find('first' , []);
	 * If not set conditions default set to id
	 * @param Array $options
	 */
	public function read( $options = array() )
	{
		if ( empty( $options['conditions'] ) && $this->id )
			$options['conditions'][ $this->pk ] = $this->id;

		return $this->find( 'first' , $options );
	}

	/**
	 * beforeFind Events
	 * @param  	Array	$options 	Find Options
	 * @return 	Array 	Find Options
	 */
	protected function beforeFind( $options = array() )
	{
		
		if ( !empty( $this->fields ) )
			$options['fields'] = $this->fields;

		return	Events::raiseEvent( $this->toString() . '::beforeFind' , $options );
	}

	protected function afterFind( $results = array() , $options = array() )
	{

		if ( isset($options['alias']) AND $options['alias'] === false )
			return $results;

		if ( !empty( $this->procedures ) )
			if ( $options['findType'] == 'all' )
			{
			foreach ( $results as $i => $result )
				foreach ( $this->procedures as $field => $proc )
					if ( isset( $proc['get'] ) )
						if ( isset( $results[$i][$this->alias][$field] ) )
						$results[$i][$this->alias][$field] = $proc['get']( $results[$i][$this->alias][$field] );
			}
			else if ( $options['findType'] == 'first' )
			{
				foreach ( $this->procedures as $field => $proc )
					if ( isset( $proc['get'] ) )
						if ( isset( $results[$this->alias][$field] ) )
						$results[$this->alias][$field] = $proc['get']( $results[$i][$this->alias][$field] );
			}

		return 	Events::raiseEvent( $this->toString() . '::afterFind' , $results );
	}

	public function trans( $trans , $rollbackIfFail = true )
	{
		$this->adapter->autoCommit(FALSE);
		
		$this->adapter->catchErrors 	= false;

		$trans( $this );

		!($state = $this->adapter->commit()) ? $this->adapter->rollback() : null;

		$this->adapter->catchErrors 	= true;
		
		return $state;
	}

	public function rollback()
	{
		$this->adapter->rollback();
	}

	public function _findBy( $in , $args )
	{
		list( $fields , $conditions ) = $in;

		$type 		= $fields == 'All' ? 'all' : 'first';

		if ( $type == 'all' )
			$fields 	= array();

		if ( empty($fields) )
		{
			$fields 	= array('*');
		}else{
			$fields 	= String::underscore( $fields );
			$fields 	= explode( '_and_' , $fields );
		}

		$conditions	= String::underscore( $conditions );
		$conditions = strtr( $conditions , array(
				'_and_'	=> '|AND ',
				'_or_'	=> '|OR '
			) );

		$conditions = explode( '|' , $conditions );

		if ( count($conditions) != count($args) )	return;

		$conditions = array_combine( $conditions , $args );

		$result 	= $this->find( $type , compact('fields','conditions') );

		if ( count( $fields ) == 1 && $fields[0] != '*' && $fields[0] != 'All' )
			return $result[ $this->alias ][ $fields[0] ];

		return $result;

	}

	public function delete( $conditions = array() )
	{
		return $this->adapter->delete( $this , $conditions );
	}

	public function _deleteBy( $in , $args )
	{
		list( $fields , $conditions ) = $in;

		if ( empty($fields) )
		{
			$fields 	= array('*');
		}else{
			$fields 	= String::underscore( $fields );
			$fields 	= String::split( $fields , '_and_' );
		}

		$conditions	= String::underscore( $conditions );
		$conditions = String::replace( $conditions , array(
				'_and_'	=> '|AND ',
				'_or_'	=> '|OR '
			) );

		$conditions = String::split( $conditions , '|' );

		if ( count($conditions) != count($args) )	return;

		$conditions = array_combine( $conditions , $args );

		return $this->delete( $conditions );

	}

	public function _setWhere( $in , $args )
	{
		list( $fields , $conditions ) = $in;

		if ( empty($fields) )
			return;

		$fields 	= String::underscore( $fields );
		$fields 	= String::split( $fields , '_and_' );

		$conditions	= String::underscore( $conditions );
		$conditions = String::replace( $conditions , array(
				'_and_'	=> '|AND ',
				'_or_'	=> '|OR '
			) );

		$conditions = String::split( $conditions , '|' );

		$flen 	= count($fields);
		$clen 	= count($conditions);

		if ( ($flen + $clen) != count($args) )	return false;

		$conditions = array_combine( $conditions , array_slice( $args , $flen ) );
		$data 		= array_combine( $fields 	 , array_slice( $args , 0 , $flen ) );

		return $this->save( $data , $conditions );

	}

	public function save( $data = array()  , $conditions = array() )
	{
		if ( empty( $conditions ) )
			$conditions[ $this->pk ] = $this->id;

		foreach ( $this->procedures as $field => $proc )
			if ( isset( $data[ $field ] ) )
				if ( isset( $proc['set'] ) )
					$data[$field] 	= $proc['set']( $data[$field] );

		return $this->adapter->update( $this , $data , $conditions );
	}

	public function create( $data = array() )
	{
		return $this->id = $this->adapter->insert( $this , $data );
	}

	public function __call( $method , $args )
	{
		foreach ( $this->regexCalls as $pattern => $proc )
		{
			
			if ( !preg_match( $pattern , $method , $out ) ) continue;
	
			unset( $out[0] );
			
			if ( method_exists( $this , $proc ) )
				return $this->{$proc}( array_values($out) , $args );
		}

		return $this->behavior->dispatchMethod( $method , $args );
	}

}

namespace Azera\Database\Model;

class RELATION {
	const MANY_MANY	= 'Many_Many';
	const ONE 		= 'One';
	const MANY 		= 'Many';
	const BELONG 	= 'BelongsTo';
}
?>