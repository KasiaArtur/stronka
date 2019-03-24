<?php


include("inc/include.php");

$thesitename = html_entity_decode($config['site_name'],ENT_COMPAT,"UTF-8");
$thebaseurl = "http:".$config['baseurl'];
$thepostsurl = "http:".$config['posts_url'];
$themetadescription = html_entity_decode($config['metadescription'],ENT_COMPAT,"UTF-8");
header('Content-Type: text/xml');  
  
echo '<?xml version="1.0" encoding="UTF-8"?>  
<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>  
<title>'.$thesitename.'</title>  
<description>'.$themetadescription.'</description>  
<link>'.$thebaseurl.'</link>
<atom:link href="'.$thebaseurl.'/rss.php" rel="self" type="application/rss+xml" />';  
$get_gags = "SELECT PID, story, time_added,mediafile FROM posts WHERE active=1 ORDER BY PID DESC LIMIT 15";  
  
$gags = mysql_query($get_gags) or die(mysql_error());  
  
while ($gag = mysql_fetch_array($gags)){  
    $pubdate = date('r', $gag['time_added']);
    echo '  
       <item>  
          <title>'.html_entity_decode($gag['story'],ENT_COMPAT,"UTF-8").' - '.$thesitename.'</title>  
          <description><![CDATA['.html_entity_decode($gag['story'],ENT_COMPAT,"UTF-8").']]></description>  
          <link>'.$thebaseurl.$config['postfolder'].$gag['PID'].'/</link>  
		  <guid>'.$thebaseurl.$config['postfolder'].$gag['PID'].'/</guid>';
		  if($gag['mediafile'] != ""){echo '
		  <enclosure type="image/jpeg" url="'.$thepostsurl.'/t/s-'.$gag['mediafile'].'" length="1" />';
		  }
    echo  '
		  <pubDate>'.$pubdate.'</pubDate>  
      </item>';  
}  
echo '</channel>  
</rss>';  
?>