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

<!-- Block Popup -->
<script>
    function pushStates(val) {
        $.ajax({
            type: "GET",
            datatype: "json",
            url: "{$base_dir|escape:'htmlall':'UTF-8'}modules/sociallogin/includes/sociallogin_get_states.php",
            data: "id_country=" + val,
            success: function (data) {
                if (data == '') {
                    document.getElementById("location-state-div").style.display = "none";
                }
                else {
                    data = JSON.parse(data);
                }
                if (data.states && data.states !== "null" && data.states !== "undefined" && Object.keys(data.states).length > 0) {
                    document.getElementById("location-state-div").style.display = "block";
                    var str = "<span class=spantxt>State:</span><select class=inputtxt name=location-state id=location-state>";
                    for (var i in data.states) {
                        str += "<option value=" + i + ">" + data.states[i] + "</option>";
                    }
                    str += "</select>";
                    $("#location-state-div").html(str);
                }
                else {
                    document.getElementById("location-state-div").style.display = "none";
                }
            }
        });
    }
</script>
<div id="fade" class="LoginRadius_overlay">
    <div id="popupouter" style="top:{if $count_profile_data}{(36 - $count_profile_data)}{else}36{/if}%">
        <div id="textmatter"><b>
                <p {if ($error_message.status == "error")} style="margin-bottom: -6px;color:red;" {/if}>
                    {$error_message.message|escape:'html':'UTF-8'|replace:'|error|':'<p style="font-size: 10px;margin-bottom: -10px;color: red;">'}</p>
            </b></div>
        <div id="popupinner">

            <form method="post" name="validfrm" id="validfrm" action="" onsubmit="return popupvalidation();">
                {foreach $profile_data as $profiledata }
                    <div>

                        {if ($profiledata.name == "location_country")}
                            <span class="spantxt">{l s="{$profiledata.text}" mod='sociallogin'}</span>
                            <select onchange="pushStates(this.value)" id="{$profiledata.name|escape:'htmlall':'UTF-8'}"
                                    name="{$profiledata.name|escape:'htmlall':'UTF-8'}"
                                    class="inputtxt">
                                <option value="0">None</option>
                                {foreach $profiledata.value as $country}
                                    <option value="{$country.iso_code|escape:'htmlall':'UTF-8'}"
                                            {if isset($smarty.post[$profiledata.name]) && ($smarty.post[$profiledata.name] == $country.iso_code)}selected='selected'{/if} >{$country.name}</option>
                                {/foreach}
                            </select>
                        {elseif $profiledata.name == 'location-state'}
                            {if !is_array($profiledata.value)}
                                <div id="location-state-div" style="display:none;">
                                    <input id={$profiledata.name|escape:'htmlall':'UTF-8'} type="text"
                                           name={$profiledata.name|escape:'htmlall':'UTF-8'} value="empty"/>
                                </div>
                            {else}
                                <div id="location-state-div">
                                    <span class="spantxt">{l s="{$profiledata.text}" mod='sociallogin'}</span>
                                    <select id={$profiledata.name|escape:'htmlall':'UTF-8'} name={$profiledata.name}
                                            class="inputtxt">
                                        {foreach $profiledata.value as $state}
                                            <option value="{$state.iso_code|escape:'htmlall':'UTF-8'}"
                                                    {if isset($smarty.post[$profiledata.name]) && ($smarty.post[$profiledata.name] == $state.iso_code)}selected='selected'{/if}>{$state.name}</option>
                                        {/foreach}
                                    </select></div>
                            {/if}
                        {else}
                            <span class="spantxt">{l s="{$profiledata.text}" mod='sociallogin'}</span>
                            <input type="text" name="{$profiledata.name|escape:'htmlall':'UTF-8'}"
                                   id="{$profiledata.name|escape:'htmlall':'UTF-8'}"
                                   placeholder="{$profiledata.text|escape:'htmlall':'UTF-8'}"
                                   value="{if isset($smarty.post[$profiledata.name])}{$smarty.post[$profiledata.name]}{else}{$profiledata.value}{/if}"
                                   class="inputtxt"/>
                        {/if}
                    </div>
                {/foreach}
        </div>
        <div class="footerbox">
            <input type="hidden" name="hidden_val" value="{$cookie->sl_hidden|escape:'htmlall':'UTF-8'}"/>
            <input type="submit" id="LoginRadius" name="LoginRadius"
                   value="{l s='Submit' mod='sociallogin'}" class="inputbutton">
            <input type="button" value="{l s='Cancel' mod='sociallogin'}"
                   class="inputbutton" onclick="window.location.href=window.location;"/>
        </div>
    </div>
    </form>
</div>
</div>

<!-- /Block Popup -->


