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
    // optional image captcha parameters
    $optional_parameters = array();
    // $optional_parameters['iscase'] = 'true';            // case sensitive captcha
    // $optional_parameters['ismath'] = 'true';            // instructs worker that a math captcha has to be solved
    // $optional_parameters['isphrase'] = 'true';          // text contains at least one space (phrase)
    // $optional_parameters['alphanumeric'] = '1';         // 1 - digits only, 2 - letters only
    // $optional_parameters['minlength'] = '3';            // captcha text length (minimum)
    // $optional_parameters['maxlength'] = '8';            // captcha text length (maximum)
    $captcha_id = $i->submit_image('captcha.jpg', $optional_parameters);
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
