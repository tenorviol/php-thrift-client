php-thrift-client
=================

A Thrift client wrapper for php simplifying
the business of connecting to Thrift services.

Usage
-----

Set the global PHP Thrift root, include the Thrift_Client class,
and load the thrift generated client.

	$GLOBALS['THRIFT_ROOT'] = '/path/to/thrift/lib/php/src';
	require_once 'src/Thrift/Client.php';
	require_once $GLOBALS['THRIFT_ROOT'].'/packages/scribe/Scribe.php';

Create a new client with a list of available servers:

	$client = new Thrift_Client('Scribe', array('localhost:2112', 'localhost:2113'));

Do normal client stuff:

	$entry = new LogEntry(array('category'=>'foo', 'message'=>'bar));
	$client->Log(array($entry));

If `localhost:2112` is not available, the Thrift_Client will
automatically try `localhost:2113`.

Options
-------

Set the receive timeout in seconds:

	$client->timeout = .5

Todo
----

* Shuffle servers for round-robin load balancing
* Error and logging callbacks
* Persistent deadpool management
* Transport factory (for framed/buffered transports)
* Stream, memory and null transport support in server list
* Cleaner thrown exceptions
