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
include_once(dirname(__FILE__).'/sociallogin_settings_validation.php');

/**
 * Content Admin UI
 *
 * @return string rendered html form of admin page
 */
function loginRadiusGetAdminContent()
{
	$module = new SocialLogin();
	$context = Context::getContext();
	$html = '';

	if (Tools::isSubmit('submitKeys'))
		$html .= loginRadiusSaveModuleSettings();

	$api_key = trim(Configuration::get('API_KEY'));
	$api_secret = trim(Configuration::get('API_SECRET'));
	$context->controller->addCSS(__PS_BASE_URI__.'modules/sociallogin/css/socialloginandsocialshare.css');
	$context->controller->addJS(__PS_BASE_URI__.'modules/sociallogin/js/sociallogin_admin.js');
	$context->controller->addJQueryUI('ui.sortable');
	$html .= loginRadiusScriptToShowAdminTabs();

	if ($api_key == '' && $api_secret == '' && Configuration::get('enable_bitshares_login') == '0')
		$html .= '<div title="warning" style="background-color: #FFFFE0;
		border:1px solid #E6DB55; margin-bottom:5px; width: 900px;padding: 4px 2px 2px 30px;
		background-image: url(../modules/sociallogin/img/warning.png);background-repeat: no-repeat;background-position:left;">
		'.$module->l('To activate the Social Login, insert LoginRadius API Key and Secret in the API Settings section below.
		 Social Sharing do not require API Key and Secret.', 'sociallogin').'</div>';

	$html .= '<div style="float:left; width:70%;">';
	$html .= loginRadiusInformationHeaderBlock();
	$html .= '<div class="row row_button" style="background:none; border:none; background:none;">
	<div class="button2-left">
	<div class="blank" style="margin:0 0 10px 0;">
	<div class="button" style="float:left; cursor:pointer;">  <a  href="http://ish.re/4" target="_blank">
	'.$module->l('Set up my FREE account!', 'sociallogin').'</a></div>
	</div></div></div></fieldset></div>
	<form action = "'.$_SERVER['REQUEST_URI'].'" method = "post" enctype = "multipart/form-data">
	<dl id="pane" class="tabs">
	<dt class="panel1 open" id="panel1"  style="cursor:pointer;" onclick=javascript:panelshow("first") >
	<span>'.$module->l('Social Login', 'sociallogin').'</span></dt>
	<dt class="panel2" id="panel2" style="cursor:pointer;" onclick=javascript:panelshow("second") >
	<span>'.$module->l('Social Share', 'sociallogin').'</span></dt>
	<dt class="panel3" id="panel3" style="cursor:pointer;" onclick=javascript:panelshow("third") >
	<span>'.$module->l('Advance Settings', 'sociallogin').'</span></dt>
	</dl>
	<div class="current">';
	$html .= loginRadiusSocialLoginTabSettings();
	$html .= loginRadiusSocialSharingTabSettings();
	$html .= loginRadiusAdvanceTabSettings();
	$html .= '<input class="button" type="submit" name="submitKeys" value="'.$module->l('Save Configuration', 'sociallogin').'" style="cursor:pointer;margin: 0 !important;"/>
	</div>
	</form>
	</div>';
	$html .= loginRadiusHelpSideBox();

	return $html;
}

/**
 * Script to show UI in tabs.
 *
 * @return string
 */
function loginRadiusScriptToShowAdminTabs()
{
	$countericons = unserialize(Configuration::get('socialshare_show_counter_list'));

	if (empty($countericons))
		$countericons = loginRadiusGetSharingDefaultNetworks('counter');

	$verticalcountericons = unserialize(Configuration::get('socialshare_counter_list'));

	if (empty($verticalcountericons))
		$verticalcountericons = loginRadiusGetSharingDefaultNetworks('counter');

	$js_val = Configuration::get('LoginRadius_redirect') == 'url' ? 0 : 1;
	$html = '
<script type="text/javascript">
    function panelshow(id) {
        var firsttab = "none";
        var secondtab = "none";
        var thirdtab = "none";
        var firstpanel = "removeClass";
        var secondpanel = "removeClass";
        var thirdpanel = "removeClass";
        if (id == "first") {
            var firsttab = "block";
            var firstpanel = "addClass";
        } else if (id == "second") {
            var secondtab = "block";
            var secondpanel = "addClass";
        } else if (id == "third") {
            var thirdtab = "block";
            var thirdpanel = "addClass";
        }
        $("#first").css("display", firsttab);
        $("#second").css("display", secondtab);
        $("#third").css("display", thirdtab);
        $("#panel1")[firstpanel]("open");
        $("#panel2")[secondpanel]("open");
        $("#panel3")[thirdpanel]("open");
    }
    $(document).ready(function() {
        $("div.productTabs").find("a").each(function() {
            $(this).attr("href", "javascript:void(0)");
        });
        $("div.productTabs a").click(function() {
            var id = $(this).attr("id");
            $(".nav-profile").removeClass("selected");
            $(this).addClass("selected");
            $(".tab-profile").hide()
            $("." + id).show();
        });
        $(function() {
            $("#sortable").sortable();
            $("#sortable").disableSelection();
            $("#verticalsortable").sortable();
            $("#verticalsortable").disableSelection();
        });
    });

    function hidetextbox(hide) {
        if (hide == 1) {
            $("#redirecturl").hide();
        } else {
            $("#redirecturl").show();
        }
    }
    window.onload = function() {
        sharingproviderlist();
        counterproviderlist('.Tools::jsonEncode($countericons).', '.Tools::jsonEncode($verticalcountericons).');
        Makehorivisible();
        hidetextbox('.$js_val.');
        show_profilefield('.Configuration::get('user_require_field').');
    }
</script>';

	return $html;
}

