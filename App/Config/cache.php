<?php
use Azera\Cache\Cache;

/** Database Models Cache **/
Cache::config('models',array(
	'path'	=> CACHE . DS . 'models'
));
?>