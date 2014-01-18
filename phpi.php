<?php

class PHPi{

	private $input = null;

	function __construct(&$input)	{$this->init($input);}

	function sum($rules, $ucallback='defaultCallback')	{ //lets do some array summing magic
		if(is_array($this->input) == true)	{ //summing array
			$callback[0] = function(&$x, &$data, &$log, $ext, $callback)	{ //make sure that $data becomes referance at some time
				$ext_count = count($ext);
				if($ext_count <= count($log)+1)	{
					foreach($ext as $dex => $dat)	{ //loop trough the rules
						if(($dat == $log[$dex] || $dat == $x) || ($dat == "*" && isset($log[$dex]) == true))	{ //check for matching
							if(($ext_count-1) == $dex)	{ //if we are on the last rule all good
								return $callback($data[$x]);
							}
						} else {
							return null; //return null
						}
					}
				}
			};
			return array_sum($this->_array_map_recursive($this->input, $callback, $rules, $ucallback)[0]); //once we run through the loop time to sum the vailes in a single array
		}
	}

	function avg($rules, $ucallback='defaultCallback')	{
		if(is_array($this->input) == true)	{
			$callback[0] = function(&$x, $data, &$log, $ext, $callback)	{
				$ext_count = count($ext);
				if($ext_count <= count($log)+1)	{
					foreach($ext as $dex => $dat)	{ //loop trough the rules
						if(($dat == $log[$dex] || $dat == $x) || ($dat == "*" && isset($log[$dex]) == true))	{ //check for matching
							if(($ext_count-1) == $dex)	{ //if we are on the last rule all good
								return $callback($data[$x]);
							}
						} else {
							return null; //return null
						}
					}
				}
			};
			$clean = array_diff($this->_array_map_recursive($this->input, $callback, $rules, $ucallback)[0],array(null));
			return array_sum($clean)/count($clean);
		}
	}

	function count($rules, $ucallback='defaultCallback')	{
		if(is_array($this->input))	{
			$callback[0] = function(&$x, $data, &$log, $ext, $callback){
				$ext_count = count($ext);
				if($ext_count <= count($log)+1)	{
					foreach($ext as $dex => $dat)	{ //loop trough the rules
						if(($dat == $log[$dex] || $dat == $x) || ($dat == "*" && isset($log[$dex]) == true))	{ //check for matching
							if(($ext_count-1) == $dex)	{ //if we are on the last rule all good
								return $callback(true);
							}
						} else {
							return null; //return null
						}
					}
				}
			};
			return array_sum($this->_array_map_recursive($this->input, $callback, $rules, $ucallback)[0]);
		}
	}

	//
	// start loop codes
	function each($callback)	{ //same as a normal php foreach loop
		while(next($this->input))	{
			$var = each($this->input);
			$result[$var[0]] = $callback($var[0], $var[1]);
		}
	}

	function upto($max, $callback)	{ //same as the ruby upto
		for($x=$this->input; $x<$max; $x++)	{
			$callback($x);
		}
	}



	//
	// end loop code
	//

	/*function for($start, $stop, $diff, $callback)	{
		for($x=$start, $x)
	}*/

	function sort($rules, $sort, $callback)	{

	}

	function scandir($rules, $only, $ucallback='defaultCallback')	{
		$callback[0] = function($ext, $path, $type=false, $ucallback)	{if($only == null || $ext['type']==$type)	{if(fnmatch($ext['rule'], $path)==true) {return $ucallback($path, $type); };}}; //call back for scanning
		$core['check'] = function($path){return is_dir($path);};//call back for checking if its a dir or not
		$core['list'] = function($path){return array_slice(scandir($path), 2);}; //call back to get anotehr list of directors
		return $this->_list_map_recursive($core, $callback, array('rule'=>$rules, 'type'=>$only), $ucallback); //run the stuff
	}

