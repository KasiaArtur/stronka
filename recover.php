<?php


include("inc/include.php");

$request_txt_array = array('recover_sec','email');
foreach($request_txt_array as $request_value)
{
	if(isset($_REQUEST[$request_value])){$$request_value = cleanit($_REQUEST[$request_value]);}else{$$request_value = '';}
}
if($recover_sec == "1")
{
	if($email != "")
	{
		if(verify_valid_email($email))
		{
			$query="SELECT * from users WHERE email='".mysql_real_escape_string($email)."'";
			$result=$dbconn->execute($query);
			if($result->recordcount()>0)
			{
				$token = generateCode(5).time();
				$query="UPDATE users SET token='".mysql_real_escape_string($token)."' WHERE email='".mysql_real_escape_string($email)."'";
				$result=$dbconn->execute($query);
				$rlink = 'http:'.$config['baseurl']."/reset_password.php?remail=".$email."&token=".$token;
				// Send E-Mail Begin
				$sendto = $email;
				$sendername = $config['site_name'];
				$from = $config['site_email'];
				$subject = $lang['239'];
				$sendmailbody = $lang['240'].$config['site_name'].$lang['241']."<br><br>";
				$sendmailbody .= $lang['242']." <a href='".$rlink."'>".$rlink."</a><br><br>";
				$sendmailbody .= $lang['243']."<br><br>";
				$sendmailbody .= $lang['233'].",<br>".stripslashes($sendername);
				mailme($sendto,$sendername,$from,$subject,$sendmailbody,$bcc="");
				// Send E-Mail End
				$message= $lang['244'];
				STemplate::assign('message',$message);
			}
			else
			{
				$error = $errors['17'];
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
	STemplate::assign('email',$email);
}

if ($config['channels'] == 1)
{
	$cats = loadallchannels();
	$c = loadtopchannels($cats);
	STemplate::assign('allchannels',$cats);
	STemplate::assign('c',$c);
}

//TEMPLATES BEGIN
STemplate::assign('pagetitle',$titles['10']);
if(isset($error)){STemplate::assign('error',$error);}
STemplate::assign('nosectionnav',1);
STemplate::assign('norightside',1);
STemplate::assign('footerlinks',1);
STemplate::display('header.tpl');
STemplate::display('recover.tpl');
STemplate::display('footer.tpl');
//TEMPLATES END
?>