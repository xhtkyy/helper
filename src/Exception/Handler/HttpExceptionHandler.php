<?php
declare(strict_types=1);
/**
 * @author   crayxn <https://github.com/crayxn>
 * @contact  crayxn@qq.com
 */

namespace Xhtkyy\Helper\Exception\Handler;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\HttpMessage\Exception\HttpException;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use \Throwable;

class HttpExceptionHandler extends ExceptionHandler
{
    public function __construct(protected StdoutLoggerInterface $logger, protected FormatterInterface $formatter)
    {
    }

    /**
     * Handle the exception, and return the specified result.
     * @param HttpException $throwable
     */
    public function handle(Throwable $throwable, ResponseInterface $response): \Psr\Http\Message\MessageInterface|ResponseInterface
    {
        $this->logger->debug($throwable->getMessage(), [
            'caller' => $throwable->getFile() . '#' . $throwable->getLine(),
            'trace' => "\n" . $throwable->getTraceAsString()
        ]);

        $this->stopPropagation();

        return $response->withStatus($throwable->getStatusCode())->withBody(new SwooleStream($throwable->getMessage()));
    }

    /**
     * Determine if the current exception handler should handle the exception.
     *
     * @return bool If return true, then this exception handler will handle the exception,
     *              If return false, then delegate to next handler
     */
    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof HttpException;
    }
}