	function cache($max_age, $ucallback, $where='/tmp/cache')	{
		if(file_exists($where.'/'.$this->input) == true && time()-filemtime($where.'/'.$this->input) < $max_age)	{
			return file_get_contents($where.'/'.$this->input);
		} else {
			if(file_exists($where) == false)	{
				if(mkdir($where, 755, true) == false)	{
					return "Permission Denied on folder creation";
				} // make folder
			}
			file_put_contents($where.'/'.$this->input, $ucallback($key));
		}

	}

	function init(&$input)	{
		$this->clear(); //make sure we have a clean slate
		$this->input =& $input; //yes we just dubble referanced something what now &!%(#
	}
	function clear()	{
		$this->input = null;
	}

	function printAll($x, $data, $log, $ext=null)	{
		echo $x," + ".implode('/', $log),"val:".$data[$x],"<br />";
	}

	private function _array_map_recursive(&$array, $callback, $ext=null, $ucallback='defaultCallback')	{
		$cb_size = count($callback);
		foreach($array as $dex=>$dat)	{
			$history = array(); $x = $dex; $ref = array(&$array); $pointer = array(null); $dref=&$ref[0];//set values for next loop
			$z = 0; $root = true;
			while(true)	{

				for($cb=0; $cb<$cb_size; $cb++)	{
					$result[$cb][] = $callback[$cb]($x, $dref, $history, $ext, $ucallback); // hit the callback
				}

				if(is_array(end($ref)[$x]) == true)	{ //do we need to go deeper into the beast
					$history[] = $x; $ref[] =& $ref[count($ref)-1][$x]; $pointer[] = $z+1; $dref=&$ref[count($ref)-1];//add a level to history add a new ref and add a new poiner
					$keys = array_keys(end($ref)); $x=$keys[0]; $z=0; //get a list of keys for this part of the array and set x to taht value
					$root = false; //we are not root level;
				} else {
					$z++; //update where we are in the keys
					if(isset($keys[$z]) == FALSE)	{ //no more elements lets move back 1 point
						$x = end($history);
						if(count($history) > 2)	{
							array_pop($history); array_pop($ref); $pointer = array_pop($history); $dref=&$ref[count($ref)-1];//delete the last elemenets no longer needed
							$keys = array_keys(end($ref)); $z=end($pointer); $x=$keys[$z];//get a list of keys
						} else {
							break;
						}
					}
					elseif($root==true)	{ //check to see if we are still root level
						break;
					} else { //nothing else to do but to up the counter
						$x=$keys[$z];
					}
				}

			}
		}
		return $result;
	}

	private function _list_map_recursive($core, $callback, $ext, $ucallback)	{
		$items = array_values($core['list']($this->input)); $item_size = count($items);
		$history = array(''); $x = 0; $list = array($items); $pointer = array();
		$cb_size = count($callback);
		while(true)	{
			if($core['check']($this->input.implode('/', $history).'/'.$items[$x]) == true && $x<$item_size)	{
				for($cb=0; $cb<$cb_size; $cb++){
					$result[$cb][] = $callback[$cb]($ext, $this->input.implode('/', $history).'/'.$items[$x], 'dir', $ucallback); // hit the callback
				}
				$history[] = $items[$x]; $items = array_values(array_slice($core['list']($this->input.implode('/', $history).'/'),2));  $pointer[] = $x+1; $x=0; $list[] = $items; $item_size = count($items);
			} else {
				if($x >= $item_size || $item_size == 0)	{ //need to back trace one
					if(count($history) == 1)	{ //i guess we are done no where else to go
						break;
					} else {
						array_pop($history); $x=end($pointer); array_pop($pointer); array_pop($list); $items = end($list); $item_size = count($items);
						if($history == null)	{
							$hisotry = array(''); $pointer=array();
						}
					}
				} else {
					for($cb=0; $cb<$cb_size; $cb++){
						$result[$cb][] = $callback[$cb]($ext, $this->input.implode('/', $history).'/'.$items[$x], 'file', $ucallback); // hit the callback
					}
					$x++; //move the pointers
				}
			}
		}
		return $result[0];
	}

