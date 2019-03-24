<?php


include("inc/include.php");

if(isset($_REQUEST['page'])){$page = intval($_REQUEST['page']);}else{$page = "1";}
$currentpage = $page;
if ($page >=2){$pagingstart = ($page-1)*$config['items_per_page'];}
else{$pagingstart = "0";}
if(isset($_REQUEST['query'])){$q = cleanit($_REQUEST['query']);}else{$q = '';}

if($q != "")
{
	$sterm[] = $q;
	$sterm[0] = str_replace("'", "''", $sterm[0]);
	$sterm[0] = str_replace("  ", "", $sterm[0]);
	$sterm[0] = str_replace("-", "", $sterm[0]);
	$stermsplit = explode(" ",$sterm[0]);
	$stermstr = "";
	if (count($stermsplit)>=1) 
	{
		for($i=0;$i<count($stermsplit);$i++)
		{
			if ($stermsplit[$i] != "" && $stermsplit[$i] != "-" && $stermsplit[$i] != " ")
			{
				$stermstr.="AND (A.story like '%$stermsplit[$i]%' or A.tags like '%$stermsplit[$i]%') ";
			}
		}
	}
	$stermstr .= " ";

	$query1 = "SELECT count(*) as total from posts A, users B where A.active='1' AND A.USERID=B.USERID $stermstr order by A.favclicks desc limit $config[maximum_results]";
	$query2 = "SELECT A.*, B.username from posts A, users B where A.active='1' AND A.USERID=B.USERID $stermstr order by A.favclicks desc limit $pagingstart, $config[items_per_page]";
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
		STemplate::assign('total',$totalposts);
	}
}
else
{
	$error = $errors['47'];
	STemplate::assign('error',$error);
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

$pagetitle = $q." ".$titles['12'];

$_SESSION['location'] = "search?query=".$q."&page=".$currentpage;


//TEMPLATES BEGIN
STemplate::assign('pagetitle',$pagetitle);
STemplate::assign('query',$q);
STemplate::assign('nosectionnav',1);
if($config['AUTOSCROLL'] != "1"){STemplate::assign('footerlinks',1);}
STemplate::display('header.tpl');
STemplate::display('search.tpl');
STemplate::display('footer.tpl');
//TEMPLATES END
?>