/**
 * Content of header block of Admin UI
 *
 * @return string content of header block
 */
function loginRadiusInformationHeaderBlock()
{
	$module = new SocialLogin();
	$html = '<div>
	<fieldset class="sociallogin_form sociallogin_form_main" style="background: none repeat scroll 0 0 #FFFFE0;border: 1px solid #E6DB55;">
	<div class="row row_title" style="color: #000000; font-weight:normal; background:none;">
	<strong>'.$module->l('Thank you for installing the LoginRadius Prestashop Extension!', 'sociallogin').'</strong>
	</div>
	<div class="row" style="color: #000000;width:90%; line-height:160%; background:none;">
	'.$module->l('To activate the extension, please configure it and manage the social networks from your LoginRadius account.
	 you do not have an account, click', 'sociallogin').'<a href="http://www.loginradius.com" target="_blank"> here
	 </a>'.$module->l('and create one for FREE!', 'sociallogin').'
	</div>
	<div class="row" style="color: #000000; width:90%; line-height:160%; background:none;">
	'.$module->l('We also have Social Plugin for', 'sociallogin').'
	<a href="http://ish.re/ALT8" target="_blank">Joomla</a>,
	<a href="http://ish.re/ADDT" target="_blank">WordPress</a>,
	<a href="http://ish.re/B46C" target="_blank">Drupal</a>,
	<a href="http://ish.re/ALTA" target="_blank">vBulletin</a>,
	<a href="http://ish.re/ALUN" target="_blank">VanillaForum</a>,
	<a href="http://ish.re/ALT6" target="_blank">Magento</a>,
	<a href="http://ish.re/ALTC" target="_blank">osCommerce</a>,
	<a href="http://ish.re/ALUL" target="_blank">X-Cart</a>,
	<a href="http://ish.re/ALUM" target="_blank">Zen-Cart</a>,
	<a href="http://ish.re/ALUP" target="_blank">DotNetNuke</a>,
	<a href="http://ish.re/A7MH" target="_blank">phpBB</a> and
	<a href="http://ish.re/A7MF" target="_blank">SMF</a>!
	</div>';

	return $html;
}

/**
 * Content of Social Login tab
 *
 * @return string content of Social Login tab
 */
function loginRadiusSocialLoginTabSettings()
{
	$module = new SocialLogin();
	$login_radius_redirect = Configuration::get('LoginRadius_redirect');
	$redirect = '';
	$checked = array(0 => '', 1 => '', 2 => '');

	if ($login_radius_redirect == 'profile')
		$checked[1] = 'checked="checked"';
	elseif ($login_radius_redirect == 'url')
	{
		$checked[2] = 'checked="checked"';
		$redirect = Configuration::get('redirecturl');
	}
	else
		$checked[0] = 'checked="checked"';

	$html = '<dd><div style="display:block;" id="first">
	<table class="form-table sociallogin_table">
	<tr>
	<th class="head" colspan="2">'.$module->l('LoginRadius API Settings.', 'sociallogin').'</small></th>
	</tr>
	<tr class="row_white" id="enable_social_horizontal_sharing"><td colspan="2" >
	<span class="subhead">'.$module->l('Do you want to enable Bitshares Login for your website?', 'sociallogin').'</span><br /><br />
	<label><input type="radio" name="enable_bitshares_login" value="1"
	'.(Tools::getValue('enable_bitshares_login', Configuration::get('enable_bitshares_login')) == 1 ? 'checked="checked"' : '').' />
	'.$module->l('Yes', 'sociallogin').'</label>
	<label><input type="radio" name="enable_bitshares_login" value="0"
	'.(Tools::getValue('enable_bitshares_login', Configuration::get('enable_bitshares_login')) == 0 ? 'checked="checked"' : '').' />
	'.$module->l('No', 'sociallogin').'</label>
	</td>
	</tr>
	<tr class="row_white">
	<input id="connection_url" type="hidden" value="'.__PS_BASE_URI__.'" />
	<td colspan="2" ><span class="subhead"> '.$module->l('To activate the LoginRadius portion of the module, insert LoginRadius API Key & Secret', 'sociallogin').
		' (<a href="http://ish.re/9VBI"
		target="_blank" style="color: #0000ff;">'.$module->l('How to get it?', 'sociallogin').'</a>)</span>
	<br/><br />
	<span class="lr_apis">'.$module->l('API Key', 'sociallogin').'
	</span><input type="text" size="50" name="API_KEY" id="API_KEY" value="'.trim(Configuration::get('API_KEY')).'" />
	<br /><br />
	<span class="lr_apis">'.$module->l('API Secret', 'sociallogin').'</span>
	<input type="text" name="API_SECRET" id="API_SECRET"  size="50" value="'.trim(Configuration::get('API_SECRET')).'" />
	</td>
	</tr>
	</table>
  	<table class="form-table sociallogin_table">
	<tr><th class="head" colspan="2">'.$module->l('Social Login Basic Settings', 'sociallogin').'</small></th></tr>
  	<tr class="row_white">
	<td colspan="2" ><span class="subhead">
	'.$module->l('Where do you want to redirect your users after successfully log in?', 'sociallogin').'</span><br /><br />
	<label><input name="LoginRadius_redirect" value="backpage" type="radio" onclick="javascript:hidetextbox(1);" '.$checked[0].' />
	'.$module->l('Redirect to Same page (Same as traditional login)', 'sociallogin').'</label> <br/>
	<label><input name="LoginRadius_redirect" value="profile" type="radio" onclick="javascript:hidetextbox(1);" '.$checked[1].' />
	'.$module->l('Redirect to the profile', 'sociallogin').'</label> <br/>
	<label><input name="LoginRadius_redirect" value="url" type="radio" onclick="javascript:hidetextbox(0);" '.$checked[2].' />
	'.$module->l('Redirect to the following url:', 'sociallogin').'</label> <br/>
	<input style ="display:none;" type="text" name="redirecturl" id="redirecturl"  size="40" value="'.$redirect.'" />
	</td>
	</tr>
	</table>
	</dd>';

	return $html;
}

