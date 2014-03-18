<?php
namespace Azera\IO;

class Request
{

	public static $passedArgs 	= [];

	static function passedArgs( $key )
	{
		return self::$passedArgs[ $key ];
	}

	# return client ip
	static function remoteIP()
	{
		return server('REMOTE_ADDR');
	}

	static function IP()
	{
		return server('REMOTE_ADDR');
	}

    /**
     * @return string   - Request Url
     */
    static function uri()
	{
		return URI;
	}

    /**
     * @return server port
     */
    static function port()
	{
		return server('SERVER_PORT');
	}

	static function scheme()
	{
		return server('REQUEST_SCHEME');
	}

	static function GET( $item , $default = false )
	{
		return eval(eas('_GET' , $item , $default));
	}

	static function POST( $item , $default = false )
	{
		return eval(eas('_POST',$item,$default));
	}

	static function item( $item , $default = false )
	{
		return eval(eas( '_REQUEST' , $item , $default ));
	}

	static function method()
	{
		return server('REQUEST_METHOD');
	}

	static function all( $type = 'REQUEST' )
	{
		switch ( strtoupper($type) )
		{
			case 'GET':
				return $_GET;break;
			case 'POST':
				return $_POST;break;
			default:
				return $_REQUEST;break;
		}
	}

}
?>