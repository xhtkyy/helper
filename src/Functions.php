<?php
declare(strict_types=1);
/**
 * @author   crayxn <https://github.com/crayxn>
 * @contact  crayxn@qq.com
 */

namespace Xhtkyy\Helper {

    use Psr\Http\Message\ServerRequestInterface;

    /**
     * verify ip
     * @param string $ip
     * @return bool
     */
    function verifyIpv4(string $ip): mixed {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
    }

    /**
     * get client ip
     * @param ServerRequestInterface $request
     * @return string
     */
    function clientIp(ServerRequestInterface $request): string {
        foreach (['x-hwwaf-real-ip', 'x-forwarded-for', 'remote-host', 'x-real-ip'] as $key) {
            if (verifyIpv4($request->getHeaderLine($key))) {
                return $request->getHeaderLine($key);
            }
        }
        //remote_addr
        if (isset($request->getServerParams()['remote_addr']) && verifyIpv4($request->getServerParams()['remote_addr'])) {
            return $request->getServerParams()['remote_addr'];
        }
        return '0.0.0.0';
    }
}

namespace Xhtkyy\Helper\Encipher {

    use Hyperf\Context\ApplicationContext;

    function encrypt(?string $plaintext, ?int $length = null, bool $withPrefix = true): ?string {
        if (!ApplicationContext::hasContainer()) {
            throw new \RuntimeException('The application context lacks the container.');
        }

        $container = ApplicationContext::getContainer();

        if (!$container->has(EncipherInterface::class)) {
            throw new \RuntimeException('EncipherInterface is missing in container.');
        }

        return $container->get(EncipherInterface::class)->encrypt($plaintext, $length, $withPrefix);
    }

    function decrypt(?string $encrypted, bool $withPrefix = true): ?string {
        if (!ApplicationContext::hasContainer()) {
            throw new \RuntimeException('The application context lacks the container.');
        }

        $container = ApplicationContext::getContainer();

        if (!$container->has(EncipherInterface::class)) {
            throw new \RuntimeException('EncipherInterface is missing in container.');
        }

        return $container->get(EncipherInterface::class)->decrypt($encrypted, $withPrefix);
    }

    function encrypt_without_prefix(?string $plaintext, ?int $length = null): ?string {
        return encrypt($plaintext, $length, false);
    }

    function decrypt_without_prefix(?string $plaintext): ?string {
        return decrypt($plaintext, false);
    }
}

namespace Xhtkyy\Helper\Sql {
    function like_filter(string $value, int $mode = 0): string {
        $value = str_replace('%', '[%]', $value);
        return match ($mode) {
            0 => "%{$value}%",
            1 => "%{$value}",
            2 => "{$value}%",
        };
    }
}