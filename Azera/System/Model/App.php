<?php
namespace Azera\System\Model;

use Azera\Bundle\Model;

class App extends Model
{

	public $scheme 	= array(
			'id'	=> array(
					'type'		=> 'integer',
					'size'		=> 11,
					'auto'		=> true,
					'primary'	=> true,
					'comment'	=> 'Application Identify'
				),
			'name'	=> array(
					'type'		=> 'string',
					'size'		=> '255',
					'null'		=> false,
					'comment'	=> 'Application Name'
				),
			'active'	=> array(
					'type'		=> 'integer',
					'size'		=> 1,
					'default'	=> 1,
					'comment'	=> 'Application State'
				),
			'_comment'	=> 'System Application'
		);

	public $createTable 	= true;

}
?>