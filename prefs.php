<?php
require_once "header.php";

// If no character created
if ($character->location == "") {
	jsChangeLocation("create_character.php", 0);
}
// Get Slapped! If in combat and clicked travel
if(isset($_SESSION['opponent_id'])) {
	jsChangeLocation("combat.php?comb_act=npc_attack", 0);
}
?>

<script>
	function LoadAppearance(sPreviewID) {
		var oAppearance = window.document.getElementById("new_image_path");
		var sAppearanceImg = oAppearance.options[oAppearance.selectedIndex].value;

		window.document.getElementById(sPreviewID).src = sAppearanceImg;
	}
</script>

<?php
function GetAppearanceList($folder) {
	$dirFiles = array();
	// Opens Images Folder
	if ($handle = opendir($folder)) {
		while (false !== ($file = readdir($handle))) {
			$dirFiles[] = $file;
		}
	}
	sort($dirFiles);
	foreach($dirFiles as $file) {
		// Strips file extensions      
		$crap = array(".jpg", ".jpeg", ".png", ".gif", ".bmp", "_", "-");    
		$newstring = str_ireplace($crap, " ", $file);
		
		if (is_file($folder.$file)) {
			$sList .= "<option value='".$folder.$file."'>".$newstring."</option>\n";
		}
	}
	closedir($handle);
	return $sList;
}

/**
* Delete character feature
* Returns 0 on failure and 1 on all done successfully. (mainly SQL-errors!!)
*/
function delete_character() {
	global $db, $PHP_PHAOS_USER;
	
	// FIXME, if player is guild master, and deletes character, not guild first?
	$res = $db->query("SELECT name FROM phaos_characters WHERE username = '$PHP_PHAOS_USER'");
	if ($row = $res->fetch_assoc()) {
		$name = $row["name"];
		
		// Is player in a clan?
		$res = $db->query("SELECT * FROM phaos_clan_in WHERE clanmember = '".$name."'");
		if ($row = $res->fetch_assoc()) {
			$clanmember = $row["clanmember"];
			$clanname = $row["clanname"];
			$oldname = $row["oldname"];
			$clanrank = $row["clanrank"];

			if ($name == $clanmember) {		
				if ($clanrank == 99) { // Update (or remove) members clan tags and then delete clan.								
					$res = $db->query("SELECT * FROM phaos_clan_in WHERE clanname = '".$clanname."'");
					while ($row = $res->fetch_assoc()) {
						$updatename = $row["clanmember"];
						$newname = $row["oldname"];											
							
						$query = "UPDATE phaos_characters SET name = '".$newname."' WHERE name = '".$updatename."'";
						$result = $db->query($query) or die ("Error in query: $query. " .
						mysqli_error());	
						
						// Remove Clan from all DB now
						$query = "DELETE FROM phaos_clan_admin WHERE clanname = '".$clanname."'";
						$result = $db->query($query) or die ("Error in query: $query. " .
						mysqli_error());
						
						$query = "DELETE FROM phaos_clan_in WHERE clanname = '".$clanname."'";
						$result = $db->query($query) or die ("Error in query: $query. " .
						mysqli_error());						
						
						$query = "DELETE FROM phaos_clan_search WHERE clanname = '".$clanname."'";
						$result = $db->query($query) or die ("Error in query: $query. " .
						mysqli_error());					
					}
				} else {
					$query = "UPDATE phaos_clan_admin SET clanmembers = clanmembers-1 WHERE clanname = '".$clanname."'";
					$result = $db->query($query) or die ("Error in query: $query. " .mysqli_error());
					
					$query = "DELETE FROM phaos_clan_in WHERE clanmember = '".$name."'";
					$result = $db->query($query) or die ("Error in query: $query. " .
					mysqli_error());
				}			
			} 
		}
		// Remove from clan requests also.
		$db->query("DELETE FROM phaos_clan_search WHERE charname = '$name'");
		$db->query("DELETE FROM phaos_char_inventory WHERE username = '$PHP_PHAOS_USER'");
		
		$query = "DELETE FROM phaos_characters WHERE username = '$PHP_PHAOS_USER'";
		$db->query($query) or die ("Error in query: $query. " .
		mysqli_error());
	}
	return 1;
}

// Did you click to delete your character?
if (@$_POST['delete'] == "yes") {
	delete_character();
	// Go back to create new character.
	jsChangeLocation("create_character.php", 0);
}
			
