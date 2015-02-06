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

require_once '../../../config/config.inc.php';
require_once '../../../init.php';
$errors = array();

if (Configuration::get('PS_TOKEN_ACTIVATED') == 1 && strcmp(Tools::getToken(), Tools::getValue('token')))
	$errors[] = Tools::displayError('Invalid token');

/*
 * Get states list based off country ID.
 */
if (Tools::getValue('id_country') && !count($errors))
{
	$array = array();
	$id_country = Tools::getValue('id_country');
	$country_id = Db::getInstance()->executeS('
			SELECT * FROM '._DB_PREFIX_.'country  c WHERE c.iso_code= "'.$id_country.'"');
	$states = State::getStatesByIdCountry($country_id['0']['id_country']);
	$value = array();

	foreach ($states as $state)
	{
		$id = $state['iso_code'];
		$value[$id] = $state['name'];
	}

	$array['states'] = $value;
	echo Tools::jsonEncode($array);
}

