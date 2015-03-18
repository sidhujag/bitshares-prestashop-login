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

class SocialloginAccountModuleFrontController extends ModuleFrontController
{
	public $ssl = true;

	/**
	 * @see FrontController::init()
	 */
	public function init()
	{
		parent::init();

		require_once($this->module->getLocalPath().'sociallogin.php');
		require_once($this->module->getLocalPath().'includes/sociallogin_functions.php');
	}

	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		parent::initContent();
		if (!Context::getContext()->customer->isLogged())
			Tools::redirect('index.php?controller=authentication&redirect=module&module=sociallogin&action=account');
		if (Context::getContext()->customer->id)
		{
			if (isset($this->context->cookie->lrmessage) && $this->context->cookie->lrmessage != '')
			{
				$this->context->smarty->assign('socialloginlrmessage', $this->context->cookie->lrmessage);
				$this->context->cookie->lrmessage = '';
			}
			else
				$this->context->smarty->assign('socialloginlrmessage', '');
			$this->context->smarty->assign('sociallogin', SocialLogin::loginRadiusJsInterface());
			$this->setTemplate('sociallogin-account.tpl');
		}
	}
}