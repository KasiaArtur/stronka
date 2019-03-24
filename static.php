<?php


include("inc/include.php");

if(isset($_REQUEST['stid'])){$stid = intval($_REQUEST['stid']);}else{$stid = '';}
if($stid > 0 && $stid <6)
{
	$query="SELECT * FROM static WHERE ID='".mysql_real_escape_string($stid)."'";
	$executequery=$dbconn->execute($query);
	$title = strip_mq_gpc($executequery->fields['title']);
	$content = strip_mq_gpc($executequery->fields['value']);
	STemplate::assign('pagetitle',$title);
	STemplate::assign('title',$title);
	STemplate::assign('content',$content);
	STemplate::assign('stid',$stid);
}
else
{
	$error = $errors['28'];
	STemplate::assign('error',$error);
}

if ($config['topgags'] > 0)
{
	$topgags = load_topgags();
	STemplate::assign('topgags',$topgags);
}	

if ($config['channels'] == 1)
{
$cats = loadallchannels();
STemplate::assign('allchannels',$cats);

$c = loadtopchannels($cats);
STemplate::assign('c',$c);
}

//TEMPLATES BEGIN
STemplate::assign('nosectionnav',1);
STemplate::assign('norightside',1);
STemplate::assign('footerlinks',1);
STemplate::display('header.tpl');
STemplate::display("static.tpl");
STemplate::display('footer.tpl');
//TEMPLATES END
?>