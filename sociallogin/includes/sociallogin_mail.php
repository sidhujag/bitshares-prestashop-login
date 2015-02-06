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
 * Email verification link when user click on resend email button.
 *
 * @param string $social_id Social network ID
 * @return string rendered html form
 */
function loginRadiusResendEmailVerification($social_id)
{
	$module = new SocialLogin();
	$getdata = Db::getInstance()->ExecuteS('SELECT * from '._DB_PREFIX_.'customer AS c INNER JOIN '._DB_PREFIX_."sociallogin
	AS sl ON sl.id_customer=c.id_customer  WHERE sl.provider_id='$social_id'");

	if ($getdata['0']['verified'] == 1)
	{
		$msg = $module->l('Email has been already verified. Now you can login using Social Login.', 'sociallogin_functions');
		return loginRadiusPopupVerify($msg);
	}
	else
	{
		$to = $getdata['0']['email'];
		$rand = loginRadiusGetRandomString();
		Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'sociallogin SET rand='.$rand." WHERE provider_id='$social_id'");
		$sub = $module->l('Verify your email id.', 'sociallogin_functions');
		$link = Context::getContext()->link->getPageLink('index')."?SL_VERIFY_EMAIL=$rand&SL_PID=".$social_id.'';
		$msgg = $module->l('Please click on the following link or paste it in browser to verify your email: ', 'sociallogin_functions').$link;
		return loginRadiusVerificationEmail($to, $sub, $msgg, $social_id);
	}
}


/**
 * Verify email-address.
 *
 * @return string Rendered html form
 */
function loginRadiusVerifyEmail()
{
	$module = new SocialLogin();
	$tbl = pSQL(_DB_PREFIX_.'sociallogin');
	$pid = pSQL(Tools::getValue('SL_PID'));
	$rand = pSQL(Tools::getValue('SL_VERIFY_EMAIL'));
	$db = Db::getInstance()->ExecuteS('SELECT * FROM  '.pSQL(_DB_PREFIX_)."sociallogin  WHERE rand='".pSQL($rand)."' and provider_id='".pSQL($pid)."' and verified='0'");
	$num = (!empty($db['0']['id_customer']) ? $db['0']['id_customer'] : '');
	$provider_name = (!empty($db['0']['Provider_name']) ? pSQL($db['0']['Provider_name']) : '');

	if ($num < 1)
		return;

	Db::getInstance()->Execute("UPDATE $tbl SET verified='1' , rand='' WHERE rand='".pSQL($rand)."' and provider_id='".pSQL($pid)."'");
	Db::getInstance()->Execute("UPDATE $tbl SET rand='' WHERE Provider_name='".pSQL($provider_name)."' and id_customer='".pSQL($num)."'");
	$msg = $module->l('Email is verified. Now you can login using Social Login.', 'sociallogin_functions');
	return loginRadiusPopupVerify($msg);
}

/**
 * Send credenntials to customer through email.
 *
 * @param object $customer Customer account information
 * @param string $password Customer password
 */
function userNotificationEmail($customer, $password)
{
	$module = new SocialLogin();
	$sub = $module->l('Thank You For Registration', 'sociallogin_functions');
	$vars = array('{firstname}' => $customer->firstname, '{lastname}' => $customer->lastname, '{email}' => $customer->email, '{passwd}' => $password);
	$id_lang = (int)Configuration::get('PS_LANG_DEFAULT');
	Mail::Send($id_lang, 'account', $sub, $vars, $customer->email);
}

/**
 * Notify admin when new user register through email.
 *
 * @param object $customer Customer account information
 */
function loginRadiusNotifyAdmin($customer)
{
	$email = $customer->email;
	$module = new SocialLogin();
	$sub = $module->l('New User Registration', 'sociallogin_functions');
	$msg = $module->l('New User Registered to your site<br/> E-mail address: ', 'sociallogin_functions');
	$msg .= $email;

	if (_PS_VERSION_ >= 1.6)
	{
		$vars = array('{name}' => 'admin', '{message}' => $msg, '{subject}' => $sub);
		$mail_format = 'lrsociallogin_account';
	}
	else
	{
		$vars = array('{email}' => $email, '{message}' => $msg);
		$mail_format = 'contact';
	}

	$db = Db::getInstance()->ExecuteS('SELECT * FROM  '.pSQL(_DB_PREFIX_).'employee  WHERE id_profile=1 ');
	$id_lang = (int)Configuration::get('PS_LANG_DEFAULT');

	foreach ($db as $row)
	{
		$find_email = $row['email'];
		Mail::Send($id_lang, $mail_format, $sub, $vars, $find_email);
	}
}

/**
 * Send verification link to customer to verify account.
 *
 * @param string $to recipient email address
 * @param string $sub Subject of email
 * @param string $msg Body of email
 * @param string $social_id Social network ID
 */
function loginRadiusVerificationEmail($to, $sub, $msg, $social_id)
{
	$module = new SocialLogin();
	$id_lang = (int)Configuration::get('PS_LANG_DEFAULT');

	if (_PS_VERSION_ >= 1.6)
	{
		$vars = array('{name}' => 'customer', '{message}' => $msg, '{subject}' => $sub);
		Mail::Send($id_lang, 'lrsociallogin_account', $sub, $vars, $to);
	}
	else
	{
		$vars = array('{email}' => $to, '{message}' => $msg);
		Mail::Send($id_lang, 'contact', $sub, $vars, $to);
	}

	$msgg = $module->l('Your confirmation link has been sent to your email address.
	Please verify your email by clicking on confirmation link.', 'sociallogin_functions');
	return loginRadiusPopupVerify($msgg, $social_id);
}