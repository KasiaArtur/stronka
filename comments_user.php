<?php


include("inc/include.php");

$SID = $_SESSION['USERID'];
STemplate::assign('SID',$SID);

if(isset($_REQUEST['page'])){$page = intval($_REQUEST['page']);}else{$page = "1";}
$currentpage = $page;
if ($page >=2){$pagingstart = ($page-1)*$config['items_per_page'];}
else{$pagingstart = "0";}
if(isset($_REQUEST['uname'])){$uname = cleanit($_REQUEST['uname']);}else{$uname = '';}

if($uname != "" && $config['comments_enabled'] == "1")
{
	STemplate::assign('uname',$uname);
	$queryp = "select * from users where username='".mysql_real_escape_string($uname)."' AND status='1'"; 
	$resultsp=$dbconn->execute($queryp);
	if($resultsp->rowcount() > 0)
	{
		$users = $resultsp->getrows();
		STemplate::assign('user',$users[0]);
		$USERID = intval($users[0]['USERID']);
		$totalcomments = intval($users[0]['comments']);
	}
	else
	{
		$USERID = 0;
		$totalcomments = 0;
	}
	
	if($USERID > 0)
	{
		if($totalcomments > 0)
		{
			$query = "SELECT A.*, B.username, C.story from comments A, users B, posts C where A.USERID='".mysql_real_escape_string($USERID)."' AND A.USERID=B.USERID AND A.PID=C.PID order by A.CMID desc limit  $pagingstart, $config[items_per_page]";
			$results=$dbconn->execute($query);
			$comments = $results->getrows();
			STemplate::assign('comments',$comments);
		}
		STemplate::assign('usercomments',1);
		STemplate::assign('pagetitle',$uname);
		$theprevpage=$currentpage-1;
		$thenextpage=$currentpage+1;
		if($currentpage > 1){STemplate::assign('tpp',$theprevpage);}
		$currentposts = $currentpage * $config['items_per_page'];
		if($totalcomments > $currentposts){STemplate::assign('tnp',$thenextpage);}
		$template = 'comments_user.tpl';
	}
	else
	{
		$error = $errors['25'];
		$template = 'empty.tpl';
		STemplate::assign('norightside',1);
		STemplate::assign('footerlinks',1);
	}
}
else
{
	$error = $errors['26'];
	$template = 'empty.tpl';
	STemplate::assign('norightside',1);
	STemplate::assign('footerlinks',1);
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

$_SESSION['location'] = "/user/".$uname."/comments?&page=".$page;

//TEMPLATES BEGIN
STemplate::assign('menu',9);
STemplate::assign('nosectionnav',1);
if(isset($error)){STemplate::assign('error',$error);}
STemplate::display('header.tpl');
STemplate::display($template);
STemplate::display('footer.tpl');
//TEMPLATES END
?>