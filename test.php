
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

/*
 * 
 * SERVER
 * 
 * $server = new PHPi_server();
$server->config_server("config.ini");
$server->start("0.0.0.0", 8900, function()	{});
$server->route('/meep', function($I){
	$I->write("meep");
});
$server->run();*/

$api = new APIi();
$api->route->get('*', function($api) {
	print_r(SESSIONi::$data);
	SESSIONi::$data[] = "true";
	SESSIONi::$data = array();
	return $api->route->get_route();
});
$api->session($_COOKIE['SESSIONi']);
SESSIONi::read();
print_r($api->route->route('hello world u ...', 'GET', $api));
