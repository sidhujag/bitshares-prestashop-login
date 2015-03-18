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

/**
 * Show poup window for Email and Required fields.
 *
 * @param string $msg Header message
 * @param object $data Contain user profile data
 * @return string
 */
function loginRadiusPopUpWindow($msg = '', $status = 'status', $data)
{
	$context = Context::getContext();
	$cookie = $context->cookie;
	$context->controller->addCSS(__PS_BASE_URI__.'modules/sociallogin/css/sociallogin_style.css');
	$context->controller->addjquery();
	$context->controller->addJS(__PS_BASE_URI__.'modules/sociallogin/js/popupjs.js');
	$module = new SocialLogin();
	$show_popup = true;
	$profilefield_value = unserialize(Configuration::get('profilefield'));
	$profilefield = implode(';', $profilefield_value);

	if (empty($profilefield))
		$profilefield[] = '3';

	$cookie->sl_hidden = microtime();
	$smarty = $context->smarty;

	if ($msg == '')
	{
		$show_msg = Configuration::get('POPUP_TITLE');
		$msg = (!empty($show_msg) ? $show_msg : $module->l('Please fill the following details to complete the registration', 'sociallogin_functions'));
	}

	$profiledata = array();

	if (Configuration::get('user_require_field') == '1')
	{
		if (strpos($profilefield, '1') !== false && (empty($data->FirstName) || Tools::getValue('SL_FNAME')))
		{
			$show_popup = false;
			$profiledata[] = array('text' => 'First Name', 'name' => 'SL_FNAME', 'value' => '');
		}

		if (strpos($profilefield, '2') !== false && (empty($data->LastName) || Tools::getValue('SL_LNAME')))
		{
			$show_popup = false;
			$profiledata[] = array('text' => 'Last Name', 'name' => 'SL_LNAME', 'value' => '');
		}
	}

	if (empty($data->Email) || $data->send_verification_email == 'yes')
	{
		$show_popup = false;
		$profiledata[] = array('text' => 'Email', 'name' => 'SL_EMAIL', 'value' => '');
	}

	if (Configuration::get('user_require_field') == '1')
	{
		if (strpos($profilefield, '6') !== false)
		{
			$show_popup = false;
			$profiledata[] = array('text' => 'Address', 'name' => 'SL_ADDRESS', 'value' => $data->Address);
		}

		if (strpos($profilefield, '8') !== false)
		{
			$show_popup = false;
			$profiledata[] = array('text' => 'ZIP code', 'name' => 'SL_ZIP_CODE', 'value' => '');
		}

		if (strpos($profilefield, '4') !== false)
		{
			$show_popup = false;
			$profiledata[] = array('text' => 'City', 'name' => 'SL_CITY', 'value' => $data->City);
		}

		if (strpos($profilefield, '3') !== false)
		{
			$countries = Country::getCountries($context->language->id, true);

			if (is_array($countries) && !empty($countries))
			{
				$show_popup = false;
				$profiledata[] = array('text' => 'Country', 'name' => 'location_country', 'value' => $countries);
			}
		}

		$value = true;

		if (Tools::getValue('location_country') && strpos($profilefield, '3') !== false)
		{
			$country = new Country(Tools::getValue('location_country'));
			$value = $country->contains_states;
		}

		if (strpos($profilefield, '3') !== false)
		{
			if ($value)
			{
				$show_popup = false;
				$profiledata[] = array('text' => 'State', 'name' => 'location-state', 'value' => 'empty');
			}
			else
			{
				$country_id = Db::getInstance()->executeS('
			SELECT *
			FROM '._DB_PREFIX_.'country  c WHERE c.iso_code= "'.Tools::getValue('location_country').'"');
				$states = State::getStatesByIdCountry($country_id['0']['id_country']);

				if (is_array($states))
				{
					$show_popup = false;
					$profiledata[] = array('text' => 'State', 'name' => 'location-state', 'value' => $states);
				}
			}
		}

		if (strpos($profilefield, '5') !== false)
		{
			$show_popup = false;
			$profiledata[] = array('text' => 'Mobile Number', 'name' => 'SL_PHONE', 'value' => $data->PhoneNumber);
		}

		if (strpos($profilefield, '7') !== false)
		{
			$show_popup = false;
			$profiledata[] = array('text' => 'Address Title', 'name' => 'SL_ADDRESS_ALIAS', 'value' => '');
		}
	}

	if ($show_popup)
		return 'noshowpopup';
	$protocol_content = (Configuration::get('PS_SSL_ENABLED')) ? 'https://' : 'http://';
	$uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
	$uriWithQuery = $uri_parts[0].'?'.http_build_query($_GET);	
	$base_uri = $protocol_content.Tools::getHttpHost().$uriWithQuery;
	$count_profile_data = count($profiledata) * 3.8;
	$error_message = array('message' => $msg, 'status' => $status);
	$smarty->assign('profile_data', $profiledata);
	$smarty->assign('callbackURL', $base_uri);
	$smarty->assign('count_profile_data', $count_profile_data);
	$smarty->assign('error_message', $error_message);
	return ($module->display(__PS_BASE_URI__.'modules/sociallogin', 'htmlpopup.tpl'));
}

/**
 * Show Error Message.
 *
 * @param type $msg message to shown on popup
 * @param type $social_id Social network ID
 */
function loginRadiusPopupVerify($msg, $social_id = '')
{
	$module = new SocialLogin();
	$context = Context::getContext();
	$context->controller->addCSS(__PS_BASE_URI__.'modules/sociallogin/css/sociallogin_style.css');
	$uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
	$uriWithQuery = $uri_parts[0].'?'.http_build_query($_GET);
	$protocol_content = (Configuration::get('PS_SSL_ENABLED')) ? 'https://' : 'http://';
	$base_uri = $protocol_content.Tools::getHttpHost().$uriWithQuery;	
	$context->smarty->assign('social_id', $social_id);
	$context->smarty->assign('message', $msg);
	$context->smarty->assign('callbackURL', $base_uri);
	return ($module->display(__PS_BASE_URI__.'modules/sociallogin', 'htmlpopup-verify.tpl'));
}