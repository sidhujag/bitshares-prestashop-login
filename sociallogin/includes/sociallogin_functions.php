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
 * Include required files
 */
include_once(dirname(__FILE__).'/sociallogin_linking.php');
include_once(dirname(__FILE__).'/sociallogin_popup_functions.php');
include_once(dirname(__FILE__).'/sociallogin_mail.php');
include_once(dirname(__FILE__).'/sociallogin_sharing.php');


/**
 * Social Login table structure.
 */
function createDatabaseLrTable()
{
	$tbl = pSQL(_DB_PREFIX_.'sociallogin');
	$create_table = <<<SQLQUERY
	CREATE TABLE IF NOT EXISTS `$tbl` (
	`id_customer` int(10) unsigned NOT NULL COMMENT 'foreign key of customers.',
	`provider_id` varchar(100) NOT NULL,
	`Provider_name` varchar(100),
	`rand` varchar(20),
	`verified` tinyint(1) NOT NULL
	)
SQLQUERY;
	Db::getInstance()->Execute($create_table);
}

/**
 * Get Username from user's social network profile
 *
 * @param object $user_profile User profile object that got from social network
 * @return string Generated username
 */
function loginRadiusGetUserName($user_profile)
{
	if (!empty($user_profile->FirstName) && !empty($user_profile->LastName))
		$username = $user_profile->FirstName.' '.$user_profile->LastName;
	elseif (!empty($user_profile->FullName))
		$username = $user_profile->FullName;
	elseif (!empty($user_profile->ProfileName))
		$username = $user_profile->ProfileName;
	elseif (!empty($user_profile->NickName))
		$username = $user_profile->NickName;
	elseif (!empty($user_profile->Email))
	{
		$user_name = explode('@', $user_profile->Email);
		$username = $user_name[0];
	}
	else
		$username = $user_profile->ID;

	return $username;
}

/**
 * Get proper formatted date of birth from user profile
 *
 * @param string $dob date of birth that got from user's social network profile
 * @return string get formatted date of birth
 */
function loginRadiusGetDateOfBirth($dob)
{
	if ($dob)
	{
		$dob_arr = explode('/', $dob);
		$dob = $dob_arr[2].'-'.$dob_arr[0].'-'.$dob_arr[1];
	}

	return (!empty($dob) && Validate::isBirthDate($dob) ? $dob : '');
}
/**
 * Bitshares Login Interface Script Code.
 *
 * @return string Get script to show Bitshares Login interface.
 */
function loginRadiusBitsharesInterfaceScript()
{
	$module = new SocialLogin();
	
	$base_uri = $protocol_content.Tools::getHttpHost().__PS_BASE_URI__.(!Configuration::get('PS_REWRITING_SETTINGS') ? 'index.php' : '');
	$bitshareslogin_handler = __PS_BASE_URI__ . "modules/sociallogin/ajax.php";
	
	$lrhtml = '<script type="text/javascript"> 
	$(window).load(function() { $(".btsinterfacecontainerdiv").html("<div class=\"cell text-center\"><a href=\"javascript:void(0)\" onclick=\"javascript:getBitsharesLoginURL(\''.$base_uri.'\', \''.$bitshareslogin_handler.'\')\" class=\"btn btn-block btn-lg btn-social btn-bitshares\"><img alt=\"BTS\" height=\"42\" src=\"'.__PS_BASE_URI__.'modules/sociallogin/img/logo-bitshares.svg\" width=\"42\">&nbsp;'.$module->l('BitShares Login', 'sociallogin_functions').'</a></div>")});</script>';
	return $lrhtml;
}
/**
 * Social Login Interface Script Code.
 *
 * @return string Get script to show interface.
 */
