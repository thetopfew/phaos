<?php
require_once "header.php";

// If no character created
if (empty($character->location)) {
	jsChangeLocation("create_character.php", 0);
}
// Get Slapped! If in combat and clicked out
if (isset($_SESSION['opponent_id'])) {
	jsChangeLocation("combat.php?comb_act=npc_attack", 0);
}

//require_once "class_character.php";
//require_once "items.php";
//require_once "location_actions.php";

$character = new character($PHP_PHAOS_CHARID);

// If you've clicked to put something up for market sale -- a bunch of safety checks
if (isset($_POST['market_item'])) {
	// Is item equiped?
	$res = $db->query("SELECT equiped FROM phaos_char_inventory WHERE id = '$_POST[char_inv_id]' AND username = '$PHP_PHAOS_USER'");
	if ($row = $res->fetch_assoc()) {
		$equiped = $row["equiped"];
		if ($equiped == "N") {
			// If blank name or already public, then (re)post to public market
			if ( (empty($sell_to)) || ($sell_to == 'public') ) {
				$sell_to = "public";
				$show_msg = $lang_char["m_posted"];
				$db->query("UPDATE phaos_char_inventory SET asking_price = '$_POST[asking_price]', sell_to = '$sell_to' WHERE id = '$_POST[char_inv_id]' LIMIT 1") or die("<B>Error ".mysqli_errno()." :</B> ".mysqli_error()."");
			} else {
				// Do not allow, sell to self
				if ($sell_to == $PHP_PHAOS_USER) {
					$show_msg = $lang_char["m_no2self"];
				} else {
					// If user exists, post for private market
					$res = $db->query("SELECT username, name FROM phaos_characters WHERE username = '$sell_to' OR name = '$sell_to'");
					if ($row = $res->fetch_assoc()) { 
						$show_msg = $lang_char["m_post_priv"];
						$db->query("UPDATE phaos_char_inventory SET asking_price = '$_POST[asking_price]', sell_to = '$sell_to' WHERE id = '$_POST[char_inv_id]' LIMIT 1") or die("<B>Error ".mysqli_errno()." :</B> ".mysqli_error()."");
					} else {
						// User or char name does not exist, fail operation
						$show_msg = $lang_char["e_no_user"];
					}
				}
			}
		} else {
			$show_msg = $lang_char["m_uequip1"];
		}
		if ($_POST['asking_price'] <= 0){
			$sell_to = ''; // Stop selling, set price to 0
			$show_msg = $lang_char["m_removed"];
			$db->query("UPDATE phaos_char_inventory SET asking_price = '$_POST[asking_price]', sell_to = '$sell_to' WHERE id = '$_POST[char_inv_id]' LIMIT 1") or die("<B>Error ".mysqli_errno()." :</B> ".mysqli_error()."");
		} 
	}
}

// Clear out any bad database rows
$db->query("DELETE FROM phaos_char_inventory WHERE item_id='' AND type='' ");

// Find out if character is at a blacksmith, item shop, or magic shop, only checks while player in town.
$res = $db->query("SELECT * FROM phaos_buildings WHERE location = '".$character->location."' ") or die("<B>Error ".mysqli_errno()." :</B> ".mysqli_error()."");
$blacksmith_yn = false;
$item_yn = false;
$magicshop_yn = false;
while ($row = $res->fetch_assoc()) {
	$blacksmith_yn	|= $row["name"] == "Blacksmith";
	$item_yn		|= $row["name"] == "Item Shop";
	$magicshop_yn	|= $row["name"] == "Magic Shop";
	//echo "$row[name] $blacksmith_yn,$item_yn,$magicshop_yn<br>"; //for debugging shop locations
	
	// kaspirs: USE for DEBUGGING ONLY: Show on char.sheet if a blacksmith, item shop, or magic shop, is present.
	//$res = $db->query("SELECT * FROM phaos_buildings WHERE location = '".$character->location."' ") or die("<B>Error ".mysqli_errno()." :</B> ".mysqli_error()."");
	//if ($row = $res->fetch_assoc()) {
	//	if ($row["name"] == "Blacksmith") 	{echo "Blacksmith = True<br>";} else {echo "Blacksmith = False<br>";}
	//	if ($row["name"] = "Item Shop") 	{echo "Item Shop = True<br>";} 	else {echo "Item Shop = False<br>";}
	//	if ($row["name"] |= "Magic Shop") 	{echo "Magic Shop = True<br>";} else {echo "Magic Shop = False<br>";}
	//}
}

