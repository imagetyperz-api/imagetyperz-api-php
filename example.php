#!/usr/bin/php

<?php
// ----------------------------------------
// Imagetypers API example
// ----------------------------------------

require('lib/imagetyperzapi.php');      // load API library
// Test method

function test_api() {
    $access_token = 'your_access_token_here';
    $i = new ImagetyperzAPI($access_token);      // init API lib obj
    //legacy (will get deprecated at some point)
    //$i->set_user_password('your_username', 'your_password');

    // check account balance
    // --------------------------
    $balance = $i->account_balance();       // get balance
    echo "Balance: $balance";

    // works 
    echo 'Solving captcha ...';
    $captcha_text = $i->solve_captcha('captcha.jpg');
    echo "Captcha text: $captcha_text";
    die('here');
    // solve recaptcha
    // --------------------------------------------------------------------
    // check: http://www.imagetyperz.com/Forms/recaptchaapi.aspx on how to get page_url and googlekey
    $page_url = 'your_page_url_here';
    $sitekey = 'your_sitekey_here';
    echo 'Submitting recaptcha...';
    $captcha_id = $i->submit_recaptcha($page_url, $sitekey);

    echo 'Waiting for recaptcha to be completed ...';
    //echo 'Waiting for recaptcha to be solved ...';
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
    // $captcha_id = $i->submit_recaptcha($page_url, $sitekey, "12.34.45.78:1234");
    // $captcha_id = $i->submit_recaptcha($page_url, $sitekey, "12.34.45.78:1234:user:pass");	// proxy authentication
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
