<?php
init('@Util.Set');

use Azera\Util\Set;
use Azera\Util\String;

class Azera
{

    /**
     * Create Alias from Classes
     * @param mixed $alias      - Alias Name
     * @param string $class     - Class name
     */
    static function alias( $alias , $class = null )
    {
        if ( is_array($alias) )
        {
            foreach ( $alias as $a => $b )
                class_alias( $b , $a );
            return;
        }
        class_alias( $class , $alias );
    }
    
    static function directories( $settings = array() )
    {
         $settings     = Set::extend(array(
            'bundle'    => null,
            'module'    => null
        ),$settings);
        
        extract( $settings );
        
        $paths     = array();

                            
        if ( $module )
            $paths[]     = array(
                'name'    => 'Module Scope',
                'path'    => APP . DS . 'Bundle' . DS . $bundle . DS . $module
            );
                
        if ( $bundle )
            $paths[]     = array(
                'name'    => 'Bundle Scope',
                'path'    => APP . DS . 'Bundle' . DS . $bundle
            );
            
        $paths     = Set::extend( $paths , array(
            array(
                'name'      => 'Application Global',
                'path'      => APP
            ),
            array(
                'name'      => 'System',
                'path'      => System
            )
        ));

        return $paths;       
    }
    
    /**
     * Scan For an pattern in a folder ( objectType )
     * @return array
     */
    static function scanDirectories( $objectType = null , $settings = array() )
    {
        $dirs     = self::directories( $settings );
        
        /** Extend Settings **/
        $settings     = Set::extend(array(
            'pattern'     => '*'
        ),$settings);
        
        extract($settings);
        
        $results     = array();
        
        foreach ( $dirs as $dir )
        {
            $results     = Set::extend( $results , glob($dir['path'] . DS . $objectType . DS . $pattern) );
        }
            
        return $results;
        
    }

    /**
     * Scan for a target file
     */
    static function dispatchFile( $path )
    {
        $route  = String::className( $path );

        /** Bundle Path **/
        if ( substr( $route , 0 , 6 ) == 'Bundle' )
        {
            $route  = 'App\\' . $route;
        }
        /** Azera System Path **/
        elseif ( substr( $route , 0 , 5 ) == 'Azera' )
        {

            $file   = Azera . DS . str_replace( NS , DS , substr( $route , 6 ) ) . '.php';

            if ( file_exists( $file ) )
                return $file;

            return false;
        }

        $file   = APP . DS . str_replace( NS , DS , substr( $route , 4 )) . '.php';

        if ( file_exists($file) )
            return $file;

        return false;

    }

    static function loadAll( $objectType , $settings = array( 'bundle' => '*' , 'module' => '*' ) )
    {
        $files  = self::scanDirectories( $objectType , $settings );
        foreach ( $files as $file )
            include_once $file;
    }

    /**
     * Register Shutdown Function
     */
    static function onEnd( $callback )
    {
        register_shutdown_function( $callback );
    }
    
}
?>