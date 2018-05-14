imagetyperz-api-php - Imagetyperz API wrapper
=========================================

imagetyperzapi is a super easy to use bypass captcha API wrapper for imagetyperz.com captcha service

## Installation

    composer require imagetyperzapi/imagetyperzapi

or
    
    git clone https://github.com/imagetyperz-api/imagetyperz-api-php

## How to use?

Simply require the module, set the auth details and start using the captcha service:

``` php
require('lib/imagetyperzapi.php');      // load API library
```

Set access_token or username and password (legacy) for authentication
``` php
// get access token from: http://www.imagetyperz.com/Forms/ClientHome.aspx
$access_token = 'your_access_token_here';
$i = new ImagetyperzAPI($access_token);   
```

Legacy way, will get deprecated at some point

``` php
$i->set_user_password('your_user', 'your_password');  
```

Once you've set your authentication details, you can start using the API

**Get balance**

``` php
$balance = $i->account_balance();
```

**Submit image captcha**

``` php
$captcha_text = $i->solve_captcha('captcha.jpg');
```
**Works with URL instead of image file but only if authenticated with token**
``` php
$captcha_text = $i->solve_captcha('http://scurt.pro/captcha.jpg');
```
**Submit recaptcha details**

For recaptcha submission there are two things that are required.
- page_url
- site_key
``` php
$captcha_id = $i->submit_recaptcha($page_url, $sitekey);
```
This method returns a captchaID. This ID will be used next, to retrieve the g-response, once workers have 
completed the captcha. This takes somewhere between 10-80 seconds.

**Retrieve captcha response**

Once you have the captchaID, you check for it's progress, and later on retrieve the gresponse.

The ***in_progress($captcha_id)*** method will tell you if captcha is still being decoded by workers.
Once it's no longer in progress, you can retrieve the gresponse with ***retrieve_recaptcha($captcha_id)***  

```php
while ($i->in_progress($captcha_id))
    sleep(10);
// completed at this point
$recaptcha_response = $i->retrieve_recaptcha($captcha_id);
```

## Other methods/variables

**Affiliate id**

The constructor accepts a 2nd parameter, as the affiliate id. 
``` php
$i = new ImagetypersAPI($access_token, 123);
```

**Requests timeout**

As a 3rd parameter in the constructor, you can specify a timeout for the requests (in seconds)
``` php
$i = new ImagetypersAPI($access_token, 123, 60);
```

**Submit recaptcha with proxy**

When a proxy is submitted with the recaptcha details, the workers will complete the captcha using
the provided proxy/IP.

``` php
$captcha_id = $i->submit_recaptcha($page_url, $sitekey, "12.34.45.78:1234");
```
Proxy with authentication is also supported
``` php
$captcha_id = $i->submit_recaptcha($page_url, $sitekey, "12.34.45.78:1234:user:pass");
```

**Set captcha bad**

When a captcha was solved wrong by our workers, you can notify the server with it's ID,
so we know something went wrong.

``` php
$i->set_captcha_bad($captcha_id); 
```

## Examples
Check example.php

## License
API library is licensed under the MIT License

## More information
More details about the server-side API can be found [here](http://imagetyperz.com)


<sup><sub>captcha, bypasscaptcha, decaptcher, decaptcha, 2captcha, deathbycaptcha, anticaptcha, 
bypassrecaptchav2, bypassnocaptcharecaptcha, bypassinvisiblerecaptcha, captchaservicesforrecaptchav2, 
recaptchav2captchasolver, googlerecaptchasolver, recaptchasolverpython, recaptchabypassscript</sup></sub>