	private function _popen()	{

	}

}

class ASYNCi extends PHPi{
	private static $pipes = array();
	private static $event_path = "";

	function __construct()	{
		if(file_exists("/dev/shm/ASYNCi/") == False)	{ mkdir("/dev/shm/ASYNCi/", 775, true); }
		self::$event_path = "/dev/shm/ASYNCi/".getmypid().".";
	}

	static function run($cmd, $cb) {
		//$event_file = $this->events_path.count($pipes).'.event';
		while(true)	{
			$event_id = base_convert(mt_rand(), 10, 36);
			if(isset(self::$pipes[$event_id]) == false)	{ //lets use this id
				if (file_exists(self::$event_path.$event_id.'.event') == true) { unlink(self::$event_path.$event_id.'.event'); }
				//print $cmd.' && touch '.$this->event_path.$event_id.'.event\n'; //exit();
				self::$pipes[(string)$event_id] = array('handle' => popen($cmd.' && touch '.self::$event_path.$event_id.'.event', 'r'), 'callback' => $cb);
				break;
			}
		}
		return $event_id;
	}

	static function attempt($event_id)	{
		//print $this->event_path.$event_id.'.event\n';
		if(file_exists(self::$event_path.$event_id.'.event') == true)	{
			return self::callback($event_id);
		}
		return False;
	}

	static function callback($event_id)	{
		unlink(self::$event_path.$event_id.'.event');
		return call_user_func_array(self::$pipes[$event_id]['callback'], array(stream_get_contents(self::$pipes[$event_id]['handle'])));
	}

	static function loop($iter=1)	{
		for($x=0; $x<$inter; $x++)	{
			foreach(glob(self::$event_path.'*') as $dex => $dat)	{
				self::$pipes[$event_id]['data'] = self::callback($dat);
			}
		}
	}

	static function waitFor($events, $count=0)	{ //wait for some events to finish before continuing
		$count = ($count > count($events)) ? count($events) : $count; //make sure we are not waiting for me then we have
		while (true)	{
			$done = 0;
			foreach($events as $event_id)	{
				if(isset(self::$pipes[$event_id]['data']) || self::$pipes[$event_id] == true || file_exists(file_exists(self::$event_path.$event_id.'.event')))	{
					if(file_exists(self::$event_path.$event_id.'.event') == True)	{
						self::$pipes[$event_id]['data'] = self::callback($event_id);
					}
					$done++;
				}
			}
			if($done >= $count) { break; } //done waited for what we needed
			self::loop(1); //run the loop again
		}

	}

	function wait()	{}

	function removeEvent($event_id)	{
		unset(self::$pipes[$event_id]);
	}

}

class APIi extends PHPi{
	public $body = null;
	public $input = null;
	public $output = null;
	public $debug = null;
	private $modes = array();

	function __construct(){
		$this->route(); //start the routing
		$this->session();
		//print_r($_SERVER);
		if($_SERVER['HTTP_CONTENT_TYPE'] == 'application/json' && $_SERVER['HTTP_CONTENT_LENGTH'] > 0)	{
			//var_dump(file_get_contents("php://input"));
			$this->input = json_decode(file_get_contents("php://input"), true);
			if(json_last_error() != JSON_ERROR_NONE) {
				die('malformed json');
			}

		}
		elseif($_SERVER['REQUEST_METHOD'] != "GET") {
			print $_SERVER['HTTP_CONTENT_TYPE'];
		}
	}
	
	function decideType()	{
		return explode('/',$_SERVER['REQUEST_URI'])[1];
	}

	function setMode($mode, $set=True) {
		$this->modes[$mode] = $set;
	}

	function getMode($mode) {
		return $this->modes[$mode];
	}

	function debug($msg, $key) {
		if($this->modes['debug']) {
			$this->debug[] = array('key' => $key, 'msg' => $msg);
		}
	}

