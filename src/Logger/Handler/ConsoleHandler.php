<?php
declare(strict_types=1);
/**
 * @author   crayxn <https://github.com/crayxn>
 * @contact  crayxn@qq.com
 */

namespace Xhtkyy\Helper\Logger\Handler;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Console print
 */
class ConsoleHandler extends AbstractProcessingHandler
{
    private OutputInterface $output;

    public function __construct(int|string|Level $level = Level::Debug, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->output = new ConsoleOutput();
    }

    /**
     * @inheritDoc
     */
    protected function write(LogRecord $record): void
    {
        $tag = match ($record->level) {
            Level::Emergency, Level::Alert, Level::Critical => 'error',
            Level::Error => 'fg=red',
            Level::Warning, Level::Notice => 'comment',
            default => 'info',
        };

        $this->output->write(sprintf("<%s>%s</>", $tag, $record->formatted));
    }
}