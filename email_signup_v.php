<?php


include("inc/include.php");

$request_txt_array = array('verify','vemail','vcode');
foreach($request_txt_array as $request_value)
{
	if(isset($_REQUEST[$request_value])){$$request_value = cleanit($_REQUEST[$request_value]);}else{$$request_value = '';}
}
if($verify == "1" && $vemail!= "" && $vcode!= "")
{
	if(verify_valid_email($vemail))
	{
		$query="SELECT USERID,vcode,email from users WHERE email='".mysql_real_escape_string($vemail)."'";
		$result=$dbconn->execute($query);
		if($result->recordcount()>0)
		{
			if($vcode == $result->fields['vcode'])
			{
				$_SESSION['USERID'] = $result->fields['USERID'];
				$_SESSION['EMAIL'] = $result->fields['email'];
				$_SESSION['VERIFIED'] = '1';
				$query="UPDATE users SET verified='1', vcode='' WHERE email='".mysql_real_escape_string($vemail)."'";
				$result=$dbconn->execute($query);
				// Send E-Mail Begin
				$sendto = $vemail;
				$sendername = $config['site_name'];
				$from = $config['site_email'];
				$subject = $lang['236'];
				$sendmailbody = $lang['237'].$config['site_name'].",<br><br>";
				$sendmailbody .= $lang['238']."<br><br>";
				$sendmailbody .= $lang['233'].",<br>".stripslashes($sendername);
				mailme($sendto,$sendername,$from,$subject,$sendmailbody,$bcc="");
				// Send E-Mail End
				header("Location:".$config['baseurl']."/index.php");exit;
			}
			else
			{
				$error = $errors['48'];
			}
		}
		else
		{
			$error = $errors['9'];
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

//TEMPLATES BEGIN
STemplate::assign('pagetitle',$titles['10']);
if(isset($error)){STemplate::assign('error',$error);}
STemplate::assign('nosectionnav',1);
STemplate::assign('norightside',1);
STemplate::assign('footerlinks',1);
STemplate::display('header.tpl');
STemplate::display('email_signup_v.tpl');
STemplate::display('footer.tpl');
//TEMPLATES END
?>