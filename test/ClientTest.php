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
require_once 'src/Thrift/Client.php';
require_once $GLOBALS['THRIFT_ROOT'].'/packages/silly/Silly.php';

class Thrift_ClientTest extends PHPUnit_Framework_TestCase {
	
	private function createClient($port) {
		return new Thrift_Client('SillyClient', "localhost:$port");
	}
	
	private function openupServer($port) {
		exec("php test/silly_server.php $port >/dev/null &");
		$client = $this->createClient($port);
		$start = microtime(true);
		$pong = false;
		while (!$pong) {
			usleep(10000);
			try {
				$pong = $client->ping();
			} catch (Exception $e) {
				if (microtime(true) - $start > 1) {
					$this->fail("Unable to confirm silly server started on port $port");
				}
			}
		}
	}
	
	private function shutdownServer($port) {
		$client = $this->createClient($port);
		$client->shutdown();
	}
	
	private $port = 9876;
	
	public function setUp() {
		$this->openupServer($this->port);
	}
	
	public function tearDown() {
		$this->shutdownServer($this->port);
	}
	
	public function testSimpleConnection() {
		$silly_client = $this->createClient($this->port);
		$result = $silly_client->rot13('foo');
		$this->assertEquals(str_rot13('foo'), $result);
	}
	
	public function testFailoverConnection() {
		$dead_port = 9875;
		$this->assertNotEquals($this->port, $random_port);
		
		$dead_client = $this->createClient($dead_port);
		try {
			$dead_client->rot13('foo');
			$this->fail('The dead client should not be working');
		} catch (Exception $e) {
			// success, carry on
		}
		
		$failover_client = new Thrift_Client('SillyClient', array("localhost:$dead_port", "localhost:$this->port"));
		
		$result = $failover_client->rot13('foo');
		$this->assertEquals(str_rot13('foo'), $result);
	}
	
	public function testTimeout() {
		$client = $this->createClient($this->port);
		$client->timeout = .2;
		$client->pause(150);
		
		$start = microtime(true);
		try {
			$client->pause(250);
		} catch (TTransportException $e) {
			$time = microtime(true) - $start;
			$expected = .2;
			$this->assertLessThan(.001, abs($expected - $time), "Timeout expected at {$expected}s, actual time = $time");
			return;
		}
		$this->fail('TTransportException expected');
	}
}
