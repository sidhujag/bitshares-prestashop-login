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
function popupvalidation(){var e=/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;var t=document.getElementById("validfrm");var n=true;for(var r=0;r<t.elements.length;r++){if(t.elements[r].id=="location_country"){if(t.elements[r].value==0){document.getElementById("textmatter").style.color="#ff0000";t.elements[r].style.borderColor="#ff0000";n=false}}if(t.elements[r].value.trim()=="" || t.elements[r].value.trim()=="0"){document.getElementById("textmatter").style.color="#ff0000";t.elements[r].style.borderColor="#ff0000";n=false}else{document.getElementById("textmatter").style.color="#666666";t.elements[r].style.borderColor="#E5E5E5"}if(t.elements[r].id=="SL_PHONE"){if(isNaN(t.elements[r].value)==true){document.getElementById("textmatter").style.color="#ff0000";t.elements[r].style.borderColor="#ff0000";n=false}}if(t.elements[r].id=="SL_EMAIL"){var i=t.elements[r].value;var s=i.indexOf("@");var o=i.lastIndexOf(".");if(s<1||o<s+2||o+2>=i.length){document.getElementById("textmatter").style.color="#ff0000";t.elements[r].style.borderStyle="solid";t.elements[r].style.borderColor="#ff0000";n=false}else{document.getElementById("textmatter").style.color="#666666";t.elements[r].style.borderColor="#E5E5E5"}}}document.getElementById("textmatter").blur();return n}jQuery(document).ready(function(e){
    jQuery("#SL_LNAME").focusout(function(){document.getElementById("SL_LNAME").style.borderColor="#e5e5e5"});jQuery("#SL_EMAIL").focusout(function(){document.getElementById("SL_EMAIL").style.borderColor="#e5e5e5"});jQuery("#SL_ADDRESS").focusout(function(){document.getElementById("SL_ADDRESS").style.borderColor="#e5e5e5"});jQuery("#SL_ZIP_CODE").focusout(function(){document.getElementById("SL_ZIP_CODE").style.borderColor="#e5e5e5"});jQuery("#SL_CITY").focusout(function(){document.getElementById("SL_CITY").style.borderColor="#e5e5e5"}); jQuery("#location_country").focusout(function(){document.getElementById("location_country").style.borderColor="#e5e5e5"});jQuery("#SL_PHONE").focusout(function(){document.getElementById("SL_PHONE").style.borderColor="#e5e5e5"});jQuery("#SL_ADDRESS_ALIAS").focusout(function(){document.getElementById("SL_ADDRESS_ALIAS").style.borderColor="#e5e5e5"})});