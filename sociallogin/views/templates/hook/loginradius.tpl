{*
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
*}

<!-- Block Social Login -->
{if $right}
    <div id="mymodule_block_left" class="block">
        <h4>{l s='Social Login' mod='sociallogin'}</h4>

        <div class="block_content">
            <ul>
                <li>
                    {if $iframe == "error"}
                        <p style='color:red'>Your LoginRadius API Key or secret is not valid, please correct it or
                            contact
                            LoginRadius support at<br/><a href='http://www.LoginRadius.com' target='_blank'>www.loginradius.com</a>
                        </p>
                    {else}
                        {if $sl_title}
                            {$sl_title|escape:'htmlall':'UTF-8'}
                        {/if}
                        <br/>
                        <div class="{$iframe|escape:'htmlall':'UTF-8'}"></div>
                    {/if}
                </li>
            </ul>
        </div>
    </div>
{else}
    <div id="mymodule_block_left"  {$margin_style|escape:'htmlall':'UTF-8'}>
        <div class="block_content">
            <ul>
                {if $iframe == "error"}
                    <p style='color:red'>Your LoginRadius API Key or secret is not valid, please correct it or contact
                        LoginRadius support at<br/><a href='http://www.LoginRadius.com' target='_blank'>www.loginradius.com</a>
                    </p>
                {else}
                    {literal}
                        <script>$(function () {
                                loginradius_interface();
                            });</script>
                    {/literal}
                    <div style="padding-top:5px;">
                        <div class="{$iframe|escape:'htmlall':'UTF-8'}"></div>
                    </div>
                {/if}
            </ul>
        </div>
    </div>
{/if}


<!-- /Block Social Login -->


