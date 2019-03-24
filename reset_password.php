<?php


include("inc/include.php");

$request_txt_array = array('remail','token','csrftoken','new_password','new_password_repeat');
foreach($request_txt_array as $request_value)
{
	if(isset($_REQUEST[$request_value])){$$request_value = cleanit($_REQUEST[$request_value]);}else{$$request_value = '';}
}

if($remail!= "" && $token!= "")
{
	if(verify_valid_email($remail))
	{
		$query="SELECT USERID,token,email from users WHERE email='".mysql_real_escape_string($remail)."'";
		$result=$dbconn->execute($query);
		if($result->recordcount()>0)
		{
			if($token == $result->fields['token'])
			{
				STemplate::assign('remail',$remail);
				STemplate::assign('token',$token);
				if($csrftoken == "1")
				{
					if($new_password == ""){$error = $errors['18'];}
					elseif($new_password_repeat == ""){$error = $errors['19'];}
					elseif($new_password == $new_password_repeat)
					{
						$mp = md5($new_password);
						$query="UPDATE users SET password='".mysql_real_escape_string($mp)."', token='' WHERE email='".mysql_real_escape_string($remail)."'";
						$result=$dbconn->execute($query);
						STemplate::assign('remail',"");
						STemplate::assign('token',"");
						header("Location:".$config['baseurl']."/index.php");exit;
					}
					else
					{
						$error = $errors['20'];
					}
				}
			}
		}
		else
		{
			$error = $errors['21'];
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
STemplate::display('reset_password.tpl');
STemplate::display('footer.tpl');
//TEMPLATES END
?>