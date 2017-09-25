<?php
require_once "header.php";
require_once "item_functions.php";
require_once "shop_functions.php"; // also loads class_character.php

$character = new character($PHP_PHAOS_CHARID);
shop_valid($character->location, $shop_id);

// Auto-generate refills if the shop does not exist yet
$refills = fetch_value("SELECT count(*) FROM phaos_shop_refill WHERE shop_id='$shop_id'",__FILE__,__LINE__);

if (!$refills) {
    // Blacksmith Refills v1.0
    //insert_shop_refill($shop_id, $item_type, $item_value_min, $item_value_growth, $item_value_growth_probability, $item_count_min);
    insert_shop_refill($shop_id, 'weapon', rand(1,400), 1.5, 0.83, 5);
    insert_shop_refill($shop_id, 'armor', rand(1,3000), 1.5, 0.70, 1);
    insert_shop_refill($shop_id, 'boots', rand(1,500), 2.0, 0.70, 1);
    insert_shop_refill($shop_id, 'gloves', rand(1,500), 2.0, 0.5, 1);
    insert_shop_refill($shop_id, 'helm', rand(1,700), 2.0, 0.5, 1);
    insert_shop_refill($shop_id, 'shield', rand(1,160), 1.5, 0.5, 1);
}

// Generic processing for a shop
require_once "shop_include.php";
?>

<div>
	<div class="left" style="display:inline-block;width:15%;">
		<img src="images/armor.gif">
	</div>
	<div class="center" style="display:inline-block;width:70%;">
		<h1>
			<?php
			$result = $db->query("SELECT owner_id FROM phaos_buildings WHERE shop_id='$shop_id' ");
			$row = $result->fetch_assoc();
			
			$result = $db->query("SELECT name FROM phaos_characters WHERE id='$row[owner_id]' ");
			if ($row = $result->fetch_assoc()) {
				echo "$row[name]'s";
			}
			?>
		</h1>
		<img src="lang/<?php echo $lang ?>_images/blacksmith.png">
	</div>
	<div class="right" style="display:inline-block;width:15%;">
		<img src="images/armor.gif">
	</div>
</div>

<table width="100%" cellspacing=0 cellpadding=0>
	<?php
	if (@$sorry) {
		echo "<tr>
				<td align=center colspan=2>
					<span class='big b red'>$sorry</span>
				</td>
			</tr>";
	}
	// Close Shop to players with full inventory
	if ($character->invent_count() > $character->max_inventory + 50) {
		?>
		<tr>
			<td>
				<h3 class="center b"><?php echo $lang_shop["inv_full"];?></h3>
			</td>
		</tr>
		<tr>
			<td class="center">
				<form style="padding:10px;"><input class="button" type="button" value="<?php echo $lang_backtown;?>" onClick="location='town.php';this.value='<?php echo $lang_leaving;?>'"></form>
			</td>
		</tr>
		<?php
		exit;
	} else {
		?>
		<tr>
			<td class="center b" colspan=2> 
				<?php echo $lang_shop["smithy"];?>
			</td>
		</tr>
	<?php
	}
	?>
	<tr>
		<td class="center b halfsize">
			<br>
			<?php echo $lang_shop["wep"]; ?>
		</td>
		<td class="center b halfsize">
			<br>
			<?php echo $lang_shop["armor"]; ?>
		</td>
	</tr>
	<tr>
		<td class="center top">
			<?php
			$items = fetch_items_for_location($shop_basics['item_location_id']);

			if (is_array($items) && count($items) > 0) {
				foreach($items as $item){
					if ($item['type'] == 'weapon') {
						$info = fetch_item_additional_info($item,$character);
						if ($info) {
							echo "<form action='darksmith.php?shop_id=$shop_id' method='post'>
								<hr><img class='icon' src='$info[image_path]'><br>";
							echo "<input type='hidden' name='buy_id[]' value='$item[id]'>
								<input type='hidden' name='buy_type[]' value='$item[type]'>
								<input type='hidden' name='buy_number[]' value='1'>
								<input type='hidden' name='shop_id' value='$shop_id'>
								  <big>$info[description]</big><br>
								  ".$lang_dam." $info[min_damage]-$info[max_damage]<br>
								  <b><font color=$info[skill_need]>".$lang_shop["req"]." $info[skill_req] ".$lang_att."</font></b><br>
								  <span class='b gold'>$info[buy_price]</span> ".$lang_shop["gp"]."<br>
								<input class='button' type='submit' value='".$lang_shop["purc"]."'>";
							echo "</form>";
						}
					}
				}
			} else {
				?><h2><?php echo $lang_shop["sold_out"]; ?></h2><?php
			}
			?>
		</td>
		<td class="center top">
			<?php
			if (is_array($items) && count($items) > 0) {
				foreach($items as $item) {
					$info = fetch_item_additional_info($item,$character);
					if (in_array($item['type'],$armorItems)) {
						if ($info) {
							echo "<form action='darksmith.php?shop_id=$shop_id' method='post'>
								<hr><img class='icon' src='$info[image_path]'><br>";
							echo "<input type='hidden' name='buy_id[]' value='$item[id]'>
								<input type='hidden' name='buy_type[]' value='$item[type]'>
								<input type='hidden' name='buy_number[]' value='1'>
								<input type='hidden' name='shop_id' value='$shop_id'>
								  <big>$info[description]</big><br>
								  ".$lang_shop["ac"]." $info[armor_class]<br>
								  <b><font color=$info[skill_need]>".$lang_shop["req"]." $info[skill_req] ".$lang_def."</font></b><br>
								  <span class='b gold'>$info[buy_price]</span> ".$lang_shop["gp"]."<br>
								<input class='button' type='submit' value='".$lang_shop["purc"]."'>";
							echo "</form>";
						}
					}
				}
			} else {
				?><h2><?php echo $lang_shop["sold_out"]; ?></h2><?php
			}
			?>
		</td>
	</tr>
</table>

<div class="center">
	<form style="padding:10px;"><input class="button" type="button" value="<?php echo $lang_backtown;?>" onClick="location='town.php';this.value='<?php echo $lang_leaving;?>'"></form>
</div>

<?php require_once "footer.php";
