<?php
require_once "header.php"; 
require_once 'quest_functions.php';

$character = new character($PHP_PHAOS_CHARID);
shop_valid($character->location, $shop_id);
$reload = false;

// Adjust Inn Costs
$staycost = 100;
$drnkcost = 15;

if ($character->gold >= $staycost && @$_REQUEST['spend_night']) {
	if ($character->hit_points == $character->max_hp && $character->stamina_points == $character->max_stamina) {
		echo "<div class='center'><h3 class='b msgbox'>".$lang_inn["charfull"]."</h3></div>";
	} else {
		$character->stamina_points += $character->max_stamina;
		$character->hit_points += $character->max_hp;
		$character->gold -= $staycost;
		$reload = true;
	}
}

if ($character->gold >= $drnkcost && @$_REQUEST['have_drink']) {
	if ($character->hit_points == $character->max_hp && $character->stamina_points == $character->max_stamina) {
		echo "<div class='center'><h3 class='b msgbox'>".$lang_inn["charfull"]."</h3></div>";
	} else {
		$character->hit_points += $character->race=='Dwarf'?60:40; // Dwarves Special: get 20 more than the rest:40
		$character->stamina_points += ($character->race=='Human'?120:80);
		$character->gold -= $drnkcost;
		$reload = true;
	}
}

if ($reload) {
	// Prevents going over max hp / stam
	if ($character->hit_points > $character->max_hp) { $character->hit_points = $character->max_hp; }
	if ($character->stamina_points > $character->max_stamina) {	$character->stamina_points = $character->max_stamina; }

	// Do updates for all actions
	$query = ("UPDATE phaos_characters SET hit_points = $character->hit_points, stamina = $character->stamina_points, gold = $character->gold WHERE id = '$character->id'");
	$req = $db->query($query);
	if (!$req) {
		showError(__FILE__,__LINE__,__FUNCTION__,$query);
		exit;
	}
	echo "<div class='center'><h3 class='b msgbox'>".$lang_inn["refreshed"]."</h3></div>";
}
?>

<div>
	<div class="left" style="display:inline-block;width:15%;">
		<img src="images/inn.png">
	</div>
	<div class="center" style="display:inline-block;width:70%;">
		<img src="lang/<?php echo $lang ?>_images/inn.png">
	</div>
	<div class="right" style="display:inline-block;width:15%;">
		<img src="images/inn.png">
	</div>