	function log() {
		return True;
	}

	function obj($name, $obj)	{
		$this->obj[$name] = $obj;
	}

	function route()	{
		$this->route = new ROUTEi();
	}
	function session(){
		$this->session = new SESSIONi();
		//SESSIONi::read();
	}
	function database(){}
	//function cache(){}
	function config(){}
	function setBody($body)	{
		$this->body = $body;
	}
}

class ROUTEi extends PHPi{
	private $routes = array();
	private $route = array(); //currrent route
	private $routeList = array(); //list of routes

	function __construct($config=null){ //if there is a config for it thats cool to

	}
	
	function isVaildRoute($name, $path) {
		if(!isset(configHandler::$public[$path][$name]) || !file_exists(ROOT_PATH.$path."/route/route.".$name.".php")) {
			return False;
		}
		return True;
	}

	function route($route, $type='GET', &$api){ //route the call to where it needs to go
		$this->route = $route;
		$this->routeList[] = $route;

		foreach($this->routes as $dex=>$dat){
			if(fnmatch($dex, $route) == true)	{
				if(isset($dat[$type]) == True)	{
					if(is_callable($dat[$type]) == true)	{
						return array('status' => 200, 'body' => (is_string($dat[$type]) == true) ? call_user_func_array($dat[$type], array($route, $body)) : $dat[$type]($api));
					}
				} else {

					return array('status' => 404);
				}
			}
		}
	}


	//add routing rules
	function add($types, $rule, $cb){
		foreach($types as $dat)	{
			$this->routes[$rule][$dat] = $cb;
		}
	}

	function get($rule, $cb)	{$this->add(array('GET'), $rule, $cb);}
	function post($rule, $cb) {$this->add(array('POST'), $rule, $cb);}
	function put($rule, $cb)	{$this->add(array('PUT'), $rule, $cb);}
	function delete($rule, $cb) {$this->add(array('delete'), $rule, $cb);}

	// forground helpers
	function get_route()	{
		return $this->route;
	}

	function set_status($code)	{
		$this->status_code = $code;
	}

	// background helpers
	function getLast_routeKey()	{
		return count($this->routeList)-1;
	}

	function getLast_route()	{
		return $this->routeList[$this->getLast_routeKey()];
	}

	function delLast_route()	{
		unset($this->routeList[$this->getLast_routeKey()]);
	}

	function routeUpLevel()	{ //route up one level
		$this->delLast_route();
		$this->route = $this->getLast_route();
	}
}

class SESSIONi extends PHPi{
	private static $path = "/dev/shm/SESSIONi/";
	private static $ext = ".sess";
	private static $id = "";
	private static $file = "";
	private static $open = True;

	public static $data = array();

	function __construct()	{
		//self::$id = $id;
		self::$data =& $_SESSION['data'];
	}

	static function isLoggedIn() {
		return (is_string(self::$data['user_id'])) ? true : false;
	}
	
	static function oneTime() {
		$tmp = self::$data;
		session_destroy();
		self::$data = $tmp;
	}

	/*function create()	{
		if(file_exists(self::$path) == false)	{ mkdir(self::$path, 0775, true); } //create the folder if its not there
		while(true)	{
			self::$id = base_convert(mt_rand()*mt_rand(), 10, 36);
			if(array_search(self::$id, scandir(self::$path)) === False)	{

				if(file_put_contents(self::$path.self::$id.self::$ext, getmypid()) == strlen(getmypid())  && file_get_contents(self::$path.self::$id.self::$ext) == getmypid())	{
					break;
				}
			}
		}
		file_put_contents(self::$path.self::$id.self::$ext, "{}");
		setcookie("SESSIONi", self::$id, time()+1209600, "/");
		return self::$id;
	}

	static function read() {
		if(file_exists(self::$path.self::$id.self::$ext) == false)	{
			self::create();
		}
		$data = json_decode(file_get_contents(self::$path.self::$id.self::$ext), true);

		if(json_last_error() != JSON_ERROR_NONE)	{
			self::delete();
			self::create();
			return self::read();
		}
		setcookie("SESSIONi", self::$id, time()+1209600, "/");
		self::$data = $data;
		return $data;
	}

	static function write()	{
		if(is_array(self::$data) == false)	{
			self::$data = array(self::$data); // make it an arrray
		}
		$data = json_encode(self::$data);
		while(true)	{
			if(file_put_contents(self::$path.self::$id.self::$ext, $data) == strlen($data))	{
				break;
			}
			sleep(1);
		}
		return true;
	}

	function delete() {
		unlink(self::$path.self::$id.self::$ext);
		self::$id = null;
	}

	function __destruct()	{
		if (self::$open == true)	{
			self::write();
			self::$open = false;
		}
	}*/
}

