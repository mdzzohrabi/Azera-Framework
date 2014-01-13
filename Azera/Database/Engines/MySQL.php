<?php
namespace Azera\Database\Engines;

use Azera\Database\Engine;
use Azera\Database\Result;
use Azera\Database\Model;
use Azera\Cache\Cache;
use Azera\Util\String;
use Azera\Util\Set;

use mysqli;

class MySQLResult extends Result
{
	function __construct( $result = null )
	{
		parent::__construct( $result );
		$this->result 	= $result;

		if ( isset($this->result->num_rows) )
			$this->affected 	= $this->count 	= $this->num_rows 	= $this->result->num_rows;
		else
			$this->affected 	= $this->count 	= $this->num_rows 	= 0;
	}

	public function read()
	{
		return $this->result->fetch_assoc();
	}
}

class MySQL extends Engine
{
	
	public $useAlias 	= true;
	public $alias 		= ' AS ';
	public $startString = '"';
	public $endString 	= '"';
	public $startQuote 	= '`';
	public $endQuote 	= '`';
	public $encoding 	= 'utf8';

	public $columns = array(
		'primary_key' 	=> array('name' => 'NOT NULL AUTO_INCREMENT'),
		'string' 		=> array('name' => 'varchar', 'limit' => '255'),
		'text' 			=> array('name' => 'text'),
		'integer' 		=> array('name' => 'int', 'limit' => '11', 'formatter' => 'intval'),
		'float' 		=> array('name' => 'float', 'formatter' => 'floatval'),
		'datetime' 		=> array('name' => 'datetime', 'format' => 'Y-m-d H:i:s', 'formatter' => 'date'),
		'timestamp' 	=> array('name' => 'timestamp', 'format' => 'Y-m-d H:i:s', 'formatter' => 'date'),
		'time' 			=> array('name' => 'time', 'format' => 'H:i:s', 'formatter' => 'date'),
		'date' 			=> array('name' => 'date', 'format' => 'Y-m-d', 'formatter' => 'date'),
		'binary' 		=> array('name' => 'blob'),
		'boolean' 		=> array('name' => 'tinyint', 'limit' => '1')
	);	

	public function __construct()
	{
		parent::__construct();
		$this->setEncoding( $this->encoding );
	}

	public function setEncoding( $enc )
	{
		$this->query( 'SET NAMES ' . $enc );
	}

	public function connect()
	{
		$config 	= $this->config();
		extract($config);
		return $this->connection 	= new mysqli( $host , $username , $password , $database );
	}

	public function query( $sql )
	{

		$time 	= microtime(true);
		$this->result = new MySQLResult($this->connection->query( $sql ));
		$time 	= microtime(true) - $time;

		if ( $this->connection->error )
			throw new \Exception( "Execute Query [{$sql}]" , 1);
			

		$this->affected 	= $this->result->count;

		$this->_logs[] = array(
				'time'		=> $time,
				'query'		=> $sql,
				'affected'	=> $this->affected,
				'backtrace'	=> debug_backtrace()
			);

		return $this->result;
	}

	public function name( $name )
	{
		return $this->startQuote . $name . $this->endQuote;
	}

	public function getScheme( Model $model = null )
	{

		if ( !is_nulL( $model ) )
		{
			if ( !isset( $this->_schemes[ $model->fullTableName() ] ) ) return false;
			return $this->_schemes[ $model->fullTableName() ];
		}

		if ( !empty($this->_schemes) || $this->_schemes = Cache::read( 'dbo.sources' , 'models' ) )
			return;

		$this->query( 'SHOW TABLES FROM ' . $this->name( $this->config['database'] ) );
		foreach ( $this->fetchArray() as $table )
		{
			$table 	= array_values($table);
			$table 	= $table[0];

			$this->query('SHOW FULL COLUMNS FROM ' . $this->name( $table ) );

			$this->_schemes[ $table ] = $this->fetchArray();

		}

		Cache::write( 'dbo.sources' , $this->_schemes , 'models' );

	}

	public function fetch( $result = false )
	{
		if ( $result )
			return $result->read();
		return $this->result->read();
	}

	public function datas( Model $model , $data = array() )
	{
		$datas 	= array();
		$data 	= array_intersect_key( $data , $model->scheme );
		foreach ( $data as $field => $value )
		{
			$datas[] 	= $model->name( $field , 'field' , false ) . '="' . $value . '"';
		}
		return implode( ', ' , $datas );
	}

