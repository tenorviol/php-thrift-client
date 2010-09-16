<?php

$GLOBALS['THRIFT_ROOT'] = 'thrift';

require_once $GLOBALS['THRIFT_ROOT'].'/Thrift.php';
require_once $GLOBALS['THRIFT_ROOT'].'/protocol/TBinaryProtocol.php';
require_once $GLOBALS['THRIFT_ROOT'].'/server/TSimpleServer.php';
require_once $GLOBALS['THRIFT_ROOT'].'/transport/TServerSocket.php';
require_once $GLOBALS['THRIFT_ROOT'].'/transport/TTransportFactory.php';
require_once $GLOBALS['THRIFT_ROOT'].'/packages/silly/Silly.php';

class SillyServer implements SillyIf {
	public $host = 'localhost';
	public $port = 9876;
	
	private $server;
	
	public function startup() {
		$processor = new SillyProcessor($this);
		$this->server = new TSimpleServer(
			$processor,
			new TServerSocket($this->host, $this->port),
			new TTransportFactory(),  // inputTransportFactory
			new TTransportFactory(),  // outputTransportFactory
			new TBinaryProtocolFactory(), // inputProtocolFactory
			new TBinaryProtocolFactory() // outputProtocolFactory
		);
		$this->server->serve();
	}
	
	public function rot13($something) {
		return str_rot13($something);
	}
	
	public function shutdown() {
		$this->server->stop();
	}
}

$server = new SillyServer();
if ($port = (int)@$_SERVER['argv'][1]) {
	$server->port = $port;
}
$server->startup();
