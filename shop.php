<?php
require_once "header.php";
require_once "shop_functions.php"; // also loads class_character.php

$character = new character($PHP_PHAOS_CHARID);
shop_valid($character->location, $shop_id);
$reload = false;

if (@$_REQUEST['item_id']) {
	$result = $db->query("SELECT type,sell,quantity FROM phaos_shop_inventory WHERE shop_id='$shop_id' AND item_id='$item_id'");
	// $result = $db->query("SELECT * FROM phaos_misc_items WHERE id = '$item'"); // ADD: Special items later.
	if ($inv_row = $result->fetch_assoc()) {
		$item_type = $inv_row["type"];
		$price = $inv_row["sell"];
		$qty_avail = $inv_row["quantity"];
	}

	$number = intval($_REQUEST['number']);
	
	// Close Shop to players with full inventory option.
	if ($character->invent_count() > $character->max_inventory + 50) {
		echo "<div class='center'><h3 class='b msgbox'>$lang_shop[inv_full]</h3></div>"; 
		?>
		<div class="center">
			<form style="padding:10px;"><input class="button" type="button" value="<?php echo $lang_backtown;?>" onClick="location='town.php';this.value='<?php echo $lang_leaving;?>'"></form>
		</div>
		<?php
		jsChangeLocation("town.php", 3);
	}
	
	// Is the item still in stock?
	if ($qty_avail > 0) {
		// Does the shop have enough quantity 
		if ($number <= $qty_avail) {
			// Do you have enough gold to buy the item?
			$total = $price * $number;
			// Disallow negative qty of items.
			if ($character->pay($total)) {
				if ($number < 0) {
					$character->pay( -$total);
					$number = 0;
					$sorry = $lang_shop["negative"];
				}
				// Give gold to shop owner
				$result = $db->query("SELECT * FROM phaos_buildings WHERE shop_id='$shop_id' ");
				$shop_row = $result->fetch_assoc();
				$owner = new character($shop_row["owner_id"]);
				$owner->gold += $total;
				$db->query("UPDATE phaos_characters SET gold='".$owner->gold."' WHERE id='".$owner->id."' ");

				// Remove item from store inventory
				$new_qty = $qty_avail - $number;
				$db->query("UPDATE phaos_shop_inventory SET quantity ='$new_qty' WHERE shop_id='$shop_id' AND item_id='$item_id' ");
				
				// Take gold from player
				$db->query("UPDATE phaos_characters SET gold='".$character->gold."' WHERE id='".$character->id."' ");
				
				// Add item to player inventory
				$i = 0;
				while ($i < $number) {
					$i++;
					$character->add_item($item_id,$item_type);
					$reload = true;
				}
				//$reload = true;
			} else {
				$sorry = $lang_shop["sorry"];
			}
		} else {
			$sorry = $lang_shop["not_engh"];
		}
	} else {
		$sorry = $lang_shop["no_left"];
	}
}

if ($reload) {
	$reload = false; // Reset status
	jsChangeLocation("shop.php?shop_id=$shop_id", 2);
}
?>

<div>
	<div class="left" style="display:inline-block;width:15%;">
		<img src="images/itemshop.png">
	</div>
	<div class="center" style="display:inline-block;width:70%;">
		<?php
		// Display Shop Owner's Name
		$result = $db->query("SELECT owner_id FROM phaos_buildings WHERE shop_id='$shop_id' ");
		$row = $result->fetch_assoc();
		$result = $db->query("SELECT name FROM phaos_characters WHERE id='$row[owner_id]' ");
		if ($row = $result->fetch_assoc()) {
			echo "<h1>$row[name]'s</h1>";
		}
		?>
		<img src="lang/<?php echo $lang ?>_images/item_shop.png">
	</div>
	<div class="right" style="display:inline-block;width:15%;">
		<img src="images/itemshop.png">
	</div>
</div>

<table class="center fullsize" cellspacing=0 cellpadding=0>
	<tr>
		<td colspan=4>
			<p class="b"><?php echo $lang_shop["phaelin"];?>
		</td>
	</tr>
	<?php 
	if (@$sorry) {
		echo "<div class='center'><h3 class='b msgbox'>".$sorry."</h3></div>";
	}
	?>
	<tr class="bgcolor">
		<td colspan=5>
			<div class="center b"><?php echo $lang_shop["potioninvo"];?></div>
		</td>
	</tr>
	<tr>
		<td class="center" colspan=5>
			<table class="center fullsize" cellspacing=0 cellpadding=2>
				<tr>
					<?php // Show current potions in inventory.
					$result = $db->query("SELECT id,item_id,count(item_id) FROM phaos_char_inventory WHERE username = '".$character->user."' AND type='potion' GROUP BY item_id ");
					if ($result->num_rows) {
						
						while (list($id,$item_id,$count) = $result->fetch_row()) {
							$res = $db->query("SELECT name,image_path,heal_amount FROM phaos_potion WHERE id='$item_id' ");
							$row = $res->fetch_assoc();
							
							list($description,$image_path,$heal_amount) = [$row["name"],$row["image_path"],$row["heal_amount"]];

							echo "<td class='center'><input class='icon help' type='image' src='$image_path' title='$description: $lang_heal +$heal_amount'><br>($count)</td>";
						}
					} else {
						echo "<td class='center'>".$lang_comb["no_pot"]."</td>";
					}
					?>
				</tr>
			</table>
			<hr><hr>
		</td>
	</tr>
	<tr>
		<?php
		// Build list of available potion to buy
		$line = 0;
		$result = $db->query("SELECT * FROM phaos_shop_inventory WHERE shop_id='$shop_id' ORDER BY item_id ASC");
		while ($inv_row = $result->fetch_assoc()) {
			$result2 = $db->query("SELECT * FROM phaos_$inv_row[type] WHERE id='$inv_row[item_id]' ");
			$item_row = $result2->fetch_assoc();
			$id = $item_row["id"];				
			echo "<td class='center'><img class='icon' src='$item_row[image_path]'><br>
				<big>$item_row[name]</big><br>
				$inv_row[quantity] $lang_shop[stock]<br>
				<span class='b gold'>$inv_row[sell]</span> $lang_shop[gp] $lang_shop[each]<br>
				<form action='shop.php?shop_id=$shop_id' method='post'>
					<input type='hidden' name='shop_id' value='$inv_row[shop_id]'>
					<input type='hidden' name='item_id' value='$inv_row[item_id]'>
					$lang_shop[qu] <input type='text' name='number' value='1' size='1' maxlength='2'>
					<input class='button' type='submit' value='$lang_shop[purc]'>
					<hr>
				</form>";
			$line ++;
			if ($line == 4) {
				echo "</td></tr><tr>";
				$line = 0;
			} else { 
				echo "</td>";
			}
		}

		?>
		</td>
	</tr>
</table>

<div class="center">
	<form style="padding:10px;"><input class="button" type="button" value="<?php echo $lang_backtown;?>" onClick="location='town.php';this.value='<?php echo $lang_leaving;?>'"></form>
</div>

<?php require_once "footer.php";
