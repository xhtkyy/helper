<?php
declare(strict_types=1);
/**
 * @author   crayoon<https://github.com/crayoon>
 * @contact  so.wo@foxmail.com
 */

namespace Xhtkyy\Helper\Tracer;

use Exception;

class OpenTracerFactory implements Hyperf\Tracer\Contract\NamedFactoryInterface
{

    /**
     * @throws Exception
     */
    public function make(string $name): \OpenTracing\Tracer
    {
        $class = sprintf("OpenTracing\\%sTracer", \Hyperf\Stringable\Str::studly($name));
        if (!class_exists($class)) {
            throw new Exception("$class Tracer no found");
        }
        return new $class;
    }
}