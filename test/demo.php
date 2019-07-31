<?php
require __DIR__ . '/../vendor/autoload.php';

use NewChain\Utils;
use NewChain\NewChain;





/*

# send new

php test/demo.php

0x7d6a273be0a7bbd61e32322dd7573afa11578ffe937eb0904529a5857a0136e5

stdClass Object
(
    [blockHash] => 0x0000000000000000000000000000000000000000000000000000000000000000
    [blockNumber] =>
    [from] => 0x7508841c47882eebb4a4a007fc3ecc6df422adc1
    [gas] => 0x557c
    [gasPrice] => 0x64
    [hash] => 0x7d6a273be0a7bbd61e32322dd7573afa11578ffe937eb0904529a5857a0136e5
    [input] => 0x5468697320697320746573742e
    [nonce] => 0x2
    [to] => 0x7508841c47882eebb4a4a007fc3ecc6df422adc1
    [transactionIndex] => 0x0
    [value] => 0x9a0b1f308ed60000
    [v] => 0x801
    [r] => 0x35f2e3bafef14dadf0f3bd1dcd9ec584e3d07de37e24daa21309bf71587dae9a
    [s] => 0x4f898704e73dd230e5b02326a4d018b31f1f2b8e609485aa7926a9485ff8834
)

https://explorer.testnet.newtonproject.org/tx/0x7d6a273be0a7bbd61e32322dd7573afa11578ffe937eb0904529a5857a0136e5

*/


$private = '6dd0ec6dc18019c87adb8d5b8ec50ff19445a12d545fde388919ea4d9da6a34e';
$fromAddress = 'NEW17zP2DnVsBqcyYAkFX3iZr4jSw8HXhvRMh1N';
$chain = new NewChain($private, $fromAddress, 'test');
try {
	$hash = $chain->send('NEW17zP2DnVsBqcyYAkFX3iZr4jSw8HXhvRMh1N', 11.1, 'This is test.');
	print_r($hash);

	$tx = $chain->getTransactionByHash($hash);
	print_r($tx);

} catch (\Exception $e) {
	var_dump($e->getMessage());
}





/*

# genNewAddress

php test/demo.php

$newAddress = Utils::genNewAddress(1007);
var_dump($newAddress);

array(4) {
  ["privateKey"]=>
  string(64) "6dd0ec6dc18019c87adb8d5b8ec50ff19445a12d545fde388919ea4d9da6a34e"
  ["publicKey"]=>
  string(128) "0966577e35117ec8ecf0d94f937a0d6aca0e36a21360b4bc68daef6325540f0741a2dd1994188a3f8d478de9988698e00c6246352aea5ff826a4c360eade489a"
  ["hexAddress"]=>
  string(40) "7508841c47882eebb4a4a007fc3ecc6df422adc1"
  ["newAddress"]=>
  string(39) "NEW17zP2DnVsBqcyYAkFX3iZr4jSw8HXhvRMh1N"
}

*/


