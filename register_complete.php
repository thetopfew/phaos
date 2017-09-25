<?php
require_once "config.php";
?>
<html>
<head>
	<title><?php echo $lang_reg["title0"];?></title>
	<link rel="stylesheet" href="<?php echo auto_version('/styles/phaos.css'); ?>" type="text/css" />
</head>

<body> 
<div id="page-center">
	<img src="lang/<?php echo $lang ?>_images/register.png">
	<br><br>
	<?php
	// Check entered username while lowercase to match
	$user_exists = false;
	$res = $db->query("SELECT * FROM phaos_users WHERE LOWER(username) = LOWER('$username') AND LOWER(email_address) = LOWER('$email_address')");
	if ($res->num_rows) {
		$user_exists = true;
	}
	
	// If user already registered in game
	if ($user_exists) {
		echo "
		<span class='b msgbox halfsize'>
			<h3 class='red'>".$lang_reg["err_exists"]."</h3>
			".$lang_reg["err_ex_exp"]."
		</span>";
		?><br><input class="button" type="button" onClick="location='index.php'" value="<?php echo $lang_goback;?>"><?php
	} else {
		// Check if username exists on phpBB forums
		$phpbb_user = $phpbb_db->query("SELECT username,username_clean,user_email FROM ".$phpbb_dbprefix."_users WHERE username_clean = LOWER('$username') LIMIT 1"); 
		if ($row = $phpbb_user->fetch_assoc()) {
			$phpbb_name = $row["username"];
			$phpbb_Cname = $row["username_clean"];
			$phpbb_email = $row["user_email"];
			
			// Encrypt chosen game password
			$passwd = md5($password_1);
			
			// Ensure email match using lowercase
			$email_address = strtolower($email_address);
			$phpbb_email = strtolower($phpbb_email);
			
			// Register game account, using forum username (so case will match phpbb).
			$C_username = strtolower($username);
			if ($C_username === $phpbb_Cname AND $email_address === $phpbb_email) {
				$req = $db->query("INSERT INTO phaos_users (username,password,email_address) VALUES ('$phpbb_name','$passwd','$phpbb_email')");
				if (!$req) {echo "<B>Error ".mysqli_errno()." :</B> ".mysqli_error().""; exit;}

				echo "
				<span class='b msgbox halfsize'>
					".$lang_reg["r_success"]."
				</span>";
				?><br><input class="button" type="button" onClick="location='index.php'" value="<?php echo $lang_goback;?>"><?php
			} else { // Something doesn't match
				echo "
				<span class='b msgbox halfsize'>
					<h3 class='red'>".$lang_reg["r_failed"]."</h3>
					".$lang_reg["not_compl"]."
				</span>";
				?><br><input class="button" type="button" onClick="location='index.php'" value="<?php echo $lang_goback;?>"><?php
			}
		} else { // This username account does not exist on forum
			echo "
			<span class='b msgbox halfsize'>
				<h3 class='red'>".$lang_reg["no_account"]."</h3>
				".$lang_reg["no_acc_msg"]."
			</span>";
			//echo "<meta http-equiv=refresh content=\"3; URL=index.php \">";
			?><br><input class="button" type="button" onClick="location='register.php'" value="<?php echo $lang_goback;?>"><?php
		}
	}
	?>
</div>
</body>
</html>