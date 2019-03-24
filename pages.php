<?php


include("inc/include.php");

if(isset($_REQUEST['PAGEID'])){$PAGEID = intval($_REQUEST['PAGEID']);}else{$PAGEID = '';}

$query="SELECT * FROM pages WHERE PAGEID='".mysql_real_escape_string($PAGEID)."' AND page_active='1'";
$executequery=$dbconn->execute($query);
if ($executequery->rowcount() > 0)
{
	$page = $executequery->getrows();
	STemplate::assign('pagetitle',$page[0]['page_name']);
	STemplate::assign('page',$page[0]);
}
else
{
	$error = $errors['61'];
	STemplate::assign('error',$error);
}

if($config['pages_enabled'] != "1" || ($config['pages_header_enabled'] != "1" && $config['pages_footer_enabled'] != "1"))
{
	$pages_query1 = "SELECT count(*) as total FROM pages WHERE page_active='1' AND page_public='1' limit ".$config['maximum_results'];
	$pages_query2 = "SELECT * FROM pages WHERE page_active='1' AND page_public='1' limit 0, ".$config['items_per_page'];
	$pagesexecutequery1 = $dbconn->Execute($pages_query1);
	$totalpages = $pagesexecutequery1->fields['total'];
	if ($totalpages > 0)
	{
		$pagesexecutequery2 = $dbconn->Execute($pages_query2);
		$pages_array = $pagesexecutequery2->getrows();
		STemplate::assign('pages_array', $pages_array);
	}
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
STemplate::assign('menu',6);
STemplate::assign('nosectionnav',1);
STemplate::assign('norightside',1);
STemplate::assign('footerlinks',1);
STemplate::display('header.tpl');
STemplate::display("pages.tpl");
STemplate::display('footer.tpl');
//TEMPLATES END
?>