<?php
require_once "header.php";

// Get Slapped! If in combat and clicked travel
if(isset($_SESSION['opponent_id'])) {
	jsChangeLocation("combat.php?comb_act=npc_attack", 0);
}

// Get this players character
$result = $db->query("SELECT location,level FROM phaos_characters WHERE username = '$PHP_PHAOS_USER'");
if ($row = $result->fetch_assoc()) {
	$char_loc = $row["location"];
	$char_lvl = $row["level"];
} else {
	$char_loc = "";
	$char_lvl = "";
}
?>
<div>
	<div style="float:left;display:inline-block;width:15%;">
		<img src="images/torch.gif">
	</div>
	<div class="center" style="display:inline-block;width:70%;">
		<img src="lang/<?php echo $lang ?>_images/welcome.png"><br>
		<span class="b"><?php echo $lang_home["gametime"];?></span> <?php echo date('l jS \of F Y, h:i:s A');?><br><br>
		<a href="credits.php"><?php echo $lang_home["creddy"]; ?></a><br><br>
		<a href="<?php echo $phpbb_url;?>/viewforum.php?f=4" target="_blank"><?php echo $lang_home["need_help"];?></a>
	</div>
	<div style="float:right;display:inline-block;width:15%;">
		<img src="images/torch.gif">
	</div>
</div>
<br><hr>
<?php
$result = $db->query("SELECT name FROM phaos_locations WHERE id = '$char_loc'");
if ($row = $result->fetch_assoc()) {
	echo "<h3 class='center b'>$lang_home[yourloc] $row[name]</h3>";
}
?>
<br>
<table class="left halfsize" cellspacing=0 cellpadding=1 border=1>
	<tr class="bgcolor">
		<td>
			<h3 class="center b"><?php echo $lang_home['maps']; ?></h3>
		</td>
	</tr>
	<tr>
		<form method="post" action="map_to_image.php" target="_blank">
			<td class="center">
				<input type="hidden" name="begin_id" value="0">
				<input type="hidden" name="end_id" value="10000">
				<input class="button" type="submit" value="<?php echo $lang_home['map_allied']; ?>">
			</td>
		</form>
	</tr>
	<tr>
		<form method="post" action="map_to_image.php" target="_blank">
			<td class="center">
				<input type="hidden" name="begin_id" value="10000">
				<input type="hidden" name="end_id" value="20000">
				<input class="button" type="submit" value="<?php echo $lang_home['map_illandry']; ?>">
			</td>
		</form>
	</tr>
	<tr>
		<form method="post" action="map_to_image.php" target="_blank">
			<td class="center">
				<input type="hidden" name="begin_id" value="20000">
				<input type="hidden" name="end_id" value="30000">
				<input class="button" type="submit" value="<?php echo $lang_home['map_lanus']; ?>">
			</td>
		</form>
	</tr>
	<tr>
		<form method="post" action="map_to_image.php" target="_blank">
			<td class="center">
				<input type="hidden" name="begin_id" value="30000">
				<input type="hidden" name="end_id" value="40000">
				<input class="button" type="submit" value="<?php echo $lang_home['map_thanium']; ?>">
			</td>
		</form>
	</tr>
	<tr>
		<form method="post" action="map_to_image.php" target="_blank">
			<td class="center">
				<input type="hidden" name="begin_id" value="40000">
				<input type="hidden" name="end_id" value="50000">
				<input class="button" type="submit" value="<?php echo $lang_home['map_wath']; ?>">
			</td>
		</form>
	</tr>
	<tr>
		<form method="post" action="map_to_image.php" target="_blank">
			<td class="center">
				<input type="hidden" name="begin_id" value="50000">
				<input type="hidden" name="end_id" value="60000">
				<input class="button" type="submit" value="<?php echo $lang_home['map_kjelk']; ?>">
			</td>
		</form>
	</tr>
	<tr>
		<form method="post" action="map_to_image.php" target="_blank">
			<td class="center">
				<input type="hidden" name="begin_id" value="60000">
				<input type="hidden" name="end_id" value="70000">
				<input class="button" type="submit" value="<?php echo $lang_home['map_tel-khaliid']; ?>">
			</td>
		</form>
	</tr>
	<tr>
		<form method="post" action="map_to_image.php" target="_blank">
			<td class="center">
				<input type="hidden" name="begin_id" value="70000">
				<input type="hidden" name="end_id" value="80000">
				<input class="button" type="submit" value="<?php echo $lang_home['map_qu-nai']; ?>">
			</td>
		</form>
	</tr>
	<tr>
		<form method="post" action="map_to_image.php" target="_blank">
			<td class="center" colspan=2>
				<input type="hidden" name="begin_id" value="80000">
				<input type="hidden" name="end_id" value="90000">
				<input class="button" type="submit" value="<?php echo $lang_home['map_gilanthia']; ?>">
			</td>
		</form>
	</tr>
		<?php if ($char_lvl >= 15) { ?>
	<tr>
		<form method="post" action="map_to_image.php" target="_blank">
			<td class="center" colspan=2>
				<input type="hidden" name="begin_id" value="100001">
				<input type="hidden" name="end_id" value="100225">
				<input class="button" type="submit" value="<?php echo $lang_home['map_dung1']; ?>">
			</td>
		</form>
	</tr>
	<tr>
		<form method="post" action="map_to_image.php" target="_blank">
			<td class="center" colspan=2>
				<input type="hidden" name="begin_id" value="100226">
				<input type="hidden" name="end_id" value="100450">
				<input class="button" type="submit" value="<?php echo $lang_home['map_dung2']; ?>">
			</td>
		</form>
	</tr>
	<tr>
		<form method="post" action="map_to_image.php" target="_blank">
			<td class="center" colspan=2>
				<input type="hidden" name="begin_id" value="100451">
				<input type="hidden" name="end_id" value="100675">
				<input class="button" type="submit" value="<?php echo $lang_home['map_dung3']; ?>">
			</td>
		</form>
	</tr>
		<?php } 
		if ($char_lvl >= 30) { ?>
	<tr>
		<form method="post" action="map_to_image.php" target="_blank">
			<td class="center" colspan=2>
				<input type="hidden" name="begin_id" value="100686">
				<input type="hidden" name="end_id" value="103185">
				<input class="button" type="submit" value="<?php echo $lang_home['map_mines']; ?>">
			</td>
		</form>
	</tr>
		<?php } ?>
