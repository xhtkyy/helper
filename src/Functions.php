<?php
declare(strict_types=1);
/**
 * @author   crayxn <https://github.com/crayxn>
 * @contact  crayxn@qq.com
 */

namespace Xhtkyy\Helper;

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