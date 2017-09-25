<?php
require_once "header.php";

// Get Slapped! If in combat and clicked travel
if(isset($_SESSION['opponent_id'])) {
	jsChangeLocation("combat.php?comb_act=npc_attack", 0);
}

function get_item_name($item_id,$item_type) {
	global $db;
	
	if ($item_type == "armor")		{$result = $db->query("SELECT name FROM phaos_armor WHERE id = '$item_id'");}
	if ($item_type == "weapons")	{$result = $db->query("SELECT name FROM phaos_weapons WHERE id = '$item_id'");}
	if ($item_type == "gloves")		{$result = $db->query("SELECT name FROM phaos_gloves WHERE id = '$item_id'");}
	if ($item_type == "helms")		{$result = $db->query("SELECT name FROM phaos_helmets WHERE id = '$item_id'");}
	if ($item_type == "shields")	{$result = $db->query("SELECT name FROM phaos_shields WHERE id = '$item_id'");}
	if ($item_type == "boots")		{$result = $db->query("SELECT name FROM phaos_boots WHERE id = '$item_id'");}
	if ($row = $result->fetch_assoc()) {
		return ($row["name"]);
	} else {return false;}
}
function get_item_pic($item_id,$item_type) {
	global $db;
	
	if ($item_type == "armor")		{$result = $db->query("SELECT image_path FROM phaos_armor WHERE id = '$item_id'");}
	if ($item_type == "weapons")	{$result = $db->query("SELECT image_path FROM phaos_weapons WHERE id = '$item_id'");}
	if ($item_type == "gloves")		{$result = $db->query("SELECT image_path FROM phaos_gloves WHERE id = '$item_id'");}
	if ($item_type == "helms")		{$result = $db->query("SELECT image_path FROM phaos_helmets WHERE id = '$item_id'");}
	if ($item_type == "shields")	{$result = $db->query("SELECT image_path FROM phaos_shields WHERE id = '$item_id'");}
	if ($item_type == "boots")		{$result = $db->query("SELECT image_path FROM phaos_boots WHERE id = '$item_id'");}
	if ($row = $result->fetch_assoc()) {
		return ($row["image_path"]);
	} else {return 'images/icons/'.$item_type.'/na.gif';}
}
function get_armor_ac($item_id,$item_type) {
	global $db;
	
	if ($item_type == "armor")		{$result = $db->query("SELECT armor_class FROM phaos_armor WHERE id = '$item_id'");}
	if ($item_type == "gloves")		{$result = $db->query("SELECT armor_class FROM phaos_gloves WHERE id = '$item_id'");}
	if ($item_type == "helms")		{$result = $db->query("SELECT armor_class FROM phaos_helmets WHERE id = '$item_id'");}
	if ($item_type == "shields")	{$result = $db->query("SELECT armor_class FROM phaos_shields WHERE id = '$item_id'");}
	if ($item_type == "boots")		{$result = $db->query("SELECT armor_class FROM phaos_boots WHERE id = '$item_id'");}
	if ($row = $result->fetch_assoc()) {
		return ($row["armor_class"]);
	} else {return false;}
}
function get_mindam($item_id) {
	global $db;

	$result = $db->query("SELECT min_damage FROM phaos_weapons WHERE id = '$item_id'");
	if ($row = $result->fetch_assoc()) {
		return ($row["min_damage"]);
	} else {return false;}
}
function get_maxdam($item_id) {
	global $db;
	
	$result = $db->query("SELECT max_damage FROM phaos_weapons WHERE id = '$item_id'");
	if ($row = $result->fetch_assoc()) {
		return ($row["max_damage"]);
	} else {return false;}
}

// This updates activity on the user currently refreshing this page.
$login_result = $db->query("SELECT last_active FROM phaos_users WHERE username = '$PHP_PHAOS_USER'");
if ($row = $login_result->fetch_assoc()) { 
	$ts = date("Y-m-d H:i:s");
    $vts = $row["last_active"];
    if ($ts != $row["last_active"]) {
      $db->query("UPDATE phaos_users SET last_active = '$ts' WHERE username = '$PHP_PHAOS_USER'");
	}
}