class PHPi_server extends PHPi{
	private $input = null;
	private $conn_count = 0; //connection counter

	private $curr_sock = null;
	private $conns = array(); //an array of all the active connections to the outside world
	private $nodes = array(); // list of all the nodes we have
	private $node_socks = array(); // array of all the node sockets
	private $serv_sock = null; // the socket the server uses to bind to the out side world
	private $pending_conn = array(); //conections that are still being read

	private $backlog = array(); //the backlog of accapted connections
	private $process_list = array(); // this is for active connections if node that is working on it dies we will be able to push the connection onto another machine
	private $config = array('server' => array('max-backlog' => 10)); //the config
	//private $socket_path = "/tmp/sock/"; //path to the socket files
	private $in_read_list = array(); //connections that are not being done read yet

	private $routes = array();
	private $filters = array();

	/*function __contruct($input)	{$this->input = $input;}
	function init()	{}
	*/

	function __construct()	{}

	function start($address, $port, $ucallback)	{

		if(file_exists($this->config['server']['socket_path']) == false)	{
			mkdir($this->config['server']['socket_path'], true);
		}
		$this->serv_sock = stream_socket_server("tcp://".$this->config['server']['address'].":".$this->config['server']['port']."", $errno, $errstr, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN);
		//var_dump($this->serv_sock); exit();
		if(!$this->serv_sock)	{
			 die("$errstr ($errno)");
		}
		return $this;
	}

	function config_server($path, $type="file")	{
		if($type == "file")	{
			if(file_exists($path) == true)	{
				if(($config = parse_ini_file($path, true)) == false)	{
					die("There was an issue parsing your config file");
				}
				print_r($config);
				$this->config = array_merge_recursive($this->config, $config);
				return true;
			} else {
				die("The given config path does not exist: ".$path);
			}
		}


	}

	//
	//start connecitons
	function run()	{
		$this->_forkNodes();
		while(true)	{
			$this->_accapet(); //try to accapet any connections and keep accpeting them until we cant and have to get some work done
			$this->_return(); //return any compleated jobs to the clients waiting for them
			$this->_incomming(); //read connections in
			$this->_try_backlog(); //try some backlog servers
			print "\n";
		}
	}

	private function _accapet()	{ //accapet user connections
		//print "\nstart accepet loop: ".time();
		while(count($this->backlog) < $this->config['server']['max-backlog'] && ($conn = @stream_socket_accept($this->serv_sock, .01)) !== false)	{ //keep accapeting connections until there are no more to accapet or the backlog is full
			stream_set_blocking($conn, 0); //set this stream to non blocking
			//print "\nstart accpet: ".time();
			$conn_data = $this->_read_conn($conn, ++$this->conn_count); //read the connection data
			if($conn_data != false)	{
				//print "\n finish reading conn: ".time();
				$this->_handle_conn($this->conn_count, $conn_data);
				//print "\nend accpet".time();
			} else {
				$this->pending_conn[$this->conn_count] = $conn;
			}
		}
		//print "\nend accept loop: ".time();
	}

