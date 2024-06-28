<?php
declare(strict_types=1);
/**
 * @author   hahn
 * @contact  hahnz@qq.com
 */
namespace Xhtkyy\Helper\Encipher;

interface EncipherInterface {
    /**
     * 加密
     * @param string|null $plaintext 明文
     * @param int|null $length 分词字符数量
     * @param bool $withPrefix
     * @return string|null 密文
     */
    public function encrypt(?string $plaintext, ?int $length = null, bool $withPrefix = true): ?string;

    /**
     * @param string|null $encrypted 密文
     * @param bool $withPrefix
     * @return string|null 明文
     */
    public function decrypt(?string $encrypted, bool $withPrefix = true): ?string;
}