<?php


$lskip = "1";
include("inc/include.php");
if(isset($_SESSION['USERNAME'])){destroy_slrememberme($_SESSION['USERNAME']);}
session_destroy();
header("location:".$config['baseurl']."/");
?>
