<?php

namespace NewChain;


use Web3\Web3;
use Web3\Utils as web3Utils;
use NewChain\Utils as NewUtils;

/*
	$newchain = new NewChain();
	// rpc地址
	$newchain->setChainRpc('私钥', '钱包地址', 'test');
	$newchain->send('对谁发送', '发送金额', '备注');
	
*/

class NewChain
{

	private $private;
	private $fromAddress;

	//Test Net
	public $chainRpc = 'https://rpc1.newchain.newtonproject.org';
	public $chainId = '1007';

	private $web3;
	private $eth;
	private $nonce;
	private $gasPrice;

	public function __construct($private='', $fromAddress='', $env='test')
	{

		if ($env == 'main') {
			$this->chainRpc = 'https://global.rpc.mainnet.newtonproject.org';
			$this->chainId  = '1012';
		} else {
			$this->chainRpc = 'https://rpc1.newchain.newtonproject.org';
			$this->chainId  = '1007';
		}

		$this->private = $private;
		$this->fromAddress = $fromAddress;

		$this->init();
	}

	public function init()
	{
		if (!$this->chainRpc) {
			throw new \Exception('Incorrect Chain Rpc');
		}
		if (!$this->private) {
			throw new \Exception('Incorrect private key');
		}
		if (!$this->fromAddress) {
			throw new \Exception('Incorrect From Address');
		}
		if (!$this->chainId) {
			throw new \Exception('Incorrect From Address');
		}

		$this->web3 = new Web3($this->chainRpc);
		$this->eth = $this->web3->eth;

	}

	// 发NEW
	public function send($toAddress, $value, $data)
	{

		if (!$toAddress) {
			throw new \Exception('Incorrect to address');
		}
		$this->toAddress = $toAddress;

		if (!$value) {
			throw new \Exception('Incorrect value');
		}
		$this->value = $value;

		if ($data) {
			$this->data = trim($data);
			$data = web3Utils::toHex($this->data, true);
		} else {
			$data = '0x';
		}

		// NEW 开头 转换为 hex
		if (strpos($this->fromAddress, 'NEW') === 0) {
			$this->fromAddress = NewUtils::newAddress2HexAddress($this->fromAddress);
		}

		if (strpos($this->toAddress, 'NEW') === 0) {
			$this->toAddress = NewUtils::newAddress2HexAddress($this->toAddress);
		}

		// 已经有nonce，第二次调用+1
		if ($this->nonce) {
			$this->nonce = $this->nonce->add(new \phpseclib\Math\BigInteger(1));
		} else {
			$this->eth->getTransactionCount($this->fromAddress, 'pending', function ($err, $nonce) {
				if ($err !== null) {
			    	throw new \Exception($err->getMessage());
			    }
			    $this->nonce = $nonce;
			});
		}

		$this->eth->gasPrice(function ($err, $gasPrice) {

		    if ($err !== null) {
		    	throw new \Exception($err->getMessage());
		    }
		    $this->gasPrice = $gasPrice;

		});

		$hexGasPrice = web3Utils::toHex($this->gasPrice->toString(), true);
		$hexNonce = web3Utils::toHex($this->nonce->toString(), true);
		$hexValue = web3Utils::toHex(web3Utils::toWei($this->value, 'ether')->toString(), true);

	    $this->eth->estimateGas([
				'from'     => $this->fromAddress, 
				'to'       => $this->toAddress, 
				'gasPrice' => $hexGasPrice,
				'nonce'    => $hexNonce,
				'value'    => $hexValue,
				'data'     => $data,
			], function ($err, $gas) {

		    if ($err !== null) {
		    	throw new \Exception($err->getMessage());
		    } 
		    $this->gasEstimate = $gas;
		});

        // $nonce, $gasPrice, $gasLimit, $to, $value, $data
		$transaction = new Transaction (
			$hexNonce,
			$hexGasPrice,
			web3Utils::toHex($this->gasEstimate->toString(), true),
			$this->toAddress,
			$hexValue,
			$data
		);

		$txHex = '0x'.$transaction->getRaw($this->private, $this->chainId);

		$result = $this->eth->sendRawTransaction($txHex, function($err, $transaction) {
			if ($err) {
				throw new \Exception($err->getMessage());
			}
			$this->transactionHash = $transaction;
		});


		$transactionHash = $this->transactionHash;

		// 清理
		// $this->value = '0';
		// $this->data = '';

		return $transactionHash;

	}



	public function getTransactionByHash($hash)
	{

		$this->eth->getTransactionByHash($hash, function($err, $transaction) {
			if ($err !== null) {
		    	throw new \Exception($err->getMessage());
		    }
		    $this->transaction = $transaction;
		});

		return $this->transaction;

	}


}