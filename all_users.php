<?php require_once "header.php"; ?>

<div class="center bgcolor">
	<h2><?php echo $lang_users["all-users"]; ?></h2>
</div>
<br>
<div>
	<?php
	$sql = $db->query("SELECT username FROM phaos_users ORDER BY username ASC");
	while ($row = $sql->fetch_assoc()) {
		// Only display username if a character is created
		$char_exist = $db->query("SELECT username FROM phaos_characters WHERE username = '".$row["username"]."' LIMIT 1");
		if ($char_exist->num_rows) {
			echo "<dt class='center button'><a href='player_info.php?player_name=".$row["username"]."' title='$lang_users[v_profile] '><span class='b'>".$row["username"]."</span></a></dt>";
		}
	}
	?>
</div>

<?php require_once "footer.php";
