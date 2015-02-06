<?php
/*
 * Copyright 2010 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
  This is a basic drop-in replacement for a subset of the Google API PHP Client
  It should allow use of Bitshares based blockchain login in place of OAuth authentication,
  allowing a relative straight forward port of any plugin that relies on the Google-Plus api.

  * Note that this is a small subset of oauth *
  * https://github.com/google/google-api-php-client
 
  Previous code would have something like this.  The Service class is ignored and not implemented

  $client = new Bitshares();
  $oauth2 = new apiOauth2Service($client); // This is no longer used.

*/
// Check for the required json and curl extensions, the Google API PHP Client won't function without them.
if (!function_exists('json_decode')) {
    throw new Exception('Bitshares PHP API Client requires the JSON PHP extension');
}
/*
if (!function_exists('http_build_query')) {
    throw new Exception('Bitshares PHP API Client requires http_build_query()');
}
*/
if (!ini_get('date.timezone') && function_exists('date_default_timezone_set')) {
    date_default_timezone_set('UTC');
}

// hack around with the include paths a bit so the library 'just works'
$cwd = dirname(__FILE__);
set_include_path("$cwd" . PATH_SEPARATOR . get_include_path());


require_once 'easybitcoin.php';


class Bitshares {

    // THere isnt a constructor written for these directly.. 
    private $RPC_SERVER_ADDRESS;
    private $RPC_SERVER_PORT;
    private $RPC_SERVER_USER;
    private $RPC_SERVER_PASS;
    private $RPC_SERVER_WALLET;
    private $RPC_SERVER_WALLET_PASS;
    private $BITSHARES_USER_NAME;
    private $SITE_DOMAIN;

    private $authenticated = false;
    private $uid = - 1;
    private $userinfo = null;
    private $authenticateUnRegisteredBlockchain = true; // let em through the gate?

    function __construct() { 
		require_once '/../../../bitshares/config.php';


        // wallet settings
        $this->RPC_SERVER_ADDRESS = 'localhost';
        $this->RPC_SERVER_PORT = rpcPort;
        $this->RPC_SERVER_USER = rpcUser;
        $this->RPC_SERVER_PASS = rpcPass;

        $this->BITSHARES_USER_NAME = accountName;
        $this->SITE_DOMAIN = baseURL;

        //
        $this->authenticateUnRegisteredBlockchain = false;
    }

    

    /*
    We determined that a troll-athon might happen if people can register with unregistered accounts and no email verification, so we are adding
    this to the plugin
    */
    public function setAuthenticateUnRegisteredBlockchain($v) {
    	   $authenticateUnRegisteredBlockchain = $v;
    }

    

    /* this was the original array used by the gplus/oauth plugin.  We need to determine what
     determined these and if they can be removed/ modified, but for now we leave it alone to maintain backwards compat
    */

    private function init_userinfo() {
		  $this->userinfo = array();
		  $this->userinfo['FullName'] = '';
		  $this->userinfo['ProfileName'] = '';
		  $this->userinfo['NickName'] = '';
		  $this->userinfo['FirstName'] = '';
		  $this->userinfo['LastName'] = '';
		  $this->userinfo['ID'] = '';
		  $this->userinfo['Provider'] = 'Bitshares-Login';
		  $this->userinfo['Email'] = array();
		  $emailArray = array();
		  $emailArray['Value'] = '';
		  array_push($this->userinfo['Email'], $emailArray);
		  $this->userinfo['ImageUrl'] = '';
		  $this->userinfo['BirthDate'] = '';
		  $this->userinfo['Gender'] = '';
		  $this->userinfo['Industry'] = 'CryptoCurrency';
		  $this->userinfo['Addresses'] = '';
		  $this->userinfo['HomeTown'] = '';
		  $this->userinfo['About'] = '';
		  $this->userinfo['ProfileUrl'] = '';
		  $this->userinfo['State'] = '';
		  $this->userinfo['City'] = '';
		  $this->userinfo['LocalCity'] = '';
		  $this->userinfo['Country'] = '';

    }

