<?php

define('CAPTCHA_ENDPOINT', 'http://captchatypers.com/Forms/UploadFileAndGetTextNEW.ashx');
define('RECAPTCHA_SUBMIT_ENDPOINT', 'http://captchatypers.com/captchaapi/UploadRecaptchaV1.ashx');
define('RECAPTCHA_RETRIEVE_ENDPOINT', 'http://captchatypers.com/captchaapi/GetRecaptchaText.ashx');
define('BALANCE_ENDPOINT', 'http://captchatypers.com/Forms/RequestBalance.ashx');
define('BAD_IMAGE_ENDPOINT', 'http://captchatypers.com/Forms/SetBadImage.ashx');
define('PROXY_CHECK_ENDPOINT', 'http://captchatypers.com/captchaAPI/GetReCaptchaTextJSON.ashx');
define('GEETEST_SUBMIT_ENDPOINT', 'http://captchatypers.com/captchaapi/UploadGeeTest.ashx');
define('GEETEST_SUBMIT_ENDPOINT_TOKEN', 'http://captchatypers.com/captchaapi/UploadGeeTestToken.ashx');
define('RETRIEVE_JSON_ENDPOINT', 'http://captchatypers.com/captchaapi/GetCaptchaResponseJson.ashx');
define('CAPY_ENDPOINT', 'http://captchatypers.com/captchaapi/UploadCapyCaptchaUser.ashx');
define('HCAPTCHA_ENDPOINT', 'http://captchatypers.com/captchaapi/UploadHCaptchaUser.ashx');
define('TIKTOK_ENDPOINT', 'http://captchatypers.com/captchaapi/UploadTikTokCaptchaUser.ashx');

define('CAPTCHA_ENDPOINT_CONTENT_TOKEN', 'http://captchatypers.com/Forms/UploadFileAndGetTextNEWToken.ashx');
define('CAPTCHA_ENDPOINT_URL_TOKEN', 'http://captchatypers.com/Forms/FileUploadAndGetTextCaptchaURLToken.ashx');
define('RECAPTCHA_SUBMIT_ENDPOINT_TOKEN', 'http://captchatypers.com/captchaapi/UploadRecaptchaToken.ashx');
define('RECAPTCHA_RETRIEVE_ENDPOINT_TOKEN', 'http://captchatypers.com/captchaapi/GetRecaptchaTextToken.ashx');
define('BALANCE_ENDPOINT_TOKEN', 'http://captchatypers.com/Forms/RequestBalanceToken.ashx');
define('BAD_IMAGE_ENDPOINT_TOKEN', 'http://captchatypers.com/Forms/SetBadImageToken.ashx');
define('PROXY_CHECK_ENDPOINT_TOKEN', 'http://captchatypers.com/captchaAPI/GetReCaptchaTextTokenJSON.ashx');

define('USER_AGENT', 'phpAPI1.0');

// Utils class
class Utils {

    // Check if string starts with
    public static function starts_with($haystack, $needle) {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    // Make post request
    public static function post($url, $params, $user_agent, $timeout) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        $results = curl_exec($ch);
        curl_close($ch);

        return trim($results);
    }

    // Read file
    public static function read_file($file_path) {
        // check if file exists
        if (!file_exists($file_path)) {
            throw new Exception("captcha file does not exist: " . $file_path);
        }
        $fp = fopen($file_path, "rb");      // open file
        if (!$fp)
            throw new Exception("cannot read captcha file: " . $file_path);
        $file_size = filesize($file_path);      // get file size

        if ($file_size <= 0)        // check it's length (if OK)
            throw new Exception("cannot read captcha file: " . $file_path);

        $data = fread($fp, $file_size);     // read file
        fclose($fp);                        // close file

        $b64_data = base64_encode($data);   // encode it to base64
        return $b64_data;                   // return it
    }

}

class ImagetyperzAPI {

    private $_access_token;
    private $_username = '';
    private $_password = '';
    private $_affiliate_id;
    private $_timeout;

    function __construct($access_token, $affiliate_id = 0, $timeout = 120) {
        $this->_access_token = $access_token;
        $this->_affiliate_id = $affiliate_id;
        $this->_timeout = $timeout;
    }

    // Set username and password - DEPRECATED - token should be used
    function set_user_password($user, $password) {
        $this->_username = $user;
        $this->_password = $password;
    }

