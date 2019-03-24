<?php


include("inc/include.php");

$SID = intval($_SESSION['USERID']);
if ($SID != "" && $SID >= 0 && is_numeric($SID))
{
	$query1 = "SELECT count(*) as total from notifications WHERE USERID='".$SID."' AND date_added>'".date('Y-m-d', strtotime(' -6 day'))."' ORDER BY NID";
	$query2 = "SELECT A.*, A.time_added as n_time_added, B.username, C.story, C.favclicks, C.unfavclicks from notifications A JOIN users B ON (A.LASTID=B.USERID) JOIN posts C ON (A.PID=C.PID) WHERE A.date_added>'".date('Y-m-d', strtotime(' -6 day'))."' AND A.USERID='".$SID."' order by A.NID desc";
	$executequery1 = $dbconn->Execute($query1);
	$totalposts = $executequery1->fields['total'];
	if ($totalposts > 0)
	{
		$executequery2 = $dbconn->Execute($query2);
		$results = $executequery2->getrows();
		$notifications = sort_notifications($results);
		STemplate::assign('notifications',$notifications);
	}
	else
	{
		$error = $errors['27'];
		STemplate::assign('error',$error);
	}
	$query3 = "UPDATE notifications SET new='0' WHERE USERID='".$SID."' AND new='1'";
	$executequery3 = $dbconn->Execute($query3);
	$_SESSION['NOTIFICATIONS'] = "0";
	$_SESSION['NTOTAL'] = "0";
}
else
{
	header("Location:".$config['baseurl']."/login");exit;
}

if ($config['topgags'] > 0)
{
	$topgags = load_topgags();
	STemplate::assign('topgags',$topgags);
}	

if($config['rhome'] == 1)
{
	$r = load_rhome();
	STemplate::assign('r',$r);
}

if ($config['channels'] == 1)
{
	$cats = loadallchannels();
	STemplate::assign('allchannels',$cats);

	$c = loadtopchannels($cats);
	STemplate::assign('c',$c);
}

$_SESSION['location'] = "/notifications";

//TEMPLATES BEGIN
STemplate::display('header.tpl');
STemplate::display('notifications.tpl');
STemplate::display('footer.tpl');
//TEMPLATES END
?>