<?php
require_once "header.php";
require_once "shop_functions.php"; // also loads class_character.php

$character = new character($PHP_PHAOS_CHARID);
shop_valid($character->location, $shop_id);
$reload = false;

if (@$_REQUEST['spell_items']) {
	$result = $db->query("SELECT buy_price,req_skill FROM phaos_spells_items WHERE id = '$spell_items'");
	if ($row = $result->fetch_assoc()) {
		$price = $row["buy_price"];
		$req = $row["req_skill"];
	}
	if ($character->wisdom * 1.25 < $req) {  // Latest change: chars may buy spells up to 125% their wisdom, even if it means they fumble, they can try!
		$sorry = $lang_shop["sorry_w"];
	} elseif ($price * $number_purchased <= $character->gold) {
		// Disallow negative qty of scrolls.
		if ($character->pay($price * $number_purchased)) {
			if ($number_purchased < 0) {
				$character->pay( -$price * $number_purchased);
				$number_purchased = 0;
				$sorry = $lang_shop["negative"];
			}
			$i = 0;
			while ($i < $number_purchased) {
				$i++;
				$character->add_item($spell_items,"spell_items");
				$reload = true;
			}
		} else {
			$sorry = $lang_shop["sorry"];
		}
	} else {
		$sorry = $lang_shop["sorry"];
	}
}
if ($reload) {
	$reload = false; // Reset status
	jsChangeLocation("magic_shop.php?shop_id=$shop_id", 2);
}
?>

<div>
	<div class="left" style="display:inline-block;width:15%;">
		<img src="images/magicshop.png">
	</div>
	<div class="center" style="display:inline-block;width:70%;">
		<img src="lang/<?php echo $lang ?>_images/magic_shop.png">
	</div>
	<div class="right" style="display:inline-block;width:15%;">
		<img src="images/magicshop.png">
	</div>
</div>

<table class="center fullsize" cellspacing=5 cellpadding=0>
	<tr>
		<td colspan=4>
			<p class="b"><?php echo $lang_shop["mgc_keeper"];?>
		</td>
	</tr>
	<?php 
	if (@$sorry) {
		echo "<h3 class='center b'>".$sorry."</h3>";
	}

/*	// Close Shop to players with full inventory option.
	if ($character->invent_count() > $character->max_inventory + 50) {
		echo "<h3 class='center b'>$lang_shop[inv_full]</h3>"; 
		?>
		<div class='center'>
			<form style='padding:10px;'><input class='button' type='button' value='<?php echo $lang_backtown;?>' onClick='location="town.php";this.value="<?php echo $lang_leaving;?>"'></form>
		</div>
		<?php
		exit;
	}
*/
	?>
	<tr class="bgcolor">
		<td colspan=5>
			<div class="center b"><?php echo $lang_shop["spellinvo"];?></div>
		</td>
	</tr>
	<tr>
		<td class="center" colspan=5>
			<table class="center fullsize" cellspacing=0 cellpadding=2>
				<tr>
					<?php // Show current magic in inventory
					$result = $db->query("SELECT id,item_id,count(item_id) FROM phaos_char_inventory WHERE username = '".$character->user."' AND type='spell_items' GROUP BY item_id ");
					if ($result->num_rows) {
						
						while (list($id,$item_id,$count) = $result->fetch_row()) {
							$res = $db->query("SELECT name,image_path,damage_mess,min_damage,max_damage,req_skill FROM phaos_spells_items WHERE id=$item_id ");
							$row = $res->fetch_assoc();
							
							list($description,$image_path,$damage_mess,$min_damage,$max_damage,$req_skill) = 
								[$row["name"],$row["image_path"],$row["damage_mess"],$row["min_damage"],$row["max_damage"],$row["req_skill"]];

							if ($character->wisdom >= $req_skill) {
								if ($damage_mess == 0) { 
									$damage_mess = $lang_shop["mgc_eff1"];
								} else {
									$damage_mess = $lang_shop["mgc_eff2"];
								}
								echo "<td class='center'><input class='icon help' type='image' src='$image_path' title='$description $damage_mess $min_damage-$max_damage $lang_dam' ><br>($count)</td>";
							} else {
								if ($damage_mess == 0) {
									$damage_mess = $lang_shop["mgc_eff1"];
								} else {
									$damage_mess = $lang_shop["mgc_eff2"];
								}
								echo "<td class='center' style='color:red;'><input class='icon help' type='image' src='$image_path' title='$description $damage_mess $min_damage-$max_damage $lang_dam' ><br>($count)</td>";
							}
						}
					} else {
						echo "<td class='center' colspan='5'>".$lang_comb["no_mag"]."</td>";
					}
					?>
				</tr>
			</table>
			<hr><hr>
		</td>
	</tr>
	<tr> 
		<?php 
		$line = 0;
		$result = $db->query("SELECT * FROM phaos_spells_items"); 
		if ($row = $result->fetch_assoc()) {
			do {
				echo "<td class='quartersize center' valign='top'> ";
				$id = $row["id"];
				$description = $row["name"]; 
				$min_damage = $row["min_damage"];
				$max_damage = $row["max_damage"];
				$buy_price = $row["buy_price"];
				$image_path = $row["image_path"];
				$skill_req = $row["req_skill"];
				$damage_mess = $row["damage_mess"];
				if ($damage_mess == 0) { 
					$damage_mess = $lang_shop["mgc_eff1"];
				} else {
					$damage_mess = $lang_shop["mgc_eff2"];
				} 
				if ($skill_req > $character->wisdom) {
					$skill_need = "red";
				} else {
					$skill_need = "white";
				}
				?>
				<form action="magic_shop.php?shop_id=<?php echo $shop_id;?>" method="post"><img class="icon" src="<?php echo $image_path;?>">
					<br>
					<input type="hidden" name="spell_items" value="<?php echo $id;?>">
					<input type="hidden" name="shop_id" value="<?php echo $shop_id;?>">
					<?php
					echo "<span class='b big'>$description</span><br><span class='b'>";
					echo $lang_dam;
					echo "&nbsp;";
					echo $min_damage;
					echo "-";
					echo $max_damage;
					echo "</span><br>";
					echo "$damage_mess";
					echo "<br>";
					echo "<span class='b'><font color=".$skill_need.">".$lang_shop["req"]." ".$skill_req." ".$lang_wis."</font></span><br>";
					echo "<span class='b gold'>$buy_price</span> ";
					echo $lang_shop["gp"];
					echo "<br>".$lang_shop["qu"]." ";
					echo "	<input type='text' name='number_purchased' value='1' size='1' maxlength='2'>
							<input class='button' type='submit' value='".$lang_shop["purc"]."'>";
					?>
					<hr>
				</form>
				<?php
				$line ++;
				if ($line == 4) {
					echo "</td></tr><tr>";
					$line = 0;
				} else { 
					echo "</td>";
				}
			}
			while ($row = $result->fetch_assoc()); 
		}
		?> 
	</tr>
</table>

<div class="center">
	<form style="padding:10px;"><input class="button" type="button" value="<?php echo $lang_backtown;?>" onClick="location='town.php';this.value='<?php echo $lang_leaving;?>'"></form>
</div>

<?php require_once "footer.php";