$shopForItemtype = array();
function setShopForItemtype($its,$value) {
	global $shopForItemtype;
	
	foreach($its as $it){
		$shopForItemtype[$it] = $value;
	}
}
setShopForItemtype( array( "armor","weapon","boots","shield","helm","gloves"), $blacksmith_yn);
setShopForItemtype( array( "potion" ), $item_yn);
setShopForItemtype( array( "spell_items"), $magicshop_yn);

// SELL AN ITEM
if (!empty($sell_id)) {
	$result = $db->query("SELECT type FROM phaos_char_inventory WHERE id = '$id' AND equiped = 'N'");
	if ($row = $result->fetch_assoc()) {
		$ite_type = $row["type"];
	}

	if ($ite_type == "potion") {
		if ($item_yn) {
			$priceresult = $db->query("SELECT sell_price FROM phaos_potion WHERE id = '$item_id'");
			if ($row = $priceresult->fetch_assoc()) {$sell_price = $row["sell_price"];}
		} else {$sell_price = 0;}
	} elseif ($ite_type == "weapon") {
		if ($blacksmith_yn) {
			$priceresult = $db->query("SELECT sell_price FROM phaos_weapons WHERE id = '$item_id'");
			if ($row = $priceresult->fetch_assoc()) {$sell_price = $row["sell_price"];}
		} else {$sell_price = 0;}
	} elseif ($ite_type == "armor") {
		if ($blacksmith_yn) {
			$priceresult = $db->query("SELECT sell_price FROM phaos_armor WHERE id = '$item_id'");
			if ($row = $priceresult->fetch_assoc()) {$sell_price = $row["sell_price"];}
		} else {$sell_price = 0;}
	} elseif ($ite_type == "boots") {
		if ($blacksmith_yn) {
			$priceresult = $db->query("SELECT sell_price FROM phaos_boots WHERE id = '$item_id'");
			if ($row = $priceresult->fetch_assoc()) {$sell_price = $row["sell_price"];}
		} else {$sell_price = 0;}
	} elseif ($ite_type == "gloves") {
		if ($blacksmith_yn) {
			$priceresult = $db->query("SELECT sell_price FROM phaos_gloves WHERE id = '$item_id'");
			if ($row = $priceresult->fetch_assoc()) {$sell_price = $row["sell_price"];}
		} else {$sell_price = 0;}
	} elseif ($ite_type == "helm") {
		if ($blacksmith_yn) {
			$priceresult = $db->query("SELECT sell_price FROM phaos_helmets WHERE id = '$item_id'");
			if ($row = $priceresult->fetch_assoc()) {$sell_price = $row["sell_price"];}
		} else {$sell_price = 0;}
	} elseif ($ite_type == "shield") {
		if ($blacksmith_yn) {
			$priceresult = $db->query("SELECT sell_price FROM phaos_shields WHERE id = '$item_id'");
			if ($row = $priceresult->fetch_assoc()) {$sell_price = $row["sell_price"];}
		} else {$sell_price = 0;}
	} elseif ($ite_type == "spell_items") {
		if ($magicshop_yn) {
			$priceresult = $db->query("SELECT sell_price FROM phaos_spells_items WHERE id = '$item_id'");
			if ($row = $priceresult->fetch_assoc()) {$sell_price = $row["sell_price"];}
		} else {$sell_price = 0;}
	}

	if ($sell_price > 0) {
		$sell_gold = $sell_price + $character->gold;
		$req = $db->query("UPDATE phaos_characters SET gold = '$sell_gold' WHERE username = '$PHP_PHAOS_USER'");
		if (!$req) {echo "<B>Error ".mysqli_errno()." :</B> ".mysqli_error().""; exit;}
		
		$query = "DELETE FROM phaos_char_inventory WHERE id = '$id'";
		$db->query($query) or die ("Error in query: $query. " .mysqli_error());

		$show_msg = "$lang_char[soldtoshop] <span class='gold'>$sell_price</span> $lang_gold.";
	} else {
		$show_msg = "$lang_char[noshop]";
	}
	$refsidebar = true;
}