function loginRadiusInterfaceScript()
{
	$loginradius_apikey = trim(Configuration::get('API_KEY'));
	$protocol_content = (Configuration::get('PS_SSL_ENABLED')) ? 'https://' : 'http://';
	$base_uri = $protocol_content.Tools::getHttpHost().__PS_BASE_URI__.(!Configuration::get('PS_REWRITING_SETTINGS') ? 'index.php' : '');
	$interface_icon_size = Configuration::get('social_login_icon_size') == 1 ? 'small' : '';
	$background_color = Configuration::get('social_login_background_color');
	$interface_background_color = (!empty($background_color) ? trim($background_color) : '');
	$column = Configuration::get('social_login_icon_column');
	$interface_column = (!empty($column) && is_numeric($column) ? trim($column) : 0);
	return '<script src="//hub.loginradius.com/include/js/LoginRadius.js"></script>
    <script src="'.__PS_BASE_URI__.'modules/sociallogin/js/LoginRadiusSDK.2.0.0.js"></script>
<script type="text/javascript">
	function loginradius_interface() { $ui = LoginRadius_SocialLogin.lr_login_settings;$ui.interfacesize = "'.$interface_icon_size.'";
	$ui.lrinterfacebackground="'.$interface_background_color.'";$ui.noofcolumns='.$interface_column.';$ui.apikey = "'.$loginradius_apikey.'";
	$ui.callback="'.$base_uri.'";$ui.is_access_token=true; $ui.lrinterfacecontainer ="interfacecontainerdiv"; LoginRadius_SocialLogin.init(options); }var options={};
	options.login=true; LoginRadius_SocialLogin.util.ready(loginradius_interface); 
  LoginRadiusSDK.setLoginCallback(function () {
    var form = document.createElement("form");
    form.action = window.location;
    form.method = "POST";
    var hiddenToken = document.createElement("input");
    hiddenToken.type = "hidden";
    hiddenToken.value = LoginRadiusSDK.getToken();
    hiddenToken.name = "token";
    form.appendChild(hiddenToken);
    document.body.appendChild(form);
    form.submit();
});
</script>';
}

/**
 * Get Redirection url after user login
 *
 * @return string get redirection url
 */
function loginRadiusRedirectUrl()
{
	$redirect = '';
	$loc = Configuration::get('LoginRadius_redirect');

	if ($loc == 'profile')
		$redirect = 'my-account.php';
	elseif ($loc == 'url')
	{
		$custom_url = Configuration::get('redirecturl');
		$redirect = !empty($custom_url) ? $custom_url : 'my-account.php';
	}
	else
	{
		if (Tools::getValue('back'))
		{
			if (_PS_VERSION_ >= 1.6)
			{		
				$loc = $_SERVER['REQUEST_URI'];
				$redirect_location = explode('back=', $loc);
				$redirect = $redirect_location['1'];
			}
			else
				$redirect = Tools::getValue('back');
		}
		elseif (empty($redirect))
		{
			$http = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'Off' && !empty($_SERVER['HTTPS'])) ? 'https://' : 'http://');
			$uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
			$uriWithQuery = $uri_parts[0].'?'.http_build_query($_GET);				
			$redirect = urldecode($http.$_SERVER['HTTP_HOST'].$uriWithQuery);
		}
	}

	return $redirect;
}

/**
 * Update the logged in user data in context.
 *
 * @param object $customer Contain logged in customer information
 * @param string $social_id Social network id through which customer logged in.
 */
function loginRadiusUpdateContext($customer, $social_id)
{
	$context = Context::getContext();
	$cookie = $context->cookie;
	$cookie->id_customer = $customer->id;
	$cookie->customer_lastname = $customer->lastname;
	$cookie->customer_firstname = $customer->firstname;
	$cookie->logged = 1;
	$cookie->passwd = $customer->passwd;
	$cookie->email = $customer->email;
	$cookie->loginradius_id = $social_id;
	$cookie->lr_login = 'true';

	if ((empty($cookie->id_cart) || Cart::getNbProducts($cookie->id_cart) == 0))
		$cookie->id_cart = (int)Cart::lastNoneOrderedCart($cookie->id_customer);

	// OPC module compatibility
	$cart = $context->cart;
	$cart->id_address_delivery = 0;
	$cart->id_address_invoice = 0;
	$cart->update();
	$cookie->id_compare = isset($cookie->id_compare) ? $cookie->id_compare : CompareProduct::getIdCompareByIdCustomer($cookie->id_customer);
	Hook::exec('authentication');
	$redirect = loginRadiusRedirectUrl();
	Tools::redirectLink($redirect);
}

