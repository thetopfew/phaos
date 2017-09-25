<?php
require_once "header.php";

// List all character icons from folder for dropdown
function GetAppearanceList($folder) {
	$dirFiles = array();
	// Opens Images Folder
	if ($handle = opendir($folder)) {
		while (false !== ($file = readdir($handle))) {
			$dirFiles[] = $file;
		}
	}
	sort($dirFiles);
	foreach($dirFiles as $file) {
		// Strips file extensions      
		$crap = array(".jpg", ".jpeg", ".png", ".gif", ".bmp", "_", "-");    
		$newstring = str_ireplace($crap, " ", $file);
		
		if (is_file($folder.$file)) {
			$sList .= "<option value='".$folder.$file."'>".$newstring."</option>\n";
		}
	}
	closedir($handle);
	return $sList;
}
?>

<script>
	function verify() {
		if (document.form.name.value == "") {
			window.alert("<?php echo $lang_crt["u_must"]; ?>");
			return false;
		} else if (document.getElementById("new_image_path").value == "nothing") {
			window.alert("<?php echo $lang_crt["u_must2"]; ?>");
			return false;
		} else {
			return true;
		}
	}

	function LoadAppearance(sPreviewID) {
		var oAppearance = window.document.getElementById("new_image_path");
		var sAppearanceImg = oAppearance.options[oAppearance.selectedIndex].value;

		window.document.getElementById(sPreviewID).src = sAppearanceImg;
	}
</script>

<?php
// Fail if user already has a character?? POSSIBLY: Change to disallow dupilicate character names instead?
$result = $db->query("SELECT * FROM phaos_characters WHERE username = '$PHP_PHAOS_USER' LIMIT 1");
if ($result->num_rows) {
	$duplicate = "yes";
}

//if ($create_char == "yes") {
if (isset($_POST['create_char'])) {
	if (empty($duplicate)) {
		// Get Race Starting Stats
		$races_lookup = $db->query("SELECT * FROM phaos_races WHERE name = '$race'");
		if ($row = $races_lookup->fetch_assoc()) {
			$race_name = $row["name"];
			$strength = $row["str"];
			$dexterity = $row["dex"];
			$wisdom = $row["wis"];
			$constitution = $row["con"];
		}
		// Get Starting Attack & Defense & Skills
		$skillquery = $db->query("SELECT fight,defence,weaponless FROM phaos_classes WHERE name='$class'");
		$skills = $skillquery->fetch_assoc();
		
		if (DEBUG) {
			$GLOBALS['debugmsgs'][] = "**DEBUG: $lang_race : $race_name\n";
			$GLOBALS['debugmsgs'][] = "**DEBUG: $lang_class : $class\n";
			$GLOBALS['debugmsgs'][] = "**DEBUG: $lang_str : $strength\n";
			$GLOBALS['debugmsgs'][] = "**DEBUG: $lang_dex : $dexterity\n";
			$GLOBALS['debugmsgs'][] = "**DEBUG: $lang_wis : $wisdom\n";
			$GLOBALS['debugmsgs'][] = "**DEBUG: $lang_cons : $constitution\n";
			$GLOBALS['debugmsgs'][] = "**DEBUG: $lang_att : $skills[fight]\n";
			$GLOBALS['debugmsgs'][] = "**DEBUG: $lang_def : $skills[defence]\n";
			$GLOBALS['debugmsgs'][] = "**DEBUG: $lang_wplss : $skills[weaponless]\n";
		}
		
		// Begin creating new character in database
		$attribute_check = $strength + $dexterity + $wisdom + $constitution;
		if ($attribute_check == "24" AND preg_match('/^[a-zA-Z_\-0-9]{3,15}$/', $name)) {
			// Set player starting vars
			$start_gold = 5000;
				// These must match whats in class-character.php
				$stamina = (int)(($constitution * 10) + ($strength * 2)) * 5;
				$hit_points = ($constitution * 15) + 2;
			
			//$startloc = rand(1,225);	// incase no race is given??  How does that happen?
			// These specific locations are the best starting cities for each race.
			$race == "Orc"			AND $startloc = 1037; // City of Nising
			$race == "Vampire"		AND $startloc = 25112; // City of Blood Moon
			$race == "Lizardfolk"	AND $startloc = 77111; // Rune Gate (Land of the Qu-Nai)
			$race == "Undead"		AND $startloc = 25112; // City of Blood Moon
			$race == "Gnome"		AND $startloc =	5170; // Town of Kjal
			$race == "Elf"			AND $startloc = 6173; // City of Allisan
			$race == "Dwarf"		AND $startloc = 7179; // City of Pah-Loran
			$race == "Human"		AND $startloc = 8052; // City of Doonmoor
			
			//$startloc = array(1037,5170,6173,7179,8052,25112); #TRYME
			
			// Set race start location
			// if ($race == "Lizardfolk")		{$startloc = rand(3001,3225);}
			// else if ($race == "Gnome")		{$startloc = rand(5001,5225);}
			// else if ($race == "Orc")			{$startloc = rand(1001,1225);}
			// else if ($race == "Vampire")		{$startloc = rand(2001,2225);}
			// else if ($race == "Dwarf")		{$startloc = rand(7001,7225);}
			// else if ($race == "Undead")		{$startloc = rand(4001,4225);}
			// else if ($race == "Human")		{$startloc = rand(8001,8225);}
			// else if ($race == "Elf")			{$startloc = rand(6001,6225);}
			// else								{$startloc = rand(1,225);}
			
			$character = new character($PHP_PHAOS_CHARID);
			if ($character) {
				// Give random low weapon
				$randm = array(1,10,19);
				$weapon = $randm[array_rand($randm, 1)];
				$item_id = $weapon;
				$item_type = 'weapon';
				$character->add_item($item_id,$item_type);
				
				// Give random low armor
				$armor = rand(11,13);
				$item_id = $armor;
				$item_type = 'armor';
				$character->add_item($item_id,$item_type);
				
				// Give random low boots
				$boots = rand(105,106);
				$item_id = $boots;
				$item_type = 'boots';
				$character->add_item($item_id,$item_type);
				
				// Set starting gears to 'equipped' in table
				$db->query("UPDATE phaos_char_inventory SET equiped='Y' WHERE username='$PHP_PHAOS_USER'");
			}

			// Insert new character data to table
			$sql = "INSERT INTO phaos_characters (location,username,name,sex,level,image_path,strength,dexterity,wisdom,constitution,hit_points,race,class,gold,stamina,fight,defence,weaponless,weapon,boots,armor) 
			VALUES
			('$startloc','$PHP_PHAOS_USER','$name','$sex','1','$new_image_path','$strength','$dexterity','$wisdom','$constitution','$hit_points','$race','$class','$start_gold','$stamina','".$skills["fight"]."','".$skills["defence"]."','".$skills["weaponless"]."','$weapon','$boots','$armor')";
			$inject = $db->query($sql);
			
			// TASK: DELETE CHARACTER FILES WITH BLANK NAMES
			$db->query("DELETE FROM phaos_characters WHERE name = ''");
			
			// Display Creation Success
			echo "<h4 class='center b'>".$lang_crt['char_succ_crt']."</h4><br>";
			jsChangeLocation("travel.php", 3);
		} else {
			echo "<br><h4 class='center b red'>".$lang_crt['fail_name']."</h4><br>";
			jsChangeLocation("logout.php", 3);
		}
	} else {
		echo "<br><h4 class='center b red'>".$lang_crt['alr_hav_char']."</h4><br>";
		jsChangeLocation("logout.php", 3);
	}
}
?>

