Prestashop Social Login Addon installation instructions:
/****
For any help feel free to contact at hello@loginradius.com

Step by steps screenshots: https://www.loginradius.com/developers/Plugins/Prestashop

***/


1. Download Social Login module for Prestashop v1.5/v1.6 by LoginRadius

2. Go to your PrestaShop’s back end (admin panel) and log in.

3. Now click on Modules tab.

4. Click on Add a module from my computer, then browse Social Login v2.0 zip file and click on Upload this module.

5. Click on Other Modules and it will expand up. Now click on Install button .

6. After that click on Configure.

7. Under Module settings, enter LoginRadius API Key and API Secret that you get from your LoginRadius account. (Please see note below on ‘How to get LoginRadius Key & Secret’). Few ID providers do not provide user's Email ID so select Yes in Email Required if you want users to enter their email ID (you need to have SMTP account to send email at server) or select No if you don't. Social login will auto generate a virtual email ID for those users). Finally, hit Save button.

8. With this you are done with the LoginRadius setup for PrestaShop Add on.

Note: How to get LoginRadius API Key & Secret: Log in to LoginRadius through one of the ID Provider and in ‘My LoginRadius’ section of your account, enter your website information:

a. Enter valid web domain where you want to implement LoginRadius.

b. Select providers which you want your users to log in through.

c.Click "Save and Get Code" to generate API Key & Secret.

== Changelog ==
= 1.2 =

* Trim the API key and Secret.
* Resolve the redirection url Save problem
* AccountMapping(Link to existing account) option added

== Changelog ==
= 2.0 =

* Added attractive admin UI.
* Added social sharing. 
* Added social counter.
* Validation of api/secret key entered through admin
* Added Pinterest and Hybridshare in Social Counter
* Added popup for option field.
* Social Linking option added.

== Changelog ==
= 2.5 =

* Language translation
* Sharing Admin UI / Functionality.
* Resend email verification.
* Update user profile .
* Login functionality improve.

== Changelog ==
= 2.6 =

* Added OPC module compatibility code.

== Changelog ==
= 2.7 =

* Fixed issue of Customer Group.
* Popup post value shown in popup textbox.

== Changelog ==
= 2.8 ==
* Fixed validation issue

== Changelog ==
= 2.9 ==
* Provide compatiblity with PS v1.6
* Changed CMS Links

== Changelog ==
= 3.0 ==
* Improve performance
* Add responsive popup
* Add Html5 SDK

