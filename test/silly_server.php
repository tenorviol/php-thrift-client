<?php
/**
 * Copyright (c) 2010, Christopher Johnson
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 *    * Redistributions of source code must retain the above copyright notice,
 *      this list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright notice,
 *      this list of conditions and the following disclaimer in the documentation
 *      and/or other materials provided with the distribution.
 *    * Neither the name of the Somo Enterprises, Inc. nor the names of its contributors
 *      may be used to endorse or promote products derived from this software
 *      without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE
 * GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
 * HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

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
