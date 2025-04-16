<?php 
namespace Fitify;
include_once 'MoreDBUtil.php';
session_start();

$timeout_duration = 600;
if (isset($_SESSION['LAST_ACTIVITY']) &&
    (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) 
    {
        session_unset();
        session_destroy();
        header("Location: login.php"); 
        exit();
    } //timeout timer

?>
<html>
    <head>
    <link href="FitifyRules2.css" type="text/css" rel="stylesheet"/>
        <p id="Logo"> <a href="FitHomepage.php"> fitify </a></p>

        <style>

        #Logo { /*Logo is just text */
        width: auto; 
        height: auto; 
        position: fixed;
        bottom: 72%;
        left: 12px; 
        transform: translateY(-50%);
        z-index: 9999; 
        font-size: 58px;
        text-shadow: 1px 1px 2px black;
        font-weight: bold;
        font-style: italic;
        color:white; 
    }
    a {
        color: white;
        text-decoration:none;
        background: none !important;
    } 
    head {
        background-image: url('ColorBlack.png') !important;
        color:black;
    }
        </style>
    </head>
<h1> Nothing here yet </h1>

</html>