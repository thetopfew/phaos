<?php
require_once "header.php";

// If no character created, bu-bye!
if ($character->location == "") {
	jsChangeLocation("create_character.php", 0);
}
?>

<div class="center">
	<img src="lang/<? echo $lang;?>_images/clan_ask.png">
	<div class="big b bgcolor"><?php echo $lang_clan["sendrequ"];?></div>
	<?php
	// Are you in a clan already?
	if (ru_inclan($character->name)) {
		?>
		<h3 class="b red msgbox"><?php echo $lang_clan["inclanalrd"];?></h3>
		<?php
		$clan = get_clanname($character->name);
		jsChangeLocation("clan_home.php?clan=$clan", 2);
	}

	// In clan search still?
	if (ru_inclansearch($character->name)) {
		?>
		<h3 class="b red msgbox"><?php echo $lang_clan["insearch"];?></h3>
		<?php
		jsChangeLocation("clan_join.php", 2);
	}

	// If request comment left blank
	if ($Text1 == "" AND $questionsend == $lang_clan["send_me"]) {
		?>
		<h3 class="b red msgbox"><?php echo $lang_clan["plz_fill"];?></h3>
		<?php
		jsChangeLocation("clan_send.php", 2);
	}
	
	// Submit Request if not blank
	if ($Text1 != "" AND $questionsend == $lang_clan["send_me"]) {
		
		$db->query("INSERT INTO phaos_clan_search (clanname,charname,description) VALUES ('$clanname','$character->name','$Text1')");
		
		// Get username of clanleader for mail
		$clanleader = claninfo("leader",$clanname); //$claninfo["leader"]
		$res = $db->query("SELECT username FROM phaos_characters WHERE name = '$clanleader' LIMIT 1");
		if ($row = $res->fetch_assoc()) {
			$sendto_usr = $row["username"];
		}
		
		// Send the notice mail
		$subject = "New Guild Applicant";
		$message = "$PHP_PHAOS_USER $lang_clan[u_req_2join] $clanname.";
		$date = date("m/d/Y h:i");
		$db->query("INSERT INTO phaos_mail (UserTo,UserFrom,Subject,Message,STATUS,SentDate) VALUES ('$sendto_usr','$PHP_PHAOS_USER','$subject','$message','unread','$date')");
		
		?>
		<div>
			<h3 class="b msgbox"><?php echo $lang_clan["has_sent"];?></h3>
		</div>
		<?php
		jsChangeLocation("clan_join.php", 2);
	}
	
	// Default Display form
	if (empty($error)) {
		?>
		<div>
			<?php echo "<h3>$lang_clan[cl_ask] $clanname_ask</h3>"; ?>
			<hr><br>
			<form method="post" action="clan_send.php">
				<span class="b"><?php echo $lang_clan["ur_messss"];?></span>
				<br>
				<input type="hidden" name="clanname" value="<?php echo $clanname_ask;?>">
				<input type="text" name="Text1" size="40" maxlength="40" value="<?php echo $lang_clan["ur_txxxx"];?>"><br>
				<span class="b msgbox"><?php echo $lang_clan["cn_ent_mes"];?></span>
				<div id="actionbar" class="fullsize bgcolor">
					<input class="left button" type="button" onClick="location='clan_join.php'" value="<?php echo $lang_goback;?>">
					<input class="right button" type="submit" value="<?php echo $lang_clan["send_me"];?>" name="questionsend">
				</div>
			</form>
		</div>
		<?php
	}
	?>
</div>

<?php require_once "footer.php";
