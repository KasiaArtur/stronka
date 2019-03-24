<?php


include("inc/include.php");

$SID = intval($_SESSION['USERID']);
if ($SID != "" && $SID >= 0 && is_numeric($SID))
{
	$request_txt_array = array('subform','settings');
	foreach($request_txt_array as $request_value)
	{
		if(isset($_POST[$request_value])){$$request_value = cleanit($_POST[$request_value]);}else{$$request_value = '';}
	}
	if($subform == "1" && $settings == "account")
	{
		$request_txt_array = array('user_email','mylang','nsfw');
		foreach($request_txt_array as $request_value)
		{
			if(isset($_REQUEST[$request_value])){$$request_value = cleanit($_REQUEST[$request_value]);}else{$$request_value = '';}
		}
		
		if($user_email == ""){$error = $errors['33'];}
		elseif(!verify_valid_email($user_email)){$error = $errors['34'];}
		else
		{
			$query = "select count(*) as total from users where email='".mysql_real_escape_string($user_email)."' AND USERID!='".mysql_real_escape_string($SID)."' limit 1"; 
			$executequery = $dbconn->execute($query);
			$te = $executequery->fields['total']+0;
			if($te > 0){$error = $errors['38'];}
		}	
		
		if(!isset($error))
		{			
			$query="UPDATE users SET email='".mysql_real_escape_string($user_email)."', mylang='".mysql_real_escape_string($mylang)."', filter='".mysql_real_escape_string($nsfw)."' WHERE USERID='".mysql_real_escape_string($SID)."' AND status='1'";
			$result=$dbconn->execute($query);
			$_SESSION['FILTER'] = $nsfw;
			if($user_email != $_SESSION['EMAIL'])
			{
				$_SESSION['EMAIL'] = $user_email;
				$_SESSION['VERIFIED'] = 0;
				$verifycode = generateCode(5).time();
				$vlink = $config['baseurl']."/email_signup_v.php?vemail=".$user_email."&vcode=".$verifycode."&verify=1";
				$query = "UPDATE users SET verified='0', vcode='".mysql_real_escape_string($verifycode)."' WHERE USERID='".mysql_real_escape_string($SID)."'";
				$dbconn->execute($query);
				
				// Send E-Mail Begin
				$sendto = $user_email;
				$sendername = $config['site_name'];
				$from = $config['site_email'];
				$subject = "Verify your email address.";
				$sendmailbody = "You have changed your email in ".$config['site_name'].",<br><br>";
				$sendmailbody .= "To continue your with verification, please click on the following link to verify your email address : <a href='".$vlink."'>".$vlink."</a><br><br>";
				$sendmailbody .= "Or you can <a href='".$config['baseurl']."/login'>Login</a> and use this code '$verifycode' - without quotations<br><br>";
				$sendmailbody .= "Thanks,<br>".stripslashes($sendername);
				mailme($sendto,$sendername,$from,$subject,$sendmailbody,$bcc="");
				// Send E-Mail End
			}
			$message = $lang['249'];
		}
		$template = "settings.tpl";
	}
	elseif($subform == "1" && $settings == "password")
	{
		$request_txt_array = array('new_password','new_password_repeat');
		foreach($request_txt_array as $request_value)
		{
			if(isset($_REQUEST[$request_value])){$$request_value = cleanit($_REQUEST[$request_value]);}else{$$request_value = '';}
		}
		if($new_password != "" || $new_password_repeat != "")
		{
			if($new_password == ""){$error = $errors['18'];}
			elseif($new_password_repeat == ""){$error = $errors['19'];}
			else
			{
				if($new_password == $new_password_repeat)
				{
					$mp = md5($new_password);
					$query = "UPDATE users SET password='".mysql_real_escape_string($mp)."' WHERE USERID='".mysql_real_escape_string($SID)."' AND status='1'";
					$dbconn->execute($query);
					$message = $lang['249'];
				}
				else{$error = $errors['20'];}
			}
		}
		$template = "settings_password.tpl";
	}
	elseif($subform == "1" && $settings == "profile")
	{
		$request_txt_array = array('fname','gender','country','details','website','news','remove_avatar','remove_cover');
		foreach($request_txt_array as $request_value)
		{
			if(isset($_REQUEST[$request_value])){$$request_value = cleanit($_REQUEST[$request_value]);}else{$$request_value = '';}
		}
			
		$gstop = "1";
		if(isset($_FILES['avatar']['tmp_name'])){$gphoto = $_FILES['avatar']['tmp_name'];}else{$gphoto = "";}
		if($gphoto != "")
		{
			$ext = substr(strrchr($_FILES['avatar']['name'], '.'), 1);
			$ext2 = strtolower($ext);
			if($ext2 == "jpeg" || $ext2 == "jpg" || $ext2 == "gif" || $ext2 == "png")
			{
				$theimageinfo = getimagesize($gphoto);
				if($theimageinfo[2] != 1 && $theimageinfo[2] != 2 && $theimageinfo[2] != 3)
				{
					$gstop = "1";
				}
				else
				{
					$gstop = "0";	
				}
			}
			else{$error = $errors['52'];}
		}
		
		$cstop = "1";
		if(isset($_FILES['cover']['tmp_name'])){$cphoto = $_FILES['cover']['tmp_name'];}else{$cphoto = "";}
		if($cphoto != "")
		{
			$ext = substr(strrchr($_FILES['cover']['name'], '.'), 1);
			$ext2 = strtolower($ext);
			if($ext2 == "jpeg" || $ext2 == "jpg" || $ext2 == "gif" || $ext2 == "png")
			{
				$theimageinfo = getimagesize($cphoto);
				if($theimageinfo[2] != 1 && $theimageinfo[2] != 2 && $theimageinfo[2] != 3)
				{
					$cstop = "1";
				}
				else
				{
					$cstop = "0";	
				}
			}
			else{$error = $errors['53'];}
		}
		if(!isset($error))
		{	
			
			if($remove_avatar == "1")
			{
				$query = "select profilepicture from users where USERID='".mysql_real_escape_string($SID)."' limit 1"; 
				$executequery = $dbconn->execute($query);
				$delpp = $executequery->fields['profilepicture'];
				if($delpp != "")
				{
					$del1=$config['usersavatardir']."/".$delpp;
					if(file_exists($del1))
					{
						unlink($del1);
					}
					$del2=$config['usersavatardir']."/thumbs/".$delpp;
					if(file_exists($del2))
					{
						unlink($del2);
					}
					$del3=$config['usersavatardir']."/o/".$delpp;
					if(file_exists($del3))
					{
						unlink($del3);
					}
					$query = "UPDATE users SET profilepicture='', profilepicture_removed='1' WHERE USERID='".mysql_real_escape_string($SID)."' limit 1";
					$dbconn->execute($query);
				}
			}
			
			if($remove_cover == "1")
			{
				$query = "select coverpicture from users where USERID='".mysql_real_escape_string($SID)."' limit 1"; 
				$executequery = $dbconn->execute($query);
				$delpp = $executequery->fields['coverpicture'];
				if($delpp != "")
				{
					$del1=$config['userscoverdir']."/t/".$delpp;
					if(file_exists($del1))
					{
						unlink($del1);
					}
					$del2=$config['userscoverdir']."/o/".$delpp;
					if(file_exists($del2))
					{
						unlink($del2);
					}
					$query = "UPDATE users SET coverpicture='' WHERE USERID='".mysql_real_escape_string($SID)."' limit 1";
					$dbconn->execute($query);
				}
			}
			$query="UPDATE users SET fullname='".mysql_real_escape_string($fname)."', gender='".mysql_real_escape_string($gender)."', country='".mysql_real_escape_string($country)."', description='".mysql_real_escape_string($details)."', website='".mysql_real_escape_string($website)."', news='".mysql_real_escape_string($news)."' WHERE USERID='".mysql_real_escape_string($SID)."' AND status='1'";
			$result=$dbconn->execute($query);
			$pid = $SID;
			if($gstop == "0")
			{
				$thepp = $pid;
				$theimageinfo = getimagesize($gphoto);
				if($theimageinfo[2] == 1)
				{
					$thepp .= ".gif";
				}
				elseif($theimageinfo[2] == 2)
				{
					$thepp .= ".jpg";
				}
				elseif($theimageinfo[2] == 3)
				{
					$thepp .= ".png";
				}
				$myvideoimgnew=$config['usersavatardir']."/o/".$thepp;
				if(file_exists($myvideoimgnew))
				{
					unlink($myvideoimgnew);
				}
				move_uploaded_file($gphoto, $myvideoimgnew);
				
				$myvideoimgnew2=$config['usersavatardir']."/".$thepp;
				makeThumb( $myvideoimgnew, $myvideoimgnew2 , 200 );
				$myvideoimgnew3=$config['usersavatardir']."/thumbs/".$thepp;
				makeThumb( $myvideoimgnew, $myvideoimgnew3 , 100 );
				if(file_exists($config['usersavatardir']."/o/".$thepp))
				{
					$query = "UPDATE users SET profilepicture='$thepp' WHERE USERID='".mysql_real_escape_string($SID)."'";
					$dbconn->execute($query);
				}
					
			}
			
			if($cstop == "0")
			{
				$thepp = $pid;
				$theimageinfo = getimagesize($cphoto);
				if($theimageinfo[2] == 1)
				{
					$thepp .= ".gif";
				}
				elseif($theimageinfo[2] == 2)
				{
					$thepp .= ".jpg";
				}
				elseif($theimageinfo[2] == 3)
				{
					$thepp .= ".png";
				}
				$myvideoimgnew=$config['userscoverdir']."/o/".$thepp;
				if(file_exists($myvideoimgnew))
				{
					unlink($myvideoimgnew);
				}
				move_uploaded_file($cphoto, $myvideoimgnew);
				
				$myvideoimgnew2=$config['userscoverdir']."/t/".$thepp;
				resize_crop_image(960, 255, $myvideoimgnew, $myvideoimgnew2);
				if(file_exists($config['userscoverdir']."/o/".$thepp))
				{
					$query = "UPDATE users SET coverpicture='$thepp' WHERE USERID='".mysql_real_escape_string($SID)."'";
					$dbconn->execute($query);
				}
					
			}
			$message = $lang['249'];
		}
		$template = "settings_profile.tpl";
	}
	
	$query="SELECT * FROM users WHERE USERID='".mysql_real_escape_string($SID)."' AND status='1'";
	$results=$dbconn->execute($query);
	$p = $results->getrows();
	STemplate::assign('p',$p[0]);
	
	if ($config['channels'] == 1)
	{
	$cats = loadallchannels();
	STemplate::assign('allchannels',$cats);
	}
	if(!isset($template))
	{
		if(isset($_REQUEST['tab'])){$tab = cleanit($_REQUEST['tab']);}else{$tab = "";}
		if($tab == "password"){$template = "settings_password.tpl";}
		elseif($tab == "profile"){$template = "settings_profile.tpl";}
		elseif($tab == "notifications"){$template = "settings_notifications.tpl";}
		else{$template = "settings.tpl";}
	}
}
else
{
	header("Location:".$config['baseurl']."/login");exit;
}

//TEMPLATES BEGIN
STemplate::assign('pagetitle',$titles['15']);
STemplate::assign('nosectionnav',1);
STemplate::assign('norightside',1);
STemplate::assign('footerlinks',1);
if(isset($error)){STemplate::assign('error',$error);}
if(isset($message)){STemplate::assign('message',$message);}
STemplate::display('header.tpl');
STemplate::display($template);
STemplate::display('footer.tpl');
//TEMPLATES END
?>