	public function conditions( Model $model = null , $data = array()  , $prefix = 'WHERE ' , $alias = true )
	{
		$sql 		= $prefix;
		$conds 		= array();

		foreach ( $data as $field => $value )
		{

			if ( is_int( $field ) )
				{
					$conds[] = $value;
					continue;
				}

			$ao 		= 'AND ';
			$_p 		= explode( ' ' , $field , 2 );

			if ( in_array( strtoupper($_p[0]) , array( 'AND' , 'OR' ) ) )
			{
				$ao 	= strtoupper($_p[0]) . ' ';
				unset($_p[0]);
				$field 	= implode( $_p , ' ' );
			}
			if ( empty($conds) ) $ao = '';

			if ( !is_array( $value ) )
			{
				if ( $value === false ) continue;
				if ( is_null($value) )
				{
					$operand 	= 'IS';
					$value 		= 'NULL';
				}
				else
				{
					$operand 	= '=';
					foreach ( $this->sqlCondOps as $op )
						if ( strpos( $value , "{$op} " ) === 0 )
						{
							$operand 	= $op;
							$value 		= substr( $value , strlen( "{$op} " ) );
							break;
						}
					$value 		= $model->value( $value , $field );
				}
			}else{
				$values 	= array();
				foreach ( $value as $v )
					$values[] = $model->value( $v , $field );
				$value 		= '(' . implode(',',$values) . ')';
				$operand 	= 'IN';
			}

			$field 		= $model->name( $field , 'field' , $alias );

			$conds[] 	= "{$ao}{$field} {$operand} {$value}";

		}

		if ( empty( $conds ) )
			return;

		return $sql . implode( ' ' , $conds );
	}

	public function renderStatement( $type = 'select' , $options = array() , Model $model = null )
	{
		parent::renderStatement( $type , $options );
		extract( $options );

		foreach ( (array)$fields as $i => $field) {
			if ( $field != '*' )
				if ( is_string($i) )
					$fields[$i] 	= "($field)" . $this->alias . $this->startQuote . $i . $this->endQuote;
				else
					$fields[$i]	= $model->name( $field );
		}

		switch ( $type )
		{
			case 'select':
				$table 	= $model->name( $model );
				$fields	= implode( ', ' , (array)$fields );
				$where 	= $this->conditions( $model , $conditions , ' WHERE ' );
				$sort 	= ( $sort ? ' ORDER BY ' . $model->name($sort) : '' );
				$limit 	= ( $limit ? " LIMIT {$limit}" : '');
				return "SELECT {$fields} FROM {$table}{$joins}{$where}{$sort}{$limit}";
				break;
			case 'update':
				$table 	= $model->name( $model , 'table' );
				$data 	= $this->datas( $model , $data );
				$where 	= $this->conditions( $model , $conditions , ' WHERE ' );
				return "UPDATE {$table} SET {$data}{$where}";
				break;
			case 'insert':
				$table 	= $model->name( $model , 'table' , false );
				$data 	= $this->datas( $model , $data );
				return "INSERT INTO {$table} SET {$data}";
				break;
			case 'delete':
				$table = $model->fullTableName();
				$conditions = $this->conditions( $model , $options , ' WHERE ' , false );
				return "DELETE FROM {$table}{$conditions}";
				break;
		}
	} 

	public function find( Model $model , $options = array() )
	{
		$options 	= parent::find( $model , $options );
		extract( $options );
		$sql 		= $this->renderStatement( 'select' , $options , $model );

		return $this->query( $sql );

	}

	public function delete( Model $model , $conditions = array() )
	{

		$sql 	= $this->renderStatement( 'delete' , $conditions , $model );

		return $this->query( $sql );
		
	}

	public function update( Model $model , $data = array() , $conditions = array() )
	{
		$sql 		= $this->renderStatement( 'update' , compact('data','conditions') , $model );

		$result 	= $this->query( $sql );

		return $this->connection->affected_rows;
	}
	
	public function insert( Model $model , $data = array() )
	{
		$sql 		= $this->renderStatement( 'insert' , compact('data') , $model );
		$this->query( $sql );
		return $this->connection->insert_id;
	}

	public function createTable( $table , $scheme , Model $model = null )
	{
		$prefix = $this->config['prefix'];
		$sql 	= "CREATE TABLE IF NOT EXISTS `{$prefix}{$table}` (" . NL;
		$fields = array();
		$extra 	= array();
		foreach ($scheme as $field => $attrs) {

			if ( $field[0] == '_' ) continue;

			$attrs 	= (object)Set::extend( array(
					'type'		=> 'integer',
					'size'		=> null,
					'comment'	=> null,
					'null'		=> false,
					'default'	=> null,
					'primary'	=> false,
					'auto'		=> false
				) , $attrs );

			$flag 	= null;
			$type 	= $this->columns[ $attrs->type ]['name'];
			$type  	= $type . ( $attrs->size ? "({$attrs->size})" : null );

			$null 	= ( !$attrs->null ? ' NOT NULL' : ' NULL');
			$default = ( $attrs->default ? " DEFAULT '{$attrs->default}'" : null );
 			$comment = ( $attrs->comment ? " COMMENT '{$attrs->comment}'" : null );

 			if ( $attrs->auto )
 				$flag	= ' AUTO_INCREMENT';

 			if ( $attrs->primary )
 				$extra[] 	= "PRIMARY KEY (`{$field}`)";

			$fields[] = "`{$field}` {$type}{$null}{$flag}{$default}{$comment}";
		}

		$sql 	.= implode( ', ' . NL , Set::extend($fields , $extra) );
		$sql 	.= NL . ') DEFAULT CHARSET=' . $this->encoding;
		
		/**
		 * Table Comment
		 */
		if ( !empty($scheme['_comment']) )
		{
			$comment 	= $scheme['_comment'];
			$sql 		.= " COMMENT='{$comment}'";
		}

		$this->query( $sql );
		
		//Cache::delete('dbo.sources','models');

	}

}
?>