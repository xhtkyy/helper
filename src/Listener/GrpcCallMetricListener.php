<?php
declare(strict_types=1);
/**
 * @author   crayxn <https://github.com/crayxn>
 * @contact  crayxn@qq.com
 */

namespace Xhtkyy\Helper\Listener;

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Metric\Contract\MetricFactoryInterface;
use Xhtkyy\GrpcClient\Event\GrpcCallEvent;
use function Hyperf\Support\make;

class GrpcCallMetricListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            GrpcCallEvent::class
        ];
    }

    public function process(object $event): void
    {
        make(MetricFactoryInterface::class)
            ->makeHistogram('rpc_calls', ["code", "path"])
            ->with((string)$event->code, $event->path)
            ->put($event->at);
    }
}