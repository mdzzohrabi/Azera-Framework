<?php
namespace Azera\Debug;

use Azera\Core\Config as Configure;
use Azera\Util\String;

class ErrorHandler
{
    
    private static $css     = '
    <style>
    .az-debug-error
    {
        border: 1px #e6e6e6 solid;
        background : #f0f0f0;
        padding : 10px;
        display:block;
        cursor:pointer;
        font: 100 9pt monospace;
    }
    .az-debug-error .az-code
    {
        background-color : #2C2C2C;
        color : #FFF;
        padding : 5px;
        border-radius : 5px;
    }
    .az-debug-error .az-code .highlight
    {
        display:block;
        background: #555;
    }
    </style>
    ';
    
    public static $errorTypes   = array(
            E_USER_WARNING  => 'Warning',
            E_USER_ERROR    => 'Error',
            E_USER_NOTICE   => 'Notice'
        );
        
    public static $cssWrited    = false;

    static function variable( $value )
    {
        switch ( gettype($value) )
        {
            case "string":
                $value  = String::truncate( str_replace( NL , '' , $value ) , 60 );
                return "'$value'";
                break;
            case "NULL":
                return 'null';
                break;
            case "boolean":
                return $value ? 'true' : 'false';
                break;
            case "Closure":
                return 'Closure{}';
                break;
            case 'object':
                return 'Object';
                break;
            default:
                return $value;
                break;
        }
    }
    
    static function implodeArgs( $args , $before = '(' , $after = ')' )
    {
        
        $out    = $before;
        
        $i      = 0;
        
        foreach ( $args as $arg )
        {
            if ( $i++ > 0 )
                $out    .= ', ';
                
            $out    .= ( is_array( $arg ) ? self::implodeArgs( $arg ,'[',']' ) : self::variable($arg) );
        }
        
        $out    .= $after;
        
        return $out;
        
    }
    
    static function getCode( $file , $line )
    {
        if ( file_exists( $file ) )
        {
            $lines  = explode( NL , file_get_contents( $file ));
            $out     = ($line-3) . " " . $lines[ $line - 4 ] . BR;
            $out    .= ($line-2) . " " . $lines[ $line - 3 ] . BR;
            $out    .= ($line-1) . " " . $lines[ $line - 2 ] . BR;
            $out    .= '<span class="highlight">' . ($line-0) . " " . $lines[ $line - 1 ] . '</span>';
            $out    .= ($line+1) . " " . $lines[ $line ];
            return $out;
        }
        return 'File not found';
    }

    static function handleException( $exception )
    {
    }

    static function handleError( $errorNumber , $errorString , $errorFile , $errorLine )
    {
        
        if ( !self::$cssWrited )
            print( self::$css ); 
        
        self::$cssWrited    = true;
        
        if ( error_reporting() === 0 )
        {
            return false;
        }

        $backtrace  = debug_backtrace();// unset( $backtrace[0] );

        $errorType  = ( isset(self::$errorTypes[ $errorNumber ]) ? self::$errorTypes[ $errorNumber ] : 'Error' );
        
        $id     = ceil(rand(1000,9000));
        
        printf('<pre class="az-debug-error %s" onclick="document.getElementById(\'az-debug-trace-%s\').style.display = ( document.getElementById(\'az-debug-trace-%s\').style.display == \'none\' ? \'block\' : \'none\' );">', $errorType,$id,$id);

        printf("<b>%s</b> " , $errorType);
        print( $errorString . '<br/>' );

        $trace  = current($backtrace);

        printf( "%s(%s)" , $trace['file'] , $trace['line'] );

        printf('<div id="az-debug-trace-%s" style="display:none">' , $id );
        print('<br/><b>From</b><br/>');

        $i  = 0;

        foreach ( $backtrace as $trace )
        {
            if ( $i++ > 0 )
                printf("%s(%s) => <b>%s%s</b> <br/>" ,
                $trace['file'] ,
                $trace['line'] ,
                 ( isset($trace['class']) ? $trace['class'] . $trace['type'] . $trace['function'] : $trace['function'] ),
                 ( isset($trace['args'])  ? self::implodeArgs( $trace['args'] ) : '()' )
                );
            else
                printf( "%s(%s) <br/>" , $trace['file'] , $trace['line'] );

            if ( Configure::read('Error.code') )
            printf('<pre class="az-code">%s</pre><br/>' , self::getCode( $trace['file'] , $trace['line'] ));
        }

        print('</div>');

        printf('</pre>');

        return 1;

    }
       
}
?>