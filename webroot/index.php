<?php

require '../vendor/autoload.php';

$app = new \Slim\Slim();

$app->get('/', function () {
    echo "Usage: https://rates.startwallet.com/currency/CURRENCY or https://rates.startwallet.com/all";
});

$app->get('/all', function () {

    $redis = new Predis\Client();

    $start_price = $redis->get('start_price');
    $bitcoin_average_data = $redis->get('bitcoin_average_data');
    $bitcoin_average_data = json_decode($bitcoin_average_data);
    $output = array();

    $currencies = file_get_contents('../config/currencies.json');
    $currencies = json_decode($currencies);


    if(empty($bitcoin_average_data)){
        return json_encode(array());
    }
    foreach ($bitcoin_average_data as $code => $currency) {

        if (isset($currencies->{$code})) {

            $rate = number_format(($currency->last * $start_price),8);

            $output[] = array("code" => $code, "name" => $currencies->{$code}, 'rate' => $rate);
            
        }
    }
    
    echo json_encode($output);
});

$app->get('/currency/:currency', function ($currency) {
    
    $redis = new Predis\Client();

    $start_price = $redis->get('start_price');
    $bitcoin_average_data = $redis->get('bitcoin_average_data');
    $bitcoin_average_data = json_decode($bitcoin_average_data);
    $output = array();

    $currencies = file_get_contents('../config/currencies.json');
    $currencies = json_decode($currencies);
    
    if(isset($currencies->{$currency})){
        
        $currency_data = $bitcoin_average_data->{$currency};
        
        $rate = number_format(($currency_data->last * $start_price),8);
        $output = array("code" => $currency, "name" => $currencies->{$currency}, 'rate' => $rate);
        echo json_encode($output);
    }
    
});

$app->notFound(function () {
    echo "Usage: https://rates.startwallet.com/currency/CURRENCY or https://rates.startwallet.com/all";
});

$app->run();
