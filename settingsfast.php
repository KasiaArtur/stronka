<?php


include("inc/include.php");
$SID = intval($_SESSION['USERID']);
if ($SID != "" && $SID >= 0 && is_numeric($SID))
{
	if(isset($_POST['subform'])){$subform = cleanit($_POST['subform']);}else{$subform = "";}
	if($subform == "1")
	{
		$request_int_array = array('filter','auto_animated','auto_expand');
		foreach($request_int_array as $request_value)
		{
			if(isset($_REQUEST[$request_value])){$$request_value = intval(cleanit($_REQUEST[$request_value]));}else{$$request_value = 0;}
		}
	
		$query="UPDATE users SET filter='".mysql_real_escape_string($filter)."', auto_animated='".mysql_real_escape_string($auto_animated)."', auto_expand='".mysql_real_escape_string($auto_expand)."' WHERE USERID='".mysql_real_escape_string($SID)."' AND status='1'";
		$result=$dbconn->execute($query);
		$_SESSION['FILTER'] = $filter;
		$_SESSION['AUTO_ANIMATED'] = $auto_animated;
		$_SESSION['AUTO_EXPAND'] = $auto_expand;
		$redirect = $_SESSION['location'];
		if($redirect == "")
		{
			header("Location:".$config['baseurl']."/");exit;
		}
		else
		{
			header("Location:".$config['baseurl'].$redirect);exit;
		}
	}
	
}
else
{
	header("Location:".$config['baseurl']."/login");exit;
}

?>