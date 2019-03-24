<?php


$mobile = "1";
include("inc/include.php");

if(isset($_SESSION['location'])){$redirect = $_SESSION['location'];}else{$redirect = "";}

$SID = intval($_SESSION['USERID']);
if ($SID != "" && $SID >= 0 && is_numeric($SID))
{
	if($redirect != ""){header("Location:".$config['baseurl'].$redirect);exit;}
	else{header("Location:".$config['baseurl']);exit;}
}

if(isset($_REQUEST['login_sec'])){$login_sec = cleanit($_REQUEST['login_sec']);}else{$login_sec = "";}
if($login_sec == "1")
{
	if($config['captcha_enabled'] == "1")
	{
		if($config['captcha_default'] == "1" && $config['googlecaptcha_enabled'] == "1")
		{
			$captcha_verified_array = verify_captcha();
			$captcha_verified = intval($captcha_verified_array['captcha_verified']);
			if($captcha_verified_array['error'] != "")
			{
				$error = $captcha_verified_array['error'];
			}
		}
		if($config['captcha_default'] == "2" && $config['ayahcaptcha_enabled'] == "1")
		{
			$captcha_verified = 1;
		}
	}
	if($config['captcha_enabled'] == "0" || $captcha_verified == 1)
	{
		$request_txt_array = array('username','password');
		foreach($request_txt_array as $request_value)
		{
			if(isset($_REQUEST[$request_value])){$$request_value = cleanit($_REQUEST[$request_value]);}else{$$request_value = '';}
		}
		if($username==""){$error=$errors['11'];}
		elseif($password==""){$error=$errors['12'];}
		else
		{
			$encryptedpassword = md5($password);
			if(!verify_valid_email($username))
			{				
				$query1="SELECT * from users WHERE username='".mysql_real_escape_string($username)."' and password='".mysql_real_escape_string($encryptedpassword)."'";
				$query2="update users set lastlogin='".time()."', lip='".$_SERVER['REMOTE_ADDR']."' WHERE username='".mysql_real_escape_string($username)."'";
			}
			else
			{
				$query1="SELECT * from users WHERE email='".mysql_real_escape_string($username)."' and password='".mysql_real_escape_string($encryptedpassword)."'";
				$query2="update users set lastlogin='".time()."', lip='".$_SERVER['REMOTE_ADDR']."' WHERE email='".mysql_real_escape_string($username)."'";
			}
		
			$executequery1=$dbconn->execute($query1);
			if($executequery1->recordcount()<1){$error=$errors['13'];}
			elseif($executequery1->fields['status']=="0"){$error = $errors['14'];}
		
			if(!isset($error))
			{
				$executequery2 =$dbconn->execute($query2);
				$result = $executequery1->getrows();
				prepare_session($result[0]);	
				if(isset($_REQUEST["rememberme"])){create_slrememberme();}
					
				if($redirect == "")
				{
					if ( $config['regredirect'] == 1){header("Location:".$config['baseurl']."/index.php");exit;}
					else{header("Location:".$config['baseurl']."/settings");exit;}
				}
				else{header("Location:".$config['baseurl'].$redirect);exit;}
			}
		}
	}
	else
	{
		$error= $errors['4'];
	}
}

if(isset($error))
{
	STemplate::assign('username',$username);
}

if ($config['channels'] == 1)
{
	$cats = loadallchannels();
	$c = loadtopchannels($cats);
	STemplate::assign('allchannels',$cats);
	STemplate::assign('c',$c);
}

//TEMPLATES BEGIN
STemplate::assign('pagetitle',$titles['6']);
if(isset($error)){STemplate::assign('error',$error);}
STemplate::assign('nosectionnav',1);
STemplate::assign('norightside',1);
STemplate::assign('footerlinks',1);
STemplate::assign('no_login_popup',1);
STemplate::display('header.tpl');
STemplate::display('login.tpl');
STemplate::display('footer.tpl');
//TEMPLATES END
?>