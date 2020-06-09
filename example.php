#!/usr/bin/php

<?php
// ----------------------------------------
// Imagetypers API example
// ----------------------------------------

require('lib/imagetyperzapi.php');      // load API library
// Test method

function test_api() {
    $access_token = 'access_token_here';
    $i = new ImagetyperzAPI($access_token);      // init API lib obj
    //legacy (will get deprecated at some point)
    //$i->set_user_password('your_username', 'your_password');

    // check account balance
    // --------------------------
    $balance = $i->account_balance();       // get balance
    echo "Balance: $balance";

    echo 'Solving captcha ...';
    // optional image captcha parameters
    $optional_parameters = array();
    // $optional_parameters['iscase'] = 'true';            // case sensitive captcha
    // $optional_parameters['ismath'] = 'true';            // instructs worker that a math captcha has to be solved
    // $optional_parameters['isphrase'] = 'true';          // text contains at least one space (phrase)
    // $optional_parameters['alphanumeric'] = '1';         // 1 - digits only, 2 - letters only
    // $optional_parameters['minlength'] = '3';            // captcha text length (minimum)
    // $optional_parameters['maxlength'] = '8';            // captcha text length (maximum)
    $captcha_text = $i->solve_captcha('captcha.jpg', $optional_parameters);
    echo "Captcha text: $captcha_text";

    // solve recaptcha
    // --------------------------------------------------------------------
    // check: https://github.com/imagetyperz-api/API-docs#submit-recaptcha for more details
    echo 'Submitting recaptcha...';
    $params = array();
    $params['page_url'] = 'page_url_here';		// add --capy or --hcaptcha at the end, to submit capy or hCaptcha
    $params['sitekey'] = 'sitekey_here';
    // type: 1 - normal recaptcha, 2 - invisible recaptcha, 3 - v3 recaptcha, default: 1
    //$params['type'] = 3;    // optional
    //$params['v3_min_score'] = 0.3;          // min score to target when solving v3 - optional
    //$params['v3_action'] = 'homepage';      // action to use when solving v3 - optional
    // proxy to use when solving recaptcha, works with auth as well 126.45.34.53:123:user:password
    //$params['proxy'] = '126.45.34.53:123';  // - optional
    // user agent to use when resolving recaptcha - optional
    //$params['user_agent'] = 'Mozilla/5.0 (X11; Linux x86_64; rv:60.0) Gecko/20100101 Firefox/60.0'; // - optional
    //$params['data-s'] = 'recaptcha data-s value'; // - optional
    $captcha_id = $i->submit_recaptcha($params);
    echo 'Waiting for recaptcha to be completed ...';
    
    // check every 10 seconds if recaptcha was solved
    while ($i->in_progress($captcha_id))
        sleep(10);
    // completed at this point
    $recaptcha_response = $i->retrieve_recaptcha($captcha_id);
    echo "Recaptcha response: $recaptcha_response";

    // Other examples
    // -----------------
    // $i = new ImagetypersAPI($access_token, 123);      // use affiliateid
    // $i = new ImagetypersAPI($access_token, 123, 60);   // affiliate id and 60 seconds timeout
    // submit recaptcha with proxy from which it will be solved

    echo $i->was_proxy_used($captcha_id);			// tells if proxy submitted (if any) was used or not, and if not used, reason

    // echo $i->set_captcha_bad($captcha_id);       // set captcha bad
    // getters
    // echo $i->captcha_id();              // get last captcha id
    // echo $i->captcha_text();      	   // get last captcha text
    // echo $i->recaptcha_id();            // get last recaptcha id
    // echo $i->recaptcha_response();      // get last recaptcha response
    // echo $i->error();                   // get last error   
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
