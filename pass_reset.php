<?php
require_once "config.php";
?>
<html>
<head>
	<title><?php echo $lang_pass["rtitle"];?></title>
	<link rel="stylesheet" href="<?php echo auto_version('/styles/phaos.css'); ?>" type="text/css" />
</head>

<body>
<div id="page-center">
	<?php
	// Check for existing entered username & email address
	$user_exists = false;
	$res = $db->query("SELECT * FROM phaos_users WHERE LOWER(username) = LOWER('$username') AND LOWER(email_address) = LOWER('$email_address')");
	if ($res->num_rows) {
		$user_exists = true;
	}
	
	if ($user_exists == false) {
		echo "<span class='b msgbox halfsize'><h4 class='red'>".$lang_pass["err1"]."</h4><br>".$lang_pass["err2"]."</span>";
		?><br><input class="button" type="button" onClick="location='pass_lost.php'" value="<?php echo $lang_goback;?>"><?php
	} else {
		// Encrypt new password of course... and continue with new password save.
		$passwd = md5($password_1);

		$req = $db->query("UPDATE phaos_users SET password='$passwd' WHERE username = '$username' AND email_address = '$email_address'") or die ($lang_pass["failed"]);

		if ($req) { // Success
			echo "<span class='b msgbox halfsize'>".$lang_pass["success"]."$username</span>";
			?><br><input class="button" type="button" onClick="location='index.php'" value="<?php echo $lang_goback;?>"></div><?php
		}
	}
	?>
</div>
</body>
</html>