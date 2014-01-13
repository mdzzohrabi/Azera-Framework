<?php
namespace Azera\Debug;

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
    }
    .az-debug-error .az-code
    {
        background-color : #2C2C2C;
        color : #FFF;
        padding : 5px;
        border-radius : 5px;
    }
    </style>
    ';
    
    public static $errorTypes   = array(
            E_USER_WARNING  => 'Warning',
            E_USER_ERROR    => 'Error',
            E_USER_NOTICE   => 'Notice'
        );
        
    public static $cssWrited    = false;
    
    static function implodeArgs( $args )
    {
        
        $out    = '( ';
        
        $i      = 0;
        
        foreach ( $args as $arg )
        {
            if ( $i++ > 0 )
                $out    .= ', ';
                
            $out    .= ( is_array( $arg ) ? self::implodeArgs( $arg ) : $arg );
        }
        
        $out    .= ' )';
        
        return $out;
        
    }
    
    static function getCode( $file , $line )
    {
        if ( file_exists( $file ) )
        {
            $lines  = explode( "\n" , file_get_contents( $file ));
            $out     = ($line-3) . "\t\t" . $lines[ $line - 4 ] . BR;
            $out    .= ($line-2) . "\t\t" . $lines[ $line - 3 ] . BR;
            $out    .= ($line-1) . "\t\t" . $lines[ $line - 2 ] . BR;
            $out    .= ($line-0) . "\t\t" . $lines[ $line - 1 ] . BR;
            $out    .= ($line+1) . "\t\t" . $lines[ $line ];
            return $out;
        }
        return 'File not found';
    }

    static function handle( $errorNumber , $errorString , $errorFile , $errorLine )
    {
        
        if ( !self::$cssWrited )
            print( self::$css ); 
        
        self::$cssWrited    = true;
        
        if ( error_reporting() === 0 )
        {
            return false;
        }

        $backtrace  = debug_backtrace(); unset( $backtrace[0] );

        $errorType  = ( isset(self::$errorTypes[ $errorNumber ]) ? self::$errorTypes[ $errorNumber ] : 'Error' );
        
        $id     = ceil(rand(1000,9000));
        
        printf('<pre class="az-debug-error %s" onclick="document.getElementById(\'az-debug-trace-%s\').style.display = ( document.getElementById(\'az-debug-trace-%s\').style.display == \'none\' ? \'block\' : \'none\' );">', $errorType,$id,$id);

        printf("<b>%s</b> " , $errorType);
        print( $errorString );

        printf('<div id="az-debug-trace-%s" style="display:none">' , $id );
        print('<br/><b>From</b><br/>');
        foreach ( $backtrace as $trace )
        {
            printf("%s(%s) => <b>%s%s</b> <br/>" ,
                $trace['file'] ,
                $trace['line'] ,
                 ( isset($trace['class']) ? $trace['class'] . $trace['type'] . $trace['function'] : $trace['function'] ),
                 ( isset($trace['args'])  ? self::implodeArgs( $trace['args'] ) : '()' )
                );
            printf('<pre class="az-code">%s</pre><br/>' , self::getCode( $trace['file'] , $trace['line'] ));
        }
        print('</div>');

        printf('</pre>');

    }
       
}
?>