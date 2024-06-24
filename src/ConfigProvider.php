<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Xhtkyy\Helper;


use Xhtkyy\Helper\Encipher\EncipherInterface;
use Xhtkyy\Helper\Encipher\Facades\EncipherFactory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                EncipherInterface::class => EncipherFactory::class
            ],
            'commands' => [
            ],
            'listeners' => [
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'config_encipher',
                    'description' => 'the config for encipher',
                    'source' => __DIR__ . '/../publish/kyy_security.php',
                    'destination' => BASE_PATH . '/config/autoload/kyy_security.php',
                ],
            ],
        ];
    }
}