/**
 * Create random string.
 *
 * @return string  Random string
 */
function loginRadiusGetRandomString()
{
	$char = '';

	for ($i = 0; $i < 20; $i++)
		$char .= rand(0, 9);

	return ($char);
}

/**
 * Insert popup optional fields.
 *
 * @param object $data Conatin required popup field information
 * @param object $customer Contain logged in customer information
 */
function loginRadiusSaveExtraRequiredFields($data, $customer)
{
	$str = '';

	if (!empty($data->Country))
	{
		$country = $data->Country;
		$id = pSQL(loginRadiusGetIdByCountryISO($country));

		if (!empty($id))
			$str .= "id_country='$id',";
	}
	if (!empty($data->Country) && empty($id))
	{
		$country = $data->Country;
		$id = pSQL(loginRadiusGetIdByCountryName($country));

		if (!empty($id))
			$str .= "id_country='$id',";
	}
	elseif (empty($id))
	{
		$id = (int)Configuration::get('PS_COUNTRY_DEFAULT');

		if (!empty($id))
			$str .= "id_country='$id',";
	}
	if (isset($data->State) && $data->State != 'empty' && !empty($data->State))
	{
		$state = $data->State;
		$iso = pSQL(loginRadiusGetIsoByState($state));

		if (!empty($iso))
			$str .= "id_state='$iso',";
	}

	if (isset($data->City) && !empty($data->City))
	{
		$city = pSQL($data->City);
		$str .= "city='$city',";
	}

	if (isset($data->Zipcode) && !empty($data->Zipcode))
	{
		$zip = trim(pSQL($data->Zipcode));
		$str .= "postcode='$zip',";
	}

	if (isset($data->Address) && !empty($data->Address))
	{
		$address = pSQL($data->Address);
		$str .= "address1='$address',";
	}

	if (isset($data->PhoneNumber) && !empty($data->PhoneNumber))
	{
		$phone = pSQL($data->PhoneNumber);
		$str .= "phone_mobile='$phone',";
	}

	if (isset($data->Addressalias) && !empty($data->Addressalias))
	{
		$add_alias = pSQL($data->Addressalias);
		$str .= "alias='$add_alias',";
	}

	$tbl = _DB_PREFIX_.'address';
	$date = date('y-m-d h:i:s');
	$fname = pSQL($customer->firstname);
	$lname = pSQL($customer->lastname);

	$str .= "date_add='$date',date_upd='$date',";
	Db::getInstance()->Execute("INSERT into $tbl SET ".$str." id_customer='$customer->id', lastname='$lname',firstname='$fname'");
}

/**
 * Get country ID by Counter ISo code.
 *
 * @param string|array $iso_value Iso code of country
 * @return int Get Country ID
 */
function loginRadiusGetIdByCountryISO($iso_value)
{
	if (!empty($iso_value))
	{
		$tbl = _DB_PREFIX_.'country';
		$field = 'iso_code';

		if (isset($iso_value->Code))
			$iso_value = $iso_value->Code;

		if (isset($iso_value) && !is_array($iso_value))
		{
			$iso_value = pSQL(trim($iso_value));
			$q = Db::getInstance()->ExecuteS("SELECT * from $tbl WHERE $field='$iso_value'");
			return (isset($q[0]['id_country']) ? $q[0]['id_country'] : '');
		}

		return '';
	}
}

