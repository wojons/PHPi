<?php

include 'phpi.php';
I('/home/www/PHPi')->scandir('*', 'file', function($path, $type)	{
	return $path;
});