	private function _incomming()	{
		//print "\nstart incomming loop: ".time();
		foreach($this->in_read_list as $dex => $dat) {
			$read_return = $this->_read_conn($this->pending_conn[$dex], $dex, 10);
			if($read_return != false) { //lets process this data
				$this->_handle_conn($dex, $dat.$read_return);
				unset($this->in_read_list[$dex]); //remove this from the pending reading list
			}
		}
		//print "\nend incomming loop: ".time();
	}

	private function _return(){
		//print "\nstart return loop".time();
		foreach($this->process_list as $dex => $dat){ //loop down the list of the connections we know being worked on
			$payload = ""; //reset it like a boss
			if(stream_socket_recvfrom($this->node_socks[$dex], $this->config['server']['write_packet_size'], STREAM_PEEK) != ""){//check if there is anything to write
				do {
					$peak = stream_socket_recvfrom($this->node_socks[$dex], $this->config['server']['write_packet_size']);
					//print var_dump($peak);
					if($peak != "")	{ //grab the data waiting
						$payload .= $peak;
					}
					else { break; } //if thre is no data then break out of the looop
				} while (stream_socket_recvfrom($this->node_socks[$dex], $this->config['server']['write_packet_size'], STREAM_PEEK) != ""); //keep trying until we fail at something
				if($payload != "") { // send data to client
					stream_socket_sendto($this->conns[$this->nodes[$dex]['conn']], $payload);//lets return this data back to the person that neededs it
					//var_dump($payload);
					if(@fwrite($this->node_socks[$dex], "\0") == false)	{ //null bit at end close connection
						$this->_rm_active($dex);
						$this->_rm_conn($this->nodes[$dex]['conn']); //close connection to client
						$this->_node_inactive($dex); //close the node
					}
				}
			}
		}
		//print "\nend return loop".time();
	}

	private function _handle_conn($id, $data){
		if(($node = $this->_available_nodes(false)) == true && count($this->backlog) == 0){
			//print "\nstart handoff: ".time();
			$this->_conn_handoff($node, $id, $data);
			//print "\nend handoff: ".time();
		} else { //no server can take the connnections right now so we are going to backlog it
			//print "\nstart backlog entery: ".time();
			$this->_add_backlog($id, $data); //take the current connection number and use that as the id
			//print "\nend backlog write: ".time();
		}
	}

	private function _try_backlog(){ //try to get something done
		print "\nbacklog: ".count($this->backlog);
		if(count($this->backlog) > 0){ //if there is something in the backlog lets try it
			while(($backlog = $this->_get_backlog()) === true && ($node = $this->_available_nodes(false) == true)){ //get pulling backlogs till we cant
					$this->_conn_handoff($node, $backlog['conn_id'], $backlog['data']); //hand off the connection to the node to handle
			}
		}
	}

	private function _conn_handoff($node, $conn_id, $conn_data)	{ // hand off the connection to a child
		$this->_sendTo_node($node, $conn_id, $conn_data);// write to the node socket
		$this->_set_active($node, $conn_id, $conn_data);
	}

	private function _set_active($node, $conn_id, $data){
		$this->process_list[$node] = array($conn_id, $data);
		return $node;
	}

	private function _rm_active($node){ //data not being processed any more
		unset($this->process_list[$node]);
	}

	private function _read_conn($conn, $conn_id=null, $loops=1){
		//print "\nstart reading conn: ".time();
		$x=0; $conn_data = "";
		if(stream_socket_recvfrom($conn, $this->config['server']['read_packet_size'], STREAM_PEEK) != ""){//check if there is anything to read
			do {
				//print "\nreading conn round {$x}: ".time(); $x++;
				$new = stream_socket_recvfrom($conn, $this->config['server']['read_packet_size']); $conn_data .= $new; //grab the data waiting
				//print "\n"; var_dump($new);
				if(substr($conn_data, -4) == "\r\n\r\n"){ //see if the end of a header request
					$this->_set_conn($conn, $conn_id);
					return $conn_data;
				}
				elseif($new == "")	{
					break;
				} //handle when we are no longer getting any data
			} while ($x < $loop); //keep trying until we fail at something
		}
		//handle bad connections like when the user closes there broswer on u like a little bitch

		$this->in_read_list[$conn_id] = (isset($this->in_read_list[$conn_id]) == true) ? $this->in_read_list[$conn_id].$conn_data : $conn_data;
		return false; //let it know its not done yet
	}

