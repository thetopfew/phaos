<?php
// If no character created, do not execute right_side.php
if ($character->location == "") { echo ""; } else {
	// Get total # of users
	$total_users = 0;
	$res = $db->query("SELECT * FROM phaos_users");
	if ($res->num_rows) { $total_users = $res->num_rows; }
	?>

	<div class="center">
		<?php
		if (ru_inclan($character->name)) {
			$clanname = get_clanname($character->name);
			?><br>
			<input class="button" type="button" value="<?php echo $lang_rside["clanlist"]; //create new page soon ?>" onClick="location='clan_join.php'"><br>
			<input class="button" type="button" value="<?php echo $lang_rside["go_guild"];?>" onClick="location='clan_home.php?clan=<?php echo $clanname;?>'"><br>
			<?php 
		} else { 
			?><br>
			<input class="button" type="button" value="<?php echo $lang_rside["look_guild"];?>" onClick="location='clan_join.php'"><br>
			<?php
			if ($character->level >= 10) {
				?>
				<input class="button" type="button" value="<?php echo $lang_rside["create_guild"];?>" onClick="location='clan_create.php'"><br>
				<?php
			}
		}
		?>
		<hr class="halfsize">
		<a href="all_users.php"><span class="b"><?php echo "$lang_tu: "; echo $total_users;?></span></a>
		<br>
		<a href="who.php"><span class="b"><?php echo $lang_home["hos_online"]; ?></span></a>
		<br><hr class="halfsize">

		<?php //echo $lang_mus; <a href="music/index.php?play_music=YES" target="_blank"><?php echo $lang_plays; ?>

		<form name="chat_form">
			<textarea cols="24" rows="4" name="chat_text" placeholder="<?php echo $lang_rside["prechatbox"];?>"></textarea><br><br>
			<input class="button" type="button" onclick="add_chat_line();" value="<?php echo $lang_rside["submit"]; ?>">
		</form>
		<div id="chat_right_side"></div> <?php //NOTE: this div is required for init sajax in header.php ?>
	</div>
<?php
}
?>