<?php
namespace Azera\Cache;

import('String')->from('@Util')->alias( __NAMESPACE__ . NS . 'String');

class Registry
{
    
    /**
     *    Storage variable
     */
    private static $_storage    = array();
    
    /**
     *    Set a Registry Variable
     *    e.g   Registry::set('Sys.Path' , __DIR__ );
     */
    static function set( $route , $value )
    {
        return eval('return self::$_storage["' . String::replace( $route , '.' , '"]["' ) . '"] = $value;');
    }
    
    /**
     *  e.g     Registry::get('Sys.Path');
     */
    static function get( $route )
    {
        $_storage   = self::$_storage;
        return eval(eas('_storage', $route));
    }

    static function increase( $route , $value = 1 )
    {
        $value  = self::get($route) + $value;
        return self::set( $route , $value );
    }
    
}
?>