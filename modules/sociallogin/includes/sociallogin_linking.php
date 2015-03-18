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
 * Provide Social linking.
 *
 * @param object $arrdata Contain cookie information
 * @param object $user_profile User profile data that got from social network
 */
function loginRadiusAccountLinking($arrdata, $user_profile)
{
	$module = new SocialLogin();
	$context = Context::getContext();
	$cookie = $context->cookie;
	$cookie->lrmessage = '';

	//check User is authenticate and user data is not empty.
	if (!empty($user_profile) && isset($user_profile->ID) && $user_profile->ID != '')
	{
		//Check Social ID and  provider is in database.
		$tbl = pSQL(_DB_PREFIX_.'sociallogin');
		$getdata = Db::getInstance()->ExecuteS('SELECT * FROM '.pSQL(_DB_PREFIX_.'customer')." as c WHERE c.email='".pSQL($arrdata->email)."' LIMIT 0,1");
		$num = (!empty($getdata['0']['id_customer']) ? $getdata['0']['id_customer'] : '');
		$sql = "SELECT COUNT(*) as num from $tbl where `id_customer`='".$num."' and `Provider_name`='".pSQL($user_profile->Provider)."'";
		$row = Db::getInstance()->getRow($sql);

		if ($row['num'] == 0)
			loginRadiusAddLinkedAccount($num, $user_profile->ID, $user_profile->Provider);
		//Already linked with socialid and provider.
		//Show Warning message.
		else
			$cookie->lrmessage = $module->l('Account cannot be mapped as it already exists in database', 'sociallogin_functions');
	}

	//After Linking Provide redirection.
	$loc = $cookie->currentquerystring;
	$cookie->currentquerystring = '';
	Tools::redirectLink($loc);
}

/**
 * Remove Social Linking
 *
 * @param object $cookie Contain cookie information
 * @param string $value Social network ID
 */
function loginRadiusRemoveLinking($cookie, $value)
{
	$module = new SocialLogin();
	$deletequery = 'delete from '.pSQL(_DB_PREFIX_.'sociallogin')." where provider_id ='".pSQL($value)."'";
	Db::getInstance()->Execute($deletequery);
	$cookie->lrmessage = $module->l('Your Social identity has been removed successfully', 'sociallogin');
	Tools::redirect($_SERVER['HTTP_REFERER']);
}

/**
 * Add Linked account to customer account
 *
 * @param type $num Customer account ID
 * @param type $id Social network ID
 * @param type $provider Social Network
 */
function loginRadiusAddLinkedAccount($num, $id, $provider)
{
	$module = new SocialLogin();
	$context = Context::getContext();
	$tbl = pSQL(_DB_PREFIX_.'sociallogin');
//check only social id is in database.
	$check_user_id = Db::getInstance()->ExecuteS('SELECT c.id_customer FROM '.pSQL(_DB_PREFIX_.'customer').' AS c INNER JOIN '.$tbl.'
			AS sl ON sl.id_customer=c.id_customer WHERE sl.provider_id= "'.pSQL($id).'"');

	if (empty($check_user_id['0']['id_customer']))
		Db::getInstance()->Execute('DELETE FROM '.$tbl."  WHERE provider_id='".pSQL($id)."'");

	$lr_id = Db::getInstance()->ExecuteS('SELECT provider_id FROM '.$tbl."  WHERE provider_id= '".pSQL($id)."'");

//Present then show warning message.
	if (!empty($lr_id['0']['provider_id']))
		$context->cookie->lrmessage = $module->l('Account cannot be mapped as it already exists in database', 'sociallogin_functions');
	else
	{
		$query = "INSERT into $tbl (`id_customer`,`provider_id`,`Provider_name`,`verified`,`rand`)
				values ('$num','".$id."' , '".pSQL($provider)."','1','') ";
		Db::getInstance()->Execute($query);
		$context->cookie->lrmessage = $module->l('Your account is successfully mapped', 'sociallogin_functions');
	}
}