/**
 * Content of Social Sharing tab
 *
 * @return string content of Social Sharing tab
 */
function loginRadiusSocialSharingTabSettings()
{
	$module = new SocialLogin();
	$html = '<!-- social share -->
	<dd><div style="display:none;" id="second">
	<table class="form-table sociallogin_table">
	<tr>
	<th class="head" colspan="2">'.$module->l('LoginRadius Social Share Settings', 'sociallogin').'</small></th>
	</tr>
	<tr class="row_white">
	<td colspan="2" >
	<span class="subhead">'.$module->l('Please select the social sharing widget, horizontal and vertical widgets can be enabled simultaneously.', 'sociallogin').'</span><br /><br />';
	$style_visible = 'style="position:absolute;border-bottom:8px solid #EBEBEB;
	  border-right:8px solid transparent; border-left:8px solid transparent; margin:19px 0 0 -106px"';
	$html .= '<a id="mymodal1" href="javascript:void(0);" onclick="Makehorivisible();"><b>'.$module->l('Horizontal widget', 'sociallogin').'</b></a> |
	<a id="mymodal2" href="javascript:void(0);" onclick="Makevertivisible();"><b>'.$module->l('Vertical widget', 'sociallogin').'</b></a>
	<table style="border:#dddddd 1px solid; padding:10px; background:#FFFFFF; margin:10px 0 0 0;border-collapse: separate !important;">
	<span id = "arrow" '.$style_visible.'></span>
	<tr class="row_white" id="enable_social_horizontal_sharing"><td colspan="2" >
	<span class="subhead">'.$module->l('Do you want to enable horizontal social sharing for your website?', 'sociallogin').'</span><br /><br />
	<label><input type="radio" name="enable_social_horizontal_sharing" value="0"
	'.(Tools::getValue('enable_social_horizontal_sharing', Configuration::get('enable_social_horizontal_sharing')) == 0 ? 'checked="checked"' : '').' />
	'.$module->l('Yes', 'sociallogin').'</label>
	<label><input type="radio" name="enable_social_horizontal_sharing" value="1"
	'.(Tools::getValue('enable_social_horizontal_sharing', Configuration::get('enable_social_horizontal_sharing')) == 1 ? 'checked="checked"' : '').' />
	'.$module->l('No', 'sociallogin').'</label>
	</td>
	</tr>
	<tr class="row_white" id ="enable_social_vertical_sharing"><td colspan="2" ><span class="subhead">
	'.$module->l('Do you want to enable vertical social sharing for your website?', 'sociallogin').'</span><br /><br />
	<label><input type="radio" name="enable_social_vertical_sharing" value="0"
	'.(Tools::getValue('enable_social_vertical_sharing', Configuration::get('enable_social_vertical_sharing')) == 0 ? 'checked="checked"' : '').' />
	'.$module->l('Yes', 'sociallogin').'</label>
	<label><input type="radio" name="enable_social_vertical_sharing" value="1"
	'.(Tools::getValue('enable_social_vertical_sharing', Configuration::get('enable_social_vertical_sharing')) == 1 ? 'checked="checked"' : '').' />
	'.$module->l('No', 'sociallogin').'
	</label></td>
	</tr>
	<tr class="sharing_block" style="background: #EBEBEB;"><td><div class="subhead">'.$module->l('Select your social sharing widget:', 'sociallogin').'</div>';
	$html .= '<div><div id="sharehorizontal" style=display:block>
	<label><input name="chooseshare" id = "hori32" '.(Configuration::get('chooseshare') == 0
		|| Configuration::get('chooseshare') == '' ? 'checked="checked"' : '').'  type="radio"  value="0"
	 onclick ="toggle_loginradius_horizontal_sharing(true);" />
	<img src = "../modules/sociallogin/img/horizonSharing32.png"/></label>
	<label><input name="chooseshare" '.(Configuration::get('chooseshare') == 1 ? 'checked="checked"' : '').'
	type="radio" value="1" onclick ="toggle_loginradius_horizontal_sharing(true);" />
	<img src = "../modules/sociallogin/img/horizonSharing16.png" /></label>
	<label for="horithemelarge"><input name="chooseshare" id = "horithemelarge" '.(Configuration::get('chooseshare') == 2 ? 'checked="checked"' : '').'
	 type="radio" value="2"  onclick ="toggle_loginradius_horizontal_sharing(false);" />
	<img src = "../modules/sociallogin/img/single-image-theme-large.png" /></label>
	<label for="horithemesmall"><input name="chooseshare" id = "horithemesmall" '.(Configuration::get('chooseshare') == 3 ? 'checked="checked"' : '').'
	type="radio" value="3"  onclick ="toggle_loginradius_horizontal_sharing(false);" />
	<img src = "../modules/sociallogin/img/single-image-theme-small.png" /></label>
