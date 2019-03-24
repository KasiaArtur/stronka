<?php


include("inc/include.php");
if($config['staff_page'] == "1")
{
	if($config['admins_unames'] != "")
	{
		$admins_unames_array = explode( ',', $config['admins_unames'] );
		$admins_unames = join("','",$admins_unames_array);
		$query1 = "SELECT * from users where username IN ('".$admins_unames."') order by USERID desc";
		$executequery1 = $dbconn->Execute($query1);
		$admins = $executequery1->getrows();
		STemplate::assign('admins',$admins);
	}
	if($config['mods_unames'] != "")
	{
		$mods_unames_array = explode( ',', $config['mods_unames'] );
		$mods_unames = join("','",$mods_unames_array);
		$query2 = "SELECT * from users where username IN ('".$mods_unames."') order by USERID desc";
		$executequery2 = $dbconn->Execute($query2);
		$mods = $executequery2->getrows();
		STemplate::assign('mods',$mods);
	}
	if($config['dev_unames'] != "")
	{
		$dev_unames_array = explode( ',', $config['dev_unames'] );
		$dev_unames = join("','",$dev_unames_array);
		$query3 = "SELECT * from users where username IN ('".$dev_unames."') order by USERID desc";
		$executequery3 = $dbconn->Execute($query3);
		$devs = $executequery3->getrows();
		STemplate::assign('devs',$devs);
	}
}
else
{
	$error = $errors['50'];
}

if ($config['topgags'] > 0)
{
	$topgags = load_topgags();
	STemplate::assign('topgags',$topgags);
}

if ($config['rhome'] == 1)
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

$_SESSION['location'] = "/staff";

//TEMPLATES BEGIN
STemplate::assign('pagetitle',$titles['14']);
STemplate::assign('nosectionnav',1);
STemplate::display('header.tpl');
STemplate::display('staff.tpl');
STemplate::display('footer.tpl');
//TEMPLATES END
?>