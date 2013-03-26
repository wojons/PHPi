<?php

include 'phpi.php';
/*I('/home/www/PHPi')->scandir('*', 'file', function($path, $type)	{
	print $path."<br />";
	return $path;
});*/
/*print I('abcdef')->cache(60, function($key){
	$var = shell_exec('cat /proc/loadavg');
	print $var;
	return $var;
});*/

/*W('')->start('10.120.0.8', 10000, function($conn){
	$conn->write("hello world");
	$got = $conn->readUntil('*gg');
	$conn->write($got);
});*/

$server = new PHPi_server();
$server->start("0.0.0.0", 8900, function()	{})->run();