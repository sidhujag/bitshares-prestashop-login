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

if (!defined('_PS_VERSION_'))
	exit;

include_once(dirname(__FILE__).'/sociallogin_sharing.php');

/**
 * Build string of module settings.
 *
 * @return string complete string of all settigs of tabs.
 */
function loginRadiusSettingsBulid()
{
	$string = '~1#';
	$string .= loginRadiusGetValue(loginRadiusSocialLoginSettings());
	$string .= loginRadiusGetValue(loginRadiusSocialSharingSettings());
	$string .= loginRadiusGetValue(loginRadiusAdvanceSettings());
	return $string;
}

/**
 * Get value of module settings.
 *
 * @param array $data module settings
 * @return string Build string of module settings
 */
function loginRadiusGetValue($data)
{
	$string = '';

	foreach ($data as $key => $value)
	{
		if (is_string($value) && in_array($key, array('rearrange_settings', 'vertical_rearrange_settings', 'socialshare_counter_list', 'socialshare_show_counter_list', 'profilefield')))
		{
			$check_value = (unserialize($value));

			if (empty($check_value))
			{
				$sharing_key = 'counter';

				if ($key == 'rearrange_settings' || $key == 'vertical_rearrange_settings')
					$sharing_key = 'sharing';

				$value = serialize(loginRadiusGetSharingDefaultNetworks($sharing_key));
			}

			$string .= "['".implode("','", unserialize($value))."']".'|';
			continue;
		}

		if (is_string($value))
			$string .= "'".$value."'".'|';
		else
			$string .= $value.'|';
	}

	return $string;
}

/**
 * Validate the api credentials and module settings
 *
 * @return string if settings is not validated then return error message
 */
function loginRadiusModuleSettingsValidate()
{
	$module = new SocialLogin();
	include_once(dirname(__FILE__).'/LoginRadiusSDK.php');
	$obj = new LoginRadius();
	$string = loginRadiusSettingsBulid();
	$loginradius_api_key = trim(Tools::getValue('API_KEY'));
	$loginradius_api_secret = trim(Tools::getValue('API_SECRET'));
	$empty_api_credentials = $module->l('LoginRadius API Key or Secret is invalid. Get your LoginRadius API key from ', 'sociallogin')."<a href='http://www.loginradius.com' target='_blank'>LoginRadius</a>";

	if (empty($loginradius_api_key) || empty($loginradius_api_secret))
		return $empty_api_credentials;

	$validateurl = 'https://'.LR_DOMAIN.'/api/v2/app/validate?apikey='.rawurlencode($loginradius_api_key).'&apisecret='.rawurlencode($loginradius_api_secret);
	$data = array(
		'addon' => 'Prestashop',
		'version' => '3.0',
		'agentstring' => $_SERVER['HTTP_USER_AGENT'],
		'clientip' => $_SERVER['REMOTE_ADDR'],
		'configuration' => $string,
	);

	try
	{
		$json_result = $obj->loginRadiusApiClient($validateurl, '', $data);
		$result = Tools::jsonDecode($json_result);

		if (empty($result))
			return $module->l('please check your php.ini settings to enable CURL or FSOCKOPEN', 'sociallogin');

		if (isset($result->Status) && !$result->Status)
		{
			$error = array(
				'API_KEY_NOT_VALID' => $module->l('LoginRadius API key is invalid. Get your LoginRadius API key from ', 'sociallogin')."<a href='http://www.loginradius.com' target='_blank'>LoginRadius</a>",
				'API_SECRET_NOT_VALID' => $module->l('LoginRadius API Secret is invalid. Get your LoginRadius API Secret from ', 'sociallogin')."<a href='http://www.loginradius.com' target='_blank'>LoginRadius</a>",
				'API_KEY_NOT_FORMATED' => $module->l('LoginRadius API Key is not formatted correctly', 'sociallogin'),
				'API_SECRET_NOT_FORMATED' => $module->l('LoginRadius API Secret is not formatted correctly', 'sociallogin'),
			);

			foreach ($result->Messages as $value)
				return $error["$value"];
		}
	} catch (Exception $e)
	{
		return '';
	}
}

/**
 * Get social login settings value
 *
 * @return array social login settings value
 */
function loginRadiusSocialLoginSettings()
{
	return array('LoginRadius_redirect' => trim(Tools::getValue('LoginRadius_redirect')), 'redirecturl' => trim(Tools::getValue('redirecturl')));
}

/**
 * Get social sharing settings values
 *
 * @return array social sharing settings values
 */
function loginRadiusSocialSharingSettings()
{
	return array('enable_social_horizontal_sharing' => (int)Tools::getValue('enable_social_horizontal_sharing'),
		'enable_social_vertical_sharing' => (int)Tools::getValue('enable_social_vertical_sharing'),
		'chooseshare' => Tools::getValue('chooseshare'),
		'rearrange_settings' =>
			count(Tools::getValue('rearrange_settings')) > 0 ? serialize(Tools::getValue('rearrange_settings')) : '',
		'vertical_rearrange_settings' => count(Tools::getValue('vertical_rearrange_settings')) > 0 ? serialize(Tools::getValue('vertical_rearrange_settings')) : '',
		'social_share_home' => (int)Tools::getValue('social_share_home'), 'social_share_cart' => (int)Tools::getValue('social_share_cart'),
		'social_share_product' => (int)Tools::getValue('social_share_product'),
		'enable_social_vertical_sharing' => (int)Tools::getValue('enable_social_vertical_sharing'),
		'chooseverticalshare' => Tools::getValue('chooseverticalshare'),
		'choosesharepos' => Tools::getValue('choosesharepos'),
		'socialshare_counter_list' => count(Tools::getValue('socialshare_counter_list')) > 0 ? serialize(Tools::getValue('socialshare_counter_list')) : '',
		'socialshare_show_counter_list' =>
			count(Tools::getValue('socialshare_show_counter_list')) > 0 ? serialize(Tools::getValue('socialshare_show_counter_list')) : '',
		'social_verticalshare_home' => (int)Tools::getValue('social_verticalshare_home'),
		'social_verticalshare_cart' => (int)Tools::getValue('social_verticalshare_cart'),
		'social_verticalshare_product' => (int)Tools::getValue('social_verticalshare_product'));
}

/**
 * Get advance settings values
 *
 * @return array advance settings values
 */
function loginRadiusAdvanceSettings()
{
	return array('TITLE' => Tools::getValue('TITLE', 'Login with Social ID'),
		'social_login_icon_size' => (int)Tools::getValue('social_login_icon_size'),
		'social_login_icon_column' => trim(Tools::getValue('social_login_icon_column')),
		'social_login_background_color' => trim(Tools::getValue('social_login_background_color')),
		'SEND_REQ' => (int)Tools::getValue('SEND_REQ'),
		'user_notification' => Tools::getValue('user_notification'),
		'user_require_field' => Tools::getValue('user_require_field'),
		'profilefield' => count(Tools::getValue('profilefield')) > 0 ? serialize(Tools::getValue('profilefield')) : '',
		'EMAIL_REQ' => (int)Tools::getValue('EMAIL_REQ'),
		'POPUP_TITLE' => Tools::getValue('POPUP_TITLE'),
		'ERROR_MESSAGE' => Tools::getValue('ERROR_MESSAGE'),
		'update_user_profile' => Tools::getValue('update_user_profile')
	);
}