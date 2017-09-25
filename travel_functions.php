<?php
// Select an NPC/creature to move - let's pick the one that hasn't moved in the longest time
function movenpc() {
	global $db;
	
	// Pick one random NPC at a time (includes phaos_merchants)
	$result = $db->query("SELECT id FROM phaos_characters WHERE username = 'phaos_npc' OR username = 'phaos_merchant' ORDER BY stamina_time ASC LIMIT 1");
	if ($row = $result->fetch_assoc()) {
		$npc = new character($row["id"]);

        if (DEBUG) { $GLOBALS['debugmsgs'][] = "**DEBUG: Moving Lvl($npc->level)<b>$npc->name</b>, id($npc->id) at Loc.($npc->location)"; }
		
		// Let this NPC heal HP and stamina
		if ($npc->hit_points < $npc->max_hp2) {
			$npc->hit_points += (int)($npc->max_hp2 / 5);	// heal 20%
			if ($npc->hit_points > $npc->max_hp2) { $npc->hit_points = $npc->max_hp2; }
		}
		if ($npc->stamina_points < $npc->max_stamina) {
            $npc->stamina_points += 3;     // 3 at a time -- perhaps this should be calc another way
            if ($npc->stamina_points > $npc->max_stamina) { $npc->stamina_points = $npc->max_stamina; }
        }
		// Reset stamina and regen times
		$npc->stamina_time	= time()+1000;	// FIXME: using 1000 as temp amount
		$npc->regen_time	= time()+1000;	// FIXME: using 1000 as temp amount

		// Finally, move this NPC to a passable area nearby
		if ($npc->location != 0) {
            $npc->relocate( (int)rand(1,8) );
        }

		// Correct any NPC in bad location
		if ($npc->location == 0) {
            $condition_pass = $npc->sql_may_pass();
		    // How did he get here??  -- let's put him at a random location
			$result = $db->query("SELECT id FROM phaos_locations WHERE $condition_pass ORDER BY RAND() LIMIT 1") or die(mysqli_error());
			list($newloc) = $result->fetch_assoc();
			$npc->place($newloc);
		}

		// Now we can update the DB
        $query = "UPDATE phaos_characters SET
			hit_points		=".$npc->hit_points.",
			stamina			=".$npc->stamina_points.",
			stamina_time	=".$npc->stamina_time.",
			regen_time		=".$npc->regen_time."
			WHERE id		='".$npc->id."'";
		$result = $db->query($query);
		if (!$result) {echo "$query:<B>Error ".mysqli_errno()." :</B> ".mysqli_error().""; exit;}

	} else {	// Failed to set $row from sql query
		die("We could not select a creature to move..");
	}
}

// Refill shops with more items
function updateshops() {
	global $db;
	
	if (rand(1,10) < 4) {	// Shops replenish - adjust here
		$result = $db->query("SELECT shop_id,type,item_id FROM phaos_shop_inventory WHERE quantity<max  ORDER BY RAND() LIMIT 1;");
		if ($row = $result->fetch_assoc()) {
			$db->query("UPDATE phaos_shop_inventory SET quantity = quantity+3 WHERE shop_id = '$row[shop_id]' AND type = '$row[type]' AND item_id = '$row[item_id]' ");
		}
	}
}

// LARGE 7x7 map grid
function draw_all($out_loc) {

	$locs = array(
	46,	47, 48, 25, 26, 27, 28,
	45,	23, 24,  2,  4,  5, 29,
	44,	22, 21,  1,  3,  6, 30,
	43,	20, 19, 49,  7,  8, 31,
	42,	18, 15, 13,  9, 10, 32,
	41,	17, 16, 14, 12, 11, 33,
	40,	39,	38,	37,	36,	35,	34
	);
	
	$x = 1;
	echo "<table cellpadding=0 cellspacing=1 style='background:#000;'><tr>";
	foreach ($locs as $block) {
		echo $out_loc[$block]['html'];
		$x++ == 7 && $block != 34 AND print "</tr><tr>" AND $x = 1;
	} 
	echo "</tr></table>";
}

