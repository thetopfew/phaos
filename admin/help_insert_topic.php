<?php
include "aup.php";
session_start();
?>
<html>

<head>
<title>WoP Admin Panel - Insert Help Topic</title>
<link rel=stylesheet type="text/css" href="../styles/phaos.css">
<meta http-equiv="refresh"content="0;URL=admin_help_Add_Topic.php">
</head>

<body bgcolor="#000000" link="#FFFFFF" alink="#FFFFFF" vlink="#FFFFFF" text="#FFFFFF">
<?php
$db = mysqli_connect("$game_host", "$game_dbuser", "$game_dbpasswd", "$game_db") or die ("Unable to connect to MySQL server.");
//$db = mysqli_select_db($db, "$game_db") or die ("Unable to select requested database.");

$title = addslashes($title);
$file = addslashes($file);
$body = addslashes($body);

$query = "INSERT INTO phaos_help
(title,file,body) 
VALUES
('$title','$file','$body')";
$req = mysqli_query($db, $query);
if (!$req)
{ echo "<B>Error ".mysqli_errno()." :</B> ".mysqli_error()."";
exit; } 

session_destroy();
?>
</body>
</html>
