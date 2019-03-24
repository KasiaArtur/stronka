<?php


include("inc/include.php");

if(isset($_REQUEST['page'])){$page = intval($_REQUEST['page']);}else{$page = "1";}
$currentpage = $page;
if ($page >=2){$pagingstart = ($page-1)*$config['items_per_page'];}
else{$pagingstart = "0";}

$query1 = "SELECT count(*) as total FROM users WHERE verified='1' AND username!='' ORDER BY posts desc limit $config[maximum_results]";
$executequery1 = $dbconn->Execute($query1);
$totalposts = $executequery1->fields['total'];
if ($totalposts > 0)
{
	$queryr = "SELECT * FROM users WHERE verified='1' AND username!='' ORDER BY posts desc limit $pagingstart, $config[items_per_page]";
	$executequeryr = $dbconn->Execute($queryr);
	$users = $executequeryr->getrows();
	$ranks = ($currentpage - 1) * $config['items_per_page'];
	for($i = 0; $i < count($users); $i++){$users[$i]['rank'] = $i + 1 + $ranks;}
	STemplate::assign('users',$users);
	
	$theprevpage=$currentpage-1;
	$thenextpage=$currentpage+1;
	if($currentpage > 1){STemplate::assign('tpp',$theprevpage);}
	$currentposts = $currentpage * $config['items_per_page'];
	if($totalposts > $currentposts){STemplate::assign('tnp',$thenextpage);}
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

$_SESSION['location'] = "/topusers?page=".$currentpage;

//TEMPLATES BEGIN
if($config['AUTOSCROLL'] != "1"){STemplate::assign('footerlinks',1);}
STemplate::assign('pagetitle', $titles['13']);
STemplate::assign('menu',4);
STemplate::display('header.tpl');
STemplate::display('topusers.tpl');
STemplate::display('footer.tpl');
//TEMPLATES END
?>