<?php

require_once __DIR__.'/vendor/autoload.php';

use Defuse\Crypto\Key as CryptoKey;

$encKey = CryptoKey::createNewRandomKey()->saveToAsciiSafeString();

$secret = bin2hex(random_bytes(32));

echo "\n===> Copy the following code and include it into your .env file for this project.";
echo "\nDO NOT STORE THEM INTO GIT!!!";
echo "\nTOKEN_SECRET=\"{$secret}\"";
echo "\nTOKEN_ENC_KEY=\"{$encKey}\"\n\n";
