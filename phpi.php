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

	class PHPi_server extends PHPi{
		private $input = null;
		private $conn_count = 0; //connection counter
		
		private $curr_sock = null;
		private $conns = array(); //an array of all the active connections to the outside world
		private $nodes = array(); // list of all the nodes we have
		private $node_socks = array(); // array of all the node sockets
		private $serv_sock = null; // the socket the server uses to bind to the out side world

		private $backlog = array(); //the backlog of accapted connections
		private $process_list = array(); // this is for active connections if node that is working on it dies we will be able to push the connection onto another machine
		private $config = array('backlog' => array('max' => 10)); //the config
		private $socket_path = "/tmp/sock/"; //path to the socket files

		/*function __contruct($input)	{$this->input = $input;}
		function init()	{}
		*/

		function __construct()	{}

		function start($address, $port, $ucallback)	{
			$this->serv_sock = stream_socket_server("tcp://".$address.":".$port."", $errno, $errstr, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN);
			//var_dump($this->serv_sock); exit();
			if(!$this->serv_sock)	{
				 die("$errstr ($errno)");
			}
			$this->_forkNodes();
			return $this;
		}

		//
		//start connecitons
		function run()	{
			while(true)	{
				$this->_accapet(); //try to accapet any connections and keep accpeting them until we cant and have to get some work done
				$this->_return(); //return any compleated jobs to the clients waiting for them
				$this->_try_backlog(); //try some backlog servers
			}
		}

		private function _accapet()	{ //accapet user connections
			while(count($this->backlog) < $this->config['backlog']['max'] && ($conn = @stream_socket_accept($this->serv_sock, 0)) !== false)	{ //keep accapeting connections until there are no more to accapet or the backlog is full
				$conn_data = $this->_read_conn($conn, ++$this->conn_count); //read the connection data
				if(($node = $this->_available_nodes(false)) == true && count($this->backlog) > 0)	{
					$this->_conn_handoff($node, $this->conn_count);
				} else { //no server can take the connnections right now so we are going to backlog it
					$this->_add_backlog($this->conn_count, $conn_data); //take the current connection number and use that as the id
				}
			}
		}

		private function _conn_handoff($node, $conn_id, $conn_data)	{ // hand off the connection to a child
			$this->sendTo_node($node, $conn_id);// write to the node socket
			$this->_set_active($node, $conn_id, $conn_data);
		}

		private function _set_active($node, $conn_id, $data)	{
			$this->process_list[$node] = array($conn_id, $data);
			return $node;
		}

		private function _rm_active($node)	{ //data not being processed any more
			unset($this->process_list[$node]);
		}

		private function _read_conn($conn)	{
			$conn_data = "";
			if(stream_socket_recvfrom($conn, 4096, STREAM_PEEK) != "")	{//check if there is anything to read
				do {
					$conn_data .= stream_socket_recvfrom($conn, 4096);
					if($conn == "")	{ break; } //grab the data waiting
				} while (true); //keep trying until we fail at something
			}

			$this->_set_conn($conn);
			return $conn_data;
		}

		private function _set_conn($conn)	{ //bound the connection to something
			$this->conns[$this->conn_count] = $conn;
		}

		private function _rm_conn($conn_id)	{ // remove the connection from lists and close it
			fclose($this->conns[$conn_id]);
			unset($this->conns[$conn_id]);
		}

		private function _try_backlog()	{ //try to get something done
			if(count($this->backlog) > 0)	{ //if there is something in the backlog lets try it
				while(($backlog = _get_backedlog()) === true && ($node = $this->_available_nodes(false) == true))	{ //get pulling backlogs till we cant
						$this->_conn_handoff($node, $backlog['conn_id'], $backlog['data']); //hand off the connection to the node to handle
				}
			}
		}

		private function _return()	{
			foreach($this->process_list as $dex => $dat)	{ //loop down the list of the connections we know being worked on
				$return = ""; //reset it like a boss
				if(stream_socket_recvfrom($this->node_socks[$dex], 4096, STREAM_PEEK) != "")	{//check if there is anything to read
					do {
						$peak = stream_socket_recvfrom($this->node_socks[$dex], 4096);
						if($peak != "")	{ $return .= $peak; } //grab the data waiting
						else { break; } //if thre is no data then break out of the looop
					} while (true); //keep trying until we fail at something

					stream_socket_sendto($this->conns[$this->nodes[$dex]['conn']], $return);//lets return this data back to the person that neededs it
				}
			}
		}

		// end connections
		//

		//
		// start the node stuff
		private function _available_nodes($multi=false)	{ //finds avaiable node to processes request
			return ($multi == false) ? array_search(0, $this->nodes) : array_keys($this->nodes, 0);
		}

		private function _sendTo_node($node, $connData)	{
			stream_socket_sendto($socket, $connData);
			$this->_node_active($node, $connNum); // set the node to active
		}

		private function _forkNodes()	{ // fork the nodes over
			for($x=0; $x<1; $x++)	{
				$pid = pcntl_fork();
				if ($pid == -1) { die('could not fork'); } 
				elseif ($pid) {
					$this->_registerNode($pid);
				} else { new PHPi_node($this->socket_path); exit(); }
			}
		}

		private function _stopNodes()	{
			foreach($this->nodes as $dex => $dat)	{
				if($dat == 0)	{ //we can stop this node because it is not doing anything
					$this->_unregisterNode($dex);
				}
			}
		}

		private function _connectTo_node($node)	{
			$this->node_socks[$node] = stream_socket_client('unix://'.$this->socket_path.$node.'.sock', $errno, $errstr, 0, STREAM_CLIENT_ASYNC_CONNECT);
		}
		
		private function _node_active($node, $conn)	{
			$this->nodes[$pid] = array('conn' => $conn, 'ts' => microtime());
		}

		private function _registerNode($pid)	{ //register the node in our list
			/*do {
				$this->node_socks[$pid] = @stream_socket_client('unix://'.$this->socket_path.$pid.'.sock', $errno, $errstr, 0, STREAM_CLIENT_ASYNC_CONNECT);
			} while ($this->node_socks[$pid] === false); //takes a little bit of time for the fork to boot up you know what i mean*/
			$this->nodes[$pid] = 0; //0 means its not doing anything; 1 means that it is currently handling some request
		}

		private function _unregisterNode($pid)	{ //remove the node from the working list
			unset($this->node[$pid]);
		}
		//end the node stuff
		//

		//
		//  start backlog stuff
		private function _add_backlog($conn_id, $data)	{ //add this connection to the backlog
			$this->backlog[] = array('conn_id' => $conn_id, 'data' => $data);
			return true;
		}

		private function _rm_backlog()	{ //only really called when pulling a backlog
			unset($this->backlog);
		}

		private function _get_backedlog()	{ //get the next item on the backlog list
			return array_shift($this->backlog); //removes first element of the array returns that and lowers the size of the array by 1
		}
		// end backlog stuff
		//	


		protected function write($text)	{
			socket_write($this->curr_sock, $text);
		}

		protected function readUntil($pattern)	{
			$recv = "";
			do { 
				print "ff";
			     $recv .= socket_read($this->curr_sock, '1400'); 
			} while(fnmatch($pattern, $recv) != true);
			return $recv;
		}

		function info()	{ //get information about this connection

		}
	}

	class PHPi_node	extends PHPi_server {
		private $routes = array(); //an array of all the routes and there callbacks
		private $pid = null; //stores the pid
		private $sock_file = null;
		private $socket = null;
		private $fp = null;

		function __construct($path)	{
			$this->pid = getmypid();
			$this->_get_socket_file($path);
			$this->socket = stream_socket_server('unix://'.$this->sock_file, $errno, $errstr, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN);
			if(!$this->socket)	{
				 die("$errstr ($errno)");
			}
			$this->run();
		}

		private function _get_socket_file($path)	{
			$this->sock_file = $path.$this->pid.'.sock';
		}

		function route($rule, $callback)	{//adds a route to the system
			$this->routes[$rule] = $callback;
		} 

		private function _route_request($request_path)	{ //use the array of routes and route the request to the correct block
			foreach($this->routes as $dex=>$dat)	{
				if(fnmatch($dex, $request_path) == true)	{
					$this->route[$dex](); //pass the request of to the user call back
					break;
				}
			}
		}

		private function _signal()	{ //checks to see if there is a signal to do exit or something like that

		}

		private function _process_request()	{/*processes the request load it with the data get it running*/

		}

		private function _gc_event()	{
			fclose($this->fp);
		}

		function run()	{
			while(true && $this->_signal() == false)	{ //wait for the connection withthe data to be sent /* soon it will be better to leave the connection open forever and just to keep reading 
				if($this->fp = @stream_socket_accept($this->socket, 0) !== false)	{ //if there is a socket connection
					$this->_process_request(); //get the request procssed
					$this->_gc_event(); //clean up the trash
				}
			}
		}
	}

	function defaultCallback()	{if(func_num_args>1) {return func_get_args();}else{ return func_get_arg(0);};}
	function S(&$input)	{return new PHPi($input);}
	function I($input)	{return new PHPi($input);}
	function W($input)	{return new PHPi_server($input);}
?>