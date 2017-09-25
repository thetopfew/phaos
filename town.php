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

$reload = false;

$location_name = fetch_value ("SELECT name FROM phaos_locations WHERE id = '".$character->location."'");

include_once "location_actions.php";
$pickedup = pickup_actions($character);

/* Creates a nice looking form button, used in town.php for treasure */
function actionButton($label,$action,$fields,$target='self',$method='POST') {
    ?><form action="<?php echo $action; ?>" method="<?php echo $method; ?>">
		<?php
		foreach($fields as $name=>$value){
			hiddenFields($name,$value);
		}
		?>
		<input class="button" type="submit" value="<?php echo $label; ?>">
	</form><?php
}

if ($pickedup > 0) {
    $reload = true;
    ?>
	<div class="center">
		<h3 class="b msgbox">
		<?php
		echo $pickedup." ".$lang_char['itemspickedup'];
		?>
		</h3>
	</div>
	<?php
}

if ($reload) {
	$reload = false; // Reset status
    jsChangeLocation("travel.php", 3);
}
?>
<table class="center fullsize" cellspacing=5 cellpadding=0>
	<tr>
		<td colspan=2>
			<img src="lang/<?php echo $lang ?>_images/explore.png">
			<br>
			<h2 class="b"><?php echo $location_name; ?></h2><br>
		</td>
	</tr>
<?php
$result = $db->query("SELECT shop_id,name,type FROM phaos_buildings WHERE name != 'Arena' AND name != 'Town Hall' AND location = '".$character->location."' ORDER by name ASC");
while ($row = $result->fetch_assoc()) {
	$name = $row["name"];
	//if ($name == "Arena") { $isname = "ad_arena"; }
	if ($name == "Bank") { $isname = "ad_bank"; }
	if ($name == "Blacksmith") { $isname = "ad_blacksmith"; }
	if ($name == "Magic Shop") { $isname = "ad_magic_sh"; }
	if ($name == "Item Shop") { $isname = "ad_itm_sh"; }
	if ($name == "Inn") { $isname = "ad_inn"; }
	//if ($name == "Town Hall") { $isname = "ad_twn_hall"; } //note: these tables still exist in DB.
	if ($name == "Stable") { $isname = "ad_stable"; }
	?>
	<tr>
		<td>
			<input class="button" type="submit" value="<?php echo $name; ?>" onclick="window.location='<?php echo "$row[type]?shop_id=$row[shop_id]";?>';">
		</td>
	</tr>
	<?php
}
if (char_intown($character->location)) {
	?>
	<tr>
		<td>
			<input class="button" type="submit" value="<?php echo $lang_town["market"]; ?>" onclick="window.location='market.php';">
		</td>
	</tr>
	<?php
}
?>
<tr>
	<td colspan=2>
		<form style="padding-top:50px;">
			<input class="button" type="button" onClick="location='travel.php';this.value='<?php echo $lang_leaving;?>'" value="<?php echo $lang_gotrav;?>">
		</form>
	</td>
</tr>

<?php
//------------- stuff on the ground -------------

auto_ground(); //tick

//$fchance = $character->finding();
$fchance = 100; //always find at same place, so you can pick up items you dropped
$ground_items = fetch_items_for_location($character->location, $fchance );

if (count($ground_items) > 0) {
    ?>
	<tr>
		<td class="center">
			<hr class="halfsize">
			<table align=center>
				<tr>
					<th colspan=4><?php echo $lang_town["u_find"]; ?></th>
				</tr>
				<?php
				foreach($ground_items as $item) {
					$info = fetch_item_additional_info($item,$character);
					$info['number'] = $info['number'] > 1?($info['number']." "):"";
					?>
					<tr>
						<td>
							<img class="icon" src="<?php echo $info['image_path'];?>">
						</td>
						<td style="color:<?php echo $info['skill_need']; ?>;">
							<?php echo $info['number'].$info['description']; ?>
						</td>
						<td>
							<?php
							actionButton($lang_town['pickup'],$this_url,
								array(
									'pickup_id[]'=> $item['id'],
									'pickup_type[]'=> $item['type'],
									'pickup_number[]'=> $item['number']
								)
							);
							?>
						</td>
					</tr>
				<?php
				}
				?>
			</table>
		</td>
	</tr>
<?php
}
?>
	<tr>
		<td class="left">
			<br><br>
			<span class="b"><?php echo $lang_town["ot_on"]; ?></span><br>
			<?php echo who_is_online($character->location); ?>
			<br><br>
			<span class="b"><?php echo $lang_town["ot_of"]; ?></span><br>
			<?php echo who_is_offline($character->location); ?>
		</td>
	</tr>
</table>

<?php require_once "footer.php";
