<?php

namespace Xhtkyy\Helper\Encipher\Bridge;

use Xhtkyy\Helper\Encipher\EncipherInterface;

class AesEncipher implements EncipherInterface {

    public function __construct(
        private string $encipher_secret,
        private string $encipher_iv,
        private string $encipher_algo = 'AES-256-CBC',
        private string $encipher_delimiter = '.'
    ) {
    }

    /**
     * 加密
     * @param string $plaintext
     * @param int $length
     * @return string
     */
    public function encrypt(string $plaintext, int $length): string {
        $groups = [];
        $len    = mb_strlen($plaintext);
        if ($len <= $length) {
            return openssl_encrypt($plaintext, $this->encipher_algo, $this->encipher_secret, 0, $this->encipher_iv);
        }
        for ($i = 0; $i <= $len - $length; $i++) {
            $groups[] = openssl_encrypt(mb_substr($plaintext, $i, $length), $this->encipher_algo, $this->encipher_secret, 0, $this->encipher_iv);
        }
        return implode($this->encipher_delimiter, $groups);
    }

    /**
     * 解密
     * @param string $encrypted
     * @param int $length
     * @return string
     */
    public function decrypt(string $encrypted, int $length): string {
        if (mb_strlen($encrypted) <= $length) {
            // 如果加密数据长度小于或等于分组长度，直接解密
            return openssl_decrypt($encrypted, $this->encipher_algo, $this->encipher_secret, 0, $this->encipher_iv);
        }

        $plaintext       = '';
        $encryptedGroups = explode($this->encipher_delimiter, $encrypted); // 按分组长度分割加密数据

        foreach (array_filter($encryptedGroups) as $key => $encryptedGroup) {
            // 对每个分组进行解密
            $decryptedGroup = openssl_decrypt($encryptedGroup, $this->encipher_algo, $this->encipher_secret, 0, $this->encipher_iv);
            $plaintext      .= ($key == 0 ? $decryptedGroup : mb_substr($decryptedGroup, -1)); // 将解密后的分组拼接起来
        }

        // 去除解密后字符串末尾可能出现的填充字符
        return $plaintext;
    }
}