// Output a square and all contents
function draw_square($link = false, $picture, $id='', $ch_img='images/clear.gif', $locname='', $mobs=array('text'=>''), $markers=array(), $dir='') {
	global $lang_nogrid;
	
	// Anyone else here?
	if ($mobs && $mobs['text']) {
		$ch_img = $mobs['char'][0]['image_path']; // Get NPC & others player images for square
	} else {
		$mobs = array('text'=>''); // No one to display in this square
	}
	
	// Set the clickable clear.gif, if no other image markers (ie. towns, mobs, treasure)
	(!$ch_img AND count($markers) > 0 ) AND $ch_img = 'images/'.$markers[0];
	$ch_img OR $ch_img = "images/clear.gif";

	DEBUG OR $dir = '';
	
	// Finally, draw the square
	if ($link) { // Clickable
		$image = '<input style="width:48px;height:48px;border:none;background:none;" type="image" src="'.$ch_img.'" alt="'.$mobs['text'].'" onMouseOver="displayInfo(this.alt);" name="destination_button" value="'.$id.' ">'; //kaspir removed: displayInfo(\'\'); from MouseOut
	
		$square = '<form action="travel.php" method="post">
		<td align=center valign=middle style="background:url('.$picture.');">
			<input type="hidden" name="destination" value="'.$id.'">'.$image.'
		</td>
		</form>';
	} else { // Not clickable
		if($picture) {
			$image = '<img style="width:48px;height:48px;" src="'.$ch_img.'">';
		} else { // Failed to get image as $ch_img
			$image = '<img src="images/land/49.png" alt="'.$lang_nogrid.'" title="'.$lang_nogrid.'">';
			$picture = "images/land/49.png"; // NOTE: 49.png is the all water image
		}
		
		$square = '<td width=52 height=52 align=center valign=middle style="background:url('.$picture.');">'.$image.'</td>';
	}

	return $square;
}

