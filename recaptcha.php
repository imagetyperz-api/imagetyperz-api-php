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
    $params['sitekey'] = '7LrGJmcUABBAALFtIb_FxC0LXm_GwOLyJAfbbUCL';

    // reCAPTCHA type(s) - optional, defaults to 1
    // ---------------------------------------------
    // 1 - v2
    // 2 - invisible
    // 3 - v3
    // 4 - enterprise v2
    // 5 - enterprise v3
    //
    //$params['type'] = 1;    // optional
    //
    //$params['v3_min_score'] = 0.3;          // min score to target when solving v3 - optional
    //$params['v3_action'] = 'homepage';      // action to use when solving v3 - optional
    // proxy to use when solving recaptcha, works with auth as well 126.45.34.53:123:user:password
    //$params['proxy'] = '126.45.34.53:123';  // - optional
    // user agent to use when resolving recaptcha - optional
    //$params['user_agent'] = 'Mozilla/5.0 (X11; Linux x86_64; rv:60.0) Gecko/20100101 Firefox/60.0'; // - optional
    //$params['data-s'] = 'recaptcha data-s value'; // - optional
    //$params['cookie_input'] = 'a=b;c=d'; // - optional
    $captcha_id = $i->submit_recaptcha($params);
    echo "Waiting for captcha to be solved...\n";
    $response = null;
    while($response === null) {
        sleep(10);
        $response = $i->retrieve_response($captcha_id);
    }
    echo "Response: ";
    var_dump($response);

    // Other examples
    // $i = new ImagetypersAPI($access_token, 123);      // use affiliateid
    // $i = new ImagetypersAPI($access_token, 123, 60);  // affiliate id and 60 seconds timeout
    // $i->set_captcha_bad($captcha_id);                 // if response is invalid, set captcha bad
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
