<?php
namespace Azera\View;

//use Azera\Bundle\Controller;
use Azera\Controller;
use Azera;
use Azera\Debug\Exceptions;
use Azera\Cache\Cache;
use Azera\Util\Parser\Html as HtmlParser;
use Azera\View\Parser\HAML;
use Azera\Core\Process;

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

	public $format 	= 'view';

	public $output 	= null;

	public $tags 	= array();

	private $obj 	= null;

	private $filters 	= [];

	public $__section 	= [];

	public $blocks		= [];

	private $__func 	= [

		'define'		=> 'define'

	];

	private $layout 	= null;

	/**
	 *	View controller
	 */
	public $controller 	= null;

	public static function make( $view = null , $vars = [] , &$controller = null )
	{
		return new static( $controller , $view , $vars );
	}

	/**
	 * View Package Constructor
	 * 
	 * 
	 * 
	 * @param 	object 		$controller 	View owner controller
	 * @param 	string 		$view 			View name
	 * @param 	array 		$vars 			View variables
	 * 
	 */
	public function __construct( &$controller = null , $view = null , $vars = array() )
	{

		$this->__func 	+= [

			'extend'	=> array($this , 'extend')
		
		];

		$this->set( $vars );

		$this->view 		= $view;

		$this->controller 	= $controller;

		// Document
		if ( $controller instanceof Controller\Controller )
			$document 			= ( $this->controller->controllerType == Controller\Types::Area ? $this->controller : $this->controller->document );
		else
			$document		= Process::area();

		// Theme
		$theme 				= $document ? $document->theme : null;

		// Convert view to Full address view
		// index 	to 	Azera.User.index
		if ( strpos($this->view,'.') === false && $this->controller )
			$this->view 	= implode('.', array_filter(array(
					$this->controller->bundle,
					$this->controller->module,
					$this->view
				)));

		// File address
		// Azera.Acl.index 	to Azera/Acl/index.ctp
		$file	= str_replace( '.' , DS , $this->view ) . '.' . $this->format;

		// Searchable paths
		$scans 	= array_filter([
				
					$theme 	? Themes . DS . $theme . DS . $file : null,
					
					$this->controller ? $this->controller->appPath . DS . VIEW . DS . $file : null,

					APP . DS . VIEW . DS . $file,

					System . DS . VIEW . DS . $file

				]);

		// Search for view
		foreach ( $scans as $scan )
		{
			if ( file_exists( $scan ) )
			{
				$this->file 	= $scan;
				break;
			}
		}

		if ( !$this->file )
		{
			throw new Exceptions\ViewNotFound( $this->controller , $this->view , $scans );
		}

		//$this->obj 	= new Parser\View( $this , $this->file , $this->vars );

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

		if ( TRUE || Cache::inThe('view')->old( $this->file , $this->file ) ):

		$fileContent 	= file_get_contents( $this->file );

		/*
		$HAML 			= new HAML();

		$HAML->setContent( $fileContent );

		$fileContent 	= $HAML->parse();
		*/

		$fileContent 	= (new Parser\View( $this , $fileContent ))->parse();

		//$fileContent 	= Parser::parse( $fileContent , $this->vars );

		Cache::inThe('view')->write( $this->file , $fileContent );

		endif;

		ob_start();
			//include_once __DIR__ . DS . 'Functions.php';
			$this->blocks 	= include_once Cache::inThe('view')->path( $this->file );				
		$this->output 	= ob_get_clean();

		//$this->output = $parsed = HtmlParser::getInstance( $this->output )->addTag( $this->tags )->parse();
		
		// Layout
		if ( $this->layout )
		{
			$Layout 	= new View( $this->controller , $this->layout , [ 'content' => $this->output ] );
			return $Layout->render();
		}

		return $this->output;

	}
	
	/**
	 *	Pass variable to view
     *  @param  mixed   $key
     *  @param  mixed   $value
     *  @return self
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

    public function call( $function , $line , $args )
    {
    	if ( !isset($this->__func[$function]) ) return null;
    	
    	$args 	= array_merge( [$line] , $args );

    	return call_user_func_array( $this->__func[$function] , $args);
    }

    public function __toString()
    {
    	return $this->render();
    }

    function &without( $block )
    {
    	$this->filters[] 	= $block;
    	return $this;
    }

    function extend( $line , $view )
    {

    	echo $line;

    }

}
?>