/**
 * Get Country ID by Country Name.
 *
 * @param object|string $country Country Name
 * @return string|int empty if  Country ID not found
 */
function loginRadiusGetIdByCountryName($country)
{
	if (!empty($country))
	{
		if (isset($country->Name))
			$country = $country->Name;

		if (is_string($country))
		{
			$tbl = _DB_PREFIX_.'country_lang';
			$country = pSQL(trim($country));
			$q = Db::getInstance()->ExecuteS("SELECT * from $tbl WHERE name='$country'");

			if (!empty($q))
				return $q[0]['id_country'];
		}
	}

	return '';
}

/**
 * Get State from ISO-code.
 *
 * @param string $state State ISO code
 * @return int State ID
 */
function loginRadiusGetIsoByState($state)
{
	if (!empty($state) && is_string($state))
	{
		$tbl = _DB_PREFIX_.'state';
		$q = Db::getInstance()->ExecuteS("SELECT * from $tbl WHERE  iso_code ='$state'");

		if (!empty($q))
			return $q[0]['id_state'];
	}

	return '';
}

/**
 * Remove special character from name.
 *
 * @param string $field Name from which remove special charcter
 * @return string removed special character name
 */
function LoginRadiusRemoveSpecialCharacter($field)
{
	$in_str = str_replace(array('<', '>', '&', '{', '}', '*', '/', '(', '[', ']', '@', '!', ')', '&', '*', '#', '$', '%', '^', '|', '?', '+', '=',
		'"', ','), array(''), $field);
	$cur_encoding = mb_detect_encoding($in_str);

	if ($cur_encoding == 'UTF-8' && mb_check_encoding($in_str, 'UTF-8'))
		$name = $in_str;
	else
		$name = utf8_encode($in_str);

	if (!Validate::isName($name))
	{
		$len = Tools::strlen($name);
		$return_val = '';

		for ($i = 0; $i < $len; $i++)
		{
			if (ctype_alpha($name[$i]))
				$return_val .= $name[$i];
		}

		$name = $return_val;

		if (empty($name))
		{
			$letters = range('a', 'z');

			for ($i = 0; $i < 5; $i++)
				$name .= $letters[rand(0, 26)];
		}
	}

	return $name;
}

/**
 * Retrieve random Email address using provider id.
 *
 * @param string $id Social network ID
 * @param string $provider Social network
 * @return string random email id
 */
function loginRadiusGetRandomEmail($id, $provider)
{
	switch ($provider)
	{
		case 'twitter':
			$email = $id.'@'.$provider.'.com';
			break;
		default:
			$email_id = Tools::substr($id, 7);
			$email_id2 = str_replace('/', '_', $email_id);
			$email = str_replace('.', '_', $email_id2).'@'.$provider.'.com';
			break;
	}
	return $email;
}

/**
 * Returns a list of files to install
 *
 * @return boolean false when files not install
 */
function loginRadiusGetFilesToInstall()
{
	$module = new SocialLogin();
	$files = array(
		'lrsociallogin_account.html' => array(
			'source' => _PS_MODULE_DIR_.$module->name.'/upload/mails/en/',
			'target' => _PS_MAIL_DIR_.'en/'
		),
		'lrsociallogin_account.txt' => array(
			'source' => _PS_MODULE_DIR_.$module->name.'/upload/mails/en/',
			'target' => _PS_MAIL_DIR_.'en/'
		)
	);
	return $files;
}

/**
 * Moves a hook to the top position
 *
 * @param string $hook_name
 * @param int $position
 * @return boolean false when error occurred
 */
