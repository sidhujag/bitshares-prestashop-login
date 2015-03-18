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
 * Get Horizontal and Vertical Sharing div to show sharing widget
 *
 * @param string $horizontal DB horizontal sharing setting
 * @param string $vertical DB vertical sharing setting
 * @return string Div of horizontal and veritcal div.
 */
function loginRadiusGetSharingDiv($horizontal, $vertical)
{
	$show_horizontal = $show_vertical = false;

	if (Configuration::get($horizontal) == '1')
		$show_horizontal = true;

	if (Configuration::get($vertical) == '1')
		$show_vertical = true;

	return loginRadiusAddSharingDiv($show_horizontal, $show_vertical);
}

/**
 * Rendered html contnent of sharing widget
 *
 * @param boolean $show_horizontal true when horiozntal sharing enable
 * @param boolean $show_vertical true when vertical sharing enable
 * @return string
 */
function loginRadiusAddSharingDiv($show_horizontal, $show_vertical)
{
	$module = new SocialLogin();
	$context = Context::getContext();
	$smarty = $context->smarty;
	$smarty->assign('horizontal_sharing', '');
	$smarty->assign('vertical_sharing', '');
	if (Configuration::get('enable_social_horizontal_sharing') == '0' && $show_horizontal)
	{

		if (Configuration::get('chooseshare') != 8 && Configuration::get('chooseshare') != 9)
			$horizontal_sharing = 'lrsharecontainer';
		else
			$horizontal_sharing = 'lrcounter_simplebox';

		$smarty->assign('horizontal_sharing', $horizontal_sharing);
	}

	if (Configuration::get('enable_social_vertical_sharing') == '0' && $show_vertical)
	{
		if (Configuration::get('chooseverticalshare') != 6 && Configuration::get('chooseverticalshare') != 7)
			$vertical_sharing = 'lrshareverticalcontainer';
		else
			$vertical_sharing = 'lrcounter_verticalsimplebox';

		$smarty->assign('vertical_sharing', $vertical_sharing);
	}
	return $module->display(__PS_BASE_URI__.'modules/sociallogin', 'sharing.tpl');
}

/**
 * Horizontal Social Sharing Widget Script Code.
 *
 * @return string Script code
 */
function loginRadiusHorizontalShareScript()
{
	$context = Context::getContext();
	$context->controller->addJS(__PS_BASE_URI__.'modules/sociallogin/js/sharinginterface.js');
	$share_script = '';
	$horizontal_theme = Configuration::get('chooseshare') ? Configuration::get('chooseshare') : '0';

	if ($horizontal_theme == 8 || $horizontal_theme == 9)
	{
		$counter_list = unserialize(Configuration::get('socialshare_show_counter_list'));

		if (empty($counter_list))
			$counter_list = array('Pinterest Pin it', 'Facebook Like', 'Google+ Share', 'Twitter Tweet', 'Hybridshare');

		$providers = implode('","', $counter_list);
		$interface = 'simple';
		$type = 'horizontal';

		if ($horizontal_theme == '8')
			$type = 'vertical';

		$share_script .= '<script type="text/javascript">LoginRadius.util.ready(function () { $SC.Providers.Selected = ["'.$providers.'"];
		$S = $SC.Interface.'.$interface.'; $S.isHorizontal = true; $S.countertype = \''.$type.'\'; $S.show("lrcounter_simplebox"); });</script>';
	}
	else
	{

		$rearrange_settings = unserialize(Configuration::get('rearrange_settings'));

		if (empty($rearrange_settings))
			$rearrange_settings = array('facebook', 'googleplus', 'twitter', 'linkedin', 'pinterest');

		$providers = implode('","', $rearrange_settings);
		$interface = 'horizontal';
		$size = '32';

		if ($horizontal_theme == 2 || $horizontal_theme == 3)
			$interface = 'simpleimage';

		if ($horizontal_theme == 1 || $horizontal_theme == 3)
			$size = '16';

		$loginradius_apikey = trim(Configuration::get('API_KEY'));
		$sharecounttype = (!empty($loginradius_apikey) ? ('$u.apikey="'.$loginradius_apikey.'";
		$u.sharecounttype='."'url'".';') : '$u.sharecounttype='."'url'".';');
		$share_script .= '<script type="text/javascript">LoginRadius.util.ready(function () { $i = $SS.Interface.'.$interface.';
		$SS.Providers.Top = ["'.$providers.'"];
		$u = LoginRadius.user_settings; '.$sharecounttype.' $i.size = '.$size.';$i.show("lrsharecontainer"); });</script>';
	}

	return $share_script;
}