// Collect all data for drawing
function data_collect() {
	global $db, $character, $lang_treasure, $params;

	//if (@$_COOKIE['_timing']) {
	//	echo "time begin DC=".endTiming();
	//}
	
	// Fetch all data for current location
	$result = $db->query("SELECT * FROM phaos_locations WHERE id = '".$character->location."'");
	if ($row = $result->fetch_assoc()) {
		$out_loc[49]['id'] = $character->location; // Assign center square to current location
		$character_locname = $row['name'];

		// Fetch & display any special items & treasure, assign as markers
		$markers = array();
		$fchance = $character->finding();
		$ground_items = fetch_items_for_location($character->location, $fchance );
		if (count($ground_items) > 0) {
			$markers[] = 'icons/gold.gif';
			if ($markers > 0) {
				echo 	'<div class="center">
							<form method="POST" action="town.php">
								<input class="button" type="submit" value="'.$lang_treasure.'" name="submit">
							</form>
						</div>';
			}
		}

		// Draw only the center square (current position) & player image
		$out_loc[49]['html'] = draw_square(false, $row["image_path"], '', $character->image, $row["name"], array('text'=>''), $markers, 49);
		
		// Check for any parameters for current position
		$runegates = array(4, 11111, 22111, 33111, 44111, 55111, 66111, 77111, 88111);
		if (in_array($row['id'], $runegates)) {
			$params['rune_gate'] = "yes";
		}
		$params['name'] = $row['name'];
		//$params['special_id'] = $row["special"];
		$params['buildings'] = $row['buildings'];
		$params['explore'] = $row['explore'];
		
		// Check for any buildings
		$build_check = $db->query("SELECT type FROM phaos_buildings WHERE location = '".$character->location."'");
		$numrows = $build_check->num_rows;
		$params['one_building'] = $numrows;
		if ($bui = $build_check->fetch_assoc()) {
			$params['building_type'] = $bui['type'];
		} else {
			$params['building_type'] = "";
		}
		
		// Identify clickable squares using phaos_location data
		$out_loc[1]['id'] = $row["above"];			$out_loc[1]['link'] = true; 	$out_loc[1]['block'] = 0;
		$out_loc[3]['id'] = $row["above_right"];	$out_loc[3]['link'] = true; 	$out_loc[3]['block'] = 0;
		$out_loc[7]['id'] = $row["rightside"];		$out_loc[7]['link'] = true; 	$out_loc[7]['block'] = 0;
		$out_loc[9]['id'] = $row["below_right"];	$out_loc[9]['link'] = true; 	$out_loc[9]['block'] = 0;
		$out_loc[13]['id'] = $row["below"];			$out_loc[13]['link'] = true; 	$out_loc[13]['block'] = 0;
		$out_loc[15]['id'] = $row["below_left"];	$out_loc[15]['link'] = true; 	$out_loc[15]['block'] = 0;
		$out_loc[19]['id'] = $row["leftside"];		$out_loc[19]['link'] = true; 	$out_loc[19]['block'] = 0;
		$out_loc[21]['id'] = $row["above_left"];	$out_loc[21]['link'] = true; 	$out_loc[21]['block'] = 0;

		// Begin collecting corner square data using above clickable data
		$res = $db->query("SELECT above_left,above_right,below_right FROM phaos_locations WHERE id = ".$out_loc[3]['id']);
		if ($row = $res->fetch_assoc()) {
			$out_loc[2]['id'] = $row["above_left"];
			$out_loc[5]['id'] = $row["above_right"];
			$out_loc[8]['id'] = $row["below_right"];
		}
		$res = $db->query("SELECT above_right,below_right,below_left FROM phaos_locations WHERE id = ".$out_loc[9]['id']);
		if ($row = $res->fetch_assoc()) {
			$out_loc[8]['id'] = $row["above_right"];
			$out_loc[11]['id'] = $row["below_right"];
			$out_loc[14]['id'] = $row["below_left"];
		}
		$res = $db->query("SELECT below_right,below_left,above_left FROM phaos_locations WHERE id = ".$out_loc[15]['id']);
		if ($row = $res->fetch_assoc()) {
			$out_loc[14]['id'] = $row["below_right"];
			$out_loc[17]['id'] = $row["below_left"];
			$out_loc[20]['id'] = $row["above_left"];
		}
		$res = $db->query("SELECT below_left,above_left,above_right FROM phaos_locations WHERE id = ".$out_loc[21]['id']);
		if ($row = $res->fetch_assoc()) {
			$out_loc[20]['id'] = $row["below_left"];
			$out_loc[23]['id'] = $row["above_left"];
			$out_loc[2]['id'] = $row["above_right"];
		}
		
		// Now collect more data all in one go to speed up things. (we use @ to block php undefined notices on water blocks, map edges)
		@$set = "('".$out_loc[2]['id']."','".$out_loc[3]['id']."','".$out_loc[5]['id']."','".$out_loc[8]['id']."','".$out_loc[9]['id']."','".$out_loc[11]['id']."',
				 '".$out_loc[14]['id']."','".$out_loc[15]['id']."','".$out_loc[17]['id']."','".$out_loc[20]['id']."','".$out_loc[21]['id']."','".$out_loc[23]['id']."')";
		$data_locations = fetch_all("SELECT * FROM phaos_locations WHERE id IN ".$set);
		foreach ($data_locations as $data_location) {
			$cache_row[$data_location['id']] = $data_location;
		}

		if (@$out_loc[3]['id']) {
			$row = $cache_row[$out_loc[3]['id']];
			$out_loc[2]['id'] = $row["above_left"];		$out_loc[2]['block'] = 0;
			$out_loc[4]['id'] = $row["above"];			$out_loc[4]['block'] = 0;
			$out_loc[5]['id'] = $row["above_right"];	$out_loc[5]['block'] = 0;
			$out_loc[6]['id'] = $row["rightside"];		$out_loc[6]['block'] = 0;
			$out_loc[8]['id'] = $row["below_right"];	$out_loc[8]['block'] = 0;
		}
		if (@$out_loc[9]['id']) {
			$row = $cache_row[$out_loc[9]['id']];
			$out_loc[8]['id'] = $row["above_right"];	$out_loc[8]['block'] = 0;
			$out_loc[10]['id'] = $row["rightside"];		$out_loc[10]['block'] = 0;
			$out_loc[11]['id'] = $row["below_right"];	$out_loc[11]['block'] = 0;
			$out_loc[12]['id'] = $row["below"];			$out_loc[12]['block'] = 0;
			$out_loc[14]['id'] = $row["below_left"];	$out_loc[14]['block'] = 0;
		}
		if (@$out_loc[15]['id']) {
			$row = $cache_row[$out_loc[15]['id']];
			$out_loc[14]['id'] = $row["below_right"];	$out_loc[14]['block'] = 0;
			$out_loc[16]['id'] = $row["below"];			$out_loc[16]['block'] = 0;
			$out_loc[17]['id'] = $row["below_left"];	$out_loc[17]['block'] = 0;
			$out_loc[18]['id'] = $row["leftside"];		$out_loc[18]['block'] = 0;
			$out_loc[20]['id'] = $row["above_left"];	$out_loc[20]['block'] = 0;
		}
		if (@$out_loc[21]['id']) {
			$row = $cache_row[$out_loc[21]['id']];
			$out_loc[20]['id'] = $row["below_left"];	$out_loc[20]['block'] = 0;
			$out_loc[22]['id'] = $row["leftside"];		$out_loc[22]['block'] = 0;
			$out_loc[23]['id'] = $row["above_left"];	$out_loc[23]['block'] = 0;
			$out_loc[24]['id'] = $row["above"];			$out_loc[24]['block'] = 0;
			$out_loc[2]['id'] = $row["above_right"];	$out_loc[2]['block'] = 0;
		}
		
		if (@$out_loc[2]['id']) {
			$row = $cache_row[$out_loc[2]['id']];
			$out_loc[24]['id'] = $row["leftside"];		$out_loc[24]['block'] = 0;
			$out_loc[48]['id'] = $row["above_left"];	$out_loc[48]['block'] = 0;
			$out_loc[25]['id'] = $row["above"];			$out_loc[25]['block'] = 0;
			$out_loc[26]['id'] = $row["above_right"];	$out_loc[26]['block'] = 0;
			
		}
		if (@$out_loc[5]['id']) {
			$row = $cache_row[$out_loc[5]['id']];
			$out_loc[26]['id'] = $row["above_left"];	$out_loc[26]['block'] = 0;
			$out_loc[27]['id'] = $row["above"];			$out_loc[27]['block'] = 0;
			$out_loc[28]['id'] = $row["above_right"];	$out_loc[28]['block'] = 0;
			$out_loc[29]['id'] = $row["rightside"];		$out_loc[29]['block'] = 0;
			$out_loc[30]['id'] = $row["below_right"];	$out_loc[30]['block'] = 0;
		}
		if (@$out_loc[8]['id']) {
			$row = $cache_row[$out_loc[8]['id']];
			$out_loc[30]['id'] = $row["above_right"];	$out_loc[30]['block'] = 0;
			$out_loc[31]['id'] = $row["rightside"];		$out_loc[31]['block'] = 0;
			$out_loc[32]['id'] = $row["below_right"];	$out_loc[32]['block'] = 0;
		}

		if (@$out_loc[11]['id']) {
			$row = $cache_row[$out_loc[11]['id']];
			$out_loc[32]['id'] = $row["above_right"];	$out_loc[32]['block'] = 0;
			$out_loc[33]['id'] = $row["rightside"];		$out_loc[33]['block'] = 0;
			$out_loc[34]['id'] = $row["below_right"];	$out_loc[34]['block'] = 0;
			$out_loc[35]['id'] = $row["below"];			$out_loc[35]['block'] = 0;
			$out_loc[36]['id'] = $row["below_left"];	$out_loc[36]['block'] = 0;	
		}
		if (@$out_loc[14]['id']) {
			$row = $cache_row[$out_loc[14]['id']];
			$out_loc[36]['id'] = $row["below_right"];	$out_loc[36]['block'] = 0;
			$out_loc[37]['id'] = $row["below"];			$out_loc[37]['block'] = 0;
			$out_loc[38]['id'] = $row["below_left"];	$out_loc[38]['block'] = 0;
		}
		if (@$out_loc[17]['id']) {
			$row = $cache_row[$out_loc[17]['id']];
			$out_loc[38]['id'] = $row["below_right"];	$out_loc[38]['block'] = 0;
			$out_loc[39]['id'] = $row["below"];			$out_loc[39]['block'] = 0;
			$out_loc[40]['id'] = $row["below_left"];	$out_loc[40]['block'] = 0;
			$out_loc[41]['id'] = $row["leftside"];		$out_loc[41]['block'] = 0;
			$out_loc[42]['id'] = $row["above_left"];	$out_loc[42]['block'] = 0;
		}
		if (@$out_loc[20]['id']) {
			$row = $cache_row[$out_loc[20]['id']];
			$out_loc[42]['id'] = $row["below_left"];	$out_loc[42]['block'] = 0;
			$out_loc[43]['id'] = $row["leftside"];		$out_loc[43]['block'] = 0;	
			$out_loc[44]['id'] = $row["above_left"];	$out_loc[44]['block'] = 0;
		}
		if (@$out_loc[23]['id']) {
			$row = $cache_row[$out_loc[23]['id']];
			$out_loc[44]['id'] = $row["below_left"];	$out_loc[44]['block'] = 0;
			$out_loc[45]['id'] = $row["leftside"];		$out_loc[45]['block'] = 0;
			$out_loc[46]['id'] = $row["above_left"];	$out_loc[46]['block'] = 0;
			$out_loc[47]['id'] = $row["above"];			$out_loc[47]['block'] = 0;
			$out_loc[48]['id'] = $row["above_right"];	$out_loc[48]['block'] = 0;
		}
	}

	//$marker_loc = array(); // Not really using this var for some reason
	$close_locs = array(1,3,7,9,13,15,19,21,49); // These squares are touching center postion, boxed around it, and are clickable/move-to

	$locs = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48);
	foreach ($locs as $i) {
		if (@$out_loc[$i]['html'] == '') {
			if (@out_loc[$i]['block'] == 0 AND @$out_loc[$i]['id'] != 0) {
				$mobs = "";
				$mobs = getmobs($out_loc[$i]['id']);
				$result = $db->query("SELECT name,image_path,pass FROM phaos_locations WHERE id=".$out_loc[$i]['id']." ");
				$row = $result->fetch_assoc();
				
				// Display treasures if any
				$markers = array();
				if (in_array($i,$close_locs) || $character->finding() >= 100 ){ // Original Setting is 100. This has to be higher than $character->finding in class_character.php LINE77
					$ground_items = fetch_items_for_location($out_loc[$i]['id'], $fchance ); //fetch_items_for_location function in items.php
					if (count($ground_items) > 0) {
						$markers[] = 'icons/gold.gif';
					}
				}
				
				// Allow click/move to this square?
				if ($row['pass'] == 'n') {
					$out_loc[$i]['html'] = draw_square(false, $row['image_path'], $out_loc[$i]['id'], '', $row['name'], $mobs, $markers, $i);
				} else {
					$out_loc[$i]['html'] = draw_square(@$out_loc[$i]['link'], $row['image_path'], $out_loc[$i]['id'], '', $row['name'], $mobs, $markers, $i);
				}

			} else {
				// HACK: This allows us to load a faulty square image if data is not collected property.
				if (stristr($character_locname,'Dungeon') == true) {
					$out_loc[$i]['html'] = draw_square(false, "images/land/195.png", 0,'',"Dungeon");
				} else {
					$out_loc[$i]['html'] = draw_square(false, "images/land/49.png", 0,'',"Water");
				}
    		}
		}
	}
	//if(@$_COOKIE['_timing']) { 
	//	echo "<br>time end DC=".endTiming();
	//}
	
	//return array($out_loc,$marker_loc);
	return array($out_loc);
}

// @See Also: whos_here() in global.php
function getmobs ($loc) {
	global $db, $info_eol;
	// Return Monsters - that are at this location
	// FYI: REALLY long monster names cause problems with mouse-overs
	$mobs['char'] = array();
	$mobs['text'] = '';

	// Show all players, monsters & NPCs outside of the arena.
	$res = $db->query("SELECT name,level,image_path, (username NOT LIKE '%arena%') AS isnpc FROM phaos_characters WHERE location = $loc AND username NOT LIKE '%arena%' ORDER BY isnpc ASC");
	if (!$res) {showError(__FILE__,__LINE__,__FUNCTION__); exit;}

	$i = 0;
	while ($row = $res->fetch_assoc()) {
		$mobs['char'][$i++] = $row;
		$mobs['text'].= $info_eol."$row[name] (Lvl $row[level])<br>";
	}
	return $mobs;
}