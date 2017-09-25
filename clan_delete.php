<?php
require_once "header.php";

// If no character created, bu-bye!
if ($character->location == "") {
	jsChangeLocation("create_character.php", 0);
}
// SECURE: Check URL if = a valid clanname for user, redirect if not
if ($clan == "" OR $clan != get_clanname($character->name)) {
	echo "<div class='center'><h3 class='red msgbox'>$lang_err[del_page]</h3></div>";
	jsChangeLocation("travel.php", 2);
}
?>

<div class="center">
	<img src="lang/<? echo $lang;?>_images/clan_home.png">
	<?php
	// Are you even a clan owner?
	if (ru_clanleader($character->name) != true) {
		?>
		<h3 class="b red msgbox"><?php echo $lang_clan["1st_creat"];?></h3><br>
		<?php
		jsChangeLocation("travel.php", 2);
	}

	// Did we click confirm?
	if ($delclan == "yes") {
		// Get all clan info at once and still call by it's returned var
		list($clanid,$clanname,$clanleader,$clanleader_1,$clanbanner,$clansig,$clanlocation,$clanslogan,$clancashbox,$clanmembers,$clancreatedate,$clanrank_1,$clanrank_2,
		$clanrank_3,$clanrank_4,$clanrank_5,$clanrank_6,$clanrank_7,$clanrank_8,$clanrank_9,$clanrank_10,$clan_sig) = getall_claninfo($clan);
		
		$showform = "no";

		// Guild still has members?
		if ($clanmembers > 1) { 
			?>
			<div class="b big msgbox">
				<span class="red"><?php echo $clanname.$lang_clan["del_stop"].$clanmembers;?></span><br><br>
				<?php echo $lang_clan["m_del_all"];?>
			</div>
			<br>
			<input class="button" type="button" onClick="location='clan_leader.php?clan=<?php echo $clan;?>'" value="<?php echo $lang_goback;?>">
			<?php
			jsChangeLocation("clan_leader.php?clan=$clan", 5);
		// If not, Continue to deletion
		} else {	
			echo "<div class='center'><h3 class='red msgbox'>".$lang_clan["del_gu"]."</h3></div>";

			// Remove clansig from leader's name only. Members are removed in clan_leave.php
			$res = $db->query("SELECT clanmember,oldname FROM phaos_clan_in WHERE clanmember = '$clanleader'"); 
			if ($row = $res->fetch_assoc()) {
				$clanmember = $row["clanmember"];
				$oldname =  $row["oldname"];
			}
			
			// Delete any existing image files that were custom uploaded
			if ($clanbanner != "") {unlink($clanbanner);}
			if ($clan_sig != "") {unlink($clan_sig);}
			
			// Update database
			$db->query("UPDATE phaos_characters SET name = '$oldname' WHERE name = '$clanmember'"); 
			$db->query("DELETE FROM phaos_clan_admin WHERE id = '$clanid'");
			$db->query("DELETE FROM phaos_clan_in WHERE clanmember = '$clanleader'");
			?>
			
			<br>
			<input class="button" type="button" value="<?php echo $lang_ref;?>" onClick="location='travel.php'">
			<?php
			jsChangeLocation("travel.php", 5);
		}
	} elseif ($delclan == "no") {
		jsChangeLocation("clan_leader.php?clan=$clan", 0);
	}

	// Default Display
	if ($showform != "no") {
		?>
		<div class="b">
			<form method="post" action="clan_delete.php?clan=<?php echo $clan;?>">
				<div class="big bgcolor">
					<?php echo $lang_clan["onc"];?>
				</div>
				<br>
				<span class="big"><?php echo "$lang_clan[sure] $clanname?";?></span><br><br>
				<span class="red msgbox"><?php echo $lang_clan["del_warn"];?></span><br><br>
				<div>
					<?php echo $lang_o_yes;?> <input type="radio" value="yes" name="delclan">
					<?php echo $lang_o_no;?> <input type="radio" value="no" name="delclan" checked>
				</div><br>
				<div id="actionbar" class="fullsize bgcolor">
					<input class="left button" type="button" onClick="location='clan_leader.php?clan=<?php echo $clan;?>'" value="<?php echo $lang_goback;?>">
					<input class="right button" type="submit" value="<?php echo $lang_clan["confirm"];?>" name="B1">
				</div>
			</form>
		</div>
		<?php
	}
	?>
</div>

<?php require_once "footer.php";