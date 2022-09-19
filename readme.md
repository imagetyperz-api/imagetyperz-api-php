imagetyperz-api-php - Imagetyperz API wrapper
=========================================

imagetyperzapi is a super easy to use bypass captcha API wrapper for imagetyperz.com captcha service

## Installation
    composer require imagetyperzapi/imagetyperzapi

or
    
    git clone https://github.com/imagetyperz-api/imagetyperz-api-php

## Usage

Simply require the module, set the auth details and start using the captcha service:

``` php
require('lib/imagetyperzapi.php');      // load API library
```
Set access_token for authentication:

``` php
// get access token from: http://www.imagetyperz.com/Forms/ClientHome.aspx
$access_token = 'your_access_token_here';
$i = new ImagetyperzAPI($access_token);
```
Once you've set your authentication details, you can start using the API.

**Get balance**

``` php
$balance = $i->account_balance();
echo $balance;
```

## Solving
For solving a captcha, it's a two step process:
- **submit captcha** details - returns an ID
- use ID to check it's progress - and **get solution** when solved.

Each captcha type has it's own submission method.

For getting the response, same method is used for all types.


### Image captcha

``` php
$optional_parameters = array();
// $optional_parameters['iscase'] = 'true';            // case sensitive captcha
// $optional_parameters['ismath'] = 'true';            // instructs worker that a math captcha has to be solved
// $optional_parameters['isphrase'] = 'true';          // text contains at least one space (phrase)
// $optional_parameters['alphanumeric'] = '1';         // 1 - digits only, 2 - letters only
// $optional_parameters['minlength'] = '3';            // captcha text length (minimum)
// $optional_parameters['maxlength'] = '8';            // captcha text length (maximum)
captcha_id = $i->submit_image(image_path = 'captcha.jpg', $optional_parameters);
```
ID is used to retrieve solution when solved.

**Observation**
It works with URL instead of image file too.

### reCAPTCHA

For recaptcha submission there are two things that are required.
- page_url (**required**)
- site_key (**required**)
- type (optional, defaults to 1 if not given)
    - `1` - v2
    - `2` - invisible
    - `3` - v3
    - `4` - enterprise v2
    - `5` - enterprise v3
- v3_min_score - minimum score to target for v3 recaptcha `- optional`
- v3_action - action parameter to use for v3 recaptcha `- optional`
- proxy - proxy to use when solving recaptcha, eg. `12.34.56.78:1234` or `12.34.56.78:1234:user:password` `- optional`
- user_agent - useragent to use when solve recaptcha `- optional` 
- data-s - extra parameter used in solving recaptcha `- optional`
- cookie_input - cookies used in solving reCAPTCHA - `- optional`

``` php
d = {}
$params = array();
$params['page_url'] = 'page_url_here';
$params['sitekey'] = 'sitekey_here';
// $params['type'] = 1;    // optional
// $params['v3_min_score'] = 0.3;          // min score to target when solving v3 - optional
// $params['v3_action'] = 'homepage';      // action to use when solving v3 - optional
// $params['proxy'] = '126.45.34.53:123';  // - optional
// $params['user_agent'] = 'Mozilla/5.0 (X11; Linux x86_64; rv:60.0) Gecko/20100101 Firefox/60.0'; // optional
// $params['data-s'] = 'recaptcha data-s value'; // - optional
// $params['cookie_input'] = 'a=b;c=d';     // - optional
$captcha_id = $i->submit_recaptcha($params);
```
ID will be used to retrieve the g-response, once workers have 
completed the captcha. This takes somewhere between 10-80 seconds. 

Check **Retrieve response** 

### GeeTest

GeeTest is a captcha that requires 3 parameters to be solved:
- domain
- challenge
- gt
- api_server (optional)

The response of this captcha after completion are 3 codes:
- challenge
- validate
- seccode

**Important**
This captcha requires a **unique** challenge to be sent along with each captcha.

```php
$params = array();
$params['domain'] = 'your_domain';
$params['challenge'] = 'challenge_here';
$params['gt'] = 'gt_here';
// $params['api_server'] = 'api.geetest.com';  // - geetest domain - optional
// $params['proxy'] = '126.45.34.53:123';  // - optional
// $params['user_agent'] = 'Mozilla/5.0 (X11; Linux x86_64; rv:60.0) Gecko/20100101 Firefox/60.0'; // optional
$captcha_id = $i->submit_geetest($params);
```

Optionally, you can send proxy and user_agent along.

### GeeTestV4

GeeTesV4 is a new version of captcha from geetest that requires 2 parameters to be solved:

- domain
- geetestid (captchaID) - gather this from HTML source of page with captcha, inside the `<script>` tag you'll find a link that looks like this: https://i.imgur.com/XcZd47y.png

The response of this captcha after completion are 5 parameters:

- captcha_id
- lot_number
- pass_token
- gen_time
- captcha_output

```php
$params = array();
$params['domain'] = 'https://example.com';
$params['geetestid'] = '647f5ed2ed8acb4be36784e01556bb71';
$captcha_id = $i->submit_geetest_v4($params);
```

Optionally, you can send proxy and user_agent along.


### hCaptcha

Requires page_url and sitekey

