<?php


include("inc/include.php");

if(isset($_REQUEST['page'])){$page = intval($_REQUEST['page']);}else{$page = "1";}
$currentpage = $page;
if ($page >=2){$pagingstart = ($page-1)*$config['items_per_page'];}
else{$pagingstart = "0";}

if(isset($_REQUEST['json'])){$json = intval($_REQUEST['json']);}else{$json = '';}
if(isset($_REQUEST['section'])){$section = cleanit($_REQUEST['section']);}else{$section = '';}

if($json == "")
{
	if($section == "hot")
	{
		header("Location:".$config['baseurl']."/hot?page=".$currentpage);exit;
	}
	elseif($section == "trending")
	{
		header("Location:".$config['baseurl']."/trending?page=".$currentpage);exit;
	}
	elseif($section == "vote")
	{
		header("Location:".$config['baseurl']."/vote?page=".$currentpage);exit;
	}
	elseif($section == "channels")
	{
		$cname2 = cleanit($_REQUEST['cname']);
		header("Location:".$config['baseurl']."/channels/".$cname2."/?page=".$currentpage);exit;
	}
	elseif($section == "topposts")
	{
		$period = $_REQUEST['period'];
		header("Location:".$config['baseurl']."/topposts?period=".$period."&page=".$currentpage);exit;
	}
	elseif($section == "search")
	{
		$q = cleanit($_REQUEST['query']);
		header("Location:".$config['baseurl']."/search?query=".$q."&page=".$currentpage);exit;
	}
	elseif($section == "userpage")
	{
		$uname = cleanit($_REQUEST['uname']);
		header("Location:".$config['baseurl']."/user/".$uname."?page=".$currentpage);exit;
	}
	elseif($section == "userlikes")
	{
		$uname = cleanit($_REQUEST['uname']);
		header("Location:".$config['baseurl']."/user/".$uname."/likes?page=".$currentpage);exit;
	}
	elseif($section == "user_timeline")
	{
		$uname = cleanit($_REQUEST['uname']);
		header("Location:".$config['baseurl']."/user/".$uname."/timeline?page=".$currentpage);exit;
	}
	elseif($section == "user_bookmarks")
	{
		$uname = cleanit($_REQUEST['uname']);
		header("Location:".$config['baseurl']."/user/".$uname."/bookmarks?page=".$currentpage);exit;
	}
	elseif($section == "gifs")
	{
		header("Location:".$config['baseurl']."/gifs?page=".$currentpage);exit;
	}
	elseif($section == "videos")
	{
		header("Location:".$config['baseurl']."/videos?page=".$currentpage);exit;
	}
}

