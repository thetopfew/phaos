<?php
require_once "header.php";

// If no character created, bu-bye!
if ($character->location == "") {
	jsChangeLocation("create_character.php", 0);
}
?>

<div class="center">
	<img src="lang/<? echo $lang;?>_images/clan_home.png">
	<div class="bgcolor"> &nbsp; </div>
	<?php
	// Are you even in a clan?
	if (ru_inclan($character->name) != true) {
		?>
		<h3 class="b red msgbox"><?php echo $lang_clan["notinclan"];?></h3>
		<?php
		jsChangeLocation("clan_join.php", 2);
	}
	// Are you a clan owner?
	if (ru_clanleader($character->name)) {
		?>
		<h3 class="b red msgbox"><?php echo $lang_clan["uclanowner"];?></h3><br>
		<?php
		jsChangeLocation("clan_home.php?clan=$clan", 4);
	}

	// Get your clan name
	//$clan = get_clanname($character->name);
	
	// Go back if NO selected
	if ($quitting == "no") {
		$showform = "no";
		jsChangeLocation("clan_home.php?clan=$clan", 0);
	}

	// Clicked confirm yes quitting?
	if ($quitting == "yes") {
		$showform = "no";
		
		// Remove member tag
		$res = $db->query("SELECT clanmember,oldname FROM phaos_clan_in WHERE clanmember = '$clan_user_name'"); 
		if ($row = $res->fetch_assoc()) {
			$clanmember = $row["clanmember"];
			$oldname =  $row["oldname"];
		}
		$removetag = "UPDATE phaos_characters SET name = '$oldname' WHERE name = '$clanmember'";
		$db->query($removetag) or die ("Error in query: $removetag. " . mysqli_error());
		
		// Remove from clan
		$res = "DELETE FROM phaos_clan_in WHERE clanmember LIKE '$clan_user_name'";
		$db->query($res) or die ("Error in query: $res. " . mysqli_error());

		// Substract 1 from member count
		$db->query("UPDATE phaos_clan_admin SET clanmembers=clanmembers-1 WHERE clanname='$clan'");

		/* Guild assistant?
		if ($clanleader_1 == $clan_user_name) {
			$db->query("UPDATE phaos_clan_admin SET clanleader_1='' WHERE clanname='$clan'");
		}*/
		
		echo "<div class='center big b msgbox'>".$lang_clan["haveleft"]." ".$clan.".</div>";
		
		// Done, move out.
		jsChangeLocation("travel.php", 2);
	}

	// Default Display form
	if (empty($showform)) {
		?>
		<div class="b">
			<form method="post" action="clan_leave.php?clan=<?php echo $clan;?>&clan_user_name=<?php echo $clan_user_name;?>">
				<span class="big"><?php echo $clan_user_name, $lang_clan["sure2le"];?></span><br>
				<span class="b red msgbox"><?php echo $lang_clan["wish2le"];?></span><br><br>

				<div>
					<?php echo $lang_o_yes;?><input type="radio" value="yes" name="quitting">
					<?php echo $lang_o_no;?><input type="radio" value="no" checked name="quitting" checked>
				</div><br>
				<div id="actionbar" class="fullsize bgcolor">
					<input class="button" type="submit" value="<?php echo $lang_clan["confir"] ;?>" name="B1">
				</div>	
			</form>
		</div>
		<?php
	}
	?>
</div>

<?php require_once "footer.php";
