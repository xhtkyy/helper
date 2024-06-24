<?php

namespace Xhtkyy\Helper\Encipher\Facades;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Xhtkyy\Helper\Encipher\Bridge\AesEncipher;
use Xhtkyy\Helper\Encipher\EncipherInterface;

class EncipherFactory {

    public function __invoke(ContainerInterface $container): EncipherInterface {
        $config          = $container->get(ConfigInterface::class);
        $encipher_secret = $config->get('kyy_security.encipher_secret');
        $encipher_iv     = $config->get('kyy_security.encipher_iv');
        if (empty($encipher_secret) || empty($encipher_iv)) {
            throw new \Exception('Encipher secret and iv are required.');
        }
        return new AesEncipher($encipher_secret, $encipher_iv);
    }

}