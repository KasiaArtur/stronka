<?php
/*
 * Copyright 2011 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *		 http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
$disable_fb_connect = "1";
include("inc/include.php");
require_once 'googleoauth/Google_Client.php'; // include the required calss files for google login
require_once 'googleoauth/contrib/Google_PlusService.php';
require_once 'googleoauth/contrib/Google_Oauth2Service.php';

$client = new Google_Client();
$client->setApplicationName("Sign in with GPlus"); // Set your applicatio name
$client->setScopes(array('https://www.googleapis.com/auth/userinfo.email', 'https://www.googleapis.com/auth/plus.me')); // set scope during user login
$client->setClientId($config['google_client_id']); // paste the client id which you get from google API Console
$client->setClientSecret($config['google_client_secret']); // set the client secret
$client->setRedirectUri("http:".$config['baseurl'].'/google_callback.php'); // paste the redirect URI where you given in APi Console. You will get the Access Token here during login success
$client->setDeveloperKey($config['google_developer_id']); // Developer key
$plus = new Google_PlusService($client);
$oauth2 = new Google_Oauth2Service($client); // Call the OAuth2 class for get email address
if(isset($_GET['code']))
{
	$client->authenticate(); // Authenticate
	$_SESSION['access_token'] = $client->getAccessToken(); // get the access token here
	header('Location: http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
}

if(isset($_SESSION['access_token']))
{
	$client->setAccessToken($_SESSION['access_token']);
}

if($client->getAccessToken())
{
	$user = $oauth2->userinfo->get();
	$me = $plus->people->get('me');
	$optParams = array('maxResults' => 100);
	$activities = $plus->activities->listActivities('me', 'public',$optParams);
	// The access token may have been updated lazily.
	$_SESSION['access_token'] = $client->getAccessToken();
	$gemail = filter_var($user['email'], FILTER_SANITIZE_EMAIL); // get the USER EMAIL ADDRESS using OAuth2
	$avatar_url = $user['picture'];
	if(isset($me))
	{
		$google_id = $me['id'];
		$query="SELECT USERID FROM users WHERE email='".mysql_real_escape_string($gemail)."' limit 1";
		$executequery=$dbconn->execute($query);
		$GUID = intval($executequery->fields['USERID']);
		
		if($GUID > 0)
		{									
			$query="SELECT * from users WHERE USERID='".mysql_real_escape_string($GUID)."' and status='1'";
			$executequery = $dbconn->execute($query);
			if($executequery->recordcount()>0)
			{
				$query="update users set lastlogin='".time()."', lip='".$_SERVER['REMOTE_ADDR']."', google_connected='1', google_id='".$google_id."' WHERE USERID='".mysql_real_escape_string($GUID)."'";
				$dbconn->execute($query);
				$result = $executequery->getrows();
				if($result[0]['profilepicture'] == '' && $result[0]['profilepicture_removed'] == '0'){$result[0]['profilepicture'] = download_social_avatar($result[0]['USERID'],$avatar_url);}
				prepare_session($result[0]);			
				$redirect = $_SESSION['location'];
				if($redirect == "")
				{
					if($config['regredirect'] == 1 || isset($mobile)){header("Location:".$config['baseurl']."/index.php");exit;}
					else{header("Location:".$config['baseurl']."/settings");exit;}
				}
				else
				{
					header("Location:".$config['baseurl'].$redirect);exit;
				}
			$_SESSION['location'] = "";
			}
		}
		else
		{			
			if($google_id != "" && $gemail != "")
			{
				$query="INSERT INTO users SET email='".mysql_real_escape_string($gemail)."',username='', google_connected='1', google_id='".$google_id."', addtime='".time()."', lastlogin='".time()."', ip='".$_SERVER['REMOTE_ADDR']."', lip='".$_SERVER['REMOTE_ADDR']."', verified='1'";
				$result=$dbconn->execute($query);
				$userid = mysql_insert_id();
				if($userid != "" && is_numeric($userid) && $userid > 0)
				{
					$query="SELECT * from users WHERE USERID='".mysql_real_escape_string($userid)."'";
					$executequery =$dbconn->execute($query);
					$result = $executequery->getrows();
					if($result[0]['profilepicture'] == ''){$result[0]['profilepicture'] = download_social_avatar($result[0]['USERID'],$avatar_url);}
					prepare_session($result[0]);			
					if(!isset($mobile)){header("Location:".$config['baseurl']."/connect.php");exit;}
					else{header("Location:".$config['mobileurl']."/connect.php");exit;}
				}
			}
		}
	}
}
?>