    // TODO grep for throw and look at exceptions, try to implement them in the same way
    // review 'token' php variable and make sure we are using it properly TODO
    /*
     * The functionality this is supposed to duplicate either returns a token string or does an exception
     */
    public function authenticate() {

        $this->authenticated = false;
        $bitshares = new Bitcoin($this->RPC_SERVER_USER, $this->RPC_SERVER_PASS, $this->RPC_SERVER_ADDRESS, $this->RPC_SERVER_PORT);

        //  _GET has client_key,client_name,server_key,signed_secret
        if (isset($_REQUEST["client_key"])) {

            //  inspect loginPackage .. has user_account_key  and shared_secret

            $loginPackage = $bitshares->wallet_login_finish($_REQUEST["server_key"], $_REQUEST["client_key"], $_REQUEST["signed_secret"]);
            if ($bitshares->status != 200) {
                throw new Exception("wallet_login_finish failed");
            }
            if (! empty($bitshares->error) ) {
                throw Exception($bitshares->error);
            }

            $this->init_userinfo();
            $this->authenticated = (bool)$loginPackage; // TODO look at return code in php and trigger off working value and trigger off !=

            if ($this->authenticated == false) {
                throw new Exception("Authentication failed.");
            }

            if (isset($_REQUEST['signed_secret'])) {
                $this->setAccessToken($_REQUEST['signed_secret']); // So well set the token to be signed_secret.. dont have a better solution
            }

            $this->uid = $loginPackage["user_account_key"]; // Is this used anywhere? TODO
            $this->userinfo['ID'] = $this->uid; // later this turns into the btsid or gid or somesuch

            // if userAccount is null it may be because the account is not yet registered.
            $userAccount = $bitshares->blockchain_get_account($_GET['client_name']);

            if (empty($userAccount)) {
                if ($this->authenticateUnRegisteredBlockchain) {
                   $this->userinfo['ProfileName'] = $_REQUEST['client_name'];
					$this->userinfo['About'] = "Non-Registered Bitshares Account";
                } else {
                    throw new Exception("The BitShares account does not appear to be registered on the blockchain. Please register the account");
                }
            } else {
                $this->userinfo['ProfileName'] = $userAccount['name'];
                $this->userinfo['About'] = "Registered Bitshares Account";
            }

            $this->userinfo['ImageUrl'] = 'http://robohash.org/' . $this->userinfo['ProfileName'];
            if (isset($userAccount["delegate_info"])) { // TODO add to test case
                $this->userinfo['About'] .= ", Delegate";
            }
            if (!empty($userAccount)) {
				 $this->userinfo['About'] .= ", Registration Date: ". $userAccount['registration_date'];
				 $this->userinfo['About'] .= ", Last Update: ". $userAccount['last_update'];
				 if(isset($userAccount['public_data']))
				 {
				 	if(isset($userAccount['public_data']['gui_data']))
					{
				 		if(isset($userAccount['public_data']['gui_data']['website']))
						{	
							$this->userinfo['ProfileUrl'] = $userAccount['public_data']['gui_data']['website'];
						}					
					}
				 }
            }
            
           
            return $this->getAccessToken();
        } else {
            throw new Exception("URL is malformed. Stop hacking.");
        }

    }

    /* Original function from oAuth 
    * Set the OAuth 2.0 access token using the string that resulted from calling createAuthUrl()
    * or Google_Client#getAccessToken().
    * @param string $accessToken JSON encoded string containing in the following format:
    * {"access_token":"TOKEN", "refresh_token":"TOKEN", "token_type":"Bearer",
    *  "expires_in":3600, "id_token":"TOKEN", "created":1320790426}
     However we are not receiving JSON so this is our simplified version
    */
    public function setAccessToken($accessToken) {
        if ($accessToken == null || 'null' == $accessToken) {
            $accessToken = null;
            throw new apiAuthException('Access Token not valid');
        }
        $this->accessToken = $accessToken;
    }

    public function getAccessToken() {
        $token = $this->accessToken;
        return (null == $token || 'null' == $token) ? null : $token;
    }

    /*
    This replaces code in the Services class that was migrated into the client class
    Original code was something like $oauth2->userinfo->get()
    */
    public function userinfo_get() {
    	// userinfo is a 10 element array into $user id,email,verifiedemail,name,givenname,faimlyname,link,picture, generic,locale 
        return json_decode(json_encode($this->userinfo), FALSE); 
        
    }
	public function remove_http($url) {
	   $disallowed = array('http://', 'https://');
	   foreach($disallowed as $d) {
		  if(strpos($url, $d) === 0) {
			 return str_replace($d, '', $url);
		  }
	   }
	   return $url;
	}
    /*
    This is the code that calls the wallet to generate the authentication URL.
    That URL should be something like "bts://login ..." which will then load up the local wallet if the machine is configured properly

    It has a side effect of setting g_authurl_error which is a global. perhaps this should be the arguement to an exception.  This is not consistent in that regard but is how mimicking gauth seemed to best be accomplished at the time
    */
    public function createAuthUrl($redirectURL) {

        global $g_authurl_error; 
		$redirectURL = $this->remove_http($redirectURL);
        $bitshares = new Bitcoin($this->RPC_SERVER_USER, $this->RPC_SERVER_PASS, $this->RPC_SERVER_ADDRESS, $this->RPC_SERVER_PORT);
	
        $loginStart = $bitshares->wallet_login_start($this->BITSHARES_USER_NAME);
        if (($bitshares->status != 200) || empty($loginStart) || ($loginStart == 'null')) {
            $g_authurl_error = $bitshares->error;
            return false;
        }
		return $loginStart . $redirectURL; 
    }
}