if ($_REQUEST['saved']) {

	if ($pw != "") {
		//FIXME kaspir: add check current password.
		if ($pw === $pw2) {
			$vpw = md5($pw);
			$db->query("UPDATE phaos_users SET password='$vpw' WHERE username='$username' LIMIT 1");
			?>
			<div class="center"><h3 class="b msgbox"><?php echo $lang_prefs["saved"];?><br><?php echo $lang_prefs["auto-logout"]; ?></h3></div>
			<?php
			jsChangeLocation("logout.php", 3);
		} else {
			?>
			<div class="center"><h3 class="msgbox"><?php echo $lang_prefs["err-p-match"];?></h3></div>
			<?php
			jsChangeLocation("prefs.php", 3);
		}
	} else {
		if ($new_image_path) {
			$db->query("UPDATE phaos_characters SET image_path='$new_image_path' WHERE username='$username' LIMIT 1") or die("nope");
		}
		if ($new_lang) {
			$db->query("UPDATE phaos_users SET lang='$new_lang' WHERE username='$PHP_PHAOS_USER' LIMIT 1") or die("nope");
			setcookie('lang', $new_lang, time() + (86400 * 365));
		}
		?>
		<div class="center"><h3 class="msgbox"><?php echo $lang_prefs["saved"];?></h3></div>
		<?php
		jsChangeLocation("prefs.php", 1);
	}

// Do nothing and display form
} else {
	$result = $db->query("SELECT * FROM phaos_users WHERE username='$PHP_PHAOS_USER'");
	while ($row = $result->fetch_assoc()) {
		$id = $row['id'];
		$username = $row['username'];
		//$email = $row['email_address']; //this will need a function to get from phpBB tables
		$language = $row['lang'];
	}
	?>
	<form method="post" action="<?php echo $this_url;?>?saved=yes&username=<?php echo $username; ?>">
		<table cellspacing=0 cellpadding=5 border=1 align="center">
			<tr>
				<td colspan=2 align="center">
					<span class="b i u"><?php echo $lang_prefs["title"]; ?></span>
				</td>
			</tr>
			<tr>
				<td align="right">
					<span class="b"><?php echo $lang_prefs["username"]; ?></span>
				</td>
				<td>
					<?php echo $username; ?>
				</td>
			</tr>
			<tr>
				<td align="right">
					<span class="b"><?php echo $lang_prefs["new-pw"]; ?></span>
				</td>
				<td>
					<input type="password" name="pw" size="10" maxlength=20>
				</td>
			</tr>
			<tr>
				<td align="right">
					<span class="b"><?php echo $lang_prefs["conf-pw"]; ?></span>
				</td>
				<td>
					<input type="password" name="pw2" size="10" maxlength=20>
				</td>
			</tr>
			
			<tr>
				<td align="right">
					<span class="b"><?php echo $lang_prefs["lang"]; ?></span>
				</td>
				<td>
					<select id="new_lang" name="new_lang">
						<option selected disabled="disabled"><?php echo $lang_drop_sel;?></option>
						<option value="en">English</option>
						<option value="de">Deutsch</option>
					</select>
				</td>
			</tr>
			<tr>
				<td align="right">
					<span class="b"><?php echo $lang_crt["sel_char_img"]; ?></span>
				</td>
				<td align="left">
					<select id="new_image_path" name="new_image_path" onchange="javascript: LoadAppearance('current_appearance');">
						<option selected disabled="disabled"><?php echo $lang_drop_sel;?></option>
						<?php echo GetAppearanceList("images/icons/characters/"); ?>
					</select>
				</td>
			</tr>
			<tr>
				<td align="center" colspan="2">
					<?php
					$result = $db->query("SELECT image_path FROM phaos_characters WHERE username = '$PHP_PHAOS_USER' ");
					if ($row = $result->fetch_assoc()) {
						$char_image = $row["image_path"];
					}
					if ($char_image == "") {
						?><img id="current_appearance" name="current_appearance" src="images/icons/not-selected.gif"><?php
					} else {
						?><img id="current_appearance" name="current_appearance" src="<?php echo $char_image; ?>"><?php
					}
					?>
				</td>
			</tr>
		</table>
		<br>
		<p class="center"><input class="button" type="submit" value="<?php echo $lang_save; ?>"></p>
	</form>
<?php
}

// Display character delete button
if ($PHP_PHAOS_CHARID != 0) {
?>
<div class="center" style="margin:100px 0;">
	<form method="post" action="" onSubmit="return confirm('<?php echo $lang_prefs["del_confirm"]; ?>')">
		<input type="hidden" name="delete" value="yes">
		<input class="button" type="submit" value="<?php echo $lang_prefs["delete"]; ?>">
	</form>
</div>
<?php 
}
require_once "footer.php";
