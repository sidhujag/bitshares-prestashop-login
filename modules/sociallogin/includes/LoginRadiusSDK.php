<?php
/**
 * NOTICE OF LICENSE
 *
 * @package   sociallogin Add Social login in your Pretashop module
 * @author    LoginRadius Team
 * @copyright Copyright 2014 www.loginradius.com - All rights reserved.
 * @license   GNU GENERAL PUBLIC LICENSE Version 2, June 1991
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/**
 * LoginRadius SDK class
 */

define('LR_DOMAIN', 'api.loginradius.com');
/**
 * Class for Social Authentication.
 *
 * This is the main class to communicate with LogiRadius Unified Social API. It contains functions for Social Authentication with User Profile Data (Basic and Extended)
 *
 * Copyright 2013 LoginRadius Inc. - www.LoginRadius.com
 *
 * This file is part of the LoginRadius SDK package.
 *
 */
class LoginRadius
{

	/**
	 * LoginRadius function - It validates against GUID format of keys.
	 *
	 * @param string $value data to validate.
	 *
	 * @return boolean If valid - true, else - false
	 */
	public function loginRadiusIsValidGuid($value)
	{
		return preg_match('/^\{?[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}\}?$/i', $value);
	}

	/**
	 * LoginRadius function - Check, if it is a valid callback i.e. LoginRadius authentication token is set
	 *
	 * @return boolean True, if a valid callback.
	 */
	public function loginRadiusIsCallback()
	{
		if (Tools::getValue('token'))
			return true;
		else
			return false;
	}

	/**
	 * LoginRadius function - Fetch LoginRadius access token after authentication. It will be valid for the specific duration of time specified in the response.
	 *
	 * @param string LoginRadius API Secret
	 *
	 * @return string LoginRadius access token.
	 */
	public function loginRadiusFetchAccessToken($secret)
	{
		if (!$this->loginRadiusIsValidGuid($secret))
			return false;

		$validate_url = 'https://'.LR_DOMAIN.'/api/v2/access_token?token='.Tools::getValue('token').'&secret='.$secret;
		$response = Tools::jsonDecode($this->loginRadiusApiClient($validate_url));

		if (isset($response->access_token) && $response->access_token != '')
			return $response->access_token;
		else
			return false;
	}

	/**
	 * LoginRadius function - To fetch social profile data from the user's social account after authentication. The social profile will be retrieved via oAuth and OpenID protocols. The data is normalized into LoginRadius' standard data format.
	 *
	 * @param string $accessToken LoginRadius access token
	 * @param boolean $raw If true, raw data is fetched
	 *
	 * @return object User profile data.
	 */
	public function loginRadiusGetUserProfileData($access_token, $check_curl = '', $raw = false)
	{
		$validate_url = 'https://'.LR_DOMAIN.'/api/v2/userprofile?access_token='.$access_token;

		if ($raw)
		{
			$validate_url = 'https://'.LR_DOMAIN.'/api/v2/userprofile/raw?access_token='.$access_token;
			return $this->loginRadiusApiClient($validate_url);
		}

		return Tools::jsonDecode($this->loginRadiusApiClient($validate_url, $check_curl));
	}

	/**
	 * Call LoginRadius API to get data.
	 */
	public function loginRadiusApiClient($validate_url, $check_curl = '', $postdata = array())
	{
		if (in_array('curl', get_loaded_extensions()) && ($check_curl || function_exists('curl_exec')))
		{
			$curl_handle = curl_init();
			curl_setopt($curl_handle, CURLOPT_URL, $validate_url);
			curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 15);
			curl_setopt($curl_handle, CURLOPT_TIMEOUT, 15);
			curl_setopt($curl_handle, CURLOPT_ENCODING, 'json');
			curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);

			if ($postdata)
			{
				curl_setopt($curl_handle, CURLOPT_POST, 1);
				curl_setopt($curl_handle, CURLOPT_POSTFIELDS, http_build_query($postdata));
			}

			if (ini_get('open_basedir') == '' && (ini_get('safe_mode') == 'Off' || !ini_get('safe_mode')))
			{
				curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
				$json_response = curl_exec($curl_handle);
			}
			else
			{
				curl_setopt($curl_handle, CURLOPT_HEADER, 1);
				$valid_url = curl_getinfo($curl_handle, CURLINFO_EFFECTIVE_URL);
				curl_close($curl_handle);
				$ch = curl_init();
				$url = str_replace('?', '/?', $valid_url);
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

				$json_response = curl_exec($ch);
				curl_close($ch);
			}
		}
		elseif (ini_get('allow_url_fopen') == 1)
		{
			if ($postdata)
			{
				$options = array('http' =>
					array(
						'method' => 'POST',
						'timeout' => 10,
						'header' => 'Content-type: application/x-www-form-urlencoded',
						'content' => http_build_query($postdata)
					)
				);
				$context = stream_context_create($options);
			}
			else
				$context = null;

			$json_response = Tools::file_get_contents($validate_url, false, $context);
		}

		return $json_response;
	}
}