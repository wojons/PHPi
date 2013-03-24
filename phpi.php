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

		function sort($rules, $sort, $callback)	{

		}

		function scandir($rules, $only, $ucallback='defaultCallback')	{
			$callback[0] = function($ext, $path, $type=false, $ucallback)	{if($only == null || $ext['type']==$type)	{if(fnmatch($ext['rule'], $path)==true) {return $ucallback($path, $type); };}}; //call back for scanning
			$core['check'] = function($path){return is_dir($path);};//call back for checking if its a dir or not
			$core['list'] = function($path){return array_slice(scandir($path), 2);}; //call back to get anotehr list of directors
			return $this->_list_map_recursive($core, $callback, array('rule'=>$rules, 'type'=>$only), $ucallback); //run the stuff
		}

		function cache($max_age, $ucallback, $where='/tmp/cache')	{
			//$rules = array('max-age');
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
	function defaultCallback()	{if(func_num_args>1) {return func_get_args();}else{ return func_get_arg(0);};}
	function S(&$input)	{return new PHPi($input);}
	function I($input)	{return new PHPi($input);}
?>