<table border=1 cellspacing=0 cellpadding=5 align=center>
	<form action="create_character.php" method="post" name="form" id="form" onsubmit="return verify();" >
		<tr>
			<td align="center" colspan=2>
				<span class="big b"><?php echo $lang_crt["crt_a__char"]; ?></span>
			</td>
		</tr>
		<tr>
			<td align="right">
				<?php echo $lang_name;?>
			</td>
			<td align="left">
				<input type="text" name="name" size="17" maxlength="15">
			</td>
		</tr>
		<tr>
			<td align="right">
				<?php echo $lang_sex;?>
			</td>
			<td align="left">
				<select name="sex">
					<option value="Male"><?php echo $lang_male;?></option>
					<option value="Female"><?php echo $lang_female;?></option>
				</select>
			</td>
		</tr>
		<tr>
			<td align="right">
				<?php echo $lang_race;?>
			</td>
			<td align="left">
				<select name="race">
					<?php
					$races = $db->query("SELECT name FROM phaos_races ORDER BY name ASC");
					if ($row = $races->fetch_assoc()) {
						do {
							$race_name = $row["name"];
							print ("<option value='$race_name'>$race_name</option>");
						} while ($row = $races->fetch_assoc());
					}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td align="right">
				<?php echo $lang_class;?>
			</td>
			<td align="left">
				<select name="class">
					<?php
					$classes = $db->query("SELECT name FROM phaos_classes ORDER BY name ASC");
					if ($row = $classes->fetch_assoc()) {
						do {
							$class_name = $row["name"];
							print ("<option value='$class_name'>$class_name</option>");
						} while ($row = $classes->fetch_assoc());
					}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td align="right">
				<?php echo $lang_crt["sel_char_img"]; ?>
			</td>
			<td align="left">
				<select id="new_image_path" name="new_image_path" onchange="javascript: LoadAppearance('current_appearance');">
						<option value="nothing" selected disabled="disabled"><?php echo $lang_drop_sel;?></option>
						<?php echo GetAppearanceList("images/icons/characters/"); ?>
				</select>
			</td>
		</tr>
		<tr>
			<td align="center" colspan=2>
				<img id="current_appearance" name="current_appearance" src="images/icons/not-selected.gif">
			</td>
		</tr>
		<tr>
			<td align="center" colspan=2>
				<input type="hidden" name="create_char" value="yes">
				<input class="button" type="submit" value="<?php echo $lang_crt["crt_char"];?>">
			</td>
		</tr>
	</form>
</table>

<?php require_once "footer.php";