// DRINK POTION
if (isset($_GET['drink_potion'])) {
	$result = $db->query("SELECT type FROM phaos_char_inventory WHERE id = '$_GET[id]'");
	if ($row = $result->fetch_assoc()) {
		if ($row["type"] == "potion") {
			$character->drink_potion($_GET['id']);
			$show_msg = $lang_char['healpot'];
		}
	}
	$refsidebar = true;
}

// EQUIP AN ITEM
if (!empty($equip_id)) {
	if ($equip_id == "Y") {
		if ($character->equipt($item_type,$item_id)) {
			$refsidebar = true;
			$show_msg = $lang_char['equ_succ'];
		} else {
			$show_msg = $lang_char['equ_fail'];
		}
	}
	// UNEQUIP AN ITEM
	if ($equip_id == "N") {
		if ($character->unequipt($item_type,$item_id)) {
			$refsidebar = true;
			$show_msg = $lang_char['u_equ_succ'];
		} else {
			$show_msg = $lang_char['u_equ_fail'];
		}
	}	
}

// Display action messages
if (isset($show_msg)) {
	echo "<div class='center'><h3 class='b msgbox'>$show_msg</h3></div>";
}
// Then refresh is true
if (isset($refsidebar)) {
	$refsidebar = false;
	jsRefreshURL();
}
?>


<table align=center cellspacing=5 cellpadding=0>
<tr>
<td align=center colspan=2>
<?php echo "<a href='player_info.php?player_name=".$PHP_PHAOS_USER."'><img title='$lang_v_char' src='lang/en_images/character.png' /></a>"; ?>
</td>
</tr>
<?php
// MAKE SURE EQUIPPED ARMOR IS STILL IN INVENTORY
if ($numerrors = $character->checkequipment()) {
	echo $numerrors." ".$lang_char["eq_dropped"];
}
// Take Care of Skill-Levels!!
if ($numerrors = $character->inv_skillmatch()) {
	echo $numerrors." ".$lang_char["ins_skill"];
}
?>

<tr>
<td class="center" colspan=2>
<br><p class="b"><?php echo $lang_char["sort"]; ?></p>
<input class="button" type="submit" value="<?php echo $lang_all; ?>" onclick="window.location='character.php';">
<input class="button" type="submit" value="<?php echo $lang_weapons; ?>" onclick="window.location='character.php?act=weapon#inventory';">
<input class="button" type="submit" value="<?php echo $lang_armors; ?>" onclick="window.location='character.php?act=armor#inventory';">
<input class="button" type="submit" value="<?php echo $lang_boots; ?>" onclick="window.location='character.php?act=boots#inventory';">
<input class="button" type="submit" value="<?php echo $lang_gloves; ?>" onclick="window.location='character.php?act=gloves#inventory';">
<input class="button" type="submit" value="<?php echo $lang_helms; ?>" onclick="window.location='character.php?act=helm#inventory';">
<input class="button" type="submit" value="<?php echo $lang_shields; ?>" onclick="window.location='character.php?act=shield#inventory';">
<input class="button" type="submit" value="<?php echo $lang_potions; ?>" onclick="window.location='character.php?act=potion#inventory';">
<input class="button" type="submit" value="<?php echo $lang_spells; ?>" onclick="window.location='character.php?act=spell_items#inventory';">


<table cellspacing=0 cellpadding=0 width="100%">
<?php
$wheretype = "";