$login_result = $db->query("SELECT last_active FROM phaos_users WHERE username = '$player_name'");
if ($row = $login_result->fetch_assoc()) { 
	$char_last_login = $row["last_active"]; 
}		

$rep_update = "no";
if ($rep_update == "yes") {
	$result = $db->query("SELECT rep_points FROM phaos_characters WHERE username = '$PHP_PHAOS_USER'");
	if ($row = $result->fetch_assoc()) {
		$rep_points = $row["rep_points"];
	}
	
	$result = $db->query("SELECT rep_helpfull, rep_generious, rep_combat FROM phaos_characters WHERE username = '$player_name'");
	if ($row = $result->fetch_assoc()) {
		$char_rep_helpfull = $row["rep_helpfull"];
		$char_rep_generious = $row["rep_generious"];
		$char_rep_combat = $row["rep_combat"];
	}
		
	if (isset($rate_helpfull) && $rep_points > 0) {
		echo "<div class='center'><h3 class='b msgbox'>".$lang_play["dis_1"]."$player_name</h3></div>";
		if ($rate_helpfull == "up") {
			$char_rep_helpfull = $char_rep_helpfull + 1;
		} else {
			$char_rep_helpfull = $char_rep_helpfull - 1;
		}
		$rep_points = $rep_points - 1;
	}
	if (isset($rate_generious) && $rep_points > 0) {
		echo "<div class='center'><h3 class='b msgbox'>".$lang_play["dist_2"]."$player_name</h3></div>";
		if ($rate_generious == "up") {
			$char_rep_generious = $char_rep_generious + 1;
		} else {
			$char_rep_generious = $char_rep_generious - 1;
		}
		$rep_points = $rep_points - 1;
	}
	if (isset($rate_combat) && $rep_points > 0) {
		echo "<div class='center'><h3 class='b msgbox'>".$lang_play["dist_3"]."$player_name</h3></div>";
		if ($rate_combat == "up") {
			$char_rep_combat = $char_rep_combat + 1;
		} else {
			$char_rep_combat = $char_rep_combat - 1;
		}
		$rep_points = $rep_points - 1;
	}

	$db->query("UPDATE phaos_characters SET rep_helpfull='$char_rep_helpfull', rep_generious='$char_rep_generious', rep_combat='$char_rep_combat' WHERE username = '$player_name'");
	$db->query("UPDATE phaos_characters SET rep_points='$rep_points' WHERE username = '$PHP_PHAOS_USER'");
}

// ADDME needs to be a refresh for url change, a correct username check url too
//$result = $db->query("SELECT * FROM phaos_characters WHERE username = '$player_name' OR name = '$char_name' ");
$result = $db->query("SELECT * FROM phaos_characters WHERE username = '$player_name'");
if ($row = $result->fetch_assoc()) {
	$player_name = $row["username"];
	$char_name = $row["name"];
	$char_race = $row["race"];
	$char_sex = $row["sex"];
	$char_class = $row["class"];
	$char_image = $row["image_path"];
	$char_str = $row["strength"];
	$char_dex = $row["dexterity"];
	$char_wis = $row["wisdom"];
	$char_con = $row["constitution"];
	$char_xp = $row["xp"];
	$char_level = $row["level"];
	$char_attack = $row["fight"];
	$char_defence = $row["defence"];
	$char_rep_helpfull = $row["rep_helpfull"];
	$char_rep_generious = $row["rep_generious"];
	$char_rep_combat = $row["rep_combat"];
	$char_deaths = $row["deaths"];
	$char_kills = $row["kills"];
	
	$char_weap = $row["weapon"];
	$char_armor = $row["armor"];
	$char_boots = $row["boots"];
	$char_glves = $row["gloves"];
	$char_helm = $row["helm"];
	$char_shld = $row["shield"];
	
	$char_ac = get_armor_ac($char_armor, "armor") + get_armor_ac($char_boots, "boots") + get_armor_ac($char_helm, "helms") + get_armor_ac($char_glves, "gloves") + get_armor_ac($char_shld, "shields");
	
	if ($char_level) {
	$needed_xp = $db->query("SELECT xp_needed FROM phaos_level_chart WHERE level = '".($char_level + 1)."'");
		if ($row = $needed_xp->fetch_assoc()) {
			$char_next_lev_xp = $row["xp_needed"];
		}
	}
	
	$result = $db->query("SELECT rep_points FROM phaos_characters WHERE username = '$PHP_PHAOS_USER'");
	if ($row = $result->fetch_assoc()) {
		$rep_points = $row["rep_points"];
	}
} else {
	// Search Result: User does not exist
	echo 	"<div class='center'><h3 class='b msgbox'>".$lang_play["fail_srch"]."</h3></div>";
	
	jsChangeLocation("travel.php", 0);
}
?>

