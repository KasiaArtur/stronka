<?php


$lskip = "1";
include("inc/include.php");
$SID = intval($_SESSION['USERID']);
if ($SID != "" && $SID >= 0 && is_numeric($SID))
{
	$username = $_SESSION['USERNAME'];
	if($username == "")
	{
		if(isset($_REQUEST['connect_sec'])){$connect_sec = cleanit($_REQUEST['connect_sec']);}else{$connect_sec = "";}
		if($connect_sec == "1")
		{	
			$request_txt_array = array('user_username','password');
			foreach($request_txt_array as $request_value)
			{
				if(isset($_REQUEST[$request_value])){$$request_value = cleanit($_REQUEST[$request_value]);}else{$$request_value = '';}
			}
			if(!isset($user_username))
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
			elseif($password == "")
			{
				$error = $errors['10'];	
			}
				
			if(!isset($error))
			{
				$pw = md5($password);
				$query="UPDATE users SET username='".mysql_real_escape_string($user_username)."', password='".mysql_real_escape_string($pw)."' WHERE USERID='".mysql_real_escape_string($SID)."' AND username=''";
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
	else
	{
		header("Location:".$config['baseurl']."/settings");exit;
	}
}
else
{
	header("Location:".$config['baseurl']."/login");exit;
}

if(isset($error))
{
	STemplate::assign('user_username',$user_username);
}

if ($config['channels'] == 1)
{
	$cats = loadallchannels();
	$c = loadtopchannels($cats);
	STemplate::assign('allchannels',$cats);
	STemplate::assign('c',$c);
}

//TEMPLATES BEGIN
if(isset($error)){STemplate::assign('error',$error);}
STemplate::assign('pagetitle',$titles['1']);
STemplate::assign('nosectionnav',1);
STemplate::assign('norightside',1);
STemplate::assign('footerlinks',1);
STemplate::display('header.tpl');
STemplate::display('connect.tpl');
STemplate::display('footer.tpl');
//TEMPLATES END
?>