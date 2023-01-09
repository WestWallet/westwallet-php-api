westwallet-php-api
==================
.. image:: https://poser.pugx.org/westwallet/westwallet-php-api/v/stable
    :alt: packagist
    :target: https://packagist.org/packages/westwallet/westwallet-php-api

westwallet-php-api is a `WestWallet Public API <https://westwallet.io/api_docs>`_ wrapper for PHP programming language. Use it for building payment solutions.

Installing
----------

Install from composer:

.. code-block:: text

    composer require westwallet/westwallet-php-api


Create withdrawal example
-------------------------

.. code-block:: php

    <?php
    require_once 'vendor/autoload.php';

    use WestWallet\WestWallet\Client;
    use WestWallet\WestWallet\InsufficientFundsException;

    $client = new Client("your_public_key", "your_private_key");

    // Send 0.1 ETH to 0x57689002367b407f031f1BB5Ef2923F103015A32
    try {
        $tx = $client->createWithdrawal("ETH", "0.1", "0x57689002367b407f031f1BB5Ef2923F103015A32");
        print(implode("|", $tx)."\n");
    } catch(InsufficientFundsException $e) {
        print("You don't have enough funds to make this withdrawal"."\n");
    }

Generate address example
-------------------------

.. code-block:: php

    <?php
    require_once 'vendor/autoload.php';

    use WestWallet\WestWallet\Client;
    use WestWallet\WestWallet\CurrencyNotFoundException;

    $client = new Client("your_public_key", "your_private_key");

    // Send 0.1 ETH to 0x57689002367b407f031f1BB5Ef2923F103015A32
    try {
        $address = $client->generateAddress("BTC");
        print($address['address'])."\n");
    } catch(CurrencyNotFoundException $e) {
        print("This currency doesn't exist!"."\n");
    }


Documentation
-------------
* API: https://westwallet.io/api_docs


Other languages
---------------
* Python: https://github.com/WestWallet/westwallet-python-api
* JavaScript: https://github.com/WestWallet/westwallet-js-api
* Golang: https://github.com/WestWallet/westwallet-golang-api
