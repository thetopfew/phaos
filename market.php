<?php
require_once "header.php";

// If no character created
if ($character->location == "") {
	jsChangeLocation("create_character.php", 0);
}

// Get Slapped! If in combat and clicked travel
if(isset($_SESSION['opponent_id'])) {
	jsChangeLocation("combat.php?comb_act=npc_attack", 0);
}

// Must be in a town to visit marketplace!
if (!char_intown($character->location)) {
	echo "<div class='center'><h3 class='b red msgbox'>".$lang_err["no_market"]."</h3></div>";
	jsChangeLocation("travel.php", 3);
}

require_once "item_functions.php";
require_once "class_character.php"; //for refresh function..


//$character = new character($PHP_PHAOS_CHARID);

//FIXME: this code still accesses the phaos_character table by username
//FIXME: phaos_char_inventory uses username to store inventory

if (isset($_POST['inventory_id']) AND $_POST['owner_name'] != "".$PHP_PHAOS_USER."") {
	$inventory_id = $_POST['inventory_id'];
	$owner_name = $_POST['owner_name'];

	//$result = $db->query("SELECT asking_price FROM phaos_char_inventory WHERE id = '$inventory_id' AND username = '$owner_name' AND sell_to != ''");
	$result = fetch_value("SELECT asking_price FROM phaos_char_inventory WHERE id = '$inventory_id' AND username = '$owner_name' AND sell_to != ''");
	if ($row = $result->fetch_assoc()) {
		$asking_price = abs($row["asking_price"]);

		$result = $db->query("SELECT gold FROM phaos_characters WHERE username = '$owner_name'");
		if ($row = $result->fetch_assoc()) {
			$seller_gold = $row["gold"];
		}

		$result = $db->query("SELECT gold FROM phaos_characters WHERE username = '$PHP_PHAOS_USER'");
		if ($buy_char = $result->fetch_assoc()) {
			$buyer_gold = $buy_char["gold"];
		}

		if ($buyer_gold >= $asking_price) {
			$new_buyer_gold = $buyer_gold - $asking_price;
			$new_seller_gold = $seller_gold + $asking_price;

			$req = $db->query("UPDATE phaos_char_inventory SET username = '$PHP_PHAOS_USER',asking_price = '0',sell_to = '' WHERE id = '$inventory_id'");
			if (!$req) { echo "<B>Error ".mysqli_errno()." :</B> ".mysqli_error().""; exit; }

			$req = $db->query("UPDATE phaos_characters SET gold = ".$new_seller_gold." WHERE username = '$owner_name'");
			if (!$req) { echo "<B>Error ".mysqli_errno()." :</B> ".mysqli_error().""; exit; }

			$req = $db->query("UPDATE phaos_characters SET gold = ".$new_buyer_gold." WHERE username = '$PHP_PHAOS_USER'");
			if (!$req) { echo "<B>Error ".mysqli_errno()." :</B> ".mysqli_error().""; exit; }

		} else {
			$trade_result = $lang_markt["not_en_goo"];
		}

		if (empty($trade_result)) {
			$trade_result = $lang_markt["tr_compt"];
		}
		
	} else {
		$trade_result = $lang_markt["tr_not"];
	}
} else {
	$trade_result = "";
}
?>

<div class="center">
	<img src="lang/<?php echo $lang ?>_images/market.png">
</div>

<?php
if ($trade_result != "") {
	echo "<div class='center'><h3 class='b msgbox'>$trade_result</h3></div>";
	jsChangeLocation("market.php", 3);
}
?>

<table border=1 cellspacing=5 cellpadding=0 width="100%">
<?php
echo "<tr class='bgcolor'>
     <td class='center b'>".$lang_markt["sllr"]."</td>
     <td class='center b' width='50%'>".$lang_markt["desc"]."</td>
     <td colspan=2 class='center b'>".$lang_markt["ask_pr"]."</td>
     </tr>";

$res = $db->query("SELECT * FROM phaos_char_inventory WHERE username != '$PHP_PHAOS_USER' AND sell_to = 'public' OR UPPER(sell_to) LIKE UPPER('$PHP_PHAOS_USER') OR UPPER(sell_to) LIKE UPPER('$PHP_PHAOS_CHAR') ORDER BY type DESC, id ASC");
if ($row = $res->fetch_assoc()) {
	do {
		$owner_name = $row["username"]; // FIXME: this is bad.. we should use character name
		$inventory_id = $row["id"];
		$type = $row["type"];
		$item_id = $row["item_id"];
		$asking_price = abs($row["asking_price"]);
		$sell_to = $row["sell_to"];

        $item = fetch_item_additional_info(array('id'=>$item_id,'type'=>$type,'number'=>1), $character);
		if ($sell_to == "public") {
			echo "<tr>
     				<form method='post' action='market.php'>
						<td class='center i'>Public Offer</td>
						<td class='b halfsize top'>
							 <img class='bottom icon help' src='$item[image_path]' title='$item[effect]'>
							 <div style='display:inline-block;vertical-align:top;padding:2px;'><font color='$item[skill_need]'>$item[description]</font></div>
						</td>
						<td class='center b'><span class='gold'>$asking_price</span> GP</td>
						<td class='center b'>
							<input type='hidden' name='inventory_id' value='$inventory_id'>
							<input type='hidden' name='owner_name' value='$owner_name'>
							<button class='button' type='submit'>$lang_markt[buuy]</button>
						</td>
     				</form>
     				</tr>";
     			$sellthis = 1;
		} else {
			echo "<tr>
     				<form method='post' action='market.php'>
						<td class='center b'>Private from: <a href='player_info.php?player_name=$owner_name'>$owner_name</a></td>
						<td class='left b halfsize'>
							<img class='center icon help' src='$item[image_path]' title='$item[effect]'> <font color='$item[skill_need]'>$item[description]</font>
						</td>
						<td class='center b'><span class='gold'>$asking_price</span> GP</td>
						<td class='center'>
							<input type='hidden' name='inventory_id' value='$inventory_id'>
							<input type='hidden' name='owner_name' value='$owner_name'>
							<button class='button' type='submit'>$lang_markt[buuy]</button>
						</td>
     				</form>
     				</tr>";
     			$sellthis = 1;
		}
	} while ($row = $res->fetch_assoc());
}

if (empty($sellthis)) {
	echo "<tr>
     		<td class='center' colspan=4>$lang_markt[no_sell]</td>
     	  </tr>";
}
?>
</table>

<div class="center">
	<form style="padding:10px;"><input class="button" type="button" value="<?php echo $lang_backtown;?>" onClick="location='town.php';this.value='<?php echo $lang_leaving;?>'"></form>
</div>

<?php require_once "footer.php";
