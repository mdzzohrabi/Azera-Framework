<?php
use Azera\Util\Set;
use Azera\Util\String;

class Azera
{

    /**
     * Create Alias from Classes
     * @param mixed     $alias     Alias Name
     * @param String    $class     Class name
     */
    static function alias( $alias , $class = null )
    {
        if ( is_array($alias) )
        {
            foreach ( $alias as $alias => $ref )
                class_alias( $ref , $alias , true );
            return;
        }
        class_alias( $class , $alias );
    }

    /**
     * return application packages folders 
     */
    static function directories( $settings = array() )
    {
         $settings     = Set::extend(array(
            'bundle'    => null,
            'module'    => null
        ),$settings);
        
        extract( $settings );
        
        $paths     = array();

        // Bundle\Module
        if ( $module AND $bundle )
            $paths[]     = array(
                'name'    => 'Module Scope',
                'path'    => APP . DS . 'Bundle' . DS . $bundle . DS . $module
            );
              
        // Bundle  
        if ( $bundle )
            $paths[]     = array(
                'name'    => 'Bundle Scope',
                'path'    => APP . DS . 'Bundle' . DS . $bundle
            );
        
        $paths     = Set::extend( $paths , array(
            // App
            array(
                'name'      => 'Application Global',
                'path'      => APP
            ),
            // System Folder
            array(
                'name'      => 'System',
                'path'      => System
            )
        ));

        return $paths;       
    }
    
    /**
     * Scan For an pattern in a folder ( objectType )
     * @param   string  $objectType     Pattern
     * @param   array   $settings       Find Settings
     * e.g  search for all Model(s)
     * e.g  search for Model\User
     * @return array
     */
    static function scanDirectories( $objectType = null , $settings = array() )
    {
        $dirs     = self::directories( $settings );
        
        /** Extend Settings **/
        $settings     = Set::extend(array(
            'pattern'     => '*',
            'reverse'     => false
        ),$settings);
        
        extract($settings);
        
        $results     = array();

        if ( $reverse )
        {
            $dirs   = array_reverse($dirs);
        }
        
        foreach ( $dirs as $dir )
        {
            $results     = Set::extend( $results , glob($dir['path'] . DS . $objectType . DS . $pattern) );
        }
            
        return $results;
        
    }

    /**
     * Scan for a target file
     * 
     * Azera::dispathFile( 'Azera.Acl.Model' )
     * 
     * @param   String  $path   Azera.Acl
     * @return  String  File Path
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

    /**
     * Load All Resources
     * 
     * Azera::loadAll('Config',[ 'bundle'   => 'Azera' ])
     * include Bundle\Azera\Config\*
     * 
     * @param String    $objecType  Object Type
     * @param Array     $settings   Options
     * @return void
     */
    static function loadAll( $objectType , $settings = [ 'bundle' => '*' , 'module' => '*' ] )
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