<label for="hybrid-horizontal-horizontal"> 	<input name="chooseshare" id = "hybrid-horizontal-horizontal"
'.(Configuration::get('chooseshare') == 9 ? 'checked="checked"' : '').'
type="radio" value="9" 
onclick ="toggle_loginradius_horizontal_sharing(true, true);" /><img src = "../modules/sociallogin/img/hybrid-horizontal-horizontal.png" /></label>
	<label for="hybrid-horizontal-vertical"><input name="chooseshare" id = "hybrid-horizontal-vertical"
	'.(Configuration::get('chooseshare') == 8 ? 'checked="checked"' : '').'
	type="radio" value="8"  onclick ="toggle_loginradius_horizontal_sharing(true, true);" />
	<img src = "../modules/sociallogin/img/hybrid-horizontal-vertical.png" /></label>
	</div>';
	$html .= '<div id="sharevertical" style=display:none>
	<label for="vertibox32"><input name="chooseverticalshare" id = "vertibox32"  '.(Configuration::get('chooseverticalshare') == 4
		|| Configuration::get('chooseverticalshare') == '' ? 'checked="checked"' : '').'  type="radio" value="4"
	onclick ="loginradius_toggle_vertical_sharing(true);"/><img src =  "../modules/sociallogin/img/32VerticlewithBox.png" /></label>
	<label for="vertibox16"><input name="chooseverticalshare" id = "vertibox16"
	'.(Configuration::get('chooseverticalshare') == 5 ? 'checked="checked"' : '').'	type="radio" value="5"
	onclick ="loginradius_toggle_vertical_sharing(true);"/> <img src = "../modules/sociallogin/img/16VerticlewithBox.png"/></label>
	<label for="hybrid-verticle-vertical">	<input name="chooseverticalshare" id = "hybrid-verticle-vertical"
	'.(Configuration::get('chooseverticalshare') == 6 || Configuration::get('chooseverticalshare') == '' ? 'checked="checked"' : '').'
	type="radio" value="6"  onclick ="loginradius_toggle_vertical_sharing(false);" />
	<img src =  "../modules/sociallogin/img/hybrid-verticle-vertical.png"  style="margin-right:-5px"/></label>
    <label for="hybrid-verticle-horizontal"> 	<input name="chooseverticalshare" id = "hybrid-verticle-horizontal"
    '.(Configuration::get('chooseverticalshare') == 7 ? 'checked="checked"' : '').'  type="radio" value="7"
    onclick ="loginradius_toggle_vertical_sharing(false);"/> <img src = "../modules/sociallogin/img/hybrid-verticle-horizontal.png" />
    </label><br /><br />
	</div></div></div>
	</td>
	</tr>
  	<tr class="row_white" id="vertical_sharing_position"><td colspan="2" >
	<span class="subhead">'.$module->l('Select the position of social sharing widget', 'sociallogin').'</span><br /><br />
