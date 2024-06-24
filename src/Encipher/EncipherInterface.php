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
     * @param string $plaintext 明文
     * @param int $length 分词字符数量
     * @return string 密文
     */
    public function encrypt(string $plaintext, int $length): string;

    /**
     * @param string $encrypted 密文
     * @param int $length 密文分词规则字符数量
     * @return string 明文
     */
    public function decrypt(string $encrypted, int $length): string;
}