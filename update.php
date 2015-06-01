<?php
require 'vendor/autoload.php';

function get_data($url) {
    $ch = curl_init();
    $timeout = 5;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

function send_error_email($message) {
echo "Error detected {$message} \r\n";
mail('webmaster@startjoin.com', 'Problem updating the price on rates on rates.startwallet.com', $message);
die;

}

function write_to_redis($key, $value) {
    try {
        $redis = new Predis\Client();

        $redis->set($key, $value);
        
        
    } catch (Exception $e) {
        send_error_email("Couldn't connected to Redis");
        echo $e->getMessage();
    }
}

function read_from_redis($key) {
    try {
        $redis = new Predis\Client();

       return $redis->get($key);
        
        
    } catch (Exception $e) {
        send_error_email("Couldn't connected to Redis");
        echo $e->getMessage();
    }
}

// get start price from
// https://bittrex.com/api/v1.1/public/getticker?market=btc-start

echo "Getting data from bittrex\r\n";

$url = "https://bittrex.com/api/v1.1/public/getticker?market=btc-start";
$bittrex_data = get_data($url);


if (empty($bittrex_data) || !$bittrex_data = json_decode($bittrex_data)) {
    send_error_email("Failed to get the bittrex price");
}
if (!isset($bittrex_data->result->Ask) || !is_numeric($bittrex_data->result->Ask)) {
    send_error_email("Bittrex price is not numeric");
}

$start_price = number_format($bittrex_data->result->Last, 8);

echo "Updating redis price startcoin price to {$start_price} \r\n";

write_to_redis('start_price', $start_price);


echo "Getting bitcoin average prices\r\n";
$url = "https://api.bitcoinaverage.com/ticker/global/all";

$bitcoin_av_data = get_data($url);
if(empty($bitcoin_av_data) || !$bitcoin_av_data_decoded = json_decode($bitcoin_av_data)){
        send_error_email("Failed to get bitcoin average price");    
}
if(!isset($bitcoin_av_data_decoded->BTC->last) || !is_numeric($bitcoin_av_data_decoded->BTC->last)){
    send_error_email("Failed to get bitcoin average price");  
}

write_to_redis('bitcoin_average_data', $bitcoin_av_data);
echo "Updated bitcoin average data\r\n";