<label><input name="choosesharepos" id = "topleft" type="radio" '.(Configuration::get('choosesharepos') == 0
		|| Configuration::get('choosesharepos') == '' ? 'checked="checked"' : '').' value="0" />Top Left</label><br/>
	<label><input name="choosesharepos" id = "topright" type="radio" 
  '.(Configuration::get('choosesharepos') == 1 ? 'checked="checked"' : '').' value="1" />Top Right</label><br />
	<label><input name="choosesharepos" id = "bottomleft" type="radio" 
  '.(Configuration::get('choosesharepos') == 2 ? 'checked="checked"' : '').' value="2" />Bottom Left</label><br />
	<label><input name="choosesharepos" id = "bottomright" type="radio" 
  '.(Configuration::get('choosesharepos') == 3 ? 'checked="checked"' : '').' value="3" />Bottom Right</label>
	</td>
	</tr>
	<tr class="label_sharing_networks">
	<td colspan="2" ><span class="subhead">'.$module->l('What sharing networks do you want to show in the sharing widget? (All other
	sharing networks will be shown as part of LoginRadius sharing icon)', 'sociallogin').'</span><br /><br /><div id="loginRadiusSharingLimit"
	style="color: red; display: none; margin-bottom: 5px;">You can select only 9 providers.</div>
  <table class="form-table sociallogin_table" id="shareprovider">
	</table>
	</td>
	</tr>
	<tr class="loginradius_rearrange_icons">
	<td colspan="2" style="background:#EBEBEB;" >
	<span class="subhead">'.$module->l('What sharing network order do you prefer for your sharing widget?', 'sociallogin').'</span><br />
	<ul id="sortable" style="height:35px;">';
	$li = '';
	$rearrange_settings = unserialize(Configuration::get('rearrange_settings'));

	if (empty($rearrange_settings))
		$rearrange_settings = loginRadiusGetSharingDefaultNetworks('sharing');

	foreach ($rearrange_settings as $provider)
		$li .= '<li title="'.$provider.'" id="loginRadiusLI'.$provider.'" class="lrshare_iconsprite32 lrshare_'.$provider.'">
		<input type="hidden" name="rearrange_settings[]" value="'.$provider.'" />
		</li>';

	$html .= $li.'</ul>
	</td>
	</tr>
	<tr class="label_verticalsharing_networks" style="background: rgb(235, 235, 235);">
	<td colspan="2" ><span class="subhead">'.$module->l('What sharing networks do you want to show in the sharing widget?
	(All other sharing networks will be shown as part of LoginRadius sharing icon)', 'sociallogin').'</span><br /><br />
	<div id="loginRadiusverticalSharingLimit" style="color: red; display: none; margin-bottom: 5px;">
	You can select only 9 providers.</div>
  <table class="form-table sociallogin_table" id="verticalshareprovider">
	</table>
	</td>
	</tr>
	<tr class="loginradius_verticalrearrange_icons">
	<td colspan="2" >
	<span class="subhead">'.$module->l('What sharing network order do you prefer for your sharing widget?', 'sociallogin').'</span><br />
	<ul id="verticalsortable" style="height:35px;">';
	$vertical_li = '';
	$vertical_rearrange_settings = unserialize(Configuration::get('vertical_rearrange_settings'));

	if (empty($vertical_rearrange_settings))
		$vertical_rearrange_settings = loginRadiusGetSharingDefaultNetworks('sharing');

	foreach ($vertical_rearrange_settings as $provider)
		$vertical_li .= '<li title="'.$provider.'" id="loginRadiusverticalLI'.$provider.'" class="lrshare_iconsprite32 lrshare_'.$provider.'">
		<input type="hidden" name="vertical_rearrange_settings[]" value="'.$provider.'" />
		</li>';

	$html .= $vertical_li.'</ul>
	</td>
	</tr>
	<tr class="horizontal_location">
	<td>
	<span class="subhead">'.$module->l('What area(s) do you want to show the social sharing widget?', 'sociallogin').'</span>
	<table class="form-table">
	<tr>
	<td>';
	if (Configuration::get('social_share_home') == 0 && Configuration::get('social_share_cart') == 0 && Configuration::get('social_share_product') == 0)
		Configuration::updateValue('social_share_home', 1);

	$html .= '<label><input type="checkbox" name="social_share_home" value="1"
		'.(Tools::getValue('social_share_home', Configuration::get('social_share_home')) ? 'checked="checked"' : '').' />Show on Home page</label></td><td>
	<label><input type="checkbox" name="social_share_cart" value="1"
	'.(Tools::getValue('social_share_cart', Configuration::get('social_share_cart')) ? 'checked="checked"' : '').' />Show on Cart page</label></td><td>
	<label><input type="checkbox" name="social_share_product" value="1"
	'.(Tools::getValue('social_share_product', Configuration::get('social_share_product')) ? 'checked="checked"' : '').' />Show on Product Page
	</label></td></tr></table></td></tr>
	<tr class ="vertical_location" style="background: rgb(235, 235, 235);"><td>
	<span class="subhead">'.$module->l('What area(s) do you want to show the social sharing widget?', 'sociallogin').'</span>
	<table class="form-table"><tr><td>';

	if (Configuration::get('social_verticalshare_home') == 0 && Configuration::get('social_verticalshare_cart') == 0
		&& Configuration::get('social_verticalshare_product') == 0)
		Configuration::updateValue('social_verticalshare_home', 1);

	$html .= '<label><input type="checkbox" name="social_verticalshare_home" value="1"
	'.(Tools::getValue('social_verticalshare_home', Configuration::get('social_verticalshare_home')) ? 'checked="checked"' : '').' />
	Show on Home page</label></td><td><label><input type="checkbox" name="social_verticalshare_cart" value="1"
	'.(Tools::getValue('social_verticalshare_cart', Configuration::get('social_verticalshare_cart')) ? 'checked="checked"' : '').' />
	Show on Cart page</label></td><td><label><input type="checkbox" name="social_verticalshare_product" value="1"
	'.(Tools::getValue('social_verticalshare_product', Configuration::get('social_verticalshare_product')) ? 'checked="checked"' : '').' />
	Show on Product Page</label></td></tr></table></td></tr></table></td>
	</tr></table></div></dd>';

	return $html;
}