	private function _set_conn($conn, $conn_id){ //bound the connection to something
		$this->conns[$conn_id] = $conn;
	}

	private function _rm_conn($conn_id){ // remove the connection from lists and close it
		fclose($this->conns[$conn_id]);
		unset($this->conns[$conn_id]);
	}
	// end connections
	//

	//
	// start the node stuff
	private function _available_nodes($multi=false){ //finds avaiable node to processes request
		return ($multi == false) ? array_search(0, $this->nodes) : array_keys($this->nodes, 0);
	}

	private function _sendTo_node($node, $connNum, $connData){
		if($this->_connectTo_node($node)){ //connect to a node
			stream_socket_sendto($this->node_socks[$node], $connData);
			$this->_node_active($node, $connNum); // set the node to active
		}
	}

	private function _forkNodes(){ // fork the nodes over
		for($x=0; $x<$this->config['server']['fork_nodes']; $x++){
			$pid = pcntl_fork();
			if ($pid == -1){
				die('could not fork');
			}
			elseif ($pid) {
				$this->_registerNode($pid);
			} else {
				new PHPi_node($this->config['server']['socket_path'], $this->routes);
				exit();
			}
		}
	}

	private function _node_inactive($node){ //close the soceket to the node on this end and set it to not active
		unset($this->node_socks[$node]);
		$this->_registerNode($node);
	}

	private function _stopNodes()	{
		foreach($this->nodes as $dex => $dat){
			if($dat == 0)	{ //we can stop this node because it is not doing anything
				$this->_unregisterNode($dex);
			}
		}
	}

	private function _connectTo_node($node){
		$this->node_socks[$node] = stream_socket_client('unix://'.$this->config['server']['socket_path'].$node.'.sock', $errno, $errstr, 0, STREAM_CLIENT_ASYNC_CONNECT);
		return true;
	}

	private function _node_active($node, $conn){
		$this->nodes[$node] = array('conn' => $conn, 'ts' => microtime());
	}

	private function _registerNode($pid){ //register the node in our list
		/*do {
			$this->node_socks[$pid] = @stream_socket_client('unix://'.$this->socket_path.$pid.'.sock', $errno, $errstr, 0, STREAM_CLIENT_ASYNC_CONNECT);
		} while ($this->node_socks[$pid] === false); //takes a little bit of time for the fork to boot up you know what i mean*/
		$this->nodes[$pid] = 0; //0 means its not doing anything; 1 means that it is currently handling some request
	}

	private function _unregisterNode($pid){ //remove the node from the working list
		unset($this->node[$pid]);
	}
	//end the node stuff
	//

	//
	//  start backlog stuff
	private function _add_backlog($conn_id, $data){ //add this connection to the backlog
		$this->backlog[] = array('conn_id' => $conn_id, 'data' => $data);
		return true;
	}

	private function _rm_backlog(){ //only really called when pulling a backlog
		unset($this->backlog);
	}

	private function _get_backlog(){ //get the next item on the backlog list
		return array_shift($this->backlog); //removes first element of the array returns that and lowers the size of the array by 1
	}
	// end backlog stuff
	//

	//
	// start node processing tools
	public function route($url, $ucallback)	{
		$this->routes[$url] = $ucallback;
	}
	// end 	node processing tools
	//


	protected function write($text){
		socket_write($this->curr_sock, $text);
	}

	protected function readUntil($pattern){
		$recv = "";
		do {
			print "ff";
		     $recv .= socket_read($this->curr_sock, '1400');
		} while(fnmatch($pattern, $recv) != true);
		return $recv;
	}

