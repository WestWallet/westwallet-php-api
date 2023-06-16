<?php

namespace WestWallet\WestWallet;

use Exception;

class Client {
    private $apiKey = '';
    private $secretKey = '';
    private $basicURL = '';

    function __construct($apiKey, $secretKey, $basicURL="https://api.westwallet.io"){
        $this->apiKey = $apiKey;
        $this->secretKey = $secretKey;
        $this->basicURL = $basicURL;
    }

    public function transactionsList($currency='', $limit=10, $offset=0, $type='', $order='desc') {
        $data = array();
        $data['currency'] = $currency;
        $data['limit'] = $limit;
        $data['offset'] = $offset;
        $data['type'] = $type;
        $data['order'] = $order;
        return $this->makeRequest('/wallet/transactions', "POST", $data);
    }

    public function walletBalance($currency) {
        $data = array();
        $data['currency'] = $currency;
        return $this->makeRequest('/wallet/balance', "GET", $data);
    }

    public function walletBalances() {		
        return $this->makeRequest('/wallet/balances', "GET");
    }

    public function createWithdrawal($currency, $amount, $address, $destTag="", $description=""){
        
        $amount = strval($amount);        
        $data = array();
        $data['currency'] = $currency;
        $data['amount'] = $amount;
        $data['address'] = $address;
        $data['dest_tag'] = $destTag;
        $data['description'] = $description;
        
        return $this->makeRequest('/wallet/create_withdrawal', "POST", $data);
    }

    public function transactionInfo($id){
                
        $data = array();
        $data['id'] = $id;
        
        return $this->makeRequest('/wallet/transaction', "POST", $data);
    }

    public function generateAddress($currency, $ipnURL="", $label="") {
                
        $data = array();
        $data['currency'] = $currency;
        $data['ipn_url'] = $ipnURL;
        $data['label'] = $label;
        
        return $this->makeRequest('/address/generate', "POST", $data);
    }

    public function createInvoice($currencies, $amount, $ipn_url, $amount_in_usd=false, $success_url="", $description="", $label="", $ttl=15) {
                
        $data = array();
        $data['currencies'] = $currencies;
        $data['amount'] = $amount;
        $data['ipn_url'] = $ipn_url;
        $data['amount_in_usd'] = $amount_in_usd;
        $data['success_url'] = $success_url;
        $data['description'] = $description;
        $data['ttl'] = $ttl;
        $data['label'] = $label;
        
        return $this->makeRequest('/address/create_invoice', "POST", $data);
    }

    private function checkErrors($request, $requestJson) {
        $exceptions = array(
            "account_blocked" => new AccountBlockedException,
            "bad_address" => new BadAddressException,
            "bad_dest_tag" => new BadDestTagException,
            "insufficient_funds" => new InsufficientFundsException,
            "max_withdraw_error" => new MaxWithdrawException,
            "min_withdraw_error" => new MinWithdrawException,
            "currency_not_found" => new CurrencyNotFoundException,
            "not_found" => new TransactionNotFoundException
        );
        $statusCode = curl_getinfo( $request, CURLINFO_HTTP_CODE );
        if ($statusCode == "401") {
            throw new WrongCredentialsException;
        }
        if ($statusCode == "403") {
            throw new NotAllowedException;
        }
        $error = $requestJson['error'];
        if ($error != "ok") {
            $exception = $exceptions[$error];
            if ($exception) {
                throw $exception;
            }
            throw new WestWalletAPIException($error);
        }
    }

    private function makeRequest($methodURL, $method, $data = array()) {
        $timestamp = time();
        if (empty($data)) {
            $body = "";
        } else {
            $body = json_encode($data);
        }
        $requestData = json_encode($data, JSON_UNESCAPED_SLASHES);
        if ($method == "POST") {
            $request = curl_init($this->basicURL.$methodURL);
            curl_setopt($request, CURLOPT_POSTFIELDS, $requestData);
        } else {
            $request = curl_init($this->basicURL.$methodURL."?".http_build_query($data));
        }
        if ($requestData != "[]") {
        	$hmacMessage = $timestamp.$requestData;
        } else {
        	$hmacMessage = $timestamp;
        }
        $signature = hash_hmac("sha256", $hmacMessage, $this->secretKey);
        curl_setopt($request, CURLOPT_FAILONERROR, TRUE);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($request, CURLOPT_SSL_VERIFYPEER, 0);
        $headers = array(
            'X-API-KEY: '.$this->apiKey,
            'Content-Type: application/json',
            'X-ACCESS-SIGN: '.$signature,
            'X-ACCESS-TIMESTAMP: '.$timestamp
        );
        curl_setopt($request, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($request);
        $responseJson = json_decode($response, TRUE);
        $this->checkErrors($request, $responseJson);
        curl_close($request);
        if ($responseJson !== FALSE) {
            return $responseJson;
        }
    }
}

class WestWalletAPIException extends Exception {

}

class InsufficientFundsException extends WestWalletAPIException {

}

class CurrencyNotFoundException extends WestWalletAPIException {

}

class NotAllowedException extends WestWalletAPIException {

}

class WrongCredentialsException extends WestWalletAPIException {

}

class TransactionNotFoundException extends WestWalletAPIException {

}

class AccountBlockedException extends WestWalletAPIException {

}

class BadAddressException extends WestWalletAPIException {

}

class BadDestTagException extends WestWalletAPIException {

}

class MinWithdrawException extends WestWalletAPIException {

}

class MaxWithdrawException extends WestWalletAPIException {

}
