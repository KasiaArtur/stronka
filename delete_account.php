<?php


include("inc/include.php");

$SID = intval($_SESSION['USERID']);
if ($SID != "" && $SID >= 0 && is_numeric($SID))
{
	if(isset($_POST['sec_delete_account'])){$sec_delete_account = intval(cleanit($_POST['sec_delete_account']));}else{$sec_delete_account = '';}
	if($sec_delete_account == $SID)
	{
		if(isset($_REQUEST['password'])){$password = cleanit($_REQUEST['password']);}else{$password = '';}
		$password = cleanit($_REQUEST['password']);	
		if($password != "")
		{
			$mpassword = md5($password);
			$query = "select USERID from users where USERID='".mysql_real_escape_string($SID)."' AND password='".mysql_real_escape_string($mpassword)."' limit 1"; 
			$executequery = $dbconn->execute($query);
			$USERID = intval($executequery->fields['USERID']);
			if($USERID > 0)
			{
				delete_user($USERID);
				header("Location:".$config['baseurl']."/logout");exit;
			}
			else
			{
				$error = $errors['36'];
			}
		}
		else
		{
			$error = $errors['12'];
		}
	}
	/*else
	{
		$error = $errors['37'];
	}*/
}
else
{
	header("Location:".$config['baseurl']."/login");exit;
}

//TEMPLATES BEGIN
if(isset($error)){STemplate::assign('error',$error);}
STemplate::assign('nosectionnav',1);
STemplate::assign('norightside',1);
STemplate::assign('footerlinks',1);
STemplate::display('header.tpl');
STemplate::display('delete_account.tpl');
STemplate::display('footer.tpl');
//TEMPLATES END
?>