<?php


$lskip = "1";
include("inc/include.php");

$SID = intval($_SESSION['USERID']);
if($SID > 0)
{
	$request_txt_array = array('select_username_sec','user_username','vcode');
	foreach($request_txt_array as $request_value)
	{
		if(isset($_REQUEST[$request_value])){$$request_value = cleanit($_REQUEST[$request_value]);}else{$$request_value = '';}
	}
	if($select_username_sec == "1")
	{
		if($user_username == "")
		{
			$error = $errors['5'];	
		}
		elseif(strlen($user_username) < 2)
		{
			$error = $errors['6'];	
		}
		elseif(!preg_match("/^[a-zA-Z0-9]*$/i",$user_username))
		{
			$error = $errors['7'];
		}
		elseif(!verify_email_username($user_username))
		{
			$error = $errors['8'];
		}
		
		if(!isset($error))
		{
			$query="UPDATE users SET username='".mysql_real_escape_string($user_username)."' WHERE USERID='".mysql_real_escape_string($SID)."'";
			$result=$dbconn->execute($query);
			$_SESSION['USERNAME']=$user_username;
			if(isset($_SESSION['location'])){$redirect = $_SESSION['location'];}else{$redirect = "";}
			$nexturl = $config['baseurl'].$redirect;
			$message = $lang['252'];
			STemplate::assign('nexturl',$nexturl);
			STemplate::assign('message',$message);
		}
	}
}

if(isset($error))
{
	STemplate::assign('user_username',$user_username);
	STemplate::assign('vcode',$vcode);
}

//TEMPLATES BEGIN
if(isset($error)){STemplate::assign('error',$error);}
STemplate::assign('nosectionnav',1);
STemplate::assign('norightside',1);
STemplate::assign('footerlinks',1);
STemplate::display('header.tpl');
STemplate::display('email_signup_username.tpl');
STemplate::display('footer.tpl');
//TEMPLATES END
?>