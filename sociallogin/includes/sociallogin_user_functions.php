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

include_once(dirname(__FILE__).'/sociallogin_functions.php');
/**
 * Connect Social login Interface and Handle loginradius token.
 * @return sring html content
 */
function loginRadiusConnect()
{
	
	//create object of social login class.
	$module = new SocialLogin();
	
	if(Tools::getValue('token'))
	{
		include_once('LoginRadiusSDK.php');
		$lr_obj = new LoginRadius();
		//Get the user_profile of authenticate user.
		$user_profile = $lr_obj->loginRadiusGetUserProfileData(Tools::getValue('token'));
	}
	else if(Tools::getValue('client_key'))
	{

		include_once('sociallogin_bitsharesloginapi.php');
		$btsclient = new Bitshares();	
		try
		{	

			$btsclient->authenticate();
			$user_profile = $btsclient->userinfo_get();
			unset($_GET["server_key"]);
			unset($_GET["client_key"]);
			unset($_GET["signed_secret"]);			
        }
		catch (Exception $e){
				$errormsg = 'Authentication failed: ' . $e.getMessage();
				$msg = "<p style ='color:red;'>".$module->l($errormsg, 'sociallogin_functions').'</p>';
				return loginRadiusPopupVerify($msg);			
		} 	
	}

	//If user is not logged in and user is authenticated then handle login functionality.
	if (isset($user_profile->ID) && $user_profile->ID != '' && !Context:: getContext()->customer->isLogged())
	{

		$user_profile = loginRadiusMappingProfileData($user_profile);
		//Check Social provider id is already exist.
		$social_id_exist = 'SELECT * FROM '.pSQL(_DB_PREFIX_.'sociallogin').' as sl INNER JOIN '.pSQL(_DB_PREFIX_.'customer')." as c
		ON c.id_customer=sl.id_customer WHERE sl.provider_id='".pSQL($user_profile->ID)."'";
		$db_obj = Db::getInstance()->ExecuteS($social_id_exist);
		$user_id_exist = (!empty($db_obj[0]['id_customer']) ? $db_obj[0]['id_customer'] : '');


		if ($user_id_exist >= 1)
		{
			$email_exist = (!empty($db_obj[0]['email']) ? $db_obj[0]['email'] : '');
			$active_user = (!empty($db_obj[0]['active']) ? $db_obj[0]['active'] : '');
			//if user is blocked
			if ($active_user == 0)
			{
			$msg = $module->l('User has been disabled or blocked.', 'sociallogin_functions');
			return loginRadiusPopupVerify($msg);
			}

			//Verify user and provide login.
			return loginRadiusVerifiedUserLogin($email_exist, $user_profile);
		}
		//If Social provider is is not exist in database.
		elseif ($user_id_exist < 1)
		{
			if (!empty($user_profile->Email))
			{
				// check email address is exist in database if email is retrieved from Social network.
				$user_email_exist = Db::getInstance()->ExecuteS('SELECT * FROM '.pSQL(_DB_PREFIX_.'customer').' as c
				WHERE c.email="'.pSQL($user_profile->Email).'" LIMIT 0,1');
				$user_id = (!empty($user_email_exist['0']['id_customer']) ? $user_email_exist['0']['id_customer'] : '');
				$active_user = (!empty($user_email_exist['0']['active']) ? $user_email_exist['0']['active'] : '');

				//if user is blocked
				if ($active_user == 0)
				{
					$msg = $module->l('User has been disabled or blocked.', 'sociallogin_functions');
					return loginRadiusPopupVerify($msg);
				}

				if ($user_id >= 1)
				{
					if (loginRadiusDeletedUser($user_email_exist))
					{
						$msg = "<p style ='color:red;'>".$module->l('Authentication failed.', 'sociallogin_functions').'</p>';
						return loginRadiusPopupVerify($msg);
					}

					$tbl = pSQL(_DB_PREFIX_.'sociallogin');
					$query = "INSERT into $tbl (`id_customer`,`provider_id`,`Provider_name`,`verified`,`rand`)
						values ('".$user_id."','".pSQL($user_profile->ID)."' , '".pSQL($user_profile->Provider)."','1','') ";
					Db::getInstance()->Execute($query);
					return loginRadiusVerifiedUserLogin($user_profile->Email, $user_profile, 'yes');
				}
			}

			$user_profile->send_verification_email = 'no';

			if (Configuration::get('EMAIL_REQ') == '1' && empty($user_profile->Email))
				$user_profile->Email = loginRadiusGetRandomEmail($user_profile->ID, $user_profile->Provider);

			//new user. user not found in database. set all details
			if (Configuration::get('user_require_field') == '1')
			{
				if (empty($user_profile->Email))
					$user_profile->send_verification_email = 'yes';

				if (Configuration::get('EMAIL_REQ') == '1' && empty($user_profile->Email))
					$user_profile->send_verification_email = 'no';

				//If user is not exist and then add all lrdata into cookie.
				loginRadiusStoreInCookie($user_profile);
				//Open the popup to get require fields.
				$value = loginRadiusPopUpWindow('', 'status', $user_profile);

				if ($value == 'noshowpopup')
					return loginRadiusStoreAndLogin($user_profile);

				return $value;
			}

			//Save data into cookie and open email popup.
			if (Configuration::get('EMAIL_REQ') == '0' && empty($user_profile->Email))
			{
				$user_profile->send_verification_email = 'yes';
				loginRadiusStoreInCookie($user_profile);
				return loginRadiusPopUpWindow('', 'status', $user_profile);
			}

			//Store user data into database and provide login functionality.
			return loginRadiusStoreAndLogin($user_profile);
		}
		//If user is delete and set action to provide no login to user.
		elseif (loginRadiusDeletedUser($db_obj))
		{
			$msg = $module->l('Authentication failed.', 'sociallogin_functions');
			return loginRadiusPopupVerify($msg);
		}
	}
}

/**
 * Check user is Verified and Show notification message.
 *
 * @param string $email user email address
 * @param object $user_profile user profile data
 * @param type $td_user to idenitify user's email to be check for verification
 * @return string html content
 */
function loginRadiusVerifiedUserLogin($email, $user_profile, $td_user = '')
{
	$module = new SocialLogin();
	$customer = new Customer();
	$customer->getByemail($email);

	if (loginRadiusVerifiedUser($customer->id, $user_profile->ID, $td_user))
	{
		if (Configuration::get('update_user_profile') == 0)
			loginRadiusUpdateUserProfileData($customer->id, $user_profile);

		//login User.
		loginRadiusUpdateContext($customer, $user_profile->ID);
		return;
	}
	else
	{
		//User is not verified.
		$msg = $module->l('Your confirmation link has been sent to your email address. Please verify your email by clicking on
		confirmation link.', 'sociallogin_functions');
		return loginRadiusPopupVerify($msg, $user_profile->ID);
	}
}


/**
 * Check user deleted or not.
 *
 * @param array $db_obj contain user information strored in database.
 * @return boolean true when user deleted
 */
function loginRadiusDeletedUser($db_obj)
{
	$deleted = $db_obj['0']['deleted'];

	if ($deleted == 1)
		return true;

	return false;
}

/**
 * find user verified or not.
 *
 * @param type $num user id
 * @param type $pid social network id
 * @param string $td_user to identify user's email verified
 * @return boolean true when user is verified
 */
function loginRadiusVerifiedUser($num, $pid, $td_user)
{
	$db_obj = Db::getInstance()->ExecuteS('SELECT * FROM '.pSQL(_DB_PREFIX_.'sociallogin').' as c WHERE c.id_customer='." '$num'".'
	AND c.provider_id='." '$pid'".' LIMIT 0,1');
	$verified = $db_obj['0']['verified'];

	if ($verified == 1 || $td_user == 'yes')
		return true;

	return false;
}

/**
 * Update the user profile data.
 *
 * @param int $user_id User ID
 * @param object $user_profile user profile data
 */
function loginRadiusUpdateUserProfileData($user_id, $user_profile)
{
	$date_upd = date('Y-m-d H:i:s', time());
	$str = '';
	$user_profile->FirstName = LoginRadiusRemoveSpecialCharacter(!empty($user_profile->FirstName) ? pSQL($user_profile->FirstName) : '');
	$user_profile->LastName = LoginRadiusRemoveSpecialCharacter(!empty($user_profile->LastName) ? pSQL($user_profile->LastName) : '');

	if (isset($user_profile->FirstName) && !empty($user_profile->FirstName))
		$str .= "firstname='".$user_profile->FirstName."',";

	if (isset($user_profile->LastName) && !empty($user_profile->LastName))
		$str .= "lastname='".$user_profile->LastName."',";

	if (isset($user_profile->Gender) && !empty($user_profile->Gender))
	{
		$gender = ((!empty($user_profile->Gender)
			&& (strpos($user_profile->Gender, 'f') !== false
				|| (trim($user_profile->Gender) == 'F'))) ? 2 : 1);
		$str .= "id_gender='".$gender."',";
	}

	if (!empty($user_profile->BirthDate))
	{
		$dob_arr = explode('/', $user_profile->BirthDate);
		$dob = $dob_arr[2].'-'.$dob_arr[0].'-'.$dob_arr[1];
		$date_of_birth = (!empty($dob) && Validate::isBirthDate($dob) ? $dob : '');
		$str .= "birthday='".$date_of_birth."',";
	}

	Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'customer SET '.$str." date_upd='$date_upd' WHERE 	id_customer	= $user_id");
}

/**
 * When user have Email address then check login functionaity
 *
 * @param object $user_profile_data user profile data
 * @param string $rand tandom number
 * @return boolean false when customer is not added into database.
 */
function loginRadiusStoreAndLogin($user_profile_data, $rand = '')
{
	$module = new SocialLogin();
	$email = $user_profile_data->Email;
	$random_value = '';
	$verified = 1;

	if (!empty($rand) && $user_profile_data->send_verification_email == 'yes')
	{
		$random_value = $rand;
		$verified = 0;
	}

	$username = loginRadiusGetUserName($user_profile_data);
	$password = Tools::passwdGen();
	$optin = $newsletter = '0';
	$gender = ((!empty($user_profile_data->Gender)
		&& (strpos($user_profile_data->Gender, 'f') !== false
			|| (trim($user_profile_data->Gender) == 'F'))) ? 2 : 1);
	$required_field_check = Db::getInstance()->ExecuteS('SELECT field_name FROM  '.pSQL(_DB_PREFIX_).'required_field');

	foreach ($required_field_check as $item)
	{
		if ($item['field_name'] == 'newsletter')
			$newsletter = '1';
		if ($item['field_name'] == 'optin')
			$optin = '1';
	}

	$customer = new Customer();
	$customer->firstname = LoginRadiusRemoveSpecialCharacter(!empty($user_profile_data->FirstName) ? pSQL($user_profile_data->FirstName) : pSQL($username));
	$customer->lastname = LoginRadiusRemoveSpecialCharacter(!empty($user_profile_data->LastName) ? pSQL($user_profile_data->LastName) : pSQL($username));
	$customer->email = $email;
	$customer->id_gender = $gender;
	$customer->birthday = isset($user_profile_data->BirthDate) && !empty($user_profile_data->BirthDate) ? loginRadiusGetDateOfBirth($user_profile_data->BirthDate) : '';
	$customer->active = true;
	$customer->deleted = false;
	$customer->is_guest = false;
	$customer->passwd = Tools::encrypt($password);
	$customer->newsletter = $newsletter;
	$customer->optin = $optin;

	if ($customer->add())
	{

		$tbl = pSQL(_DB_PREFIX_.'sociallogin');
		Db::getInstance()->Execute("DELETE FROM $tbl WHERE provider_id='".pSQL($user_profile_data->ID)."'");
		$query = "INSERT into $tbl (`id_customer`,`provider_id`,`Provider_name`,`verified`,`rand`)
	values ('".$customer->id."','".pSQL($user_profile_data->ID)."','".pSQL($user_profile_data->Provider)."','".pSQL($verified)."','".pSQL($random_value)."') ";
		Db::getInstance()->Execute($query);

		//extra data from here later to complete
		if (Configuration::get('user_require_field') == '1')
			loginRadiusSaveExtraRequiredFields($user_profile_data, $customer);

		if (!empty($rand) && $user_profile_data->send_verification_email == 'yes')
		{
			$sub = $module->l('Verify your email id. ', 'sociallogin_functions');
			$link = Context::getContext()->link->getPageLink('index')."?SL_VERIFY_EMAIL=$rand&SL_PID=".$user_profile_data->ID.'';
			$msg = $module->l('Please click on the following link or paste it in browser to verify your email: ', 'sociallogin_functions').$link;
			return loginRadiusVerificationEmail($email, $sub, $msg, $user_profile_data->ID);
		}
		else
		{
			if (Configuration::get('SEND_REQ') == '1')
				loginRadiusNotifyAdmin($customer);

			if (Configuration::get('user_notification') == '0')
				userNotificationEmail($customer, $password);

			loginRadiusUpdateContext($customer, $user_profile_data->ID);
		}
	}

	//error
	return false;
}

/**
 * save the user data in cookie.
 *
 * @param object $user_profile_data User  Profile data
 */
function loginRadiusStoreInCookie($user_profile_data)
{
	$context = Context::getContext();
	$cookie = $context->cookie;
	$cookie->login_radius_data = '';
	$user_profile_data = (object)array_filter((array)$user_profile_data);
	$cookie->login_radius_data = Tools::jsonEncode($user_profile_data);
}

/**
 * Save data after popup submission.
 *
 * @param object $userprofile user profile data
 * @return string html content.
 */
function loginRadiusSaveData($userprofile)
{
	$module = new SocialLogin();
	$context = Context::getContext();
	$provider_id = pSQL($userprofile->ID);
	$provider_name = pSQL($userprofile->Provider);

	if (!Context:: getContext()->customer->isLogged())
	{
		$email = pSQL($userprofile->Email);
		$query = Db::getInstance()->ExecuteS('SELECT c.id_customer from '._DB_PREFIX_.'customer AS c INNER JOIN '._DB_PREFIX_.'sociallogin AS sl ON sl.id_customer=c.id_customer
		WHERE c.email="'.pSQL($email).'"');

		if (!empty($query['0']['id_customer']))
		{
			$error_msg = Configuration::get('ERROR_MESSAGE').'|error|Email-address has already used.';
			$userprofile->Email = '';
			return loginRadiusPopUpWindow($error_msg, 'error', $userprofile);
		}
		else
		{
			$context->cookie->login_radius_data = '';
			$context->cookie->sl_hidden = '';
			$query1 = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_."customer  WHERE email='".pSQL($email)."'");
			$num = (!empty($query1['0']['id_customer']) ? $query1['0']['id_customer'] : '');

			if (!empty($num))
			{
				$rand = pSQL(loginRadiusGetRandomString());
				$tbl = pSQL(_DB_PREFIX_.'sociallogin');
				$query = "INSERT into $tbl (`id_customer`,`provider_id`,`Provider_name`,`rand`,`verified`)
				values ('".$num."','".pSQL($provider_id)."','".pSQL($provider_name)."','".pSQL($rand)."','0') ";
				Db::getInstance()->Execute($query);
				$protocol_content = (Configuration::get('PS_SSL_ENABLED')) ? 'https://' : 'http://';
				$link = $protocol_content.$_SERVER['HTTP_HOST'].__PS_BASE_URI__."?SL_VERIFY_EMAIL=$rand&SL_PID=$provider_id";
				$msg = $module->l('Please click on the following link or paste it in browser to verify your email: ', 'sociallogin_functions').$link;
				$sub = $module->l('Verify your email id.', 'sociallogin_functions');
				return loginRadiusVerificationEmail($email, $sub, $msg, $provider_id);
			}
			else
				return loginRadiusStoreAndLogin($userprofile, pSQL(loginRadiusGetRandomString()));
		}
	}
}
