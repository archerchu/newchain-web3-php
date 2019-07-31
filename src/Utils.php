<?php

namespace NewChain;

use kornrunner\Secp256k1;
use kornrunner\keccak;
use Elliptic\EC;
use Web3\Utils as web3Utils;


class Utils
{

	const SHA3_NULL_HASH = 'c5d2460186f7233c927e7db2dcc703c0e500b653ca82273b7bfad8045d85a470';

	const TestChainId = 1007;
	const MainChainId = 1012;


	static function genNewAddress($chainId=1007)
	{
		$ec = self::ecP256();
		$keyPair = $ec->genKeyPair();
		$privateKey = $keyPair->getPrivate('hex');
		$publicKey = $keyPair->getPublic(false, 'hex');

		if (strlen($publicKey) == 130 && strpos($publicKey, '04') === 0) {
			$publicKey = substr($publicKey, -128);
		}
		$hash = Keccak::hash(hex2bin($publicKey), 256);
		$hexAddress = substr($hash, -40);
		$newAddress = self::hexAddress2NewAddress($hexAddress, $chainId);

		return [
			'privateKey' => $privateKey,
			'publicKey'  => $publicKey,
			'hexAddress' => $hexAddress,
			'newAddress' => $newAddress,
		];
	}


	static function ecP256()
	{
		static $ec = null;
		if (is_null($ec)) {
			$ec = new EC('p256');
		}
		return $ec;
	}

	/**
     * privateKeyToPublicKey
     * 
     * @param string $privateKey
     * @return string
     */
	static function privateKeyToPublicKey($privateKey)
	{
		if (self::isHex($privateKey) === false) {
            throw new \Exception('Invalid private key format.');
        }
        $privateKey = self::stripZero($privateKey);

        if (strlen($privateKey) !== 64) {
            throw new \Exception('Invalid private key length.');
        }
        $privateKey = self::ecP256()->keyFromPrivate($privateKey, 'hex');
        $publicKey = $privateKey->getPublic(false, 'hex');

        return '0x' . $publicKey;
	}

	/**
     * publicKeyToAddress
     * 
     * @param string $publicKey
     * @return string
     */
	static function publicKeyToAddress($publicKey)
	{
		if (self::isHex($publicKey) === false) {
            throw new \Exception('Invalid public key format.');
        }
        $publicKey = self::stripZero($publicKey);

        if (strlen($publicKey) !== 130) {
            throw new \Exception('Invalid public key length.');
        }
        return '0x' . substr(self::sha3(substr(hex2bin($publicKey), 1)), 24);
	}


    /**
     * sha3
     * keccak256
     * 
     * @param string $value
     * @return string
     */
    static function sha3(string $value)
    {
        $hash = Keccak::hash($value, 256);

        if ($hash === Utils::SHA3_NULL_HASH) {
            return null;
        }
        return $hash;
    }

    /**
     * isZeroPrefixed
     * 
     * @param string $value
     * @return bool
     */
    static function isZeroPrefixed(string $value)
    {
        return (strpos($value, '0x') === 0);
    }

    /**
     * stripZero
     * 
     * @param string $value
     * @return string
     */
    static function stripZero(string $value)
    {
        if (self::isZeroPrefixed($value)) {
            $count = 1;
            return str_replace('0x', '', $value, $count);
        }
        return $value;
    }

    /**
     * isHex
     * 
     * @param string $value
     * @return bool
     */
    static function isHex(string $value)
    {
        return (is_string($value) && preg_match('/^(0x)?[a-fA-F0-9]+$/', $value) === 1);
    }

	static function getHexAddressFromPrivate($privateKey, $chainId=1007)
	{

		$publicKey = self::privateKeyToPublicKey($privateKey);
		$hexAddress = self::publicKeyToAddress($publicKey);
		$newAddress = self::hexAddress2NewAddress($hexAddress, $chainId);

		return $newAddress;

	}

	
	static function base58Check()
    {
    	static $base58Check = null;
        if (is_null($base58Check)) {
            $base58Check = new \FurqanSiddiqui\Base58\Base58Check();
        }
        return $base58Check;
    }

    // New地址 转换为 Hex地址
    static function newAddress2HexAddress($newAddress)
    {
        if(is_string($newAddress) && strpos($newAddress, "NEW") === 0) {
            $hex = self::base58CheckDecode(substr($newAddress, 3));
            return "0x" . substr($hex, -40);
        } else {
            return $newAddress;
        }
    }

    static function base58CheckDecode($base58)
    {
    	// base58Check需要PHP7.2版本，
    	// 如果版本低，可以做下修改
    	// FurqanSiddiqui\BcMath\BcBaseConvert() 104行 
    	// 原: $mod = bcmod($num, $charsetLen, 0);
    	// 改: $mod = bcmod($num, $charsetLen);
    	return static::base58Check()->decode($base58)->hexits();
    }

    static function base58CheckEncode($str)
    {
    	return static::base58Check()->encode($str);
    }

    static function chainIdHex($chainId)
    {
    	return str_pad(web3Utils::toHex($chainId), 6, '0', STR_PAD_LEFT);;
    }

	// Hex地址 转换为 New地址
	static function hexAddress2NewAddress($hexAddress, $chainId=1007)
	{
		if (strpos($hexAddress, '0x') === 0) {
            $hexAddress = substr($hexAddress, 2);
        }

		$chainIdHex = self::chainIdHex($chainId);
        
        $prefix = 'NEW';
        $preAddress = $chainIdHex . $hexAddress;
        $newAddress = static::base58CheckEncode($preAddress);

        return $prefix . $newAddress->data();
	}

}