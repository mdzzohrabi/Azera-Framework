<?php
namespace Azera\View;

//use Azera\Bundle\Controller;
use Azera\Controller;
use Azera;
use Azera\Debug\Exceptions;
use Azera\Cache\Cache;
use Azera\Util\Parser\Html as HtmlParser;

/**
 *	Azera View Class
 *	@author 	Masoud Zohrabi ( @mdzzohrabi )
 *  @package	Azera\View\View
 *  @version 	1.0
 */
class View
{

	/**
	 *	View passed variables
	 */
	public $vars 	= array();

	public $view 	= null;

	public $file 	= null;

	public $format 	= 'ctp';

	public $output 	= null;

	public $tags 	= array();

	/**
	 *	View controller
	 */
	public $controller 	= null;

	public function __construct( $controller = null , $view = null , $vars = array() )
	{

		$this->set( $vars );

		$this->view 		= $view;

		$this->controller 	= &$controller;

		$document 			= ( $this->controller->controllerType == Controller\Types::Area ? $this->controller : $this->controller->document );

		$theme 				= $document->theme;

		if ( strpos($this->view,'.') === false && $this->controller )
			$this->view 	= implode('.', array_filter(array(
					$this->controller->bundle,
					$this->controller->module,
					$this->view
				)));

		$file	= str_replace( '.' , DS , $this->view ) . '.' . $this->format;

		$scans 	= array(
				Themes . DS . $theme . DS . $file,
				$this->controller->appPath . DS . 'View' . DS . $file
			);

		foreach ( $scans as $scan )
		{
			if ( file_exists( $scan ) )
			{
				$this->file 	= $scan;
			}
		}

		if ( !$this->file )
		{
			throw new Exceptions\ViewNotFound( $this->controller , $this->view , $scans );
		}

		$this->addTag('for',function( $tag ){

			$tag->attrs = array_merge(array(
					'start'	=> 0,
					'end'	=> 0,
					'step'	=> 1
				),$tag->attrs);

			$start 	= (int)$tag->attrs['start'];
			$end 	= (int)$tag->attrs['end'];
			$step 	= (int)$tag->attrs['step'];
			$out 	= null;

			for ( $i = $start ; $i <= $end ; $i += $step )
			{
				$out 	.= str_replace( '$i' , $i , $tag->innerHtml );
			}
			return $out;
		});

		return $this;
	}

	public function addTag( $tag , $function = null )
	{
		if ( is_array( $tag ) )
		{
			$this->tags 	= array_merge( $this->tags , $tag );
			return $this;
		}

		$this->tags[ $tag ] 	= $function;

		return $this;
	}

	public function render()
	{

		extract( $this->vars );

		$fileContent 	= file_get_contents( $this->file );

		$fileContent 	= Parser::parse( $fileContent , $this->vars );

		Cache::inThe('view')->write( $this->file , $fileContent );

		ob_start();
			//include_once __DIR__ . DS . 'Functions.php';
			include_once Cache::inThe('view')->path( $this->file );				
		$this->output 	= ob_get_clean();

		//$this->output = $parsed = HtmlParser::getInstance( $this->output )->addTag( $this->tags )->parse();

		return $this->output;

	}
	
	/**
	 *	Pass variable to view
	 */
	public function set( $key  , $value = null )
	{
		if ( is_array( $key ) )
			$this->vars 	= array_merge( $this->vars , $key );
		else
			$this->vars[$key] 	= $value;

		return $this;
	}
	
	public function __call( $func , $args )
    {
        if ( method_exists( $this->controller , $func ) )
            return call_user_func_array( array( $this->controller , $func ) , $args );
        return null;
    }

    public function __get( $key )
    {
    	return $this->controller->{$key};
    }

    public function __set( $key , $value )
    {
    	return $this->controller->{$key} 	= $value;
    }

}
?>