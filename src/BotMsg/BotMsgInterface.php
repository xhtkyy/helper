<?php
declare(strict_types=1);
/**
 * @author   crayxn <https://github.com/crayxn>
 * @contact  crayxn@qq.com
 */

namespace Xhtkyy\Helper\BotMsg;

interface BotMsgInterface
{
    public function text(string $content):static;
    public function notice(): bool;
}