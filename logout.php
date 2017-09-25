<?php
// Get Slapped! If still in combat and clicked out
if (isset($_SESSION['opponent_id'])) {
	header ('Location: combat.php?comb_act=npc_attack');
	exit;
}
require_once "config.php";
require_once "include_lang.php";

// Unset PASS cookie to logout & user globals
setcookie("PHP_PHAOS_PASS", $PHP_PHAOS_PASS, 1, "/", $cookie_domain, $secure);
unset ($PHP_PHAOS_USER, $PHP_PHAOS_PASS, $PHP_PHAOS_CHARID, $PHP_PHAOS_CHAR);
?>

<html>
<head>
	<title><?php echo $lang_logout["title"];?></title>
	<link rel="stylesheet" type="text/css" href="styles/phaos.css">
	
</head>
<body>
	<div id="page-center" class="center">
		<img src="images/top_logo.png"><br>
		<span class="b msgbox"><?php echo $lang_logout["signout"];?></span>
		<meta http-equiv="refresh" content="3;URL=index.php">
	</div>
</body>
</html>
