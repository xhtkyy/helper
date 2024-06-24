<?php
return [
    'encipher_secret' => \Hyperf\Support\env("ENCIPHER_SECRET"),
    'encipher_iv' => \Hyperf\Support\env("ENCIPHER_IV"),
    'encipher_code' => \Hyperf\Support\env("ENCIPHER_CODE",'UTF-8'),
    'encipher_algo' => \Hyperf\Support\env("ENCIPHER_ALGO",'AES-256-CBC'),
    'encipher_delimiter' => \Hyperf\Support\env("ENCIPHER_DELIMITER",'.'),
];