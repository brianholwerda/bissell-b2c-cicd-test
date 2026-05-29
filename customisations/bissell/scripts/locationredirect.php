<?php

$redirectLocation = $_GET['ll'];
$key = $_GET['key'];

if(!$redirectLocation)
    die;

$zipStart = (int)stripos($redirectLocation,"$key=") + (strlen($key) + 1);
$zipEnd = (int)stripos($redirectLocation,"&",$zipStart);
$fullZip = substr($redirectLocation,$zipStart,($zipEnd - $zipStart));
$zip5 = substr($redirectLocation,$zipStart,5);
$redirectLocation = str_replace($fullZip,$zip5,$redirectLocation);

header("Location: $redirectLocation");
exit;