/**
 * Vertical Social Sharing Widget Script Code.
 *
 * @return string Script code
 */
function loginRadiusVerticalShareScript()
{
	$context = Context::getContext();
	$context->controller->addJS(__PS_BASE_URI__.'modules/sociallogin/js/sharinginterface.js');
	$share_script = '';
	$vertical_theme = Configuration::get('chooseverticalshare') ? Configuration::get('chooseverticalshare') : '6';

	if ($vertical_theme == 6 || $vertical_theme == 7)
	{
		$counter_list = unserialize(Configuration::get('socialshare_counter_list'));

		if (empty($counter_list))
			$counter_list = array('Pinterest Pin it', 'Facebook Like', 'Google+ Share', 'Twitter Tweet', 'Hybridshare');

		$providers = implode('","', $counter_list);
		$type = 'horizontal';

		if ($vertical_theme == 6)
			$type = 'vertical';

		$share_script .= '<script type="text/javascript">LoginRadius.util.ready(function () { $SC.Providers.Selected = ["'.$providers.'"];
		$S = $SC.Interface.simple; $S.isHorizontal = false; $S.countertype = \''.$type.'\';';
		$choosesharepos = Configuration::get('choosesharepos');

		if ($choosesharepos == 0)
		{
			$position1 = 'top';
			$position2 = 'left';
		}
		else if ($choosesharepos == 1)
		{
			$position1 = 'top';
			$position2 = 'right';
		}
		else if ($choosesharepos == 2)
		{
			$position1 = 'bottom';
			$position2 = 'left';
		}
		else
		{
			$position1 = 'bottom';
			$position2 = 'right';
		}

		$offset = Configuration::get('verticalsharetopoffset');

		if (isset($offset) && trim($offset) != '' && is_numeric($offset))
			$share_script .= '$S.top = \''.trim($offset).'px\'; $S.'.$position2.' = \'0px\';$S.show("lrcounter_verticalsimplebox"); });</script>';
		else
			$share_script .= '$S.'.$position1.' = \'0px\'; $S.'.$position2.' = \'0px\';$S.show("lrcounter_verticalsimplebox"); });</script>';
	}
	else
	{
		$vertical_rearrange_settings = unserialize(Configuration::get('vertical_rearrange_settings'));

		if (empty($vertical_rearrange_settings))
			$vertical_rearrange_settings = array('facebook', 'googleplus', 'twitter', 'linkedin', 'pinterest');

		$providers = implode('","', $vertical_rearrange_settings);
		$interface = 'Simplefloat';
		$size = '16';

		if ($vertical_theme == 4)
			$size = '32';

		$loginradius_apikey = trim(Configuration::get('API_KEY'));
		$sharecounttype = (!empty($loginradius_apikey) ? ('$u.apikey="'.$loginradius_apikey.'";
		$u.sharecounttype='."'url'".';') : '$u.sharecounttype='."'url'".';');
		$share_script .= '</script> <script type="text/javascript">LoginRadius.util.ready(function () { $i = $SS.Interface.'.$interface.';
		$SS.Providers.Top = ["'.$providers.'"]; $u = LoginRadius.user_settings; '.$sharecounttype.' $i.size = '.$size.';';
		$choosesharepos = Configuration::get('choosesharepos');

		if ($choosesharepos == 0)
		{
			$position1 = 'top';
			$position2 = 'left';
		}
		else if ($choosesharepos == 1)
		{
			$position1 = 'top';
			$position2 = 'right';
		}
		else if ($choosesharepos == 2)
		{
			$position1 = 'bottom';
			$position2 = 'left';
		}
		else
		{
			$position1 = 'bottom';
			$position2 = 'right';
		}

		$offset = Configuration::get('verticalsharetopoffset');

		if (isset($offset) && trim($offset) != '' && is_numeric($offset))
			$share_script .= '$i.top = \''.trim($offset).'px\'; $i.'.$position2.' = \'0px\';$i.show("lrshareverticalcontainer"); });</script>';
		else
			$share_script .= '$i.'.$position1.' = \'0px\'; $i.'.$position2.' = \'0px\';$i.show("lrshareverticalcontainer"); });</script>';
	}

	return $share_script;
}

/**
 * Get dafault value of sharing and counter networks.
 *
 * @param string $key identify sharing or counter.
 * @return array default sharing and counter networks
 */
function loginRadiusGetSharingDefaultNetworks($key)
{
	if ($key == 'sharing')
		return array('facebook', 'googleplus', 'twitter', 'linkedin', 'pinterest');
	else
		return array('Pinterest Pin it', 'Facebook Like', 'Google+ Share', 'Twitter Tweet', 'Hybridshare');
}