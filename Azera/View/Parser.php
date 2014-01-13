<?php
namespace Azera\View;

use Azera\Cache\Cache;

class Parser
{

	private static $regexp 	= array(
			# @for [:key = ]:value in (:list | start..end)  [step :step]
			'/@for\s+(?:(?P<key>\w+)\s*=\s*)?(?P<value>\w+)\s+in\s+(?P<list>[a-zA-Z\."\'\d_()$]+)(?:\s*\|\s*(?P<func>[\w]+))?\s*(?:step\s+(?P<step>[\w$]+))*[ \t]*$/m'	=> 'compileFor',
			# @if exp
			'/^([\s]*)@if\s+(.*)$/m'	=> 'compileIf',
			# @code
			'/^([\s]*)@(.*)$/m'			=> 'compileCode',
			# { ($var,"String") [| $func] }
			'/{\s*(\$(\w+)|\"([^\"]*)\")\s*[\|]?\s*([a-zA-Z:_]*)\s*}/'	=> 'compileVar',
			# comment
			'/{{--((.|\s)*?)--}}/'		=> 'compileComments',
			# set variable
			'/{{\s*set\s+(?P<var>\w+)\s*=\s*(?P<value>[\w\W]*?)\s*}}/m' 	=> 'compileVariableSet',
			# @Function arg,arg,...
			//'/@(\w+)[ \t]*(.*)(?:;$|$|;)/m' => 'compileFunction',
			'/asset:\/\/(?P<asset>[\w\/.]+)/m'		=> 'compileAsset',
			'@<(?P<tag>\w+)(.*?)\s+external="true"(\s+fileName="(?P<fileName>(\w+))")?(.*?)>(?P<content>[\w\W\s]*?)</(\w+?)>@m' 	=> 'compileExternalLoad'
		);

	public static function compileExternalLoad( $m )
	{
		switch ($m['tag']) {
			case 'script':
				$format 	= 'js';
				$out 		= "<script type=\"text/javascript\" src=\"%s\"></script>";
				break;
			default:
				$format 	= 'css';
				$out 		= "<link rel=\"stylesheet\" href=\"%s\"/>";
				break;
		}

		$name 	= ( !empty($m['fileName']) ? $m['fileName'] : md5($m['content']) ) . '.' . $format;

		Cache::write( $name , $m['content'] , 'public' );

		\Azera\Routing\Router::refreshStatic('cached');

		$out 	= sprintf( $out , asset('cached/' . $name) );

		return $out;
	} 

	public static function compileAsset( $m )
	{
		return asset( $m['asset'] );
	}

	public static function compileVariableSet( $m )
	{
		extract($m);
		$value 	= trim($value);
		json_decode($value);

		if ( json_last_error() == JSON_ERROR_NONE )
		{
			$value 	= 'json_decode(\'' . $value . '\',true)';
		}

		return "<?php $$var = $value; ?>";
	}

	public static function compileIf( $match )
	{
		$exp 	= trim($match[2]);
		return $match[1] . "<?php if ( $exp ):?>";
	}

    protected function compileComments($value)
    {
    	$value 	= $value[1];
        return '<?php /*' . $value . '*/ ?>';
    }

	public static function compileFor( $m )
	{
			extract( $m );

			if ( strpos( $list , '..' ) !== false )
			{
				list( $start , $end ) = explode('..' , $list);
				$step 	= $step ? $step : 1;
				$list 	= 'range('.$start.','.$end.','.$step.')';
			}
			elseif ( strpos( $list , '(' ) !== false )
			{
				$list 	= $list;
			}
			else
			{
				$list 	= '$' . $list;
			}

			if ( $func )
			{
				$list 	= $func . '(' . $list . ')';
			}

			if ( $key )
				return "<?php foreach ( $list as $$key => $$value ): ?>";

			return "<?php foreach ( $list as $$value ): ?>";
	}

	public static function compileCode( $match )
	{
			$exp 	= trim($match[2]);
			switch ($exp) {
				case 'endfor':
					$exp = 'endforeach';
					break;
			}
			return $match[1] . "<?php $exp;?>";
	}

	public static function compileVar( $match )
	{
		$func 	= $match[4];
		$var 	= $match[1];

		$funcAlias 	= array(
				'e'	=> 'htmlspecialchars',
				't'	=> 'Locale::get',
				'u'	=> 'strtoupper',
				'l'	=> 'strtolower'
			);

		$func 	= $funcAlias[ $func ] 	? $funcAlias[$func] : $func;

		if ( !$func )
		{
			return "<?=$var;?>";
		}

		return "<?=$func($var);?>";
	}
	
	static function parse( $input )
	{

		foreach ( self::$regexp as $regexp => $compile )
		{
			$input 	= preg_replace_callback( $regexp , array(self,$compile), $input);
		}

		$replaces 	= 	array(
						'<%' 	=> '<?=',
						'%>'	=> ';?>',
						'{{'	=> '<?php',
						'}}'	=> '?>'
					);

		$input 	= strtr(
				$input,
				$replaces
			);

		return $input;

	}

}
?>