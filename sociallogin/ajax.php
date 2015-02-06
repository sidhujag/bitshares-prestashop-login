<?php
require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');
require_once(dirname(__FILE__).'/includes/sociallogin_bitsharesloginapi.php');
if(isset($_REQUEST['callback']))
{
	
	$response = '';
	$callbackURL = $_REQUEST['callback'];
	try {
		$btsclient = new Bitshares();
		$btsurl = $btsclient->createAuthUrl($callbackURL);
		if($btsurl === false)
		{
			global $g_authurl_error;
			$response['error']=  $g_authurl_error;
			die(Tools::jsonEncode($response));
		}
		$response['url'] = $btsurl;
	}	
	catch (Exception $e){

			$response['error']=  $e->getMessage();
	}
	die(Tools::jsonEncode($response));
} 
exit;