/**
 * Content of Advance settings tab
 *
 * @return string content of Advance settings tab
 */
function loginRadiusAdvanceTabSettings()
{
	$module = new SocialLogin();
	$profilefield_value = unserialize(Configuration::get('profilefield'));
	$profilefield = implode(';', $profilefield_value);

	if (empty($profilefield))
		$profilefield[] = '3';

	$title = Configuration::get('POPUP_TITLE');
	$error_message = Configuration::get('ERROR_MESSAGE');
	$html = '<dd><div style="display:none;" id="third">
	<table class="form-table sociallogin_table">
	<tr><th class="head" colspan="2">'.$module->l('Social Login Interface Customization', 'sociallogin').'</small></th></tr>
    	<tr class="row_white"> <td colspan="2" ><span class="subhead">
	'.$module->l('Please enter the title to be shown on social login interface', 'sociallogin').'</span><br/><br />
	<input type="text" name="TITLE"  size="50" value="'.Configuration::get('TITLE').'" /><br/><span class="description">'.$module->l('Leave empty for no text').'</span></td></tr>
	<tr><td colspan="2" ><span class="subhead">'.$module->l('Select the icon size to use in the social login
	interface', 'sociallogin').'</span><br /><br />
   <label> <input type="radio" name="social_login_icon_size" value="0"
	'.(Tools::getValue('social_login_icon_size', Configuration::get('social_login_icon_size')) == 0 ? 'checked="checked"' : '').' />
	'.$module->l('Large', 'sociallogin').'</label>
	<label><input type="radio" name="social_login_icon_size" value="1"
	'.(Tools::getValue('social_login_icon_size', Configuration::get('social_login_icon_size')) == 1 ? 'checked="checked"' : '').' />
	'.$module->l('Small', 'sociallogin').'</label>
	</td>
	</tr>
	<tr class="row_white"><td colspan="2" ><span class="subhead">
	'.$module->l('How many social icons would you like to be displayed per row?', 'sociallogin').'</span><br /><br />
	<input type="text" name="social_login_icon_column" id="social_login_icon_column"  size="-7" value="'.trim(Configuration::get('social_login_icon_column')).'" />
	</td>
	</tr>
	<tr><td colspan="2" ><span class="subhead">'.$module->l('What background color would you like to use for the social login interface?', 'sociallogin').'</span><br /><br />
<input type="text" name="social_login_background_color" id="social_login_background_color"
	size="50" value="'.trim(Configuration::get('social_login_background_color')).'" />
    <span class=description>'.$module->l('Leave empty for transparent. You can enter hexa-decimal code of the color as well as name of the color.', 'sociallogin').'</span>
	</td></tr></table>
	<table class="form-table sociallogin_table">
	<tr>
	<th class="head" colspan="2">'.$module->l('Social Login User Settings', 'sociallogin').'</small></th>
	</tr><tr class="row_white">
	<td colspan="2" ><span class="subhead">
	'.$module->l('Do you want to send email to admin after new user registration at your website?', 'sociallogin').'</span><br /><br />
	<label><input type="radio" name="SEND_REQ" value="1" '.(Tools::getValue('SEND_REQ', Configuration::get('SEND_REQ')) ? 'checked="checked" ' : '').'/>
	'.$module->l('Yes', 'sociallogin').'</label>
	<label><input type="radio" name="SEND_REQ" value="0"  '.(!Tools::getValue('SEND_REQ', Configuration::get('SEND_REQ')) ? 'checked="checked" ' : '').'/>
	 '.$module->l('No', 'sociallogin').'</label>
	</td></tr><tr>
	<td colspan="2" ><span class="subhead">
	'.$module->l('Do you want to send email to customer after new user registration at your website?', 'sociallogin').'</span><br /><br />
	<label><input type="radio" name="user_notification" value="0"
	 '.(!Tools::getValue('user_notification', Configuration::get('user_notification')) ? 'checked="checked" ' : '').'/>
	 '.$module->l('Yes', 'sociallogin').'</label>
	<label><input type="radio" name="user_notification" value="1"
	'.(Tools::getValue('user_notification', Configuration::get('user_notification')) ? 'checked="checked" ' : '').'/>
	'.$module->l('No', 'sociallogin').'</label>
	</td></tr>
	<tr class="row_white">
	<td colspan="2" ><span class="subhead">
	'.$module->l('Do you want users to provide required prestashop profile fields before completing registration process?
	(A pop-up will open asking user to provide details of the fields not coming from social network provider)', 'sociallogin').'</span><br /><br />
	<label><input type="radio" name="user_require_field" onchange="show_profilefield(this.value);" value="1"
	'.(Tools::getValue('user_require_field', Configuration::get('user_require_field')) ? 'checked="checked" ' : '').'/>
	'.$module->l('Yes', 'sociallogin').'</label>
	<label><input type="radio" name="user_require_field"  onchange="show_profilefield(this.value);" value="0"
	'.(!Tools::getValue('user_require_field', Configuration::get('user_require_field')) ? 'checked="checked" ' : '').'/>
	'.$module->l('No', 'sociallogin').'</label>
	<table class="form-table sociallogin_table1" id="profilefield_display" style="display:block;">
	<tr><td>
	<label><input type="checkbox" name="profilefield[]" value="1"  '.(strpos($profilefield, '1') !== false ? 'checked="checked"' : '').' />
	'.$module->l('First Name', 'sociallogin').'</label></td><td>
	<label><input type="checkbox" name="profilefield[]" value="2" '.(strpos($profilefield, '2') !== false ? 'checked="checked"' : '').' />
	'.$module->l('Last Name', 'sociallogin').'</label></td><td>
	<label><input type="checkbox" name="profilefield[]" value="3" '.(strpos($profilefield, '3') !== false ? 'checked="checked"' : '').' />
	'.$module->l('Country', 'sociallogin').'</label></td><td>
	<label><input type="checkbox" name="profilefield[]" value="4" '.(strpos($profilefield, '4') !== false ? 'checked="checked"' : '').' />
	'.$module->l('City', 'sociallogin').'</label></td><td>
	<label><input type="checkbox" name="profilefield[]" value="5" '.(strpos($profilefield, '5') !== false ? 'checked="checked"' : '').' />
	'.$module->l('Mobile Number', 'sociallogin').'</td><td>
	<label><input type="checkbox" name="profilefield[]" value="6" '.(strpos($profilefield, '6') !== false ? 'checked="checked"' : '').' />
	'.$module->l('Address', 'sociallogin').'</label></td><td>
	<label><input type="checkbox" name="profilefield[]" value="7" '.(strpos($profilefield, '7') !== false ? 'checked="checked"' : '').' />
	'.$module->l('Address title', 'sociallogin').'</label></td><td>
	<label><input type="checkbox" name="profilefield[]" value="8" '.(strpos($profilefield, '8') !== false ? 'checked="checked"' : '').' />
	'.$module->l('Zip Code', 'sociallogin').'</label>
	</td></tr></table></td></tr><tr>
	<td colspan="2" ><span class="subhead">'.$module->l('A few network providers do not supply users e-mail address as part of user profile data.
	Do you want users to provide their email address before completing registration process?', 'sociallogin').'</span><br /><br />
	<label><input type="radio" name="EMAIL_REQ" value="0"
	'.(!Tools::getValue('EMAIL_REQ', Configuration::get('EMAIL_REQ')) ? 'checked="checked" ' : '').' />
	'.$module->l('Yes, get real email address from the users (Ask users to enter their email address in a pop-up)', 'sociallogin').'</label>
	<label style="float:left;text-align:left"><input type="radio" name="EMAIL_REQ" value="1"
	'.(Tools::getValue('EMAIL_REQ', Configuration::get('EMAIL_REQ')) ? 'checked="checked" ' : '').'/>
	'.$module->l('No, just auto-generate random email address for the users', 'sociallogin').'
	</label></td>
	</tr><tr >
	<input id="connection_url" type="hidden" value="" />
	</tr><tr class="row_white"><td>
	<span class="subhead">'.$module->l('Please enter the message to be displayed to the user in the pop-up', 'sociallogin').'</span><br /><br />
	<input type="text" name="POPUP_TITLE"  size="50"
	value="'.(empty($title) ? $module->l('Please fill the following details to complete the registration', 'sociallogin') : $title).'" />
	<br /></td></tr><tr><td>
	<span class="subhead">Please enter the message to be shown to the user in case of an invalid entry in popup</span><br /><br />
	<input type="text" name="ERROR_MESSAGE"  size="50"
	value="'.(empty($error_message) ? $module->l('Please enter the following fields', 'sociallogin') : $error_message).'" />
	</td></tr>
  	<tr class="row_white">
	<td colspan="2" ><span class="subhead">
	'.$module->l('Do you want to update the user profile data in your database everytime a user logs into your website?', 'sociallogin').'</span><br /><br />
	<label><input type="radio" name="update_user_profile" value="0"
	'.(!Tools::getValue('update_user_profile', Configuration::get('update_user_profile')) ? 'checked="checked" ' : '').' />
	'.$module->l(' Yes', 'sociallogin').'</label>
	<label><input type="radio" name="update_user_profile" value="1"
	'.(Tools::getValue('update_user_profile', Configuration::get('update_user_profile')) ? 'checked="checked" ' : '').'/>
	'.$module->l(' No', 'sociallogin').'
	</label></td></tr></table></div></dd>';

	return $html;
}