if($section == "hot")
{
	if ($config['trendingenabled'] == 0){
	$query1 = "SELECT count(*) as total from posts A, users B where A.active='1' AND A.USERID=B.USERID AND A.phase>'0' order by A.phase_time desc limit ".$config['maximum_results'];
	$query2 = "SELECT A.*, B.username from posts A, users B where A.active='1' AND A.USERID=B.USERID AND A.phase>'0' order by A.phase_time desc limit $pagingstart, ".$config['items_per_page'];
	}
	else
	{
	$query1 = "SELECT count(*) as total from posts A, users B where A.active='1' AND A.USERID=B.USERID AND A.phase>'1' order by A.phase_time desc limit ".$config['maximum_results'];
	$query2 = "SELECT A.*, B.username from posts A, users B where A.active='1' AND A.USERID=B.USERID AND A.phase>'1' order by A.phase_time desc limit $pagingstart, ".$config['items_per_page'];
	}
	$_SESSION['location'] = "/hot?page=".$currentpage;
}
elseif($section == "trending")
{
	$query1 = "SELECT count(*) as total from posts A, users B where A.active='1' AND A.USERID=B.USERID AND A.phase='1' order by A.phase_time desc limit ".$config['maximum_results'];
	$query2 = "SELECT A.*, B.username from posts A, users B where A.active='1' AND A.USERID=B.USERID AND A.phase='1' order by A.phase_time desc limit $pagingstart, ".$config['items_per_page'];
	$_SESSION['location'] = "/trending?page=".$currentpage;
}
elseif($section == "vote")
{
	$query1 = "SELECT count(*) as total from posts A, users B where A.active='1' AND A.USERID=B.USERID AND A.phase='0' order by A.phase_time desc limit ".$config['maximum_results'];
	$query2 = "SELECT A.*, B.username from posts A, users B where A.active='1' AND A.USERID=B.USERID AND A.phase='0' order by A.phase_time desc limit $pagingstart, ".$config['items_per_page'];
	$_SESSION['location'] = "/vote?page=".$currentpage;
}
elseif($section == "channels")
{
	$cname2 = cleanit($_REQUEST['cname']);
	$query1 = "select * from channels"; 
	$results1=$dbconn->execute($query1);
	$cnames = $results1->getrows();
	for ($i = 0; $i < count($cnames); $i++) {
		if ( makeseo($cnames[$i]["cname"]) == $cname2)
		{
			$CID = $cnames[$i]["CID"];
			$cname = $cnames[$i]["cname"];
			STemplate::assign('CID',$CID);
			STemplate::assign('cname',$cname);
		}
	}

	$query1 = "SELECT count(*) as total from posts A, users B, channels_posts C where A.active='1' AND A.USERID=B.USERID AND A.PID=C.PID AND C.CID=$CID order by C.PID desc limit $config[maximum_results]";
	$query2 = "SELECT A.*, B.username from posts A, users B, channels_posts C where A.active='1' AND A.USERID=B.USERID AND A.PID=C.PID AND C.CID=$CID order by C.PID desc limit $pagingstart, $config[items_per_page]";
	$_SESSION['location'] = "/channels/".$cname2."/?page=".$currentpage;
}
elseif($section == "topposts")
{
	if( $_REQUEST['period'] == "day")
	{
		$utime = time() - (24 * 60 * 60);
		$addthis = "AND A.time_added>=".$utime;
		$period = "day";
	}
	elseif( $_REQUEST['period'] == "week")
	{
		$utime = time() - (7 * 24 * 60 * 60);
		$addthis = "AND A.time_added>=".$utime;
		$period = "week";
	}
	elseif( $_REQUEST['period'] == "month")
	{
		$utime = time() - (30 * 24 * 60 * 60);
		$addthis = "AND A.time_added>=".$utime;
		$period = "month";
	}
	elseif( $_REQUEST['period'] == "all")
	{
		$addthis = "";
		$period = "all";
	}
	else
	{
		$utime = time() - (24 * 60 * 60);
		$addthis = "AND A.time_added>=".$utime;
		$period = "day";
	}
	$query1 = "SELECT count(*) as total from posts A, users B where A.active='1' AND A.USERID=B.USERID $addthis order by A.favclicks desc limit ".$config['maximum_results'];
	$query2 = "SELECT A.*, B.username from posts A, users B where A.active='1' AND A.USERID=B.USERID $addthis order by A.favclicks desc limit $pagingstart, ".$config['items_per_page'];
	$_SESSION['location'] = "/topposts/?period=".$period."&page=".$currentpage;
}
elseif($section == "search")
{
	$q = cleanit($_REQUEST['query']);
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
	}

	$query1 = "SELECT count(*) as total from posts A, users B where A.active='1' AND A.USERID=B.USERID $stermstr order by A.favclicks desc limit ".$config['maximum_results'];
	$query2 = "SELECT A.*, B.username from posts A, users B where A.active='1' AND A.USERID=B.USERID $stermstr order by A.favclicks desc limit $pagingstart, ".$config['items_per_page'];
}
elseif($section == "userpage")
{
	$uname = cleanit($_REQUEST['uname']);
	if($uname != "")
	{
		STemplate::assign('uname',$uname);
		$queryp = "select * from users where username='".mysql_real_escape_string($uname)."' AND status='1'"; 
		$resultsp=$dbconn->execute($queryp);
		$users = $resultsp->getrows();
		STemplate::assign('user',$users[0]);
		$USERID = intval($users[0]['USERID']);

		if($USERID > 0)
		{
			$query1 = "SELECT count(*) as total from posts A, users B where A.active='1' AND A.USERID=B.USERID AND A.USERID='".mysql_real_escape_string($USERID)."' order by A.PID desc limit ".$config['maximum_results'];
			$query2 = "SELECT A.*, B.username from posts A, users B where A.active='1' AND A.USERID=B.USERID AND A.USERID='".mysql_real_escape_string($USERID)."' order by A.PID desc limit  $pagingstart, ".$config['items_per_page'];
		}
	}
	$_SESSION['location'] = "/user/".$uname."?page=".$currentpage;
}
elseif($section == "userlikes")
{
	$uname = cleanit($_REQUEST['uname']);
	if($uname != "")
	{
		STemplate::assign('uname',$uname);
		$queryp = "select * from users where username='".mysql_real_escape_string($uname)."' AND status='1'"; 
		$resultsp=$dbconn->execute($queryp);
		$users = $resultsp->getrows();
		STemplate::assign('user',$users[0]);
		$USERID = intval($users[0]['USERID']);

		if($USERID > 0)
		{
			$query1 = "SELECT count(*) as total from posts A, users B, posts_favorited C where A.active='1' AND A.USERID=B.USERID AND C.USERID='".mysql_real_escape_string($USERID)."' AND C.PID=A.PID order by C.FID desc limit ".$config['maximum_results'];
			$query2 = "SELECT A.*, B.username from posts A, users B, posts_favorited C where A.active='1' AND A.USERID=B.USERID AND C.USERID='".mysql_real_escape_string($USERID)."' AND C.PID=A.PID order by C.FID desc limit  $pagingstart, ".$config['items_per_page'];
		}
	}
	$_SESSION['location'] = "/user/".$uname."/likes?page=".$currentpage;
}
elseif($section == "user_timeline")
{
	$uname = cleanit($_REQUEST['uname']);
	if($uname != "")
	{
		STemplate::assign('uname',$uname);
		$queryp = "select * from users where username='".mysql_real_escape_string($uname)."' AND status='1'"; 
		$resultsp=$dbconn->execute($queryp);
		$users = $resultsp->getrows();
		STemplate::assign('user',$users[0]);
		$USERID = intval($users[0]['USERID']);

		if($USERID > 0)
		{
			$query1 = "SELECT count(*) as total from posts A, users B, users_timeline C where A.active='1' AND A.PID=C.PID AND A.USERID=B.USERID AND C.USERID='".mysql_real_escape_string($USERID)."' AND C.action_type NOT LIKE '%3%' order by C.time_added desc limit ".$config['maximum_results'];
			$query2 = "SELECT A.*, B.username, C.* from posts A, users B, users_timeline C where A.active='1' AND A.PID=C.PID AND A.USERID=B.USERID AND C.USERID='".mysql_real_escape_string($USERID)."' AND C.action_type NOT LIKE '%3%' order by C.time_added desc limit  $pagingstart, $config[items_per_page]";
		}
	}
	$_SESSION['location'] = "/user/".$uname."/timeline?page=".$currentpage;
}
elseif($section == "user_bookmarks")
{
	$uname = cleanit($_REQUEST['uname']);
	if($uname != "")
	{
		STemplate::assign('uname',$uname);
		$queryp = "select * from users where username='".mysql_real_escape_string($uname)."' AND status='1'"; 
		$resultsp=$dbconn->execute($queryp);
		$users = $resultsp->getrows();
		STemplate::assign('user',$users[0]);
		$USERID = intval($users[0]['USERID']);

		if($USERID > 0)
		{
			$query1 = "SELECT count(*) as total from posts A, users B, posts_bookmarks C where A.active='1' AND A.USERID=B.USERID AND C.USERID='".mysql_real_escape_string($USERID)."' AND C.PID=A.PID order by C.BID desc limit ".$config['maximum_results'];
			$query2 = "SELECT A.*, B.username from posts A, users B, posts_bookmarks C where A.active='1' AND A.USERID=B.USERID AND C.USERID='".mysql_real_escape_string($USERID)."' AND C.PID=A.PID order by C.BID desc limit  $pagingstart, $config[items_per_page]";
		}
	}
	$_SESSION['location'] = "/user/".$uname."/bookmarks?page=".$currentpage;
}
elseif($section == "gifs")
{
	$query1 = "SELECT count(*) as total from posts A, users B where A.active='1' AND A.USERID=B.USERID AND A.gif='1' order by A.PID desc limit ".$config['maximum_results'];
	$query2 = "SELECT A.*, B.username from posts A, users B where A.active='1' AND A.USERID=B.USERID AND A.gif='1' order by A.PID desc limit $pagingstart, ".$config['items_per_page'];
	$_SESSION['location'] = "/gifs?page=".$currentpage;
}
elseif($section == "videos")
{
	$query1 = "SELECT count(*) as total from posts A, users B where A.active='1' AND A.USERID=B.USERID AND ( A.youtube_key!='' OR A.fod_key!='' OR A.vfy_key!='' OR A.vmo_key!='' OR A.vine_key!='' OR A.fbv_key!='' ) order by A.PID desc limit ".$config['maximum_results'];
	$query2 = "SELECT A.*, B.username from posts A, users B where A.active='1' AND A.USERID=B.USERID AND ( A.youtube_key!='' OR A.fod_key!='' OR A.vfy_key!='' OR A.vmo_key!='' OR A.vine_key!='' OR A.fbv_key!='' ) order by A.PID desc limit $pagingstart, ".$config['items_per_page'];
	$_SESSION['location'] = "/videos?page=".$currentpage;
}