    function submit_image($captcha_file, $optional_arguments) {
        $data = array();
        # if username is set, act accordingly
        if (!empty($this->_username)) {
            // if http url given, doesn't work with this type of auth
            if (strpos($captcha_file, 'http') === 0) {
                throw new Exception('captcha file as HTTP URL does not work currently'
                        . ' if authenticated with username and password');
            }
            $data['username'] = $this->_username;
            $data['password'] = $this->_password;
            $url = CAPTCHA_ENDPOINT;
            $image_data = Utils::read_file($captcha_file);        // read file and b64 encode
        } else {
            if (Utils::starts_with(strtolower($captcha_file), 'http')) { // check if it's a local file or URL
                $url = CAPTCHA_ENDPOINT_URL_TOKEN;
                $image_data = $captcha_file;
            } else {
                // local file
                $url = CAPTCHA_ENDPOINT_CONTENT_TOKEN;
                $image_data = Utils::read_file($captcha_file);        // read file and b64 encode
            }
            $data['token'] = $this->_access_token;
        }

        $data["action"] = "UPLOADCAPTCHA";
        $data["file"] = $image_data;
        if (!empty($this->_affiliate_id)) $data['affiliateid'] = $this->_affiliate_id;

        // add optional parameters to request body
        foreach ($optional_arguments as $key => $value) $data[$key] = $value;

        $response = Utils::post($url, $data, USER_AGENT, $this->_timeout);
        // if file is sent as b64, uploading file ... is in response too, remove it
        $response = str_replace("Uploading file...", "", $response);
        if (strpos($response, 'ERROR:') !== false) {
            throw new Exception(trim(explode('ERROR:', $response)[1]));
        }
        // we have a good response here
        // save captcha to obj and return solved text
        return explode('|', $response)[0];
    }

    function submit_recaptcha($d) {
        $data = array(
            "action" => "UPLOADCAPTCHA",
            "pageurl" => $d['page_url'],
            "googlekey" => $d['sitekey'],
        );

        if (!empty($this->_username)) {
            $data["username"] = $this->_username;
            $data["password"] = $this->_password;
            $url = RECAPTCHA_SUBMIT_ENDPOINT;
        } else {
            $data['token'] = $this->_access_token;
            $url = RECAPTCHA_SUBMIT_ENDPOINT_TOKEN;
        }

        // affiliate
        if (!empty($this->_affiliate_id)) {
            $data['affiliateid'] = $this->_affiliate_id;
        }

        // check for proxy
        if (isset($d['proxy'])) {
            // we have a good proxy here (at least both params supplied)
            // set it to the data/params
            $data["proxy"] = $d['proxy'];
        }
        // check for user agent
        if (isset($d['user_agent'])) $data["useragent"] = $d['user_agent'];

        // v3
        $data['recaptchatype'] = '0';
        // check for other v3 params
        if (isset($d['type'])) $data["recaptchatype"] = (string)$d['type'];
        if (isset($d['v3_action'])) $data["captchaaction"] = $d['v3_action'];
        if (isset($d['v3_min_score'])) $data["score"] = (string)$d['v3_min_score'];
        if (isset($d['data-s'])) $data["data-s"] = (string)$d['data-s'];
        $response = Utils::post($url, $data, USER_AGENT, $this->_timeout);
        if (strpos($response, 'ERROR:') !== false) {
            throw new Exception(trim(explode('ERROR:', $response)[1]));
        }
        // we have a good response here
        // save captcha to obj and return solved text
        return $response;
    }

    function submit_geetest($d) {
        $data = array(
            "action" => "UPLOADCAPTCHA",
            "domain" => $d['domain'],
            "challenge" => $d['challenge'],
            "gt" => $d['gt']
        );

        if (!empty($this->_username)) {
            $data["username"] = $this->_username;
            $data["password"] = $this->_password;
            $url = GEETEST_SUBMIT_ENDPOINT;
        } else {
            $data['token'] = $this->_access_token;
            $url = GEETEST_SUBMIT_ENDPOINT_TOKEN;
        }

        // affiliate
        if (!empty($this->_affiliate_id)) {
            $data['affiliateid'] = $this->_affiliate_id;
        }

        // check for proxy
        if (isset($d['proxy'])) {
            // we have a good proxy here (at least both params supplied)
            // set it to the data/params
            $data["proxy"] = $d['proxy'];
        }
        // check for user agent
        if (isset($d['user_agent'])) $data["useragent"] = $d['user_agent'];
        $q = http_build_query($d);
        $url = "$url?$q";
        $response = Utils::post($url, $data, USER_AGENT, $this->_timeout);
        if (strpos($response, 'ERROR:') !== false) {
            throw new Exception(trim(explode('ERROR:', $response)[1]));
        }
        // we have a good response here
        // save captcha to obj and return solved text
        return $response;
    }

    function submit_capy($d) {
        $data = array(
            "action" => "UPLOADCAPTCHA",
            "pageurl" => $d['page_url'],
            "sitekey" => $d['sitekey'],
            "captchatype" => "12"
        );

        if (!empty($this->_username)) {
            $data["username"] = $this->_username;
            $data["password"] = $this->_password;
        } else {
            $data['token'] = $this->_access_token;
        }

        // affiliate
        if (!empty($this->_affiliate_id)) {
            $data['affiliateid'] = $this->_affiliate_id;
        }

        // check for proxy
        if (isset($d['proxy'])) {
            // we have a good proxy here (at least both params supplied)
            // set it to the data/params
            $data["proxy"] = $d['proxy'];
        }
        // check for user agent
        if (isset($d['user_agent'])) $data["useragent"] = $d['user_agent'];
        $response = Utils::post(CAPY_ENDPOINT, $data, USER_AGENT, $this->_timeout);
        if (strpos($response, 'ERROR:') !== false) {
            throw new Exception(trim(explode('ERROR:', $response)[1]));
        }
        return json_decode($response, true)[0]['CaptchaId'];
    }