function loginRadiusMoveLrHookPosition($hook_name, $position)
{
	$module_name = new SocialLogin();

	// Get the hook identifier.
	if (($id_hook = Hook::getIdByName($hook_name)) !== false)
	{
		// Load the social login  module.
		if (($module = Module::getInstanceByName($module_name->name)) !== false)
		{
			// Get the max position of hook .
			$sql = 'SELECT MAX(position) AS position FROM `'._DB_PREFIX_."hook_module` WHERE `id_hook` = '".$id_hook."'";
			$result = Db::getInstance()->GetRow($sql);

			if (is_array($result) && isset ($result['position']))
			{
				$way = (($result['position'] >= $position) ? 0 : 1);
				return $module->updatePosition($id_hook, $way, $position);
			}
		}
	}

	//An error occurred.
	return false;
}

/**
 * Get all popup required fields value
 *
 * @param object $data User profile data from social network
 * @return object get collected data from required popup fields
 */
function loginRadiusGetAllRequiredPopupFields($data)
{
	if (Tools::getValue('SL_EMAIL'))
		$data->Email = trim(Tools::getValue('SL_EMAIL'));

	if (Tools::getValue('SL_CITY'))
		$data->City = trim(Tools::getValue('SL_CITY'));

	if (Tools::getValue('location-state'))
		$data->State = trim(Tools::getValue('location-state'));

	if (Tools::getValue('SL_PHONE'))
		$data->PhoneNumber = trim(Tools::getValue('SL_PHONE'));

	if (Tools::getValue('SL_ADDRESS'))
		$data->Address = trim(Tools::getValue('SL_ADDRESS'));

	if (Tools::getValue('SL_ZIP_CODE'))
		$data->Zipcode = trim(Tools::getValue('SL_ZIP_CODE'));

	if (Tools::getValue('SL_ADDRESS_ALIAS'))
		$data->Addressalias = trim(Tools::getValue('SL_ADDRESS_ALIAS'));

	if (Tools::getValue('location_country'))
		$data->Country = trim(Tools::getValue('location_country'));

	if (Tools::getValue('SL_FNAME'))
		$data->FirstName = trim(Tools::getValue('SL_FNAME'));

	if (Tools::getValue('SL_LNAME'))
		$data->LastName = trim(Tools::getValue('SL_LNAME'));

	return $data;
}

/**
 * Handle popup submission and save popup data.
 *
 * @param object $cookie Contain all data which stores in cookie
 * @return null
 */
function loginRadiusHandlePopupSubmit($cookie)
{
	$lr_data = Tools::jsonDecode($cookie->login_radius_data);
	$profilefield = implode(';', unserialize(Configuration::get('profilefield')));

	if (empty($profilefield))
		$profilefield[] = '3';

	//GEt the form post value in array.
	$data = loginRadiusGetAllRequiredPopupFields($lr_data);
	$error_message = Configuration::get('ERROR_MESSAGE');

	if (Configuration::get('user_require_field') == '1')
	{
		//If form data is empty.
		if ((empty($data->City) && strpos($profilefield, '4') !== false) || (empty($data->State) && Tools::getValue('location-state'))
			|| (empty($data->PhoneNumber) && strpos($profilefield, '5') !== false)
			|| (empty($data->Address) && strpos($profilefield, '6') !== false) || (empty($data->Zipcode)
				&& strpos($profilefield, '8') !== false) || (empty($data->Country)
				&& strpos($profilefield, '3') !== false) || empty($data->Email)
			|| (empty($data->Addressalias) && strpos($profilefield, '7') !== false)
			|| (empty($data->FirstName) && strpos($profilefield, '1') !== false)
			|| (empty($data->LastName) && strpos($profilefield, '2') !== false))
			return loginRadiusPopUpWindow($error_message, 'error', $data);

//Check zipcode entered is according to country.
		if (!empty($data->Country) && !empty($data->Zipcode))
		{
			$result = loginRadiusCheckZipCode($data);

			if (!empty($result))
				return loginRadiusPopUpWindow($error_message.'|error|'.$result, 'error', $data);
		}
	}
	//Validate meail address is from email popup.
	else if (!Validate::isEmail($data->Email))
		return loginRadiusPopUpWindow($error_message, 'error', $data);

//Update/Save the data and provide login to user.
	return loginRadiusSaveData($data);

}