if(isset($query1))
{
	$executequery1 = $dbconn->Execute($query1);
	$totalposts = $executequery1->fields['total'];
}
else
{
	$totalposts = 0;
}
if ($totalposts > 0)
{
	$executequery2 = $dbconn->Execute($query2);
	$posts = $executequery2->getrows();
	$posts = gif_detector($posts);
	if($section == "user_timeline")
	{
		STemplate::assign('menu', 'user_timeline');
		$posts = prepare_user_timeline($posts);
	}
	STemplate::assign('page',$page);
	STemplate::assign('json',1);
	$theprevpage=$currentpage-1;
	$thenextpage=$currentpage+1;
	if($currentpage > 1){STemplate::assign('tpp',$theprevpage);}
	$currentposts = $currentpage * $config['items_per_page'];
	if($totalposts > $currentposts)
	{
		$loadmoreurl = $config['baseurl']."/json.php?section=".$section."&page=".$thenextpage;
		if($section == "channels"){$loadmoreurl = $loadmoreurl."&cname=".$cname2;}
		elseif($section == "topposts"){$loadmoreurl = $loadmoreurl."&period=".$period;}
		elseif($section == "search"){$loadmoreurl = $loadmoreurl."&query=".$q;}
		elseif($section == "userpage"){$loadmoreurl = $loadmoreurl."&uname=".$uname;}
		elseif($section == "userlikes"){$loadmoreurl = $loadmoreurl."&uname=".$uname;}
		elseif($section == "user_timeline"){$loadmoreurl = $loadmoreurl."&uname=".$uname;}
		elseif($section == "user_bookmarks"){$loadmoreurl = $loadmoreurl."&uname=".$uname;}
	}
	else
	{
		$loadmoreurl = "";
	}
	foreach($posts as $post)
	{
		$temposts[0] = $post;
		STemplate::assign('posts',$temposts);
		$items[$post['PID']]= STemplate::fetch('posts_bit.tpl');
		$ids[]= $post['PID'];
	}
	$b9gcs_footer =	STemplate::fetch('js_vote.tpl')."<script type=\"text/javascript\">FB.XFBML.parse();</script>";
	
	$response = array( "okay" => true,
	"items" => $items,
	"ids" => $ids,
	"b9gcs_header" => "",
	"b9gcs_footer" => $b9gcs_footer,
	"loadMoreUrl" => $loadmoreurl,
	"content_provider" => $config['baseurl']
	);
}
else
{
	$response = array( "okay" => false,
	"items" => "",
	"ids" => "",
	"b9gcs_header" => "",
	"b9gcs_footer" => "",
	"loadMoreUrl" => "",
	"content_provider" => ""
	);
}
echo json_encode($response);
?>