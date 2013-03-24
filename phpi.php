<?php

	class PHPi{

		private $input = null;

		function __construct(&$input)	{$this->init($input);}

		function sum($rules, $callback='defaultCallback')	{ //lets do some array summing magic
			if(is_array($this->input) == true)	{ //summing array
				$sum = function(&$x, $data, &$log, $ext)	{ //make sure that $data becomes referance at some time
					$ext_count = count($ext);
					if($ext_count <= count($log)+1)	{
						foreach($ext as $dex => $dat)	{ //loop trough the rules
							if(($dat == $log[$dex] || $dat == $x) || ($dat == "*" && isset($log[$dex]) == true))	{ //check for matching
								if(($ext_count-1) == $dex)	{ //if we are on the last rule all good
									return $data[$x];
								}
							} else {
								return null; //return null
							}
						}
					}
				};
				return  $callback(array_sum($this->_array_map_recursive($this->input, $sum, $rules))); //once we run through the loop time to sum the vailes in a single array
			}
		}

		function avg($rules, $callback='defaultCallback')	{
			if(is_array($this->input) == true)	{
				$avg = function(&$x, $data, &$log, $ext)	{
					$ext_count = count($ext);
					if($ext_count <= count($log)+1)	{
						foreach($ext as $dex => $dat)	{ //loop trough the rules
							if(($dat == $log[$dex] || $dat == $x) || ($dat == "*" && isset($log[$dex]) == true))	{ //check for matching
								if(($ext_count-1) == $dex)	{ //if we are on the last rule all good
									return $data[$x];
								}
							} else {
								return null; //return null
							}
						}
					}
				};
				$clean = array_diff($this->_array_map_recursive($this->input, $avg, $rules),array(null));
				return $callback(array_sum($clean)/count($clean));
			}
		}

		function count($rules, $callback='defaultCallback')	{
			if(is_array($this->input))	{
				$count = function(&$x, $data, &$log, $ext){
					$ext_count = count($ext);
					if($ext_count <= count($log)+1)	{
						foreach($ext as $dex => $dat)	{ //loop trough the rules
							if(($dat == $log[$dex] || $dat == $x) || ($dat == "*" && isset($log[$dex]) == true))	{ //check for matching
								if(($ext_count-1) == $dex)	{ //if we are on the last rule all good
									return 1;
								}
							} else {
								return null; //return null
							}
						}
					}
				};
				return $callback(array_sum($this->_array_map_recursive($this->input, $count, $rules)));
			}
		}

		function fopen($callback, $close=true, $file=null)	{
			$file = ($file==null) ? $this->input : $file;
			if(file_exists($file) == true)	{
				$proc = proc_open("php", array(array('file', $file, 'r'), array('file', $file, 'w'), array('file', '/tmp/error', 'a')), $pipes, "/tmp", array('some_option' => 'aeiou'));
				if (is_resource($proc)) {
					var_dump($pipes);
					$callback($pipes[0],$pipes[1]);
				}
				if($clode == true)	{proc_close($proc);}
				else {return "hi";}
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

		private function _array_map_recursive(&$array, $callback, $ext=null)	{
			foreach($array as $dex=>$dat)	{
				$history = array(); $x = $dex; $ref = array(&$array); $pointer = array(null); //set values for next loop
				$z = 0; $root = true;
				while(true)	{

					$result[] = $callback($x, end($ref), $history, $ext); // hit the callback

					if(is_array(end($ref)[$x]) == true)	{ //do we need to go deeper into the beast
						$history[] = $x; $ref[] =& end($ref)[$x]; $pointer[] = $z; //add a level to history add a new ref and add a new poiner
						$keys = array_keys(end($ref)); $x=$keys[0]; $z=0; //get a list of keys for this part of the array and set x to taht value
						$root = false; //we are not root level;
					} else {
						$z++; //update where we are in the keys
						if(isset($keys[$z]) == FALSE)	{ //no more elements lets move back 1 point
							$x = end($history);
							if(count($history) > 2)	{
								$history = array_pop($history); $ref = array_pop($ref); $pointer = array_pop($history); //delete the last elemenets no longer needed
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

		private function _popen()	{

		}

	}
	function defaultCallback()	{if(func_num_args>1) {return func_get_args();}else{ return func_get_arg(0);};}
	function S(&$input)	{return new PHPi($input);}
	function M(&$input)	{return new PHPi($input);}
	function W($input)	{return new PHPi($input);}
	$test = array('touch' => 'me', 'meep' => array('people' => 'ixsa', 'samsaung' => array('tv', 'laptop')), 'no' => 'girls');
	$num_test = array(array('t' => 4),array('t' => 4));
	print_r(S($num_test)->sum(array('*', 't')));
	print_r(S($num_test)->avg(array('*', 't'),function($avg)	{
		print "people ".$avg;
	}));
	/*W('/tmp/testing')->fopen(function($write, $read){
		fwrite($write, "fdasfads");
	});*/

	//S($test)->sum(null);
?>