</table>

<table class="right" cellpadding=4 border=1>
	<tr class="bgcolor">
		<td colspan=2>
			<h3 class='center b'><?php echo $lang_home['guides']; ?></h3>
		</td>
	</tr>
	<tr>
		<form method="post" action="/help/chars.php" target="_blank" >
			<td class="center">
				<input class="button" type="submit" value="<?php echo $lang_home['char_guide']; ?>">
			</td>
		</form>
		<form method="post" action="/help/monsters.php" target="_blank" >
			<td class="center">
				<input class="button" type="submit" value="<?php echo $lang_home['monsters']; ?>">
			</td>
		</form>
	</tr>

	<tr class="bgcolor">
		<td colspan=2>
			<h3 class="center b"><?php echo $lang_home['itemlists']; ?></h3>
		</td>
	</tr>
	<tr>
		<form method="post" action="/help/armors.php" target="_blank" >
			<td class="center">
				<input class="button" type="submit" value="<?php echo $lang_armors;?>">
			</td>
		</form>
		<form method="post" action="/help/potions.php" target="_blank" >
			<td class="center">
				<input class="button" type="submit" value="<?php echo $lang_potions;?>">
			</td>
		</form>
	</tr>
	<tr>
		<form method="post" action="/help/boots.php" target="_blank" >
			<td class="center">
				<input class="button" type="submit" value="<?php echo $lang_boots;?>">
			</td>
		</form>
		<form method="post" action="/help/shields.php" target="_blank" >
			<td class="center">
				<input class="button" type="submit" value="<?php echo $lang_shields;?>">
			</td>
		</form>
	</tr>

	<tr>
		<form method="post" action="/help/gloves.php" target="_blank" >
			<td class="center">
				<input class="button" type="submit" value="<?php echo $lang_gloves;?>">
			</td>
		</form>
		<form method="post" action="/help/spells.php" target="_blank" >
			<td class="center">
				<input class="button" type="submit" value="<?php echo $lang_spells;?>">
			</td>
		</form>
	</tr>
	<tr>
		<form method="post" action="/help/helms.php" target="_blank" >
			<td class="center">
				<input class="button" type="submit" value="<?php echo $lang_helms;?>">
			</td>
		</form>
		<form method="post" action="/help/weapons.php" target="_blank" >
			<td class="center">
				<input class="button" type="submit" value="<?php echo $lang_weapons;?>">
			</td>
		</form>
	</tr>
</table>

<?php require_once "footer.php";