/**
 * Content of Help box tab
 *
 * @return string content of Help box tab
 */
function loginRadiusHelpSideBox()
{
	$module = new SocialLogin();
	$html = '<div style="float:right; width:29%;">
	<!-- Help Box -->
	<div style="background: none repeat scroll 0 0 rgb(231, 255, 224);border: 1px solid rgb(191, 231, 176); overflow:auto; margin:0 0 10px 0;">
	<h3 style="border-bottom:#000000 1px solid; margin:0px; padding:0 0 6px 0; border-bottom: 1px solid #B3E2FF; color: #000000; margin:10px;">
	'.$module->l('Help & Documentations', 'sociallogin').'</h3>
	<ul class="help_ul">
	<li><a href="http://ish.re/BBM0"
	target="_blank">
	'.$module->l('Plugin Installation, Configuration and Troubleshooting', 'sociallogin').'</a></li>
	<li><a href="http://ish.re/9VBI" target="_blank">
	'.$module->l('How to get LoginRadius API Key & Secret', 'sociallogin').'</a></li>
	<li><a href="http://ish.re/2BS" target="_blank">
	'.$module->l('Support Documentations', 'sociallogin').'</a></li>
	<li><a href="http://ish.re/8PG2" target="_blank">'.$module->l('Discussion Forum', 'sociallogin').'</a></li>
	<li><a href="http://ish.re/96M7" target="_blank">
	'.$module->l('About LoginRadius', 'sociallogin').'</a></li>
	<li><a href="http://ish.re/96M9" target="_blank">
	'.$module->l('LoginRadius Products', 'sociallogin').'</a></li>
	<li><a href="http://ish.re/96M8" target="_blank">
	'.$module->l('Social Plugins', 'sociallogin').'</a></li>
	<li><a href="http://ish.re/96MA" target="_blank">
	'.$module->l('Social SDKs', 'sociallogin').'</a></li>
	</ul>
	</div>
	<div style="clear:both;"></div>
	<div style="background:#EAF7FF; border: 1px solid #B3E2FF;  margin:0 0 10px 0; overflow:auto;">
	<h3 style="border-bottom:#000000 1px solid; margin:0px; padding:0 0 6px 0; border-bottom: 1px solid #B3E2FF; color: #000000; margin:10px;">
	Stay Update!</h3>
	<p align="justify" style="line-height: 19px;font-size:12px !important;margin:10px !important;color: #000000;">
	'.$module->l('To receive updates on new features, releases, etc, please connect to one of our social media pages.', 'sociallogin').'</p>
	<center>
   <a  href="https://www.facebook.com/loginradius" target="_blank"><img src="'.__PS_BASE_URI__.'modules/sociallogin/img/footer-media-links/facebook.png" /></a>
   <a href="https://twitter.com/LoginRadius" target="_blank"><img src="'.__PS_BASE_URI__.'modules/sociallogin/img/footer-media-links/twitter.png" /></a>
  <a href="https://plus.google.com/+Loginradius" target="_blank"> <img src="'.__PS_BASE_URI__.'modules/sociallogin/img/footer-media-links/google.png" /></a>
  <a href="http://www.linkedin.com/company/loginradius" target="_blank"> <img src="'.__PS_BASE_URI__.'modules/sociallogin/img/footer-media-links/linkedin.png" /></a>
  <a href="https://www.youtube.com/user/LoginRadius" target="_blank"> <img src="'.__PS_BASE_URI__.'modules/sociallogin/img/footer-media-links/youtube.png" /></a>
  </center>
	</div>
	<div style="clear:both;"></div>
	</div>
	</div>';
	return $html;
}

/**
 * Save module settings into database
 *
 * @return string message that display to admin.
 */
function loginRadiusSaveModuleSettings()
{
	$module = new SocialLogin();
	$html = '';
	$merge_settings = array_merge(loginRadiusSocialLoginSettings(), loginRadiusSocialSharingSettings());
	$settings = array_merge($merge_settings, loginRadiusAdvanceSettings());
	$result = loginRadiusModuleSettingsValidate();

	if ($result != NULL)
		$html .= $module->displayError($result);

	Configuration::updateValue('API_KEY', trim(Tools::getValue('API_KEY')));
	Configuration::updateValue('API_SECRET', trim(Tools::getValue('API_SECRET')));
	loginRadiusUpdateModuleSettings($settings);
	$html .= $module->displayConfirmation($module->l('Settings updated.', 'sociallogin'));
	return $html;
	
}

/**
 * Update module settings inot database
 *
 * @param array $settings Conatin module settings information.
 */
function loginRadiusUpdateModuleSettings($settings)
{
	foreach ($settings as $key => $value)
		Configuration::updateValue($key, $value);
}