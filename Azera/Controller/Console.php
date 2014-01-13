<?php
namespace Azera\Controller;

import('Controller')->from('@Controller');
import('Response')->from('@IO')->alias( __NAMESPACE__ . NS . 'Response');

class Console extends Controller
{
	
	public $input 	= 'php://stdin';
	public $output  = 'php://stdout';

	public function __construct()
	{
		parent::__construct();
	}

	public function out( $str , $nl = true )
	{

		Response::write( $str . ( $nl ? NL : null ) );

	}

}
?>