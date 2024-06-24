<?php

namespace Xhtkyy\Helper\Encipher\Facades;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Xhtkyy\Helper\Encipher\Bridge\AesEncipher;

class EncipherFactory {

    public function __invoke(ContainerInterface $container) {
        return $container->make(AesEncipher::class);
    }

}