```php
$params = array();
$params['page_url'] = 'https://your-site.com';
$params['sitekey'] = '1c7062c7-cae6-4e12-96fb-303fbec7fe4f';
//$params['invisible'] = '1';  // if captcha is invisible - optional

// extra parameters, useful for enterprise
// submit userAgent from requests too, when this is used
// $params['HcaptchaEnterprise'] = array(
//       "rqdata" => "take value from web requests"
//  );

//$params['proxy'] = '126.45.34.53:123';  // - optional
//$params['user_agent'] = 'Mozilla/5.0 (X11; Linux x86_64; rv:60.0) Gecko/20100101 Firefox/60.0'; // - optional
$captcha_id = $i->submit_hcaptcha($params);
```

### Capy

Requires page_url and sitekey

```php
$params = array();
$params['page_url'] = 'https://your-site.com';
$params['sitekey'] = 'Fme6hZLjuCRMMC3uh15F52D3uNms5c';
//$params['proxy'] = '126.45.34.53:123';  // - optional
//$params['user_agent'] = 'Mozilla/5.0 (X11; Linux x86_64; rv:60.0) Gecko/20100101 Firefox/60.0'; // - optional
$captcha_id = $i->submit_capy($params);
```

### Tiktok

Requires page_url cookie_input

```php
$params = array();
$params['page_url'] = 'https://tiktok.com';	
$params['cookie_input'] = 's_v_web_id:verify_kd6243o_fd449FX_FDGG_1x8E_8NiQ_fgrg9FEIJ3f;tt_webid:612465623570154;tt_webid_v2:7679206562717014313;SLARDAR_WEB_ID:d0314f-ce16-5e16-a066-71f19df1545f;';
//$params['proxy'] = '126.45.34.53:123';  // - optional
//$params['user_agent'] = 'Mozilla/5.0 (X11; Linux x86_64; rv:60.0) Gecko/20100101 Firefox/60.0'; // - optional
$captcha_id = $i->submit_tiktok($params);
```

### FunCaptcha

Requires page_url, sitekey and s_url

```php
$params = array();
$params['page_url'] = 'https://your-site.com';
$params['sitekey'] = '11111111-1111-1111-1111-111111111111';
$params['s_url'] = 'https://api.arkoselabs.com';
$params['data'] = '{"a":"b"}';
//$params['proxy'] = '126.45.34.53:123';  // - optional
//$params['user_agent'] = 'Mozilla/5.0 (X11; Linux x86_64; rv:60.0) Gecko/20100101 Firefox/60.0'; // - optional
$captcha_id = $i->submit_funcaptcha($params);
```

### Task

Requires template_name, pageurl and usually variables

```php
$params = array();
$params['template_name'] = 'Login test page';
$params['sitekey'] = '1c7062c7-cae6-4e12-96fb-303fbec7fe4f';
$params['pageurl'] = 'https://imagetyperz.net/automation/login';
$params['variables'] = array(
        "username" => "abc",
        "password" => 'paZZW0rd'
);
//$params['proxy'] = '126.45.34.53:123';  // - optional
//$params['user_agent'] = 'Mozilla/5.0 (X11; Linux x86_64; rv:60.0) Gecko/20100101 Firefox/60.0'; // - optional
$captcha_id = $i->submit_task($params);
```

## Retrieve response

Regardless of the captcha type (and method) used in submission of the captcha, this method is used
right after to check for it's solving status and also get the response once solved.

It requires one parameter, that's the **captcha ID** gathered from first step.

```php
$captcha_id = $i->submit_recaptcha($params);
echo "Waiting for captcha to be solved...\n";
$response = null;
while($response === null) {
    sleep(10);
    // works any type of captcha, here showing with recaptcha submission
    $response = $i->retrieve_response($captcha_id);
}
echo "Response: ";
var_dump($response);
```
The response is a JSON object that looks like this:
```json
{
  "CaptchaId": 176707908, 
  "Response": "03AGdBq24PBCbwiDRaS_MJ7Z...mYXMPiDwWUyEOsYpo97CZ3tVmWzrB", 
  "Cookie_OutPut": "", 
  "Proxy_reason": "", 
  "Recaptcha score": 0.0, 
  "Status": "Solved"
}
```

## Other methods/variables

**Affiliate id**

The constructor accepts a 2nd parameter, as the affiliate id. 
``` php
$i = new ImagetypersAPI($access_token, 123);      // use affiliateid
```

**Requests timeout**

As a 3rd parameter in the constructor, you can specify a timeout for the requests (in seconds)
``` php
$i = new ImagetypersAPI($access_token, 123, 60);      // use affiliateid
```

**Set captcha bad**

When a captcha was solved wrong by our workers, you can notify the server with it's ID,
so we know something went wrong.

``` php
$i->set_captcha_bad($captcha_id);
```

## Examples
Check root folder for examples, for each type of captcha.

## License
API library is licensed under the MIT License

## More information
More details about the server-side API can be found [here](http://imagetyperz.com)


<sup><sub>captcha, bypasscaptcha, decaptcher, decaptcha, 2captcha, deathbycaptcha, anticaptcha, 
bypassrecaptchav2, bypassnocaptcharecaptcha, bypassinvisiblerecaptcha, captchaservicesforrecaptchav2, 
recaptchav2captchasolver, googlerecaptchasolver, recaptchasolverphp, recaptchabypassscript</sup></sub>

