<?php
require_once "header.php";

// If no character created
if (empty($character->location)) {
	jsChangeLocation("create_character.php", 0);
}
// Get Slapped! If in combat and clicked travel
if(isset($_SESSION['opponent_id'])) {
	jsChangeLocation("combat.php?comb_act=npc_attack", 0);
}

require_once "travel_functions.php";

## TASKS TO RUN ON EACH CLICK ##
	beginTiming();

	// Set global var
	$params = array();
	
	// Population Control
	$result = $db->query("SELECT * FROM phaos_characters WHERE username='phaos_npc'");
	$total_mobs = $result->num_rows;
	if (DEBUG) { echo "<p class='center'>**DEBUG - Total Mob Count: $total_mobs "; }

	// Number of mobs within range
	$lowerlimit = 3000;
	$upperlimit = $lowerlimit + 200;

	// Generate mobs
	if ($total_mobs < $lowerlimit ) {
		$n = ceil(sqrt($lowerlimit - $total_mobs) * 0.20);
		$n > 6 and $n = 6;
		for($i = 0; $i < $n; ++$i) {
			npcgen();
		}
	}
	
	// Kill mobs
	if ($total_mobs > $upperlimit ) {
		$delta = 3 + (int)(($total_mobs - $upperlimit) / 100);
		$result = $db->query("SELECT id,location,username,name,race FROM phaos_characters WHERE username='phaos_npc' ORDER BY rand() LIMIT $delta");
		while ($row = $result->fetch_assoc()) {
			$mob = new character($row['id']);
			$mob->kill_characterid();
		}
		if (DEBUG) { echo "<p class='center'>**DEBUG - Killed $delta mobs for population control"; }
	}

	
	// Where the hell is this _timing COOKIE defined?
	//if(@$_COOKIE['_timing']) { echo "time end pop control=".endTiming()."<br>\n"; };

	// Set a # of NPC to select and move
	$npctomov = rand(3,9);
	// Now move those NPCs!
	for($i = 0; $i < $npctomov; $i++) {
		movenpc();
	}

	//if(@$_COOKIE['_timing']) { echo "time end pop movement=".endTiming()."<br>\n"; };
	
	// Refill Item Shops
	updateshops();

	//if(@$_COOKIE['_timing']) { echo "time end shop updates=".endTiming()."<br>\n"; };

## END TASKS ##


// CHARACTER INFORMATION
$character = new character($PHP_PHAOS_CHARID);

// Make sure character is strong enough to travel
if ($character->hit_points <= "0") {
	$destination = "";
} elseif ($character->stamina_points <= "0") {
	$destination = "";
} else {
    //FIXME: this allows an instant gate travel hack, uhm, I mean, spell
	if (is_numeric(@$_POST['destination']) and $_POST['destination'] > 0) {
		$destination = $_POST['destination'];
	} else {
		$destination = "";
	}
}

//if (@$_COOKIE['_timing']) { echo "time 1=".endTiming(); };


// Finally Allow Movement
if ($destination != "") {
	// Stamina Reduction formula
	$inv_count = $character->invent_count();
	if ($inv_count > $character->max_inventory) {
		$degrade = $inv_count - $character->max_inventory;
	} else {
		$degrade = $degrade = 1;
	}
	$character->reduce_stamina($degrade);
	
	// Get connecting squares
	$result = $db->query("SELECT above_left,above,above_right,leftside,rightside,below_left,below,below_right FROM phaos_locations WHERE id = '" . $character->location . "' ");
	$row = $result->fetch_assoc();
	foreach ($row as $item) {
		// FIXME: uses untrusted input by the user
		if ($item == $destination OR @$_POST['rune_gate'] == "yes" OR @$_POST['explorable'] == "yes") {
			$req = $db->query("UPDATE phaos_characters SET location = '$destination', stamina=".$character->stamina_points." WHERE id = '$PHP_PHAOS_CHARID'");
			if (!$req) {
				echo "<B>Error ".mysqli_errno()." :</B> ".mysqli_error().""; 
				exit;
			}
			$result = $db->query("SELECT * FROM phaos_locations WHERE id = '$destination'");
			$character->location = $destination;
			if ($row = $result->fetch_assoc()) {
				$location_name = $row["name"];
			}
		}
	}
}

// Define Mob Separators for php and escaped for javascript
$info_eol = "\r";
$js_info_eol = "\\r";

// Set var. Can player move?
$message = "";

