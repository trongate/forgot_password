<?php
class Encryption extends Trongate {

    private $cipher = "aes-128-gcm";
    private $options = 0;
    private $key = 'AfHE6Ccn6wyvGpQACy61QOQ719tl/zXuZTg+thhvAgGiJr88vY6Baz6BzJKu5p8wAEty0+G4bAKwC0MDMdchiw==';

    function _encrypt($plaintext) {
        $ivlen = \openssl_cipher_iv_length($this->cipher);
        $iv = \openssl_random_pseudo_bytes($ivlen);
        $ciphertext = openssl_encrypt($plaintext, $this->cipher, $this->key, $this->options, $iv,$tag);
        $enc_string = bin2hex($iv).bin2hex($tag).$ciphertext;
        return $enc_string;
    }

    function _decrypt($enc_string) {
    	$iv = substr($enc_string, 0, 24);
    	$tag = substr($enc_string, 24, 32);
    	$ciphertext = substr($enc_string, 56, strlen($enc_string));
    	$result = \openssl_decrypt($ciphertext, $this->cipher, $this->key, $this->options, \hex2bin($iv), \hex2bin($tag));
    	return $result;
    }

}