<h2 class="center b">
	<?php echo $lang_play["info"], $char_name, getclan_logo($char_name);?>
</h2>
<img class="left" style="width:80px;height:80px;" src="<?php echo $char_image;?>">
<div id="charinvo" class="left">
	<table>
		<tr>
			<td>
				<img class="icon help" src="<?php echo get_item_pic($char_weap, "weapons"); ?>" title="<?php if (get_item_name($char_weap, "weapons")) {echo get_item_name($char_weap, "weapons"); ?> / Dam: <?php echo get_mindam($char_weap); ?>-<?php echo get_maxdam($char_weap);} else {echo $lang_na;} ?>">
			</td>
			<td>
				<img class="icon help" src="<?php echo get_item_pic($char_helm, "helms"); ?>" title="<?php if (get_item_name($char_helm, "helms")) {echo get_item_name($char_helm, "helms"); ?>: +<?php echo get_armor_ac($char_helm, "helms"), $lang_ac;} else {echo $lang_na;} ?>">
			</td>
			<td>
				<img class="icon help" src="<?php echo get_item_pic($char_shld, "shields"); ?>" title="<?php if (get_item_name($char_shld, "shields")) {echo get_item_name($char_shld, "shields"); ?>: +<?php echo get_armor_ac($char_shld, "shields"), $lang_ac;} else {echo $lang_na;} ?>">
			</td>
		</tr>
		<tr>
			<td></td>
			<td>
				<img class="icon help" src="<?php echo get_item_pic($char_armor, "armor"); ?>" title="<?php if (get_item_name($char_armor, "armor")) {echo get_item_name($char_armor, "armor"); ?>: +<?php echo get_armor_ac($char_armor, "armor"), $lang_ac;} else {echo $lang_na;} ?>">
			</td>
			<td>
				<img class="icon help" src="<?php echo get_item_pic($char_glves, "gloves"); ?>" title="<?php if (get_item_name($char_glves, "gloves")) {echo get_item_name($char_glves, "gloves"); ?>: +<?php echo get_armor_ac($char_glves, "gloves"), $lang_ac;} else {echo $lang_na;} ?>">
			</td>
		</tr>
		<tr>
			<td></td>
			<td>
				<img class="icon help" src="<?php echo get_item_pic($char_boots, "boots"); ?>" title="<?php if (get_item_name($char_boots, "boots")) {echo get_item_name($char_boots, "boots"); ?>: +<?php echo get_armor_ac($char_boots, "boots"), $lang_ac;} else {echo $lang_na;} ?>">
			</td>
		</tr> 
	</table>
</div>

