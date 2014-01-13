<?php
namespace Azera\Core;

class Object
{

	public $bundle 		= null;				# Bundle Name
	public $module 		= null;				# Module Name
	public $objectType 	= null;				# Object Type
	public $name 		= null;				# Object Name
	public $appPath 	= null;				# App Root Path
	
	public function __construct()
	{
		$this->__determineObjectBundle();
	}

	public function toString()
	{
		return get_class($this);
	}

	public function __toString()
	{
		return $this->toString();
	}

	private function __determineObjectBundle()
	{
		/**
		 *	Sample Namespaces :
		 * 	 - App\Bundle\[Azera]\[Acl]\[Controller]\[Admin]	( parts = 6 )
		 * 	 - App\Bundle\[Azera]\[Controller]\[Admin]			( parts = 5 )
		 * 	 - Bundle\	  [Azera]\[Acl]\[Controller]\[Admin]	( parts = 5 )
		 *	 - App\[Controller]\[Acl]							( parts = 3 )
		 *	 - Azera\System\[Controller]\[Admin]				( parts = 4 )
		 */

		$name 	= explode( NS , $this->toString() );
		$count 	= count($name);

		if ( $count < 3 ) return;

		/** System Namespace **/
		if ( $name[0] == 'Azera' )
		{
			$this->bundle 	= $name[1];
			$this->module 	= null;
			$this->objectType 	= $name[2];
			$this->name 	= end( $name );

			$this->appPath 	= Azera . DS . $this->bundle;		# Azera\System\...

			return;
		}

		/**
		 * Remove First App From Namespace
		 */
		if ( $name[0] == 'App' && $name[1] == 'Bundle' )
		{
			$name 	= array_slice( $name , 1 );
			$count--;
		}

		$this->name 	= end( $name );

		if ( $count == 4 )						# Without Module
		{
			$this->objectType 	= $name[2];
		}
		elseif ( $count > 3 )					# Bundle With Module
		{
			$this->module 		= $name[2];
			$this->objectType 	= $name[3];
		}
		elseif ( $count == 2 )					# App Only
		{
			$this->objectType 	= $name[0];
		}

		$this->appPath 	= APP;

		/**
		 * Bundle Informations
		 */
		if ( $name[0] != 'Bundle' ) return;

		$this->bundle 	= $name[1];

		$this->appPath 	 = APP . DS . 'Bundle' . DS . $this->bundle . ( $this->module ? DS . $this->module : null );

	}

}
?>