/**
 * Check Zip Code is valid according to Country
 *
 * @param type $data
 * @return type
 */
function loginRadiusCheckZipCode($data)
{
	$module = new SocialLogin();
	$postcode = trim($data->Zipcode);
	$zip_code = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'country c WHERE c.iso_code = "'.$data->Country.'"');

	if (is_array($zip_code))
	{
		$zip_code_format = isset($zip_code['0']['zip_code_format']) ? $zip_code['0']['zip_code_format'] : '';

		if (!empty($zip_code_format))
		{
			$zip_regexps = '/^'.$zip_code_format.'$/ui';
			$zip_regexp = str_replace(array(' ', '-', 'N', 'L', 'C'), array('( |)', '(-|)', '[0-9]', '[a-zA-Z]', $data->Country), $zip_regexps);

			if (!preg_match($zip_regexp, $postcode))
			{
				return $module->l('Your zip/postal code is incorrect.').'|error|'.$module->l('Must be typed as follows:').' '.str_replace('C', $data->Country,
					str_replace('N', '0', str_replace('L', 'A', $zip_code_format)));
			}
		}
	}
}

/**
 * Map user profule data from LoginRadius profile data according to Prestashop.
 *
 * @param object $user_profile Conatin user profile object
 * @return object filtered data that is required to prestashop database.
 */
function loginRadiusMappingProfileData($user_profile)
{
	$profile = new stdClass();
	$profile->FullName = (!empty($user_profile->FullName) ? trim($user_profile->FullName) : '');
	$profile->ProfileName = (!empty($user_profile->ProfileName) ? trim($user_profile->ProfileName) : '');
	$profile->NickName = (!empty($user_profile->NickName) ? trim($user_profile->NickName) : '');
	$profile->FirstName = (!empty($user_profile->FirstName) ? trim($user_profile->FirstName) : '');
	$profile->LastName = (!empty($user_profile->LastName) ? trim($user_profile->LastName) : '');
	$profile->ID = (!empty($user_profile->ID) ? $user_profile->ID : '');
	$profile->Provider = (!empty($user_profile->Provider) ? $user_profile->Provider : '');
	$profile->BirthDate = (!empty($user_profile->BirthDate) ? $user_profile->BirthDate : '');
	$profile->Gender = (!empty($user_profile->Gender) ? $user_profile->Gender : '');
	$profile->HomeTown = (!empty($user_profile->HomeTown) ? $user_profile->HomeTown : '');
	$profile->About = (!empty($user_profile->About) ? $user_profile->About : '');
	$profile->Email = (count($user_profile->Email) > 0 ? $user_profile->Email[0]->Value : '');
	$profile->State = (!empty($user_profile->State) ? $user_profile->State : '');
	$profile->City = (!empty($user_profile->City) ? $user_profile->City : '');

	if (empty($profile->City) || $profile->City == 'unknown')
		$profile->City = (!empty($user_profile->LocalCity) && $user_profile->LocalCity != 'unknown' ? $user_profile->LocalCity : '');
	$profile->Country = (!empty($profile->Country) ? $profile->Country : '');

	if (empty($profile->Country))
		$profile->Country = (!empty($user_profile->LocalCountry) ? $user_profile->LocalCountry : '');

	$profile->PhoneNumber = (!empty($user_profile->PhoneNumbers['0']->PhoneNumber) ? $user_profile->PhoneNumbers['0']->PhoneNumber : '');
	$profile->Address = (!empty($user_profile->Addresses['0']->Address1) ? $user_profile->Addresses['0']->Address1 : '');
	$profile->Zipcode = (!empty($user_profile->Addresses['0']->PostalCode) ? $user_profile->Addresses['0']->PostalCode : '');
	return $profile;
}
