<?php
namespace Azera\Routing\Filters;

use Azera\Routing\Filter as RoutingFilter;

class Arguments extends RoutingFilter
{
	
	public function filter( array $route )
	{
		return true;
	}

}
?>