</div>
<br>
<table cellspacing=0 cellpadding=5>
	<tr>
		<td align=center>
			<span class="b"><?php echo $lang_inn["keeper"];?></span>
		</td>
	</tr>
	<tr>
		<td>
			<form action="inn.php?shop_id=<?php echo $shop_id;?>" method="post"> 
				<input type="hidden" name="spend_night" value="yes"> 
				<input type="hidden" name="shop_id" value="<?php echo $shop_id; ?>"> 
				<input class="button" type="submit" value="<?php echo $lang_inn["spnd_night"], $staycost, $lang_inn["gp"]; ?>" onClick="this.value = '<?php echo $lang_inn["act_sleep"];?>'" style="width:200px;">
			</form>
		</td>
	</tr>
	<tr>
		<td>
			<form action="inn.php?shop_id=<?php echo $shop_id;?>" method="post"> 
				<input type="hidden" name="have_drink" value="yes"> 
				<input type="hidden" name="shop_id" value="<?php echo $shop_id; ?>">
				<input class="button" type="submit" class="button" value="<?php echo $lang_inn["hav_drnk"], $drnkcost, $lang_inn["gp"]; ?>" onClick="this.value = '<?php echo $lang_inn["act_drink"];?>'" style="width:200px;">
			</form>
		</td>
	</tr>
	<tr>
		<td>
			<form action="game_dice.php" method="post">
				<input class="button" type="submit" class="button" value="<?php echo $lang_inn["ply_dic"]; ?>" onClick="this.value = '<?php echo $lang_inn["act_gamble"];?>'" style="width:200px;">
			</form>
		</td>
	</tr>
	<!-- game_2.php -->
	<tr>
		<td align=left>
			<hr>
			<p class="b u"><?php echo $lang_inn["npcs"]; ?></p>
			<?php
			//echo who_is_online($char_loc); //FIXME, make it only show if other player is actually in the INN. //added by kaspir

			$npc_id = @$_POST['npc_id'];
			$rumors_yn = @$_POST['rumors'];
			$quests_yn = @$_POST['quests'];

			if (!$npc_id) {
				// Select NPC interaction
				$result = $db->query("SELECT id,name,image_path FROM phaos_npcs WHERE location = '$character->location'");
				if ($row = $result->fetch_assoc()) {
					$id_npc = $row["id"];
					$npc_name = $row["name"];
					$npc_image = $row["image_path"];
					?>
					<div>
						<form action="inn.php?shop_id=<?php echo $shop_id;?>" method="post">
							<input type="hidden" name="npc_id" value="<?php echo $id_npc;?>">
							<button class="button" type="submit">
								<?php
								if ($npc_image != "") {
									echo "<img src='$npc_image'><br>";
								}
								echo $npc_name;
								?>
							</button>
						</form>
					</div>
					<?php
				} else {
					// No quest NPC found.
					echo $lang_inn["inn_empty"];
				}
			} else {
				// NPC Conversation
				$result = $db->query("SELECT * FROM phaos_npcs WHERE id = '$npc_id'");
				if ($row = $result->fetch_assoc()) {
					$id_npc = $row["id"];
					$npc_name = $row["name"];
					$npc_image = $row["image_path"];
					$rumors = $row["rumors"];
					$quest = $row["quest"];
					?>
					
					<div class="left">
						<button class="button" type="button">
							<?php
							if ($npc_image != "") {
								echo "<img src='$npc_image'><br>";
							}
							echo $npc_name;
							?>
						</button>
						<form action="inn.php?shop_id=<?php echo $shop_id;?>" method="post">
							<button class="button" type="submit">
								<input type="hidden" name="npc_id" value="">
								<input type="hidden" name="<?php echo $shop_id;?>" value="<?php echo $shop_id;?>">
								<?php echo $lang_inn["gdbye"]; ?>
							</button>
						</form>
					</div>
					
					<div class="left">
						<form action="inn.php?shop_id=<?php echo $shop_id;?>" method="post">
							<button class="button" type="submit">
								<input type="hidden" name="rumors" value="yes">
								<input type="hidden" name="npc_id" value="<?php echo $id_npc;?>">
								<input type="hidden" name="<?php echo $shop_id;?>" value="<?php echo $shop_id;?>">
								<?php echo $lang_inn["heard_rumor"]; ?>
							</button>
						</form>
						<form action="inn.php?shop_id=<?php echo $shop_id;?>" method="post">
							<button class="button" type="submit">
								<input type="hidden" name="quests" value="yes">
								<input type="hidden" name="npc_id" value="<?php echo $id_npc;?>">
								<input type="hidden" name="<?php echo $shop_id;?>" value="<?php echo $shop_id;?>">
								<?php echo $lang_inn["look_stg"]; ?>
							</button>
						</form>
					</div>
			</td>
		</tr>
					<?php
				}
			}
		?>
		<tr>
			<td>
			<hr>
			<?php
			//-- Accept button
			if (@$_POST['acceptq'] == "yes") {
				addquest($character->id, $quest);
			}
			//-- Check Quest button
			if (@$_POST['checkq'] == "yes") { 
				checkquest($character->id, $quest);
				
				//echo "<br><br>DEBUG Questid = $quest"; // DEBUG: show $quest for confirmation
			}

			if ($rumors_yn) {
				if ($rumors == "") {
					echo "<h4 class='b'>".$lang_inn["sorry_no"]."</h4>";
				} else {
					echo "<h4 class='b'>$rumors</h4>";
				}
			}
			if ($quests_yn) {
				if ($quest == "0") {
					echo "<h4 class='b'>".$lang_inn["sorry_no"].".</h4>";
			} else {
				$res = $db->query("SELECT narrate,waitmsg FROM phaos_quests WHERE questid=$quest LIMIT 1;");
				$questrow = $res->fetch_assoc();
				
				if (candoquest($character->id, $quest)== -1) {
					echo "<h4 class='b'>".$lang_inn["sorry_no_bus"]."</h4>";
				}
				if (candoquest($character->id, $quest)== -2) {
					echo "<h4 class='b'>".$lang_inn["u2weak"]."</h4>";
				}
				if (candoquest($character->id, $quest)== -3) {
					echo "<h4 class='b'>".$lang_inn["2many_war"].".</h4>";
				}
				if (candoquest($character->id, $quest)== -4) {
					?>
					<h4 class="b"><?php echo $questrow["waitmsg"];?></h4>
					<form action="" method="post" style="padding-top:10px;">
						<button class="button" type="submit" style="text-align:left;">
							<?php echo $lang_inn["checkq"]; ?>
							<input type="hidden" name="checkq" value="yes">
							<input type="hidden" name="npc_id" value="<?php echo $id_npc;?>">
							<input type="hidden" name="<?php echo $shop_id;?>" value="<?php echo $shop_id;?>">
						</button>
					</form>
					<?php
				}
				if (candoquest($character->id, $quest) == 1) { ?>
					<h4 class="b"><?php echo $questrow["narrate"];?></h4>
					<form class="center" action="" method="post">
						<button class="button" type="submit">
							<?php echo $lang_inn["acceptq"]; ?>
							<input type="hidden" name="acceptq" value="yes">
							<input type="hidden" name="npc_id" value="<?php echo $id_npc;?>">
							<input type="hidden" name="<?php echo $shop_id;?>" value="<?php echo $shop_id;?>">
						</button>
					</form>
				<?php
				}
			  }
			}
			$err = candoquest($character->id, $quest = 0);
			//echo  "<p align="right">DEBUG candoquest: $err</p>"; //for debug
			?>
		</td>
	</tr>
</table>

<div class="center">
	<form style="padding:10px;"><input class="button" type="button" value="<?php echo $lang_backtown;?>" onClick="location='town.php';this.value='<?php echo $lang_leaving;?>'"></form>
</div>

<?php require_once "footer.php";
