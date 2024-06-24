<?php

namespace Xhtkyy\Helper\Encipher\Bridge;

use Exception;
use Hyperf\Contract\ConfigInterface;
use Xhtkyy\Helper\Encipher\EncipherInterface;

class AesEncipher implements EncipherInterface {

    private ?string $encipher_secret;
    private ?string $encipher_iv;
    private string $encipher_code = 'UTF-8';
    private string $encipher_algo = 'AES-256-CBC';
    private string $encipher_delimiter = '.';

    /**
     * @throws Exception
     */
    public function __construct(protected ConfigInterface $config) {
        $this->encipher_secret = $this->config->get('kyy_security.encipher_secret');
        $this->encipher_iv     = $this->config->get('kyy_security.encipher_iv');
        if (empty($this->encipher_secret) || empty($this->encipher_iv)) {
            throw new Exception('Encipher secret and iv are required.');
        }
    }

    /**
     * 加密
     * @param string $plaintext
     * @param int $length
     * @return string
     */
    public function encrypt(string $plaintext, int $length): string {
        $groups = [];
        $len    = mb_strlen($plaintext, $this->encipher_code);
        if ($len <= $length) {
            return openssl_encrypt($plaintext, $this->encipher_algo, $this->encipher_secret, 0, $this->encipher_iv);
        }
        for ($i = 0; $i <= $len - $length; $i++) {
            $groups[] = sprintf(
                "%s%s",
                openssl_encrypt(mb_substr($plaintext, $i, $length, $this->encipher_code), $this->encipher_algo, $this->encipher_secret, 0, $this->encipher_iv),
                $this->encipher_delimiter
            );
        }
        return implode('', $groups);
    }

    /**
     * 解密
     * @param string $encrypted
     * @param int $length
     * @return string
     */
    public function decrypt(string $encrypted, int $length): string {
        if (mb_strlen($encrypted, $this->encipher_code) <= $length) {
            // 如果加密数据长度小于或等于分组长度，直接解密
            return openssl_decrypt($encrypted, $this->encipher_algo, $this->encipher_secret, 0, $this->encipher_iv);
        }

        $plaintext       = '';
        $encryptedGroups = explode($this->encipher_delimiter, $encrypted); // 按分组长度分割加密数据

        foreach (array_filter($encryptedGroups) as $key => $encryptedGroup) {
            // 对每个分组进行解密
            $decryptedGroup = openssl_decrypt($encryptedGroup, $this->encipher_algo, $this->encipher_secret, 0, $this->encipher_iv);
            if ($key == 0) {
                $plaintext = $decryptedGroup;
                continue;
            }
            $plaintext .= $this->getLastCharacter($decryptedGroup); // 将解密后的分组拼接起来
        }

        // 去除解密后字符串末尾可能出现的填充字符
        return rtrim($plaintext, "\0");
    }

    /**
     * 获取字符串最后一个字符
     * @param $str
     * @return string|null
     */
    private function getLastCharacter($str): ?string {
        if (mb_strlen($str, $this->encipher_code) > 0) {
            return mb_substr($str, mb_strlen($str, $this->encipher_code) - 1, 1, $this->encipher_code);
        }
        return null;
    }
}