	function info(){ //get information about this connection

	}
}

class PHPi_node	extends PHPi_server {
	private $pid = null; //stores the pid
	private $sock_file = null;
	private $socket = null;
	private $fp = null;
	private $routes = array();
	private $JIT = array(); //some things we want to not compile unless we have to but save the compiled version once done
	public $headers = array(); //the headers of the connection

	function __construct($path, $routes){
		$this->pid = getmypid();
		$this->_get_socket_file($path);
		$this->routes = $routes; //copy them from the parent
		$this->socket = stream_socket_server('unix://'.$this->sock_file, $errno, $errstr, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN);
		if(!$this->socket)	{
			 die("$errstr ($errno)");
		}
		$this->run();
	}

	public function write($str){
		stream_socket_sendto($this->fp, $str);
	}

	public function run(){
		while(true && $this->_signal() == false){ //wait for the connection withthe data to be sent /* soon it will be better to leave the connection open forever and just to keep reading
			if(($this->fp = @stream_socket_accept($this->socket, 300)) !== false){ //if there is a socket connection
				//print "yo\n";
				$this->_receive_request(); //get the request procssed
				$this->_gc_event(); //clean up the trash
			}
		}
	}

	//
	// base stuff needed to write application based on header info
	public function url(){
		return $this->_add_jit('request_url', $this->header['Request Url']);
	}

	public function method()	{
		return $this->_add_jit('request_method', $this->header['Request Method']);
	}

	public function setcookie()	{

	}

	public function getcookie($key=null)	{
	}
	// end base stuff
	//

	private function _add_jit($key, $value, $override=false)	{
		if(isset($this->JIT[$key]) == false || $override == true)	{
			$this->JIT[$key] = $value;
			return $value;
		}
		return $value;
	}

	private function _get_socket_file($path){
		$this->sock_file = $path.$this->pid.'.sock';
	}

	private function _route_request($request_path){ //use the array of routes and route the request to the correct block
		print_r($this->headers);
		foreach($this->routes as $dex=>$dat)	{
			if(fnmatch($dex, $request_path) == true)	{
				return $dat($this); //pass the request of to the user call back
				break;
			}
		}
		//did not work so lets put this one here
		$date = @getdate(time());
		$this->write("<h1>Hello World</h1><h2>It is currently: {$date['weekday']},  {$date['month']} {$date['mday']}, {$date['year']} @ {$date['hours']}:{$date['minutes']}</h2>");
		print "\nhello world: ".time();
		return true;
	}

	private function _signal(){ //checks to see if there is a signal to do exit or something like that

	}

	private function _receive_request(){ //get the http request
		$raw = "";
		if(stream_socket_recvfrom($this->fp, 4096, STREAM_PEEK) != ""){//check if there is anything to read
			do {
				$new = stream_socket_recvfrom($this->fp, 4096); $raw .= $new; //print json_encode(array($raw)); //grab the data waiting
				if(substr($raw, -4) == "\r\n\r\n")	{ //see if the end of a header request
					return $this->_process_request($raw);
				}
				elseif($new == "")	{ break; } //handle when we are no longer getting any data
			} while (true); //keep trying until we fail at something
		}
		//handle bad connections like when the user closes there broswer on u like a little bitch
	}

	private function _process_request($payload)	{/*processes the request load it with the data get it running*/
		$this->headers = http_parse_headers($payload);
		return $this->_route_request($this->headers['Request Url']);
	}

	private function _gc_event(){
		//$this->write("\0");
		unset($this->headers);
		stream_socket_shutdown($this->fp, STREAM_SHUT_RDWR);
		fclose($this->fp);
	}
}

function defaultCallback()	{if(func_num_args>1) {return func_get_args();}else{ return func_get_arg(0);};}
function S(&$input)	{return new PHPi($input);}
function I($input)	{return new PHPi($input);}
function W($input)	{return new PHPi_server($input);}
