<?php

require_once $GLOBALS['THRIFT_ROOT'].'/Thrift.php';
require_once $GLOBALS['THRIFT_ROOT'].'/protocol/TBinaryProtocol.php';
require_once $GLOBALS['THRIFT_ROOT'].'/transport/TSocket.php';
require_once $GLOBALS['THRIFT_ROOT'].'/transport/TTransport.php';

class Thrift_Client {
	
	private $client_class;
	private $servers;
	private $options;
	
	private $client;
	
	public function __construct($client_class, array $servers, array $options = array()) {
		$this->client_class = $client_class;
		$this->servers = $servers;
		$this->options = $options;
	}
	
	private function _superUnlikelyClientMethod() {
		if ($this->client === null) {
			foreach ($this->servers as $server) {
				list($host, $port) = explode(':', $server);
				try {
					$socket = new TSocket($host, $port);
					$protocol = new TBinaryProtocol($socket);
					$client = new SillyClient($protocol);
					$socket->open();
				} catch (Exception $e) {
					continue;
				}
				$this->client = $client;
				break;
			}
		}
		return $this->client;
	}
	
	public function __call($name, $args) {
		return call_user_func_array(array($this->_superUnlikelyClientMethod(), $name), $args);
	}
}
