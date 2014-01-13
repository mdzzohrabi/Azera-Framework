<?php
namespace Azera\Database\Model\Behaviors;

use Azera\Database\Model\Behavior;

class Tree extends Behavior
{

	var $parentField 	= null;

	function __construct( $config = array() )
	{
		parent::__construct( $config );

		foreach ( array('parent_id','parent_id') as $item )
			if ( $this->model->fieldExists( $item ) )
				$this->parentField 	= $item;

	}

	function tree( $type = 'all' , $options = array() , $parentId = '0' , $level = 0 )
	{

		if ( !isset( $options['conditions'] ) )
			$options['conditions']	= array();

		if ( $level > 0 )
			unset($options['limit']);

		$options['conditions'][ $this->parentField ] 	= $parentId;

		$results	= $this->model->find('all', $options );

		extract( $options );

		$out 		= array();

		foreach ( $results as $result )
		{

			if ( $alias === false )
				$result['__level'] 	= $level;
			else
				$result[ $this->model->alias ]['__level'] 	= $level;

			if ( $type == 'list' )
				$out[] = array(
					$result[ $this->model->alias ][ $this->model->pk ],
					 ( $level > 0 ? '|' : null ) . str_repeat(' - ', $level ) . $result[ $this->model->alias ][ $this->model->displayField ]
				 );
			else
				$out[] 	= $result;

			$out	= array_merge( $out , $this->tree( $type , $options , ( $alias === false ? $result[$this->model->pk] : $result[ $this->model->alias ][ $this->model->pk ] ) , $level + 1 ));
		}

		return $out;

	}
}
?>