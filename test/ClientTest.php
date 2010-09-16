<?php

$GLOBALS['THRIFT_ROOT'] = 'thrift';
require_once 'src/Thrift/Client.php';
require_once $GLOBALS['THRIFT_ROOT'].'/packages/silly/Silly.php';

class Thrift_ClientTest extends PHPUnit_Framework_TestCase {
	
	public function test() {
		$port = 9876;
		
		// start the server
		exec("php test/silly_server.php $port >/dev/null &");
		usleep(100000);  // give it a dsec
		
		$silly_client = new Thrift_Client('SillyClient', array("localhost:$port"));
		
		$send = 'foo';
		$result = $silly_client->rot13('foo');
		$this->assertEquals(str_rot13($send), $result);
		
		$silly_client->shutdown();
	}
}
