<?php
declare(strict_types=1);
/**
 * @author   crayxn <https://github.com/crayxn>
 * @contact  crayxn@qq.com
 */

namespace Xhtkyy\Helper\Middleware;

use Hyperf\HttpMessage\Exception\HttpException;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\Metric\CoroutineServerStats;
use Hyperf\Metric\Timer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MetricMiddleware implements MiddlewareInterface
{
    public function __construct(protected CoroutineServerStats $stats)
    {
    }

    /**
     * Process an incoming server request.
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        [$serverName, $path] = $this->getPath($request);
        $labels = [
            'kind' => $serverName,
            'request_status' => '500', // default to 500 in case uncaught exception occur
            'request_path' => $path,
            'request_method' => $request->getMethod(),
        ];
        $timer = new Timer('http_requests', $labels);

        ++$this->stats->accept_count;
        ++$this->stats->request_count;
        ++$this->stats->connection_num;

        try {
            $response = $handler->handle($request);
            $labels['request_status'] = (string)$response->getStatusCode();
        } catch (\Throwable $exception) {
            if ($exception instanceof HttpException) {
                $labels['request_status'] = (string)$exception->getStatusCode();
            }
            throw $exception;
        } finally {
            $timer->end($labels);
            ++$this->stats->close_count;
            ++$this->stats->response_count;
            --$this->stats->connection_num;
        }

        return $response;
    }

    protected function getPath(ServerRequestInterface $request): array
    {
        /**
         * @var Dispatched $dispatched
         */
        $dispatched = $request->getAttribute(Dispatched::class);
        if (!$dispatched) {
            return ['http', $request->getUri()->getPath()];
        }
        if (!$dispatched->handler) {
            return ['http', 'not_found'];
        }
        return [$dispatched->serverName, $dispatched->handler->route];
    }
}