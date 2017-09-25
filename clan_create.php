<?php
require_once "header.php";

// If no character created, bu-bye!
if (empty($character->location)) {
	jsChangeLocation("create_character.php", 0);
}
?>

<div class="center">
	<img src="lang/<? echo $lang;?>_images/clan_create.png">
	<?php
	// Are you already a clan owner, get out of here..
	if (ru_clanleader($character->name)) {
		?>
		<h3 class="b red msgbox"><?php echo $lang_clan["owner"];?></h3>
		<?php
		$clan = get_clanname($character->name);
		jsChangeLocation("clan_home.php?clan=$clan", 2);
	}
	
	// Do not allow if not this level yet
	if ($character->level < 10) {
		?>
		<h3 class="b red msgbox"><?php echo $lang_clan["lv_chk"];?></h3>
		<?php
		jsChangeLocation("clan_join.php", 2);
	}

	// Get character name & location (sql needs to be done, checking for attached member tags on names)
	$res = $db->query("SELECT name,location FROM phaos_characters WHERE username = '$PHP_PHAOS_USER'");
	if ($row = $res->fetch_assoc()) {
		$char_name = $row["name"];
		$char_location = $row["location"];
	} 
	
	// Fail if player already in clan
	if (ru_inclan($char_name)) {
		?>
		<h3 class="b red msgbox"><?php echo $lang_clan["alread"];?></h3>
		<?php
		$clan = get_clanname($character->name);
		jsChangeLocation("clan_home.php?clan=$clan", 2);
	}
	
	// Set no errors yet.
	$noerror = "yes";
	
	// Did we click create?
	if ($createguild == $lang_clan["create"]) {
		// Fail if, clan name blank
		if ($clanname == "") {
			$error = $lang_clan["plz_1"];
			$createguild = "no";
			$noerror = "no";
		}
		// Fail if, dupe clan name
		$res = $db->query("SELECT * FROM phaos_clan_admin WHERE clanname = '$clanname'");
		if ($res->num_rows) {
			$error = $lang_clan["plz_2"];
			$createguild = "no";
			$noerror = "no";
		}
		// Fail if, clan sig blank
		if ($clansig == "") {
			$error = $lang_clan["plz_3"];
			$createguild = "no";
			$noerror = "no";
		}
		// Fail if, dupe clan tag
		$res = $db->query("SELECT * FROM phaos_clan_admin WHERE clansig = '$clansig'");
		if ($res->num_rows) {
			$error = $lang_clan["plz_4"];
			$createguild = "no";
			$noerror = "no";
		}
		// Create now if no errors
		if ($noerror == "yes") {
			$date = date('m/d/Y');
			$showform = "no";

			echo "<div class='center'><h3 class='b msgbox'>".$lang_clan["creating"]."</h3></div>";
			
			$query = "INSERT INTO phaos_clan_admin
			(clanname,clanleader,clansig,clanlocation,clanslogan,clancashbox,clanmembers,clancreatedate,clanrank_1,clanrank_2,clanrank_3,clanrank_4,clanrank_5,clanrank_6,clanrank_7,clanrank_8,clanrank_9,clanrank_10)
			VALUES
			('$clanname','[$clansig]$clanleader','$clansig','$char_location','$clanslogan','0','1','$date','$lang_clan[rank_1]','$lang_clan[rank_2]','$lang_clan[rank_3]','$lang_clan[rank_4]','$lang_clan[rank_5]','$lang_clan[rank_6]','$lang_clan[rank_7]','$lang_clan[rank_8]','$lang_clan[rank_9]','$lang_clan[rank_10]')";
			$req = $db->query($query);
			if (!$req) {echo "<B>Error ".mysqli_errno()." :</B> ".mysqli_error().""; exit;}
			
			$query = "INSERT INTO phaos_clan_in
			(clanname,clanmember,oldname,clanindate,givegold,rec_gold,clanrank)
			VALUES
			('$clanname','[$clansig]$clanleader','$clanleader','$date','0','0','99')";
			$req = $db->query($query);
			if (!$req) {echo "<B>Error ".mysqli_errno()." :</B> ".mysqli_error().""; exit;}

			// Update leader name with new tag
			$db->query("UPDATE phaos_characters SET name='[$clansig]$clanleader' WHERE name='$clanleader'");
			
			// Done, now to clan page
			jsChangeLocation("clan_home.php?clan=$clanname", 2);
			
			#ADDME: REMOVE IN SEARCH IF EXIST
		}
	}

	// Default Display
	if ($showform != "no") {
		?>
		<div class="big b">
			<div class="bgcolor"><?php echo $lang_clan["header"];?></div>
			<?php echo $lang_clan["greet"];?>
		</div><br>
		<?php
		
		// Display form errors, if any
		if ($error) { echo "<span class='b red msgbox'>$error</span><br><br>"; }
	
		?>
		<div>
			<form method="post" action="clan_create.php">
				<span class="b"><?php echo $lang_clan["name"];?></span> 
				<input type="text" name="clanname" size="30" maxlength="25"><br><br>
				<input type="hidden" name="clanleader" size="25" maxlength="25" value="<?php echo $PHP_PHAOS_CHAR;?>">
				
				<span class="b"><?php echo $lang_clan["tag"];?></span> <input type="text" name="clansig" size="4" maxlength="4"><br><br>
				
				<span class="center b"><?php echo $lang_clan["slogan"];?></span><br>
				<textarea id="txtArea" wrap="hard" rows="4" cols="40" maxlength="300" name="clanslogan" placeholder="<?php echo $lang_clan["hint"];?>"></textarea><br><br>
				
				<div id="actionbar" class="fullsize bgcolor">
					<input class="left button" type="button" onClick="history.go(-1);return true;" value="<?php echo $lang_goback;?>">
					<input class="center button" type="reset" value="<?php echo $lang_clan["reset"];?>" name="B2">
					<input class="right button" type="submit" value="<?php echo $lang_clan["create"];?>" name="createguild">
				</div>
			</form>
		</div>
		<?php
	} else {
		echo "";
	}
	?>
</div>

<?php require_once "footer.php";
