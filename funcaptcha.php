#!/usr/bin/php

<?php
require('lib/imagetyperzapi.php');      // load API library

function test_api() {
    # grab token from https://imagetyperz.com
    $access_token = 'your_access_token';
    $i = new ImagetyperzAPI($access_token);      // init API lib obj

    $balance = $i->account_balance();       // get balance
    echo "Balance: $balance\n";

    echo "Solving captcha ...\n";
    $params = array();
    $params['page_url'] = 'https://your-site.com';
    $params['sitekey'] = '11111111-1111-1111-1111-111111111111';
    $params['s_url'] = 'https://api.arkoselabs.com';
    $params['data'] = '{"a":"b"}';
    //$params['proxy'] = '126.45.34.53:123';  // - optional
    //$params['user_agent'] = 'Mozilla/5.0 (X11; Linux x86_64; rv:60.0) Gecko/20100101 Firefox/60.0'; // - optional
    $captcha_id = $i->submit_funcaptcha($params);
    echo "Waiting for captcha to be solved...\n";
    $response = null;
    while($response === null) {
        sleep(10);
        $response = $i->retrieve_response($captcha_id);
    }
    echo "Response: ";
    var_dump($response);
}

// Main method
function main() {
    try {
        test_api();             // test API
    } catch (Exception $ex) {
        echo "Error occured: " . $ex->getMessage();     // print error
    }
}

main();         // run main function
?>
