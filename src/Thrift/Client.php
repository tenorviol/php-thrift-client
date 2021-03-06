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

require_once $GLOBALS['THRIFT_ROOT'].'/Thrift.php';
require_once $GLOBALS['THRIFT_ROOT'].'/protocol/TBinaryProtocol.php';
require_once $GLOBALS['THRIFT_ROOT'].'/transport/TSocket.php';
require_once $GLOBALS['THRIFT_ROOT'].'/transport/TTransport.php';

class Thrift_Client {
	
	private $client_class;
	private $servers;
	private $options;
	
	private $socket;
	private $client;
	
	private static $default_options = array(
		'timeout'=>1,
	);
	
	public function __construct($client_class, $servers) {
		$this->client_class = $client_class;
		$this->servers = (array)$servers;
		$this->options = self::$default_options;
	}
	
	private function client() {
		if ($this->client === null) {
			foreach ($this->servers as $server) {
				list($host, $port) = explode(':', $server);
				$socket = new TSocket($host, $port);
				$protocol = new TBinaryProtocol($socket);
				$client = new $this->client_class($protocol);
				try {
					$socket->open();
				} catch (TException $e) {
					continue;
				}
				$this->socket = $socket;
				$this->client = $client;
				break;
			}
			if ($this->client === null) {
				throw new Exception('Unable to connect');
			}
		}
		$this->socket->setRecvTimeout($this->options['timeout']*1000);
		return $this->client;
	}
	
	public function __set($name, $value) {
		$name = strtolower($name);
		switch ($name) {
		case 'timeout':
			$this->options['timeout'] = $value <= 0 ? self::$default_options['timeout'] : $value;
			break;
		default:
			throw new InvalidArgumentException("Unsupported option, $name=$value");
		}
	}
	
	public function __call($name, $args) {
		$client = $this->client();
		return call_user_func_array(array($this->client(), $name), $args);
	}
}