    function submit_hcaptcha($d) {
        $data = array(
            "action" => "UPLOADCAPTCHA",
            "pageurl" => $d['page_url'],
            "sitekey" => $d['sitekey'],
            "captchatype" => "11"
        );

        if (!empty($this->_username)) {
            $data["username"] = $this->_username;
            $data["password"] = $this->_password;
        } else {
            $data['token'] = $this->_access_token;
        }

        // affiliate
        if (!empty($this->_affiliate_id)) {
            $data['affiliateid'] = $this->_affiliate_id;
        }

        // check for proxy
        if (isset($d['proxy'])) {
            // we have a good proxy here (at least both params supplied)
            // set it to the data/params
            $data["proxy"] = $d['proxy'];
        }
        // check for user agent
        if (isset($d['user_agent'])) $data["useragent"] = $d['user_agent'];
        $response = Utils::post(HCAPTCHA_ENDPOINT, $data, USER_AGENT, $this->_timeout);
        if (strpos($response, 'ERROR:') !== false) {
            throw new Exception(trim(explode('ERROR:', $response)[1]));
        }
        return json_decode($response, true)[0]['CaptchaId'];
    }

    function submit_tiktok($d) {
        $data = array(
            "action" => "UPLOADCAPTCHA",
            "pageurl" => $d['page_url'],
            "cookie_input" => $d['cookie_input'],
            "captchatype" => "10"
        );

        if (!empty($this->_username)) {
            $data["username"] = $this->_username;
            $data["password"] = $this->_password;
        } else {
            $data['token'] = $this->_access_token;
        }

        // affiliate
        if (!empty($this->_affiliate_id)) {
            $data['affiliateid'] = $this->_affiliate_id;
        }

        // check for proxy
        if (isset($d['proxy'])) {
            // we have a good proxy here (at least both params supplied)
            // set it to the data/params
            $data["proxy"] = $d['proxy'];
        }
        // check for user agent
        if (isset($d['user_agent'])) $data["useragent"] = $d['user_agent'];
        $response = Utils::post(TIKTOK_ENDPOINT, $data, USER_AGENT, $this->_timeout);
        if (strpos($response, 'ERROR:') !== false) {
            throw new Exception(trim(explode('ERROR:', $response)[1]));
        }
        return json_decode($response, true)[0]['CaptchaId'];
    }

    function retrieve_response($captcha_id) {
        $data = array(
            "action" => "GETTEXT",
            "captchaid" => $captcha_id,
        );

        if (!empty($this->_username)) {
            $data["username"] = $this->_username;
            $data["password"] = $this->_password;
        } else {
            $data['token'] = $this->_access_token;
        }

        // affiliate
        if (!empty($this->_affiliate_id)) {
            $data['affiliateid'] = $this->_affiliate_id;
        }

        // check for proxy
        if (isset($d['proxy'])) {
            // we have a good proxy here (at least both params supplied)
            // set it to the data/params
            $data["proxy"] = $d['proxy'];
        }
        // check for user agent
        if (isset($d['user_agent'])) $data["useragent"] = $d['user_agent'];

        // check for other v3 params
        $response = Utils::post(RETRIEVE_JSON_ENDPOINT, $data, USER_AGENT, $this->_timeout);
        if (strpos($response, 'ERROR:') !== false) {
            $x = trim(explode('ERROR:', $response)[1]);
            $y = explode('"', $x)[0];
            throw new Exception($y);
        }
        // we have a good response here
        // save captcha to obj and return solved text
        $js = json_decode($response, true)[0];
        if ($js['Status'] === 'Pending') return null;
        return $js;
    }

    // Get account balance
    function account_balance() {
        $data = array();

        if (!empty($this->_username)) {
            $data["username"] = $this->_username;
            $data["password"] = $this->_password;
            $url = BALANCE_ENDPOINT;
        } else {
            $data['token'] = $this->_access_token;
            $url = BALANCE_ENDPOINT_TOKEN;
        }

        $data["action"] = "REQUESTBALANCE";
        $data["submit"] = "Submit";

        $response = Utils::post($url, $data, USER_AGENT, $this->_timeout);
        if (strpos($response, 'ERROR:') !== false) {
            throw new Exception(trim(explode('ERROR:', $response)[1]));
        }

        return '$' . $response;     // return response
    }

    // Set captcha bad
    function set_captcha_bad($captcha_id) {
        // set data array
        $data = array(
            "action" => "SETBADIMAGE",
            "imageid" => $captcha_id,
            "submit" => "Submissssst"
        );

        if (!empty($this->_username)) {
            $data["username"] = $this->_username;
            $data["password"] = $this->_password;
            $url = BAD_IMAGE_ENDPOINT;
        } else {
            $data['token'] = $this->_access_token;
            $url = BAD_IMAGE_ENDPOINT_TOKEN;
        }

        // do request
        $response = Utils::post($url, $data, USER_AGENT, $this->_timeout);
        // parse response
        if (strpos($response, 'ERROR:') !== false) {
            throw new Exception(trim(explode('ERROR:', $response)[1]));
        }

        return $response;     // return response
    }
}

?>
