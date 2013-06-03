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
	SESSIONi::$data['time'] = time();
	SESSIONi::$data = array();
	return array($api->route->get_route());
});
$api->session($_COOKIE['SESSIONi']);
SESSIONi::read();
$re = $api->route->route($_SERVER['REQUEST_URI'], 'GET', $api);
if(headers_sent() == false)	{
	$api->session->__destruct(); //send public data
	header('Status: '.$re['status']);
	header('Content-type: application/json');
}
echo json_encode($re['body']);
