<?php


include("inc/include.php");


$request_txt_array = array('contact_sec','topic','subject','msg','name','email','username','os','imagecode');
foreach($request_txt_array as $request_value)
{
	if(isset($_POST[$request_value])){$$request_value = cleanit($_POST[$request_value]);}else{$$request_value = '';}
}
if($contact_sec == "1")
{
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
		if($topic == ""){$error = $errors['29'];}
		elseif($subject == ""){$error = $errors['30'];}
		elseif($msg == ""){$error = $errors['31'];}
		elseif($name == ""){$error = $errors['32'];}
		elseif($email == ""){$error = $errors['33'];}
		elseif(!verify_valid_email($email)){$error = $errors['34'];}
		elseif($imagecode != "" && $imagecode != $_SESSION['imagecode']){$error = $errors['35'];}
		else
		{
			// Send E-Mail Begin
			$sendto = $config['site_email'];
			$sendername = $config['site_name'];
			$from = $config['site_email'];
			$sub = $lang['245'];
			$sendmailbody = $lang['246'].",<br><br>";
			$sendmailbody .= $lang['149'].": ".$topic." <br><br>";
			$sendmailbody .= $lang['157'].": ".$subject." <br><br>";
			$sendmailbody .= $lang['159'].": ".$msg." <br><br>";
			$sendmailbody .= $lang['80'].": ".$name." <br><br>";
			$sendmailbody .= $lang['77'].": ".$email." <br><br>";
			$sendmailbody .= $lang['164'].": ".$username." <br><br>";
			$sendmailbody .= $lang['247'].": ".$_SERVER['REMOTE_ADDR']." <br><br>";
			$sendmailbody .= $lang['233'].",<br>".stripslashes($sendername);
			mailme($sendto,$sendername,$from,$sub,$sendmailbody,$bcc="");
			// Send E-Mail End
			$message = $lang['248'];
			STemplate::assign('message',$message);
		}
	}
}

if(isset($error))
{
	STemplate::assign('topic',$topic);
	STemplate::assign('subject',$subject);
	STemplate::assign('msg',$msg);
	STemplate::assign('name',$name);
	STemplate::assign('email',$email);
	STemplate::assign('username',$username);
	STemplate::assign('os',$os);
}
if ($config['topgags'] > 0)
{
	$topgags = load_topgags();
	STemplate::assign('topgags',$topgags);
}

if ($config['channels'] == 1)
{
	$cats = loadallchannels();
	$c = loadtopchannels($cats);
	STemplate::assign('allchannels',$cats);
	STemplate::assign('c',$c);
}

$_SESSION['location'] = "/contact";
//TEMPLATES BEGIN
STemplate::assign('pagetitle',$titles['2']);
if(isset($error)){STemplate::assign('error',$error);}
STemplate::assign('nosectionnav',1);
STemplate::assign('norightside',1);
STemplate::assign('footerlinks',1);
STemplate::assign('no_signup_popup',1);
STemplate::display('header.tpl');
STemplate::display('contact.tpl');
STemplate::display('footer.tpl');
//TEMPLATES END
?>