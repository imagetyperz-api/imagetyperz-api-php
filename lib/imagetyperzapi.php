<?php

define('CAPTCHA_ENDPOINT', 'http://captchatypers.com/Forms/UploadFileAndGetTextNEW.ashx');
define('RECAPTCHA_SUBMIT_ENDPOINT', 'http://captchatypers.com/captchaapi/UploadRecaptchaV1.ashx');
define('RECAPTCHA_RETRIEVE_ENDPOINT', 'http://captchatypers.com/captchaapi/GetRecaptchaText.ashx');
define('BALANCE_ENDPOINT', 'http://captchatypers.com/Forms/RequestBalance.ashx');
define('BAD_IMAGE_ENDPOINT', 'http://captchatypers.com/Forms/SetBadImage.ashx');
define('CAPTCHA_ENDPOINT_CONTENT_TOKEN', 'http://captchatypers.com/Forms/UploadFileAndGetTextNEWToken.ashx');
define('CAPTCHA_ENDPOINT_URL_TOKEN', 'http://captchatypers.com/Forms/FileUploadAndGetTextCaptchaURLToken.ashx');
define('RECAPTCHA_SUBMIT_ENDPOINT_TOKEN', 'http://captchatypers.com/captchaapi/UploadRecaptchaToken.ashx');
define('RECAPTCHA_RETRIEVE_ENDPOINT_TOKEN', 'http://captchatypers.com/captchaapi/GetRecaptchaTextToken.ashx');
define('BALANCE_ENDPOINT_TOKEN', 'http://captchatypers.com/Forms/RequestBalanceToken.ashx');
define('BAD_IMAGE_ENDPOINT_TOKEN', 'http://captchatypers.com/Forms/SetBadImageToken.ashx');

define('USER_AGENT', 'phpAPI1.0');

// Captcha class
class Captcha {

    private $_captcha_id = '';
    private $_text = '';

    function __construct($response) {
        $a = explode('|', $response);       // split response
        if (sizeof($a) < 2) {                  // check if right length
            throw new Exception("cannot parse response from server: " . $response);
        }
        $this->_captcha_id = $a[0];
        $this->_text = join('|', array_slice($a, 1, sizeof($a)));
    }

    // Get captcha ID
    function captcha_id() {
        return $this->_captcha_id;
    }

    // Get captcha text
    function text() {
        return $this->_text;
    }

}

// Recaptcha class
class Recaptcha {

    private $_captcha_id = '';
    private $_response = '';

    function __construct($captcha_id) {
        $this->_captcha_id = $captcha_id;        // set captcha ID on obj
    }

    // Set response
    function set_response($response) {
        $this->_response = $response;
    }

    // Get captcha ID
    function captcha_id() {
        return $this->_captcha_id;
    }

    // Get response
    function response() {
        return $this->_response;
    }

}

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
    private $_captcha = null;
    private $_recaptcha = null;
    private $_error = '';

    function __construct($access_token, $affiliate_id = 0, $timeout = 120) {
        $this->_access_token = $access_token;
        $this->_affiliate_id = $affiliate_id;
        $this->_timeout = $timeout;
    }

    // Set username and password - token should be used though
    function set_user_password($user, $password) {
        $this->_username = $user;
        $this->_password = $password;
    }

    // Solve captcha
    function solve_captcha($captcha_file, $case_sensitive = FALSE) {
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
        $data["chkCase"] = (int) $case_sensitive;
        $data["file"] = $image_data;
        if (!empty($this->_affiliate_id)) {
            $data['affiliateid'] = $this->_affiliate_id;
        }

        $response = Utils::post($url, $data, USER_AGENT, $this->_timeout);
        // if file is sent as b64, uploading file ... is in response too, remove it
        $response = str_replace("Uploading file...", "", $response);
        if (strpos($response, 'ERROR:') !== false) {
            $response_err = trim(explode('ERROR:', $response)[1]);
            $this->_error = $response_err;
            throw new Exception($response_err);
        }
        // we have a good response here
        // save captcha to obj and return solved text
        $this->_captcha = new Captcha($response);
        return $this->_captcha->text();     // return captcha text
    }

    // Submit recaptcha
    function submit_recaptcha($page_url, $sitekey, $proxy = '') {
        $data = array(
            "action" => "UPLOADCAPTCHA",
            "pageurl" => $page_url,
            "googlekey" => $sitekey,
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
        if (isset($proxy)) {
            // we have a good proxy here (at least both params supplied)
            // set it to the data/params
            $data["proxy"] = $proxy;
        }

        $response = Utils::post($url, $data, USER_AGENT, $this->_timeout);
        if (strpos($response, 'ERROR:') !== false) {
            $response_err = trim(explode('ERROR:', $response)[1]);
            $this->_error = $response_err;
            throw new Exception($response_err);
        }
        // we have a good response here
        // save captcha to obj and return solved text
        $this->_recaptcha = new Recaptcha($response);
        return $this->_recaptcha->captcha_id();     // return captcha text
    }

    // Get recaptcha response using captcha ID
    function retrieve_recaptcha($captcha_id) {
        $data = array(
            "action" => "GETTEXT",
            "username" => $this->_username,
            "password" => $this->_password,
            "captchaid" => $captcha_id,
        );

        if (!empty($this->_username)) {
            $data["username"] = $this->_username;
            $data["password"] = $this->_password;
            $url = RECAPTCHA_RETRIEVE_ENDPOINT;
        } else {
            $data['token'] = $this->_access_token;
            $url = RECAPTCHA_RETRIEVE_ENDPOINT_TOKEN;
        }

        $response = Utils::post($url, $data, USER_AGENT, $this->_timeout);
        if (strpos($response, 'ERROR:') !== false) {
            $response_err = trim(explode('ERROR:', $response)[1]);
            // save it to obj error only if it's not, NOT_DECODED
            if (strpos($response_err, 'NOT_DECODED') !== false) {
                $this->_error = $response_err;
            }
            throw new Exception($response_err);
        }

        // set them to obj
        $this->_recaptcha = new Recaptcha($captcha_id);  // remake obj (in case submit wasn't used)
        $this->_recaptcha->set_response($response);      // set recaptcha response
        return $this->_recaptcha->response();            // return response
    }

    // Check if captcha is still in progress
    function in_progress($captcha_id) {
        try {
            $this->retrieve_recaptcha($captcha_id);     // retrieve captcha
            return FALSE;                               // not in progress anymore
        } catch (Exception $ex) {
            if (strpos($ex->getMessage(), 'NOT_DECODED') !== false) {
                return TRUE;                            // still "decoding" it
            }
        }
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
            $response_err = trim(explode('ERROR:', $response)[1]);
            $this->_error = $response_err;
            throw new Exception($response_err);
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
            $response_err = trim(explode('ERROR:', $response)[1]);
            $this->_error = $response_err;
            throw new Exception($response_err);
        }

        return $response;     // return response
    }

    // Get last captcha text
    function captcha_text() {
        if (is_null($this->_captcha)) {
            return "";
        }
        return $this->_captcha->text();
    }

    // Get last captcha ID
    function captcha_id() {
        if (is_null($this->_captcha)) {
            return "";
        }
        return $this->_captcha->captcha_id();
    }

    // Get last recaptcha ID
    function recaptcha_id() {
        if (is_null($this->_recaptcha)) {
            return "";
        }
        return $this->_recaptcha->captcha_id();
    }

    // Get last recaptcha response
    function recaptcha_response() {
        if (is_null($this->_recaptcha)) {
            return "";
        }
        return $this->_recaptcha->response();
    }

    // Return last error
    function error() {
        return $this->_error;
    }

}

?>
