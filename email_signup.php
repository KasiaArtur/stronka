<?php


include("inc/include.php");

$request_txt_array = array('signup_sec','email','password','fullname');
foreach($request_txt_array as $request_value)
{
	if(isset($_REQUEST[$request_value])){$$request_value = cleanit($_REQUEST[$request_value]);}else{$$request_value = '';}
}
if($config['captcha_enabled'] == "1")
{
	$captcha_verified_array = verify_captcha();
	$captcha_verified = intval($captcha_verified_array['captcha_verified']);
	if($captcha_verified_array['error'] != "")
	{
		$error = $captcha_verified_array['error'];
	}
}
if($config['captcha_enabled'] == "0" || $captcha_verified == 1)
{
	if($signup_sec == "1" && $email!= "" && $password!= "")
	{
		if(verify_valid_email($email))
		{
			$query="SELECT * from users WHERE email='".mysql_real_escape_string($email)."'";
			$result=$dbconn->execute($query);
			if($result->recordcount()<1)
			{
				$vcode = generateCode(5).time();
				$pwd2 = md5($password);
				$query="INSERT INTO users SET email='".mysql_real_escape_string($email)."', password='".mysql_real_escape_string($pwd2)."', fullname='".mysql_real_escape_string($fullname)."', verified='0', vcode='".mysql_real_escape_string($vcode)."', addtime='".time()."', ip='".$_SERVER['REMOTE_ADDR']."', lip='".$_SERVER['REMOTE_ADDR']."'";
				$result=$dbconn->execute($query);
				$vlink = 'http:'.$config['baseurl']."/email_signup_v.php?vemail=".$email."&vcode=".$vcode."&verify=1";
				$UID = mysql_insert_id();
				if($UID > 0)
				{
					// Send E-Mail Begin
					$sendto = $email;
					$sendername = $config['site_name'];
					$from = $config['site_email'];
					$subject = $lang['230'];
					$sendmailbody = $lang['231'].$config['site_name'].",<br><br>";
					$sendmailbody .= $lang['232']."<a href='".$vlink."'>".$vlink."</a><br><br>";
					$sendmailbody .= $lang['233'].",<br>".stripslashes($sendername);
					mailme($sendto,$sendername,$from,$subject,$sendmailbody,$bcc="");
					// Send E-Mail End
					$message= $lang['234'];
					STemplate::assign('message',$message);
				}
			}
			else
			{
				$error = $errors['2']." <a href='".$config['baseurl']."/recover' target='_blank'>".$lang['235']."</a>";
			}
		}
		else
		{
			$error = $errors['3'];
		}
	}
	else
	{
		$error= $errors['4'];
	}
}

if(isset($error))
{
	STemplate::assign('fullname',$fullname);
	STemplate::assign('email',$email);
}

//TEMPLATES BEGIN
STemplate::assign('pagetitle',$titles['10']);
if(isset($error)){STemplate::assign('error',$error);}
STemplate::assign('nosectionnav',1);
STemplate::assign('norightside',1);
STemplate::assign('footerlinks',1);
STemplate::assign('no_signup_popup',1);
STemplate::display('header.tpl');
STemplate::display('email_signup.tpl');
STemplate::display('footer.tpl');
//TEMPLATES END
?>