// Is player dead?
if ($character->hit_points <= 0) {
	$message =  $lang_trav["zero_hp"]."<br>";
}
// Do we have stamina to move?
if ($character->stamina_points <= 0) {
	$message .= $lang_trav["zero_st"]."<br>";
}
// If just began travel, display message
if ($destination == "") {
	$message .= "<b>".$lang_trav["dest"]."</b>";
	draw_html($message);
}

// Are we in combat mode?
if ($destination != "") {
	$list = whos_here($character->location,'phaos_npc'); // List who to engage in combat
	if (count($list)) {
		$result = $db->query("SELECT buildings,special FROM phaos_locations WHERE id = '".$character->location."'");
		$row = $result->fetch_assoc();
		list($buildings,$special) = [$row["buildings"],$row["special"]];
		if ($buildings == "n" AND $special == 0) {
			// Yes we are!
			jsChangeLocation("combat.php?opp_type=roammonst", 0);
		}
	}
	draw_html(@$message);
}

session_destroy();

##--Function--##
function draw_html($message = '') {
	global $db, $character, $params, $lang_area, $js_info_eol;

	if (DEBUG) { $message.= "<p>**DEBUG - Current Location: ".$character->location."<br>"; }
	
	?>
	<table class="fullsize" border=0 cellspacing=0 cellpadding=0>
		<tr>
			<td align="center">
				<?php echo "$message<br>";
				// build and print map
				//list($out_loc,$marker_loc) = data_collect();
				list($out_loc) = data_collect();
				draw_all($out_loc);
				//draw_all($marker_loc);
				?>
			<br>
			</td>
		</tr>
	</table>
			<?php
			if (!isset($params['name'])) {
				$params['name']='';
			}
			// Display any existing buildings
			if ($params["buildings"] == "y" AND $params["one_building"] >= "1") {
				?>
				<div class="center">
					<form action="town.php" method="get">
						<button class="button" style="background:#000;border:#006600 solid 1px;color:#FFF;padding:4px;border-radius:3px;" type="submit">
							<img class="left" src="images/icons/enter.gif" style="padding-right:4px;"><span class="b"><?php echo $params["name"];?></span><br><?php echo $lang_area["enter"];?>
						</button>
					</form>
				</div>
				<?php
			}

			if ($params["explore"] != "") {
				?>
					<div class="center">
						<form action="travel.php" method="post">
							<input type="hidden" name="explorable" value="yes">
							<input type="hidden" name="destination" value="<?php echo $params["explore"];?>">
							<button class="button" style="background:#000;border:#006600 solid 1px;color:#FFF;" type="submit">
								<img class="left" src="images/icons/enter.gif" style="padding-right:4px;"><span class="b"><?php echo $lang_area["enter"];?></span>
							</button>
						</form>
					</div>
				<?php
			}
	
	// NOT USED CURRENTLY
	//if ($params["special_id"] > 0) {
	//	echo "<a href='area.php'><img src='images/icons/invest.gif' title='".$lang_trav["invest"]."'></a>";
	//}
	
	// Build list of rune gates
	if (@$params['rune_gate'] == "yes") {
		?>
		<div class="center">
			<span class="big msgbox">
				Choose a realm to travel to:
			</span>
			<table>
				<tr>
		<?php
		$id_gates = 'id = 4 OR id = 11111 OR id = 22111 OR id = 33111 OR id = 44111 OR id = 55111 OR id = 66111 OR id = 77111 OR id = 88111';
		$result = $db->query("SELECT id,name FROM phaos_locations WHERE $id_gates AND id != '$character->location' ORDER BY id ASC");
		while ($row = $result->fetch_assoc()) {
			echo "<form action='travel.php' method='post'>
					<td>
						<input type='hidden' name='destination' value='$row[id]'>
						<input type='hidden' name='rune_gate' value='yes'>
						<input class='button' type='submit' value='$row[name]'>
					</td>
				</form>";
		}
		?></tr>
		</table>
		</div><?php
	}
		 
	//if (@$_COOKIE['_timing']) { //???
	//	echo "time F=".endTiming();
	//}
	
	?><script>
		function displayInfo(info){
			var infoDiv= document.getElementById("info");
			var re = /<?php echo $js_info_eol; ?>/g;
			info = info.replace(re,"<br>");
			infoDiv.innerHTML= info;
		}
	</script>
	<div id="info" class="center b"></div><?php // Displays above script.
}

require_once "footer.php";