<table align="center" class="b" cellspacing=3 cellpadding=0>
	<tr>
		<td class="right">
			<?php echo $lang_username; ?>:
		</td>
		<td>
			<?php echo $player_name; ?>
		</td>
	</tr>
	<tr>
		<td class="right">
			<?php echo $lang_sex; ?>:
		</td>
		<td>
			<?php echo $char_sex; ?>
		</td>
	</tr>
	<tr>
		<td class="right">
			<?php echo $lang_race; ?>:
		</td>
		<td>
			<?php echo $char_race; ?>
		</td>
	</tr>
	<tr>
		<td class="right">
			<?php echo $lang_class; ?>:
		</td>
		<td>
			<?php echo $char_class; ?>
		</td>
	</tr>

	<tr>
		<td class="right">
			<?php echo $lang_str; ?>:
		</td>
		<td>
			<?php echo $char_str; ?>
		</td>
	</tr>
	<tr>
		<td class="right">
			<?php echo $lang_dex; ?>:
		</td>
		<td>
			<?php echo $char_dex; ?>
		</td>
	</tr>
	<tr>
		<td class="right">
			<?php echo $lang_wis; ?>:
		</td>
		<td>
			<?php echo $char_wis; ?>
		</td>
	</tr>
	<tr>
		<td class="right">
			<?php echo $lang_cons; ?>:
		</td>
		<td>
			<?php echo $char_con; ?>
		</td>
	</tr>
	<tr>
		<td class="right">
			<?php echo $lang_tot_ac; ?>:
		</td>
		<td>
			<?php echo $char_ac; ?>
		</td>
	</tr>
	<tr>
		<td class="right">
			<?php echo $lang_att; ?>:
		</td>
		<td>
			<?php echo $char_attack; ?>
		</td>
	</tr>
	<tr>
		<td class="right">
			<?php echo $lang_def; ?>:
		</td>
		<td>
			<?php echo $char_defence; ?>
		</td>
	</tr>
	<tr>
		<td class="right">
			<?php echo $lang_level; ?>:
		</td>
		<td>
			<?php if (!$char_level >= 1) { echo "1"; } else { echo $char_level; } ?>
		</td>
	</tr>
	<tr>
		<td class="right">
			<?php echo $lang_expe; ?>:
		</td>
		<td>
			<?php echo $char_xp; ?>/<?php echo $char_next_lev_xp; ?>		
		</td>
	</tr>
	<tr>
		<td class="right">
			<?php echo $lang_deaths; ?>:
		</td>
		<td>
			<?php echo $char_deaths; ?>
		</td>
	</tr>
	<tr>
		<td class="right">
			<?php echo $lang_kills; ?>:
		</td>
		<td>
			<?php echo $char_kills; ?>	
		</td>
	</tr>
	<tr>
		<td class="right">
			<?php echo $lang_added["last_login"]; ?>:
		</td>
		<td>
			<?php if (substr($char_last_login,0,19) == '0000-00-00 00:00:00') {echo $lang_na;} else {echo substr($char_last_login,0,19);} ?>
		</td>
	</tr>	
	<tr>
		<td class="center" colspan=4>
			<br><big><?php echo $lang_char["rep"]; ?></big>
		</td>
		<td>
			<?php
			if ($rep_points > 0 && $player_name != $PHP_PHAOS_USER) {
			?>
		</td>
	</tr>
	<tr>
		<td class="center" colspan=4>
			<?php //echo $lang_char["have_rep"]; ?>
		</td>
	</tr>
	<tr>
		<td class="center bgcolor" colspan=4>
			<?php echo $lang_play["rate"]; ?>
		</td>
	</tr>
	<tr>
		<td class="right">
			<?php echo $lang_char["help"]; ?>:
		</td>
		<td class="top">
			<?php echo $char_rep_helpfull; ?>
		</td>
		<td class="center">
			<a title="<?php echo $lang_play["rateup"];?>" href="player_info.php?player_name=<?php echo $player_name; ?>&rep_update=yes&rate_helpfull=up">
				<img class="thumb-icon" src="images/icons/thumbsup.png"/>
			</a>
		</td>
		<td class="center">
			<a title="<?php echo $lang_play["ratedown"];?>" href="player_info.php?player_name=<?php echo $player_name; ?>&rep_update=yes&rate_helpfull=down">
				<img class="thumb-icon" src="images/icons/thumbsdown.png"/>
			</a>
		</td>
	</tr>
	<tr>
		<td class="right">
			<?php echo $lang_char["gen"]; ?>:
		</td>
		<td class="top">
			<?php echo $char_rep_generious; ?>
		</td>
		<td class="center">
			<a title="<?php echo $lang_play["rateup"];?>" href="player_info.php?player_name=<?php echo $player_name; ?>&rep_update=yes&rate_generious=up">
				<img class="thumb-icon" src="images/icons/thumbsup.png"/>
			</a>
		</td>
		<td class="center">
			<a title="<?php echo $lang_play["ratedown"];?>" href="player_info.php?player_name=<?php echo $player_name; ?>&rep_update=yes&rate_generious=down">
				<img class="thumb-icon" src="images/icons/thumbsdown.png"/>
			</a>
		</td>
	</tr>
	<tr>
		<td class="right">
			<?php echo $lang_char["com"]; ?>:
		</td>
		<td class="top">
			<?php echo $char_rep_combat; ?>
		</td>
		<td class="center">
			<a title="<?php echo $lang_play["rateup"];?>" href="player_info.php?player_name=<?php echo $player_name; ?>&rep_update=yes&&rate_combat=up">
				<img class="thumb-icon" src="images/icons/thumbsup.png"/>
			</a>
		</td>
		<td class="center">
			<a title="<?php echo $lang_play["ratedown"];?>" href="player_info.php?player_name=<?php echo $player_name; ?>&rep_update=yes&rate_combat=down">
				<img class="thumb-icon" src="images/icons/thumbsdown.png"/>
			</a>
		</td>
	</tr>
	<tr>
		<td class="right">
			<hr><?php echo $lang_char["total"]; ?>:<hr>
		</td>
		<td>
			<hr><?php echo $char_rep_helpfull + $char_rep_generious + $char_rep_combat; ?><hr>
		</td>
	</tr>
	<?php
	} else {
	?>
	<tr>
		<td class="right">
			<?php echo $lang_char["help"]; ?>:
		</td>
		<td>
			<?php echo $char_rep_helpfull; ?>
		</td>
	</tr>
	<tr>
		<td class="right">
			<?php echo $lang_char["gen"]; ?>:
		</td>
		<td>
			<?php echo $char_rep_generious; ?>
		</td>
	</tr>
	<tr>
		<td class="right">
			<?php echo $lang_char["com"]; ?>:
		</td>
		<td>
			<?php echo $char_rep_combat; ?>
		</td>
	</tr>
	<tr>
		<td class="right">
			<hr><big><?php echo $lang_char["total"]; ?>:</big><hr>
		</td>
		<td>
			<hr><big><?php echo $char_rep_helpfull + $char_rep_generious + $char_rep_combat; ?></big><hr>
		</td>
	</tr>

	<?php
	}
	?>
</table>

<div class="center">
	<div>
		<form align="center" style="display:block;padding-top:20px;padding-left:50px;" enctype="multipart/form-data" method="post" action="">
			<input type="text" name="player_name" size="20" maxlength="20" placeholder="<?php echo $lang_f_names;?>">
			<input class="button" type="submit" value="<?php echo $lang_search;?>">
		</form>
	</div>
	<div>
		<?php
		if ($player_name != $PHP_PHAOS_USER) { ?> 
			<form style="padding-top:8px;">
				<input class="button" type="button" onClick="location='message.php?action=compose&to=<?php echo $player_name; ?>';this.value='<?php echo $lang_searching;?>'" value='<?php echo $lang_mssg["ad_user-msg"]; ?>'>
			</form>
		<?php } ?>
	</div>
	<div>
		<br>
		<form>
			<input class="button" type="button" onClick="history.go(-1);return true;this.value='<?php echo $lang_leaving;?>'" value="<?php echo $lang_goback;?>">
		</form>
	</div>
</div>

<?php require_once "footer.php";