$item_type = @$_GET['act'];
if (isItemType($item_type)) {
	$wheretype = " AND type = '$item_type' ";
}

// !PS: be careful with this code, it took some time to write

$items = array();
$list_inventory = $db->query("SELECT * FROM phaos_char_inventory WHERE username = '$PHP_PHAOS_USER' $wheretype ORDER BY equiped DESC, type DESC, item_id DESC");
if ($list_inventory) {
	while ($row = $list_inventory->fetch_assoc()) {
		$items[] = $row;
	}
	$items[] = null; // Add an extra empty row to trigger output row
}

if (count($items) > 1) { 
	?>
	<tr class="bgcolor big"><br>
		<td class="center b">
			&nbsp;<?php echo $lang_char["amount"]; ?>
		</td>
		<td></td>
		<td class="b">
			<?php echo $lang_char["desc"]; ?>
		</td>
		<td class="center b">
			<?php echo $lang_char["eff"]; ?>
		</td>
		<td class="center b" colspan=2>
			<?php echo $lang_action; ?>
		</td>
	</tr>
	<?php
}

// Begin output loop
$lastrow = null;
$output = null;
foreach ($items as $row) {
	if ($row) {
		$id = $row["id"];
		$equiped = $row["equiped"];
		$item_type = $row["type"];
		$item_id = $row["item_id"];
		$sell_to_name = $row["sell_to"];
		$ask_price = $row["asking_price"];

		if (!@$_GET['act'] || $_GET['act'] == $item_type || $_GET['act'] == $item_type.'s' || $_GET['act'].'s' == $item_type) {
			if ($lastrow && $row['item_id'] == $lastrow['item_id'] && $row['equiped'] == $lastrow['equiped'] && $row['type'] == $lastrow['type'] && $row['sell_to'] == $lastrow['sell_to'] && $row['asking_price'] == $lastrow['asking_price']) {
				++$lastrow['itemcount'];
				$output = null;
			} else {
				$output = $lastrow;
				$lastrow = $row;
				$lastrow['itemcount'] = 1;
			}
		} else {
			// ignore item
		}
	} else {
		$output = $lastrow;
	}
	if (!$output) {
		// no output
		continue;
	} else {
		$id = $output["id"];
		$equiped = $output["equiped"];
		$item_type = $output["type"];
		$item_id = $output["item_id"];
		$sell_to_name = $output["sell_to"];
		$ask_price = $output["asking_price"];

		$info = fetch_item_additional_info(array('id'=>$item_id,'type'=>$item_type,'number'=>1),$character);

		$description = $info["description"];
		$sell_price = $info["sell_price"];
		$image_path = $info["image_path"];
		$skill_req = $info["skill_req"];
		$skill_need = $info["skill_need"];
		$effect = $info["effect"];
		$skill_type = $info["skill_type"];

		// Start Outputting Row
		echo "<tr>"; 
		echo "<td class='center b'><span class='big'>$output[itemcount]</span>x</td>";
		?><td class="center"><img class="icon" src="<?php echo $image_path;?>"></td><?php
		echo "<td class='left'><span class='big'>".ucwords($description)."</span><br>";
		if ($item_type != "potion") {
			echo "<font color=$skill_need>".$lang_shop["req"].$skill_req." ".$skill_type."</font>";
		}
		echo "</td><td class='center b top'>$effect</td>";
		if ($item_type == "potion") {
			$res = $db->query("SELECT asking_price FROM phaos_char_inventory WHERE username = '$PHP_PHAOS_USER' AND item_id = '$item_id' AND type = '$item_type'");
			if ($row = $res->fetch_assoc()) {
				$asking_price = $row["asking_price"];
				if ($asking_price == "0") {
					echo "<td class='top'><input class='button' type='button' onClick=\"parent.location='character.php?item_id=$item_id&id=$id&drink_potion=Y'\" value='$lang_char[drink]'></td>";
				}
			}
		} else if ($item_type == "spell_items") {
			echo "<td class='top'>&nbsp;</td>";
		} else {
			echo "<td class='center top'>&nbsp;";
			if ( (!$character->equipped($item_type,$item_id,$equiped)) && (empty($sell_to_name)) ) {
				if ($item_type == "weapon") {
					if ($info['skill_req'] > $character->fight) { echo " ";} else {
						echo "<input class='button' type='button' onClick=\"parent.location='character.php?item_id=$item_id&item_type=$item_type&id=$id&equip_id=Y'\" value='$lang_char[eq]'>";
					}
				} else {
					if ($info['skill_req'] > $character->defence) { echo " ";} else {
						if ($equiped == "N") {
							echo "<input class='button' type='button' onClick=\"parent.location='character.php?item_id=$item_id&item_type=$item_type&id=$id&equip_id=Y'\" value='$lang_char[eq]'>";
						}
					}
				}
			}
			if ($character->equipped($item_type,$item_id,$equiped)) {		
				if ($equiped == "Y") {
					echo "<input class='button' type='button' onClick=\"parent.location='character.php?item_id=$item_id&item_type=$item_type&id=$id&equip_id=N'\" value='$lang_char[uneq]'>";					
				}
			}
			echo "</td>";
		}
		if ($item_type == "weapon" OR $item_type == "armor" OR $item_type == "boots" OR $item_type == "potion" OR $item_type == "gloves" OR $item_type == "helm" OR $item_type == "shield" OR $item_type == "spell_items") {
			if ($character->equipped($item_type,$item_id)) { echo " ";} else {
				echo "<td class='center top'>";
				if ($shopForItemtype[$item_type]) {
					$res = $db->query("SELECT asking_price FROM phaos_char_inventory WHERE username = '$PHP_PHAOS_USER' AND item_id = '$item_id' AND type = '$item_type'");
					if ($row = $res->fetch_assoc()) {
						$asking_price = $row["asking_price"];
						if ($asking_price == "0") {
							echo "<input class='button' type='button' title='$lang_char[sell_pr] $sell_price gold' onClick=\"parent.location='character.php?item_id=$item_id&item_type=$item_type&id=$id&sell_id=Y'\" value='$lang_char[sell]'";
							// FIXME: Drop button, if I decide to add back in
							//echo "<input class='button' type='button' title='$lang_char[dropitem]' onClick=\"parent.location='character.php'\" value='$lang_char[dropitem]'";
							/*actionButton($lang_char['dropitem'],$this_url,
								array(
									'drop_id[]'=> $item_id,
									'drop_type[]'=> $item_type,
									'drop_number[]'=> 1
								)
							);*/
						}
					}
				} else {
					echo "&nbsp;";
				}
			}
			echo "</td></tr>";
		}
		if ($character->equipped($item_type,$item_id)) { echo " ";} else {
			echo "	  <td class='center top' colspan=7>
					  <form method='post' action='character.php'>
					  <input type='hidden' name='market_item' value='yes'>
					  <input type='hidden' name='char_inv_id' value='$id'>
					  $lang_char[pub_priv] <input type='text' name='sell_to' value='$sell_to_name' placeholder='$lang_char[sale_2who]' size='16' maxlength='30'>
					  $lang_char[post_pric] <input type='text' name='asking_price' value='$ask_price' size='6' maxlength='10'>
					  <input class='button' type='submit' value='$lang_update'>
					  </form>
					  </td>";
		}
		?>
		</tr>
		<tr>
			<td colspan=8>
				<hr>
			</td>
		</tr><?php
	}
	// End output row
}
// end loop

if (!$output) {
	echo "<tr><td class='center b' colspan=4>".$lang_char["noitem"].($wheretype?" ($_GET[act])":'')."</td></tr>";
}
?>
</table>

</td>
</tr>
</table>



<?php
if ( (empty($character->name)) AND (empty($character->image)) ) { ?>
	<div class="center">
		<form action="create_character.php">
			<input class="button" type="submit" value="<?php echo $lang_char["create"]; ?>">
		</form>
	</div>
<?php
}

require_once "footer.php";
