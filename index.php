<?php


include("inc/include.php");


if(isset($_REQUEST['page'])){$page = intval($_REQUEST['page']);}else{$page = "1";}
$currentpage = $page;
if ($page >=2){$pagingstart = ($page-1)*$config['items_per_page'];}
else{$pagingstart = "0";}

if($config['index'] == 2)
{
	$voteforvisitor = $config['voteforvisitor'];
	$SID = intval($_SESSION['USERID']);
	$query1 = "SELECT count(*) as total from posts A, users B where A.active='1' AND A.USERID=B.USERID AND A.phase='0' order by A.phase_time desc limit ".$config['maximum_results'];
	$query2 = "SELECT A.*, B.username from posts A, users B where A.active='1' AND A.USERID=B.USERID AND A.phase='0' order by A.phase_time desc limit $pagingstart, ".$config['items_per_page'];
	$menu = 3;
	$section = "vote";
	$pagetitle = $titles['8'];
	if($SID > 0 || $voteforvisitor == 1){$template = "vote.tpl";}else{$template = "vote2.tpl";}
}
elseif(($config['index'] == 1 && $config['trendingenabled'] == 1) || ($config['index'] == 0 && $config['trendingenabled'] == 0))
{
	if($config['trendingenabled'] == 1)
	{
		$query1 = "SELECT count(*) as total from posts A, users B where A.active='1' AND A.USERID=B.USERID AND A.phase='1' order by A.phase_time desc limit ".$config['maximum_results'];
		$query2 = "SELECT A.*, B.username from posts A, users B where A.active='1' AND A.USERID=B.USERID AND A.phase='1' order by A.phase_time desc limit $pagingstart, ".$config['items_per_page'];
		$menu = 2;
		$section = "trending";
		$template = "trending.tpl";
		$pagetitle = $titles['7'];
	}
	else
	{
		$query1 = "SELECT count(*) as total from posts A, users B where A.active='1' AND A.USERID=B.USERID AND A.phase>'0' order by A.phase_time desc limit ".$config['maximum_results'];
		$query2 = "SELECT A.*, B.username from posts A, users B where A.active='1' AND A.USERID=B.USERID AND A.phase>'0' order by A.phase_time desc limit $pagingstart, ".$config['items_per_page'];
		$menu = 1;
		$section = "hot";
		$template = "index.tpl";
		$pagetitle = $titles['4'];
	}
}
else
{
	$query1 = "SELECT count(*) as total from posts A, users B where A.active='1' AND A.USERID=B.USERID AND A.phase>'1' order by A.phase_time desc limit ".$config['maximum_results'];
	$query2 = "SELECT A.*, B.username from posts A, users B where A.active='1' AND A.USERID=B.USERID AND A.phase>'1' order by A.phase_time desc limit $pagingstart, ".$config['items_per_page'];
	$menu = 1;
	$section = "hot";
	$template = "index.tpl";
	$pagetitle = $titles['4'];
}

$executequery1 = $dbconn->Execute($query1);
$totalposts = $executequery1->fields['total'];
if ($totalposts > 0)
{
	$executequery2 = $dbconn->Execute($query2);
	$posts = $executequery2->getrows();
	$posts = gif_detector($posts);
	STemplate::assign('posts',$posts);
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
	$c = loadtopchannels($cats);
	STemplate::assign('allchannels',$cats);
	STemplate::assign('c',$c);
}

if($config['tag_cloud_front'] == 1)
{
	$tags_cloud = load_cloud_tag();
	STemplate::assign('tags_cloud',$tags_cloud);
}

$_SESSION['location'] = "/".$section."?page=".$currentpage;

//TEMPLATES BEGIN
if($config['AUTOSCROLL'] != "1"){STemplate::assign('footerlinks',1);}
STemplate::assign('menu',$menu);
STemplate::assign('pagetitle',$pagetitle);
STemplate::assign('page',$page);
STemplate::display('header.tpl');
STemplate::display($template);
STemplate::display('footer.tpl');
//TEMPLATES END
?>