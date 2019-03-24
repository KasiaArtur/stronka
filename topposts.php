<?php


include("inc/include.php");

if(isset($_REQUEST['page'])){$page = intval($_REQUEST['page']);}else{$page = "1";}
$currentpage = $page;
if ($page >=2){$pagingstart = ($page-1)*$config['items_per_page'];}
else{$pagingstart = "0";}

if(isset($_REQUEST['period'])){$period = cleanit($_REQUEST['period']);}else{$period = "";}

if($period == "day")
{
	$ctime = 24 * 60 * 60;
	$utime = time() - $ctime;
	$addthis = "AND A.time_added>=".$utime;
}
elseif($period == "week")
{
	$ctime = 7 * 24 * 60 * 60;
	$utime = time() - $ctime;
	$addthis = "AND A.time_added>=".$utime;
}
elseif($period == "month")
{
	$ctime = 30 * 24 * 60 * 60;
	$utime = time() - $ctime;
	$addthis = "AND A.time_added>=".$utime;
}
elseif($period == "all")
{
	$ctime = 0;
	$utime = time() - $ctime;
	$addthis = "";
}
else
{
	$ctime = 24 * 60 * 60;
	$utime = time() - $ctime;
	$addthis = "AND A.time_added>=".$utime;
	$period = "day";
}

$query1 = "SELECT count(*) as total from posts A, users B where A.active='1' AND A.USERID=B.USERID $addthis order by A.favclicks desc limit $config[maximum_results]";
$query2 = "SELECT A.*, B.username from posts A, users B where A.active='1' AND A.USERID=B.USERID $addthis order by A.favclicks desc limit $pagingstart, $config[items_per_page]";
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
	STemplate::assign('allchannels',$cats);

	$c = loadtopchannels($cats);
	STemplate::assign('c',$c);
}

$_SESSION['location'] = "/topposts/".$period."?page=".$currentpage;

//TEMPLATES BEGIN
if($config['AUTOSCROLL'] != "1"){STemplate::assign('footerlinks',1);}
STemplate::assign('pagetitle',$titles['16']);
STemplate::assign('period',$period);
STemplate::assign('menu',4);
STemplate::assign('nosectionnav',1);
STemplate::display('header.tpl');
STemplate::display('topposts.tpl');
STemplate::display('footer.tpl');
//TEMPLATES END
?>