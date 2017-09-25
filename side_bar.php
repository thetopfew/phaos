<?php
require_once "class_character.php"; // Loads here once, for all other pages that require sidebar

$current_time = time();
$refresh = false; // Ensure post data has to be sent before ya refresh

// Getting Character Data (one Time at all!)
$character = new character($PHP_PHAOS_CHARID);

// If no character created, skip to end of this.
if ($character->location != "") {

	if (!$character->name == "") {	
		$character->auto_heal();
		$character->auto_stamina();
		$character->auto_reputation();
		
		if (DEBUG) { echo "<p class='center b small'>**DEBUG: Last Regen ".$character->time_since_regen."secs ago</p>"; }

		// Start Drink Potion Code
		$drink_potion = @$_POST["drink_potion"];
		if ($drink_potion) {
			$refresh = true;
			$character->fast_potion();
		}

		// Start Deploying New Lvl Stats
		$str_add = @$_POST['strength'];
		$dex_add = @$_POST['dexterity'];
		$wis_add = @$_POST['wisdom'];
		$con_add = @$_POST['constitution'];
		
		if ($str_add == "add" AND $character->stat_points > 0) {
			if (!$character->level_up("strength")) {
				echo "an error has occured";
				exit();
			}
			$refresh = true;
		}
		if ($dex_add == "add" AND $character->stat_points > 0) {
			if (!$character->level_up("dexterity")) {
				echo "an error has occured";
				exit();			
			}
			$refresh = true;
		}
		if ($wis_add == "add" AND $character->stat_points > 0) {
			if (!$character->level_up("wisdom")) {
				echo "an error has occured";
				exit();
			}
			$refresh = true;
		}
		if ($con_add == "add" AND $character->stat_points > 0) {
			if (!$character->level_up("constitution")) {
				echo "an error has occured";
				exit();
			}
			$refresh = true;
		}
		// end Deploying Points to Stats

		//$overtake=($character->level()+1);
		//$next_lev = ($overtake);

		if ($character->level() < 1000) {
			$needed_xp = $db->query("SELECT xp_needed FROM phaos_level_chart WHERE level = '".($character->level() + 1)."'");
			if ($row = $needed_xp->fetch_assoc()) {
				$char_next_lev_xp = $row["xp_needed"];
			}
		
			if ($character->xp >= $char_next_lev_xp) {
				$query = ("UPDATE phaos_characters SET hit_points = '".($character->max_hp + 2)."', stat_points = '".(3 + $character->stat_points)."', level = '".(1 + $character->level())."' WHERE username = '$PHP_PHAOS_USER'");
				$req = $db->query($query);
				if (!$req) { echo "<B>Error ".mysqli_errno()." :</B> ".mysqli_error().""; exit; }
				$refresh = true;
			}
		}
	}

	// If $_POST data has been sent and used, clear $_POST!
	if ($refresh) {
		$refresh = false;
		if ($_SESSION['endcombat'] == false) {
			jsChangeLocation("combat.php?comb_act=npc_attack", 0);
		} else { jsRefreshURL(); }
	}

	$stamina_color = ($character->fight_reduction() > 0.99 ? '#FFF':( ($character->fight_reduction() > 0.66? '#FFF' : ($character->fight_reduction() < 0.34?'#FF0000':'#FFFF00'))));

	?>
	<br>
	<table class="fullsize" cellspacing=0 cellpadding=0>
		<tr>
			<td id="charnameside" class="center" colspan=2>
				<?php getclan_logo($character->name); ?> 
				<?php echo "<a href='player_info.php?player_name=".$PHP_PHAOS_USER."' title='$lang_v_char' >".$character->name."</a>"; ?> 
			</td>
		</tr>
		<tr>
			<td class="center" colspan=2>
				<a href="message.php?action=inbox"></a><?php
				$res = $db->query("SELECT * FROM phaos_mail WHERE UserTo='$PHP_PHAOS_USER' AND STATUS='unread'");
				$newmail = $res->num_rows;
				if ($newmail > 0) {
					echo "<a href='message.php?action=inbox' title='$lang_side[unread_mail]'>[ ".$newmail." ] ".$lang_mssg["un_co"]."</a> <img src='images/unread.gif'><br>";
				} else {
					echo "<a href='message.php?action=inbox' title='$lang_side[read_mail]'>".$lang_mssg["inbox"]."</a> <img src='images/read.gif'><br>";
				}
				?>
			</td>
		</tr>
		<tr>
			<td colspan=2>
				<hr>
			</td>
		</tr>
		<tr>
			<td class="center" colspan=2>
				<img id="statspic" src="lang/<?php echo $lang ?>_images/stats.png">
			</td>
		</tr>
		<?php // Only shows available stat points if there are any.
		if ($character->stat_points >= 1) { ?>
		<tr>
			<td colspan=2 class="center">
				<span class="b"><?php echo $lang_side["unplaced_s"], $character->stat_points; ?></span>
			</td>
		</tr>
		<?php
		}
		?>
		<tr>
			<td class="right">
				<span class="b"><?php echo $lang_hp; ?></span> <img class="sideicon" src="images/icons/hp.gif">
			</td>
			<td>
				<span class="b"><?php echo $character->hit_points; ?>/<?php echo $character->max_hp; ?></span>
			</td>
		</tr>
		<tr>
			<td class="right">
				<span class="b"><?php echo $lang_str; ?></span> <img class="sideicon" src="images/icons/strength.gif">
			</td>
			<form method="post">
				<td>
					<span class="b"><?php echo $character->strength; ?></span>
					<?php
					if ($character->stat_points > 0) {
						echo "<input class='stat-button' type='submit' value='+'>";
					}
					?>
					<input type="hidden" name="strength" value="add">
				</td>
			</form>
		</tr>
		<tr>
			<td class="right">
				<span class="b"><?php echo $lang_dex; ?></span> <img class="sideicon" src="images/icons/dexterity.gif">
			</td>
			<form method="post">
			<td>
				<span class="b"><?php echo $character->dexterity; ?></span>
				<?php
				if ($character->stat_points > 0) {
					echo "<input class='stat-button' type='submit' value='+'>";
				}
				?>
				<input type="hidden" name="dexterity" value="add">
			</td>
			</form>
		</tr>
		<tr>
			<td class="right">
				<span class="b"><?php echo $lang_wis; ?></span> <img class="sideicon" src="images/icons/wisdom.gif">
			</td>
			<form method="post">
			<td>
				<span class="b"><?php echo $character->wisdom; ?></span>
				<?php
				if ($character->stat_points > 0) {
					echo "<input class='stat-button' type='submit' value='+'>";
				}
				?>
				<input type="hidden" name="wisdom" value="add">
			</td>
			</form>
		</tr>
		<tr>
			<form method="post">
				<td class="right">
					<span class="b"><?php echo $lang_cons; ?></span> <img class="sideicon" src="images/icons/constitution.gif">
				</td>
				<td>
					<span class="b"><?php echo $character->constitution;?></span>
					<?php
					if ($character->stat_points > 0) {
						echo "<input class='stat-button' type='submit' value='+'>";
					}
					?>
					<input type="hidden" name="constitution" value="add">
				</td>
			</form>
		</tr>
		<tr>
			<td class="right">
				<span class="b"><?php echo $lang_tot_ac; ?></span> <img class="sideicon" src="images/icons/wins.gif">
			</td>
			<td>
				<span class="b"><?php echo $character->ac(); ?></span>
			</td>
		</tr>
		<tr>
			<td class="right">
				<span class="b"><?php echo $lang_att; ?> </span> <img class="sideicon" src="images/icons/attack.gif">
			</td>
			<td>
				<span class="b"><?php echo $character->fight; ?></span>
			</td>
		</tr>
		<tr>
			<td class="right">
				<span class="b"><?php echo $lang_def; ?> </span> <img class="sideicon" src="images/icons/defense.gif">
			</td>
			<td>
				<span class="b"><?php echo $character->defence;?></span>
			</td>
		</tr>
		<tr>
			<td class="right">
				<span class="b"><?php echo $lang_gold; ?></span><img class="sideicon" src="images/icons/gold_side.gif">
			</td>
			<td>
				<span class="b"><?php echo $character->gold;?></span>
			</td>
		</tr>
		<tr>
			<td colspan=2>
				<hr>
			</td>
		</tr>
		<tr>
			<td class="center b" colspan=2>
				<span><?php echo $lang_level; ?>: &nbsp;<?php echo $character->level; ?></span>
			</td>
		</tr>
		<tr>
			<td colspan=2 class="center">
			<?php
			$res = $db->query("SELECT xp_needed FROM phaos_level_chart WHERE level = '".$character->level."'");
			if ($row = $res->fetch_assoc()) {
				$current_lvl_xp = $row["xp_needed"];
			}
			
			$res = $db->query("SELECT xp_needed FROM phaos_level_chart WHERE level = '".($character->level + 1)."'");
			if ($row = $res->fetch_assoc()) {
				$next_lvl_xp = $row["xp_needed"];
			}
			
			$needed_xp = $next_lvl_xp - $current_lvl_xp;
			?>
				<meter id="lvl-bar" class="halfsize" min="0" max="<?php echo $needed_xp;?>" value="<?php echo ($character->xp - $current_lvl_xp); ?>" title="<?php echo $character->xp - $current_lvl_xp.$lang_side["nextlvl"].$needed_xp; ?>">
					<span id="">
						<?php echo ($character->xp - $current_lvl_xp); ?>
					</span>
				</meter>
			</td>
		</tr>
		<tr>
			<td class="center b" colspan=2>
				<?php echo $lang_stamina; ?>: &nbsp;
				<span class="b" style="color:<?php echo $stamina_color; ?>"><?php echo $character->stamina_points."/".$character->max_stamina;?></span>
			</td>
		</tr>
		<tr>
			<td colspan=2>
				<hr>
			</td>
		</tr>
		<tr>
			<td class="right" colspan=1>
				<form method="post" action="<?php echo $this_url; ?>">
					<input type="hidden" name="drink_potion" value="Y">
					<input class="button" type="submit" value="<?php echo $lang_side["fast-potion"]; ?>">
				</form>
				<?php
					// Show lowest potion if any for fast action
					$potion = $db->query("SELECT * FROM phaos_char_inventory WHERE item_id = (SELECT MIN(item_id) FROM phaos_char_inventory WHERE username = '".$character->user."' AND type='potion' AND sell_to='')");
					if ($potion->num_rows) {
						$row = $potion->fetch_assoc();
						
						$res = $db->query("SELECT COUNT(item_id) FROM phaos_char_inventory WHERE username='".$character->user."' AND item_id='$row[item_id]' AND type='potion' AND sell_to=''");
						list($count) = $res->fetch_row();

						$result = $db->query("SELECT name,image_path,heal_amount FROM phaos_potion WHERE id='$row[item_id]'");
						list($description,$image_path,$heal_amount) = $result->fetch_row();

						echo "<td class='center'>
								<a href='character.php?act=potion#inventory'>
									<input class='icon help bottom' type='image' width='20px' height='20px' src='$image_path' title='$description +$heal_amount hp'><br><small>($count)</small>
								</a>
							</td>";
					} else {
						echo "<td class='center'><input class='icon iconred help bottom' type='image' width='20px' height='20px' src='images/icons/potions/lesserhealing.gif' title='".$lang_side["no-potion"]."'><br><small>(0)</small></td>";
					}
				?>
			</td>
		</tr>
		<tr>
			<td class="center" colspan=3><br>
				<a href="character.php" title="<?php echo $lang_side["goto_invo"];?>"><h4 class="b"><?php echo $lang_side["curr-eq"]; ?></h4></a>
				<table align=center>
					<tr>
						<td>
							<a href="character.php?act=weapon#inventory">
								<img class="icon help" src='<?php echo $character->get_eq_item_pic("weapons"); ?>' title='<?php if ($character->get_eq_item_name("weapon")) {echo $character->get_eq_item_name("weapon"); ?> / Dam: <?php echo $character->get_eq_weapon_mindam("weapons"); ?>-<?php echo $character->get_eq_weapon_maxdam("weapons");} else {echo $lang_na;} ?>'>
							</a>
						</td>
						<td>
							<a href="character.php?act=helm#inventory">
								<img class="icon help" src='<?php echo $character->get_eq_item_pic("helms"); ?>' title='<?php if ($character->get_eq_item_name("helm")) {echo $character->get_eq_item_name("helm"); ?>: +<?php echo $character->get_eq_armor_ac("helms"),$lang_ac;} else {echo $lang_na;} ?>'>
							</a>
						</td>
						<td>
							<a href="character.php?act=shield#inventory">
								<img class="icon help" src='<?php echo $character->get_eq_item_pic("shields"); ?>' title='<?php if ($character->get_eq_item_name("shield")) {echo $character->get_eq_item_name("shield"); ?>: +<?php echo $character->get_eq_armor_ac("shields"),$lang_ac;} else {echo $lang_na;} ?>'>
							</a>
						</td>
					</tr>
					<tr>
						<td></td>
						<td>
							<a href="character.php?act=armor#inventory">
								<img class="icon help" src='<?php echo $character->get_eq_item_pic("armor"); ?>' title='<?php if ($character->get_eq_item_name("armor")) {echo $character->get_eq_item_name("armor"); ?>: +<?php echo $character->get_eq_armor_ac("armor"),$lang_ac;} else {echo $lang_na;} ?>'>
							</a>
						</td>
						<td>
							<a href="character.php?act=gloves#inventory">
								<img class="icon help" src='<?php echo $character->get_eq_item_pic("gloves"); ?>' title='<?php if ($character->get_eq_item_name("gloves")) {echo $character->get_eq_item_name("gloves"); ?>: +<?php echo $character->get_eq_armor_ac("gloves"),$lang_ac;} else {echo $lang_na;} ?>'>
							</a>
						</td>
					</tr>
					<tr>
						<td></td>
						<td>
							<a href="character.php?act=boots#inventory">
								<img class="icon help" src='<?php echo $character->get_eq_item_pic("boots"); ?>' title='<?php if ($character->get_eq_item_name("boots")) {echo $character->get_eq_item_name("boots"); ?>: +<?php echo $character->get_eq_armor_ac("boots"),$lang_ac;} else {echo $lang_na;} ?>'>
							</a>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td class="center" colspan=2>
				<span class="b"><?php echo $lang_shop["inv"]; ?>:</span>
			</td>
		</tr>
		<tr>
			<td class="center" colspan=2>
				<?php
				if ($character->invent_count() > $character->max_inventory) {
					echo "<span class='b red'>".$character->invent_count()."/".$character->max_inventory." ".$lang_itt."</span>"; 
				} else {
					echo "<span class='b'>".$character->invent_count()."/".$character->max_inventory." ".$lang_itt."</span>";
				}
				?>
			</td>
		</tr>
	</table>
<?php
// Skip all & display this is no character exists.
} else {
	?>
	<h4 class="center middle b">
		<br><?php echo $lang_side["begin"];?>
	</h4>
	<br><br>
	<div class="center"><form><input class="button" type="button" value="<?php echo $lang_char["create"];?>" onClick="location='create_character.php'"></form></div>
<?php
}
