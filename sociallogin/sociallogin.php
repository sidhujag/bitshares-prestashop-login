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

include_once(dirname(__FILE__).'/includes/sociallogin_user_functions.php');

class SocialLogin extends Module
{
	/**
	 * Add required variables that are used to define module
	 *
	 * Constructor
	 **/
	public function __construct()
	{
		$this->name = 'sociallogin';
		$this->tab = 'others';
		$this->version = '3.0';
		$this->author = 'LoginRadius';
		$this->need_instance = 0;
		$this->module_key = '3afa66f922e9df102449d92b308b4532'; //Product Key //Don't change.
		parent::__construct();
		$this->displayName = $this->l('Social Login');
		$this->description = $this->l('Let your users log in and comment via their accounts with popular ID providers such as Facebook,
		Google, Twitter, Yahoo, Vkontakte and over 25 more!.');
	}

	/**
	 *  Left column hook that show social login interface left side.
	 *
	 * @param array $params Parameters
	 * @param string $str To identify hook of left, right
	 * @return string Content
	 */
	public function hookLeftColumn($params, $str = '')
	{
		$context = Context::getContext();
		$cookie = $context->cookie;
		$smarty = $context->smarty;

		if (Context::getContext()->customer->isLogged())
			return;
		$loginradius_api_key = trim(Configuration::get('API_KEY'));
		$loginradius_api_secret = trim(Configuration::get('API_SECRET'));

		if (!empty($loginradius_api_key) && !empty($loginradius_api_secret))
		{
			$cookie->lr_login = false;
			$margin_style = '';

			if ($str == 'margin')
				$margin_style = 'style="margin-left:8px;margin-top:5px;"';
			$title = Configuration::get('TITLE');
			$smarty->assign('sl_title', $title);
			$iframe = 'interfacecontainerdiv';

			if ($str == 'right' || $str == '')
				$right = true;
			else
			{
				$right = false;
				$iframe = 'interfacecontainerdiv';
			}

			$smarty->assign('right', $right);
			$smarty->assign('margin_style', $margin_style);
			$smarty->assign('iframe', $iframe);
			return $this->display(__FILE__, 'loginradius.tpl');
		}
	}

	/**
	 * Right column hook that show social login interface right side.
	 *
	 * @param array $params Parameters
	 * @return string Content
	 */
	public function hookRightColumn($params)
	{
		return $this->hookLeftColumn($params, 'right');
	}

	/**
	 * Account top hook that show social login interface at create an account (register ) .
	 *
	 * @param array $params Parameters
	 * @return string Content
	 */
	public function hookCreateAccountTop($params)
	{
		return $this->hookLeftColumn($params, 'margin');
	}

	/**
	 * Header hook that add script [Social share script, Social counter script, Social Interface script] at head .
	 *
	 * @param array $params Parameters
	 * @return string LoginRadius login and sharing script
	 */
	public function hookHeader($params)
	{
		$script = '';

			$loginradius_api_key = trim(Configuration::get('API_KEY'));
			$loginradius_api_secret = trim(Configuration::get('API_SECRET'));

			if (!empty($loginradius_api_key) && !empty($loginradius_api_secret))
				$script .= loginRadiusInterfaceScript();

			if (Configuration::get('enable_social_horizontal_sharing') == '0')
				$script .= loginRadiusHorizontalShareScript();

			if (Configuration::get('enable_social_vertical_sharing') == '0')
				$script .= loginRadiusVerticalShareScript();

		return $script;
	}

	/**
	 * home hook that showing share and counter widget at home page.
	 *
	 * @param array $params Parameters
	 * @return string sharing and counter div
	 */
	public function hookHome($params)
	{
		return loginRadiusGetSharingDiv('social_share_home', 'social_verticalshare_home');
	}

	/**
	 *  Invoice hook that showing share and counter widget at Invoice page.
	 *
	 * @param array $params Parameters
	 * @return string sharing and counter div
	 */
	public function hookInvoice($params)
	{
		return loginRadiusGetSharingDiv('social_share_product', 'social_verticalshare_product');
	}

	/**
	 * Cart hook that showing share and counter widget at Cart page.
	 *
	 * @param array $params Parameters
	 * @return string sharing and counter div
	 */
	public function hookShoppingCart($params)
	{
		return loginRadiusGetSharingDiv('social_share_cart', 'social_verticalshare_cart');
	}

	/**
	 * Show share and counter widget at right column of product page
	 *
	 * @param array $params Parameters
	 * @return string sharing and counter div
	 */
	public function hookDisplayRightColumnProduct($params)
	{
		if (_PS_VERSION_ >= 1.6)
			return loginRadiusGetSharingDiv('social_share_product', 'social_verticalshare_product');
	}

	/**
	 * Show share and counter widget at compare page
	 * @param array $params Parameters
	 * @return string sharing and counter div
	 */
	public function hookDisplayCompareExtraInformation($params)
	{
		return loginRadiusGetSharingDiv('social_share_product', 'social_verticalshare_product');
	}

	/**
	 *  Product footer hook that showing share and counter widget at product footer page.
	 *
	 * @param array $params Parameters
	 * @return string sharing and counter div
	 */
	public function hookProductFooter($params)
	{
		if (_PS_VERSION_ < 1.6)
		{
			$context = Context::getContext();
			$cookie = $context->cookie;
			/* Product informations */
			$product = new Product((int)Tools::getValue('id_product'), false, (int)$cookie->id_lang);
			$this->currentproduct = $product;
			return loginRadiusGetSharingDiv('social_share_product', 'social_verticalshare_product');
		}
	}

	/*
	*  Top hook that Handle login functionality.
	*/
	public function hookTop()
	{
		$context = Context::getContext();
		$cookie = $context->cookie;
		$module = new SocialLogin();

		//check user is already logged in.
		if (Context::getContext()->customer->isLogged())
		{
			include_once('includes/LoginRadiusSDK.php');
			$lr_obj = new LoginRadius();
			$check_curl = function_exists('curl_version');
			//Get the user profile data.
			$userprofile = $lr_obj->loginRadiusGetUserProfileData(Tools::getValue('token'), $check_curl);

			//Provide account linking when uer is laready logged in.
			if (!empty($userprofile) && Tools::getValue('token'))
				loginRadiusAccountLinking($cookie, $userprofile);

			//Remove account linking when user click on remove button.
			if (Tools::getValue('id_provider'))
				loginRadiusRemoveLinking($cookie, Tools::getValue('id_provider'));
		}

		//user is not logged in.
		//Retrieve token and provide login functionality.
		if (Tools::getValue('token') && !Tools::getValue('email_create'))
			return loginRadiusConnect();
		//Get verification link to verify email.
		elseif (Tools::getValue('SL_VERIFY_EMAIL'))
			return loginRadiusVerifyEmail();
		//Resend email verfication if user would not get verification email.
		elseif (Tools::getValue('resend_email_verification'))
			return loginRadiusResendEmailVerification(Tools::getValue('social_id_value'));
		//When Email popup is submitted by user.
		elseif (Tools::getValue('hidden_val'))
		{
			if ((Tools::getValue('LoginRadius')) && Tools::getValue('LoginRadius') == 'Submit' && (Tools::getValue('hidden_val') == $cookie->sl_hidden))
				return loginRadiusHandlePopupSubmit($cookie);
			//Saved data (cookie)is deleted.
			else
			{
				$message = $module->l('Cookie has been deleted, please try again.');
				return loginRadiusPopupVerify($message);
			}
		}
	}

	/**
	 * customer account hook that show tpl for Social linking.
	 *
	 * @param array $params Parameters
	 * @return string Social Linking widget
	 */
	public function hookDisplayCustomerAccount($params)
	{
		$this->smarty->assign('in_footer', false);
		return $this->display(__FILE__, 'my-account.tpl');
	}

	/**
	 * my account hook that show tpl for Social linking.
	 *
	 * @param array $params Parameters
	 * @return string Social Linking widget
	 */
	public function hookMyAccountBlock($params)
	{
		$this->smarty->assign('in_footer', true);
		return $this->display(__FILE__, 'my-account.tpl');
	}

	/**
	 * Install hook that  register hook which used by social Login.
	 *
	 * @return boolean true when module hooks and tables created successfully.
	 */
	public function install()
	{
		if (_PS_VERSION_ >= 1.6)
		{
			//Store the added files
			$files_added = array();
			// Copy controller files.
			$files = loginRadiusGetFilesToInstall();

			foreach ($files as $file_name => $file_data)
			{
				if (is_array($file_data) && !empty ($file_data['source']) && !empty ($file_data['target']))
				{
					if (!file_exists($file_data['target'].$file_name))
					{
						if (!copy($file_data['source'].$file_name, $file_data['target'].$file_name))
						{
							// Add Error
							$this->context->controller->errors[] = 'Could not copy the file '.$file_name.' to the directory '.$file_data['target'];

							// Remove the copied files.
							foreach ($files_added as $file_name)
							{
								if (file_exists($file_name))
									@unlink($file_name);
							}

							// Abort Installation.
							return false;
						}
						else
							$files_added[] = $file_data['target'].$file_name;
					}
				}
			}
		}

		if (!parent::install()
			|| !$this->registerHook('leftColumn')
			|| !$this->registerHook('createAccountTop')
			|| !$this->registerHook('rightColumn')
			|| !$this->registerHook('top')
			|| !$this->registerHook('Header')
			|| !$this->registerHook('Home')
			|| !$this->registerHook('Invoice')
			|| !$this->registerHook('ShoppingCart')
			|| !$this->registerHook('productfooter')
			|| !$this->registerHook('customerAccount')
			|| !$this->registerHook('displayRightColumnProduct')
			|| !$this->registerHook('displayCompareExtraInformation')
			|| !$this->registerHook('myAccountBlock'))
			return false;
		$hooks_array = array('leftColumn', 'rightColumn');

		foreach ($hooks_array as $value)
			loginRadiusMoveLrHookPosition($value, 1);
		//create the social Login table.
		createDatabaseLrTable();
		return true;
	}

	/**
	 * Login Radius Admin UI.
	 *
	 * @return string Admin UI Content
	 */
	public function getContent()
	{
		include_once(dirname(__FILE__).'/includes/sociallogin_admin.php');
		$html = loginRadiusGetAdminContent();
		return $html;
	}

	/**
	 * Show Social linking Interface for account linking.
	 *
	 * @return array Contain linked social network information
	 */
	public static function loginRadiusJsInterface()
	{
		$context = Context::getContext();
		$cookie = $context->cookie;
		$getdata = Db::getInstance()->ExecuteS('SELECT * FROM '.pSQL(_DB_PREFIX_.'customer').' as c WHERE c.email='." '$cookie->email' ".' LIMIT 0,1');
		$num = (!empty($getdata['0']['id_customer']) ? $getdata['0']['id_customer'] : '');
		$linkedprovider = Db::getInstance()->ExecuteS('SELECT * from '.pSQL(_DB_PREFIX_.'sociallogin')." where `id_customer`='".$num."'");

		if (Context::getContext()->customer->isLogged())
		{
			$http = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'Off' && !empty($_SERVER['HTTPS'])) ? 'https://' : 'http://');
			$loc = urldecode($http.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);

			if (strpos($loc, 'sociallogin') !== false)
				$cookie->currentquerystring = $loc;
		}

		if (!$cookie->lr_login)
			$cookie->loginradius_id = '';

		return $linkedprovider;
	}

	/**
	 * delete social login table form database.
	 *
	 * @return boolean true when module uninstall successfully.
	 */
	public function uninstall()
	{
		if (!parent::uninstall())
			Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'sociallogin`');
		parent::uninstall();

		if (_PS_VERSION_ >= 1.6)
		{
			// Remove controller files.
			$files = loginRadiusGetFilesToInstall();

			foreach ($files as $file_name => $file_data)
			{
				if (is_array($file_data) && !empty ($file_data['source']) && !empty ($file_data['target']))
				{
					if (file_exists($file_data['target'].$file_name))
						@unlink($file_data['target'].$file_name);
				}
			}
		}
		return true;
	}
}
