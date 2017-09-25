<?php
require_once "item_functions.php";

class character {
	
	// PHP4-style constructor.
    // This will NOT be invoked, unless a sub-class that extends `character` calls it.
    // In that case, call the new-style constructor to keep compatibility.
	//public function character($id) {
    //    self::__construct();
    //}
	
	/**
    * Constructor
	* @param id
	*/
	public function __construct($id) {
		global $db;
		
		$result = $db->query("SELECT * FROM phaos_characters WHERE id = '$id' LIMIT 1");
		if ($row = $result->fetch_assoc()) {
			// Define Main vars
			$this->id 			= $row["id"];
			$this->name 		= $row["name"];
			$this->user 		= $row["username"];
			$this->cclass 		= $row["class"];
			$this->race 		= $row["race"];
			$this->sex 			= $row["sex"];
			$this->image 		= $row["image_path"];
			$this->location 	= $row["location"];
			// Define Attribute vars
			$this->strength 	= $row["strength"];
			$this->dexterity 	= $row["dexterity"];
			$this->wisdom 		= $row["wisdom"];
			$this->constitution = $row["constitution"];
			// Define Changeable vars
			$this->hit_points 	= $row["hit_points"];
			$this->stamina_points = $row["stamina"];
			if ($row['level'] == 0 OR $row['level'] == "") {
				$this->level = 1;
			} else {
				$this->level = (int)$row['level'];
			}
			$this->xp 			= (int)$row["xp"];
			$this->gold 		= $row["gold"];
			$this->stat_points 	= $row["stat_points"];
			// Define Equipment vars
			$this->weapon 		= $row["weapon"];
			$this->armor 		= $row["armor"];
			$this->boots 		= $row["boots"];
			$this->shield 		= $row["shield"];
			$this->gloves 		= $row["gloves"];
			$this->helm 		= $row["helm"];

			// Calculated Stuff
			$this->available_points = $this->strength + $this->dexterity + $this->wisdom + $this->constitution;
			$this->max_hp = ($this->constitution * 15) + ($this->level * 2); //kaspirs max_hp, we now gain another +2 per level.
			$this->max_hp2 = $this->constitution * $this->level; //added by kaspir, for mob max hitpoints
			$this->max_stamina = (int)(($this->constitution * 10)+($this->strength * 2)) * 5;
			$this->max_rep = 3;
			// Other Stuff
			$this->regen_time = $row["regen_time"];
			$this->stamina_time = $row["stamina_time"];
			$this->rep_time = $row["rep_time"];
			$this->no_regen_hp = $row["hit_points"];
			// Regeneration
			$actTime = time();
			$this->time_since_regen = $actTime - $this->regen_time;
			$this->stamina_time_since_regen = $actTime - $this->stamina_time;
			$this->rep_time_since_regen = $actTime - $this->rep_time;
			// Skills
			$this->fight = $row["fight"];
			$this->defence = $row["defence"];
			$this->weaponless = $row["weaponless"];
			// Reputation
			$this->rep_points = $row["rep_points"];
			$this->rep_helpfull = $row["rep_helpfull"];
			$this->rep_generious = $row["rep_generious"];
			$this->rep_combat = $row["rep_combat"];
			// Weapon & Fight Calculation
			// Fill Weapon
			$result = $db->query("SELECT * FROM phaos_weapons WHERE id = '".$this->weapon."'");
			if ($row = $result->fetch_assoc()) {
				$this->weapon_min = $row["min_damage"];
				$this->weapon_max = $row["max_damage"];
				$this->weapon_name = $row["name"];
			} else { # ADDME: Base hand damage (weaponless) based on race.
                $this->weapon = 0;
				$this->weapon_min = 0;
				$this->weapon_max = 1;
				$this->weapon_name = 'Bare Hands';
			}
			// Fill Armor
			$result = $db->query("SELECT armor_class FROM phaos_armor WHERE id = '".$this->armor."'");
			if ($row = $result->fetch_assoc()) {
				$this->armor_ac = $row["armor_class"];
			} else {
				$this->armor_ac = 0;
			}
			$result = $db->query("SELECT armor_class FROM phaos_boots WHERE id = '".$this->boots."'");
			if ($row = $result->fetch_assoc()) {
				$this->boots_ac = $row["armor_class"];
			} else {
				$this->boots_ac = 0;
			}
			$result = $db->query("SELECT armor_class FROM phaos_gloves WHERE id = '".$this->gloves."'");
			if ($row = $result->fetch_assoc()) {
				$this->gloves_ac = $row["armor_class"];
			} else {
				$this->gloves_ac = 0;
			}
			$result = $db->query("SELECT armor_class FROM phaos_shields WHERE id = '".$this->shield."'");
			if ($row = $result->fetch_assoc()) {
				$this->shield_ac = $row["armor_class"];
			} else {
				$this->shield_ac = 0;
			}
			$result = $db->query("SELECT armor_class FROM phaos_helmets WHERE id = '".$this->helm."'");
			if ($row = $result->fetch_assoc()) {
				$this->helm_ac = $row["armor_class"];
			} else {
				$this->helm_ac = 0;
			}

			// Max Out Inventory Space
			$this->max_inventory = ceil($this->strength * 2);
			if ($this->max_inventory > 50) {
				$this->max_inventory = 50;
			}	
		} else {
			global $lang_na;
			$this->name = $lang_na;
			$this->strength = $lang_na;
			$this->dexterity = $lang_na;
			$this->wisdom = $lang_na;
			$this->constitution = $lang_na;
			$this->hit_points = $lang_na;
			$this->max_hp = $lang_na;
			$this->max_hp2 = $lang_na;
			$this->weapon = $lang_na;
			$this->armor = $lang_na;
			$this->boots = $lang_na;
			$this->shield = $lang_na;
			$this->gloves = $lang_na;
			$this->helm = $lang_na;
			$this->level = $lang_na;
			$this->next_lev_xp = $lang_na;
			$this->xp = $lang_na;
			$this->gold = $lang_na;
			$this->available_points = $lang_na;
		}
		
        //get location to be able to have location modifiers
        //$this->location_data = fetch_first("SELECT * FROM phaos_locations WHERE id='$this->location'"); //KAS, removed for php7, not sure if needed anymore yet.
        //FIXME: since characters now have location data, many places in the code don't need to fetch it.
	}

	// Main vars
 	var $id;
	var $name;
	var $user;
	var $cclass; //instead of Class only!
	var $race;
	var $image;
	var $sex;
	var $location;
	var $location_data;
	// Attribute vars
	var $strength;
	var $dexterity;
	var $wisdom;
	var $constitution;
	// Changeable vars
	var $hit_points;
	var $stamina_points;
	var $level;
	var $xp;
	var $gold;
	var $stats_points;
	// Equipment vars
	var $weapon;
	var $armor;
	var $gloves;
	var $helm;
	var $shield;
	var $boots;
	// Calculated vars
	var $max_hp; // char max hp
	var $max_hp2; // mob max hp
	var $next_level;
	var $available_points;
	var $max_stamina;
	var $stamina_degrade;
	var $max_rep;
	// Other vars
	var $time_since_regen;
	var $stamina_time_since_regen;
	var $rep_time_since_regen;
	var $regen_time;
	var $rep_time;
	var $no_regen_hp;
	// Fighting_Vars
	var $weapon_min;
	var $weapon_max;
	var $weapon_name;
	var $armor_ac;
	var $boots_ac;
	var $gloves_ac;
	var $shield_ac;
	var $helm_ac;
	// Inventory
	var $max_inventory;
	// Skills
	var $fight;
	var $defence;
	var $weaponless;
	// Reputation
	var $rep_points;
	var $rep_helpfull;
	var $rep_generious;
	var $rep_combat;
	// end_of_vars


	function finding() {
		// TODO: finding could be a character skill. Casters get more here..
 		$finding = ($this->wisdom * 0.50);
 		$fchance = (int)(33.33 * sqrt($finding)); // DONT add random effects to modify fchance, this has to be deterministic , this is called in travel.php & town.php.
 		return $fchance;
 	}

 	function ac() {
 		return $this->armor_ac + $this->boots_ac + $this->gloves_ac + $this->helm_ac + $this->shield_ac;
	}

	function attack_skill_min() {
 		$skill = $this->weapon == 0 ? $this->weaponless : $this->fight;
 		$skill += $this->attack_bonus();
		return fairInt(pow($skill * $this->dexterity,0.10));
 	}

 	function attack_skill_max() { 
 		$crits = fairInt(pow($this->dexterity,0.15)); // Critical Hits
		return $this->attack_skill_min() + $crits;
	}

	function defense_bonus() {
        if ($this->location_data) {
            if ('Elf' == $this->race && stristr($this->location_data['name'],'Woodland') !== false) {
                return 1 + fairInt($this->level * 0.1);
            }
			if ('Dwarf' == $this->race && stristr($this->location_data['image_path'],'images/land/4.png') !== false) {
                return 1 + fairInt($this->level * 0.1);
            }
        }
    }
	
	function defense_min() {
		return $this->ac() + 1 + $this->defense_bonus(); //kaspir, defense bonus is in, but not skill??
	}
	
	function defense_max() {
		return $this->defense_min() + fairInt(sqrt($this->dexterity * ($this->defence + 3)));
	}	
	
	function defense_skill_min() {
		return fairInt($this->ac() + $this->weaponless + $this->defense_bonus());
	}
	
	function defense_skill_max() {
		return $this->defense_min() + fairInt(sqrt($this->dexterity * ($this->defence + 1)));
	}

	function attack_min() {
		if ($this->weapon == 0) {
			return $this->attack_skill_min() + ($this->weaponless + $this->strength);
		} else {
			return $this->attack_skill_min() + ($this->weapon_min * $this->strength); //kaspir, weapons are now more accurate to thier min-max. Below, see dex helps increase dmg.
		}
	}

	function attack_max() {
		$delta = 2; // add a offset for balance
		if ($this->weapon == 0) {
			$skill = ($this->weaponless + $this->strength) * $this->attack_skill_max() + $delta;
		} else {
			$skill = ($this->weapon_max * $this->strength) * $this->attack_skill_max() + $delta;
		}
		return ($skill); //tweak finer here for everyone.
	}
	
	// START MOBS - added v1.0b, dmg_to_char (mob damage) in combat.php, To seperate from players, and to remove the use of weapon's dam from monsters! 
	function attack_min_mob() {
		return ($this->level * ($this->strength));
	}

	function attack_max_mob() {
		return ($this->attack_min_mob() * ($this->dexterity * 0.10));
	}
	
	function mob_defense_min() { // Because AC is shown in Monster Index based from Opponenets DB.
		return ($this->armor); // So we use the armor column to copy $blueprint[AC]
	}
	
	function mob_defense_max() {
		return ($this->mob_defense_min() + $this->level);
	}
	//END MOBS

	function fight_reduction() {
		$factor = $this->stamina_points / $this->max_stamina;
		if ($factor > 0.66) {
			return 1;
		} elseif ($factor < 0.33) {
			return 0.33;
		} else {
			return 0.33 + ($factor - 0.33) * 2;
		}
	}

	function attack_roll($comb_act,$test = false) {
		if (DEBUG) {
			if (!$this->user == "phaos_npc") {
				$GLOBALS['debugmsgs'][] = "**DEBUG: weapon name: $this->weapon_name";
				$GLOBALS['debugmsgs'][] = "**DEBUG: weapon dam: ".$this->weapon_min." - ".$this->weapon_max."";
				$GLOBALS['debugmsgs'][] = "**DEBUG: weaponless: ".$this->weaponless."";
				$GLOBALS['debugmsgs'][] = "**DEBUG: attack_skill_min: ".$this->attack_skill_min()."";
				$GLOBALS['debugmsgs'][] = "**DEBUG: attack_skill_max: ".$this->attack_skill_max()."";
				$GLOBALS['debugmsgs'][] = "**DEBUG: attack_min: ".$this->attack_min()."";
				$GLOBALS['debugmsgs'][] = "**DEBUG: attack_max: ".$this->attack_max()."";
			}
			if ($this->user == "phaos_npc") {
				$GLOBALS['debugmsgs'][] = "**DEBUG: strength: $this->strength";
				$GLOBALS['debugmsgs'][] = "**DEBUG: dexterity: $this->dexterity";
				$GLOBALS['debugmsgs'][] = "**DEBUG: wisdom: $this->wisdom";
				$GLOBALS['debugmsgs'][] = "**DEBUG: vitality: $this->constitution";
				$GLOBALS['debugmsgs'][] = "**DEBUG: attack: $this->fight";
				$GLOBALS['debugmsgs'][] = "**DEBUG: defense: $this->defence";
				$GLOBALS['debugmsgs'][] = "**DEBUG: weapon name: $this->weapon_name";
				$GLOBALS['debugmsgs'][] = "**DEBUG: weapon dam: ".$this->weapon_min." - ".$this->weapon_max."";
				$GLOBALS['debugmsgs'][] = "**DEBUG: weaponless: ".$this->weaponless."";
				$GLOBALS['debugmsgs'][] = "**DEBUG: attack_skill_min: ".$this->attack_skill_min()."";
				$GLOBALS['debugmsgs'][] = "**DEBUG: attack_skill_max: ".$this->attack_skill_max()."";
				$GLOBALS['debugmsgs'][] = "**DEBUG: attack_min: ".$this->attack_min()."";
				$GLOBALS['debugmsgs'][] = "**DEBUG: attack_max: ".$this->attack_max()."";
			}
		}

		$rollthedice = diceroll();
		if (DEBUG) { $GLOBALS['debugmsgs'][] = "**DEBUG: dice roll = $rollthedice"; }
		if ($comb_act == "magic_attack") {
			$char_attack = round(($this->wisdom + $rollthedice) / 2);
		} else {
			$fight_reduction = $this->fight_reduction(); // Attack based on Dexterity.
			if (DEBUG) { $GLOBALS['debugmsgs'][] = "**DEBUG: fight reduction = $fight_reduction"; }
			$char_attack = round(
				## FIXME: this string needs work I see..
				($this->dexterity + rand( (int)($this->attack_skill_min() * $fight_reduction),(int)($this->attack_skill_max() * $fight_reduction) ) + $rollthedice)
			);
		}
        if (!$test) {
            if ($this->stamina_points > 0) {
                --$this->stamina_points;
            }
        }
        return $char_attack;
    }

    function defence_roll($comb_act,$test=false) {
		$rollthedice = diceroll();
		if (DEBUG) { $GLOBALS['debugmsgs'][] = "**DEBUG: dice roll = $rollthedice"; }
		if ($comb_act == "magic_attack") {
			$char_defence = (int)(($this->wisdom + $rollthedice) / 2 + rand(0,99 ) * 0.01);
		} else {
           if (DEBUG) { $GLOBALS['debugmsgs'][] = "**DEBUG: defense_min = ".($this->defense_min()); }
            $char_def = rand($this->defense_skill_min(),$this->defense_skill_max());
			$char_defence = (int)(($this->dexterity + $char_def + $rollthedice) / 10 + rand(0,99) * 0.01 );
		}
        if (!$test) {
            if ($this->stamina_points > 0) {
                --$this->stamina_points;
            }
        }
        return $char_defence;
    }

    // Update Character Stamina Points
    function update_stamina() {
		global $db;
		
        if ($this->stamina_points > $this->max_stamina) {
			$this->stamina_points = $this->max_stamina;
        }
        $res = $db->query("UPDATE phaos_characters SET stamina = ".$this->stamina_points." WHERE id=$this->id");
        if (!$res) { showError(__FILE__,__LINE__,__FUNCTION__); exit; };
    }

    function attack_bonus() {
        if ($this->location_data) {
            if ('Elf' == $this->race && stristr($this->location_data['name'],'Woodland') !== false) {
				if (DEBUG) { $GLOBALS['debugmsgs'][] = "**DEBUG: Elf combat bonus active"; }
                return 1 + fairInt($this->level * 0.05);
            }
            if ('Dwarf' == $this->race && stristr($this->location_data['image_path'],'images/land/4.png') !== false) { //added by kaspir, on snowy terrain.
				if (DEBUG) { $GLOBALS['debugmsgs'][] = "**DEBUG: Dwarf combat bonus active"; }
                return 1 + fairInt($this->level * 0.05);
            }
        }   
    }

	// Heals the character, if he is below maxhealth (if an given time has been exceeded & if user afk, only increments once now.)
	function auto_heal() {
		global $db;
		
		$res = $db->query("SELECT healing_time, healing_rate FROM phaos_races WHERE name = '".$this->race."'");
		$data = $res->fetch_assoc();
		if ($this->time_since_regen >= $data['healing_time'] && $data['healing_rate'] > 0) {
			$char_regen_hp = $this->hit_points + (int)($data['healing_rate']);
			if ($this->hit_points < $this->max_hp) {
				if ($char_regen_hp > $this->max_hp) {
					$char_regen_hp = $this->max_hp;
				}
				$db->query("UPDATE phaos_characters SET hit_points = '$char_regen_hp', regen_time = '".time()."'WHERE id = '".$this->id."'");
				$this->hit_points = $char_regen_hp;
			} else {
				$db->query("UPDATE phaos_characters SET regen_time = '".time()."'WHERE id = '".$this->id."'");
			}
		}
	}
	
	// Regens the character stamina, if he is below max_stamina (and an given time has been exceeded)
	function auto_stamina() {
		global $db;
		
		$res = $db->query('SELECT stamina_regen_time, stamina_regen_rate FROM phaos_races WHERE name = \''.$this->race.'\'');
		$data = $res->fetch_assoc();
		if ($this->stamina_time_since_regen >= $data['stamina_regen_time'] && $data['stamina_regen_rate'] > 0) {
			if ($this->stamina_points < $this->max_stamina) {
				$char_regen = $this->stamina_points + (int)($data['stamina_regen_rate']);
				if ($char_regen > $this->max_stamina) {
					$char_regen = $this->max_stamina;
				}
				$db->query("UPDATE phaos_characters SET stamina = '$char_regen', stamina_time = '".time()."'WHERE id = '".$this->id."'");
				$this->stamina_points = $char_regen;
			}
			else {
				$db->query("UPDATE phaos_characters SET stamina_time = '".time()."'WHERE id = '".$this->id."'");
			}
		}
	}

	// Regens the character reputation points to distribute, if a given time has been exceeded.
	function auto_reputation() {
		global $db;
		//  There are 86400 seconds in 24 hours
		if ($this->rep_time_since_regen >= 86400) {
			//  Add 1 rep point every 24 hours
			$rep_regen = $this->rep_points + (int)($this->rep_time_since_regen / 86400);
			if ($this->rep_points < $this->max_rep) {
				if ($rep_regen > $this->max_rep) {
					$rep_regen = $this->max_rep;
				}
				$req = $db->query("UPDATE phaos_characters SET rep_points = '$rep_regen', rep_time = '".time()."' WHERE id = '".$this->id."'");
				$this->rep_points = $rep_regen;
				if (!$req) {showError(__FILE__,__LINE__,__FUNCTION__); exit;}

			} else {
				//echo ("Rep points at max");
				$this->rep_points = $this->max_rep;
				$req = $db->query("UPDATE phaos_characters SET rep_points='$this->rep_points', rep_time = '".time()."' WHERE id = '".$this->id."'");
				if (!$req) {showError(__FILE__,__LINE__,__FUNCTION__); exit;}
			}
		}
	}

	// Drinks the lowest found Potion in the inventory
	function fast_potion() {
		global $db, $lang_fullhp;
		
		if ($this->hit_points >= $this->max_hp) {  // Don't waste potions!
				echo "<div class='center'><h4 class='b msgbox'>$lang_fullhp</h4></div>"; return false;
			} else {
			$result = $db->query("SELECT id, item_id FROM phaos_char_inventory WHERE username = '".$this->user."' AND type = 'potion' AND sell_to = '' AND asking_price = '0' ORDER BY item_id");
			if ($row = $result->fetch_assoc()) {
				$potion_id = $row["item_id"];
				$inv_id = $row["id"];
				
				$result = $db->query("SELECT effect, heal_amount FROM phaos_potion WHERE id = '$potion_id'");
				if ($row = $result->fetch_assoc()) {
					list($effect) = explode(' ',$row["effect"]);
					if ($effect == "Heals") { // Is it a Heal potion?
						$heal_amount = $row["heal_amount"];
						$new_hp_amount = $this->hit_points + $heal_amount;
						if ($new_hp_amount > $this->max_hp) {$new_hp_amount = $this->max_hp;}

						$req = $db->query("UPDATE phaos_characters SET hit_points = $new_hp_amount WHERE id = '".$this->id."'");
							if (!$req) {showError(__FILE__,__LINE__,__FUNCTION__); exit;}
							
						$query = "DELETE FROM phaos_char_inventory WHERE id = '$inv_id' AND type = 'potion' AND sell_to = '' AND asking_price = '0'";
						$result = $db->query($query) or die ("Error in query: $query. " . mysqli_error());
						$this->hit_points = $new_hp_amount;
					}
				}
			}
		}
	}

	/* Drinks a given Potion in the inventory
	* @param $Inv_ID identifies the inventory-slot */
	function drink_potion($Inv_ID) {
		global $db, $lang_fullhp, $lang_hp, $lang_stamina, $lang_heal, $lang_err;
		
		if ($this->hit_points >= $this->max_hp) { // Don't waste potions!
			echo "<div class='center'><h4 class='b msgbox'>$lang_fullhp</h4></div>"; return false;
		} else {
			$charID = $this->id;
			$presult = $db->query("SELECT id, item_id FROM phaos_char_inventory WHERE id='$Inv_ID'");
			if ($pot = $presult->fetch_assoc()) {
				$potion_id = $pot["item_id"];
				$inv_id = $pot["id"];
				$result = $db->query("SELECT effect, heal_amount FROM phaos_potion WHERE id = '$potion_id'");
				if ($row = $result->fetch_assoc()) {
					list($effect) = explode(' ',$row["effect"]);
					if ($effect == "Heals" or $effect == "stamina") {
						$details = $row["heal_amount"];
						$current =& $this->hit_points;		// pointer to hit_points value
						$max = $this->max_hp;
						$min = 0;
						$dbfield = "hit_points";
						$sayfield = $lang_hp;
					}
					if ($effect == "stamina") {
						$details = $row["heal_amount"];
						$current =& $this->stamina_points;	// pointer to stamina value
						$max = $this->max_stamina;
						$min = 0;
						$dbfield = "stamina";
						$sayfield = $lang_stamina;
					}
					if (@$dbfield) {
						$add = $details;
						if ($current+$add > $max) {$add = $max-$current;}
						if ($current+$add < $min) {$add = $min-$current;}
						$current += $add;	// updates EITHER $this->hit_points or $this->stamina depending on pointer

						$sql = "UPDATE phaos_characters SET $dbfield = $current WHERE id = $charID";
						$db->query($sql) OR die("Error ".mysqli_errno()." : full Query: $sql Mysql-error:".mysqli_error()."");
						
						$sql = "DELETE FROM phaos_char_inventory WHERE id = $inv_id";
						$db->query($sql) OR die ("Error in query: $query. ".mysqli_error());
						
						return "<span class='b'>$lang_heal $add $sayfield</span>";
					} else {
						return "<span class='i red'>$lang_err[fail_potn]</span>";
					}
				}
			}
		}
	}


	/**
	* (string)$attribute -> Attribute collumn of the char that has to be increesed
	* @return Returns 0 on failure and 1 on all done successfully. (mainly SQL-errors!!)
	*/
	function level_up($attribute) {
		global $db;
		
		$req = $db->query("UPDATE phaos_characters SET stat_points = stat_points-1, $attribute = $attribute+1 WHERE id = '".$this->id."'");			
		if (!$req) {return(0);}
		else {return(1);}
	}
    
	// Returns character Level
	function level() {
		return $this->level;
	}

	// Returns character available_points
	function available_points() {
		return $this->available_points;
	}

    /**
    * @param (ID) ID character number to identify the desired character
    * @return Returns 0 on failure and 1 on all done successfully. (mainly SQL-errors!!)
    */
    function kill_characterid() { //KASPIR THIS IS WHERE I CAN ADD PVP!!! used in combat.php & travel.php for total mobs
		global $db;
		
		$query = "DELETE FROM phaos_characters WHERE id = '".$this->id."'";
		$result = $db->query($query) or die ("Error in query: $query. " . mysqli_error());

		// FIXME: DBS - this should be changed so items are dropped at location, remember this function is used for npcs!
		$query = "DELETE FROM phaos_char_inventory WHERE username = '".$this->user."'";
		$result = $db->query($query) or die ("Error in query: $query. " . mysqli_error());
		return 1;
    }

	/**
    * Check whether an item is equipped
	* @param (string)$item_type - choose the item type as string
	* @param (string)$item_id - the ID of the includet object
	* @return boolean
	*/
	function equipped($item_type,$item_id ) {
		if ($item_type == "armor")	{return $this->armor == $item_id;}
		if ($item_type == "weapon")	{return $this->weapon == $item_id;}
		if ($item_type == "gloves")	{return $this->gloves == $item_id;}
		if ($item_type == "helm")	{return $this->helm == $item_id;}
		if ($item_type == "shield")	{return $this->shield == $item_id;}
		if ($item_type == "boots")	{return $this->boots == $item_id;}
		return false;
	}

	/**
    * Equip an item
	* @param (string)$item_type - choose the item type as string
	* @param (string)$item_id - the ID of the includet object
	* @return Returns 0 on failure and 1 on all done successfully. (mainly SQL-errors!!)
	*/
	function equipt($item_type,$item_id) {
		global $db;
		
		$mark_N = ("UPDATE phaos_char_inventory SET equiped = 'N' WHERE username = '$_COOKIE[PHP_PHAOS_USER]' AND type = '$item_type' AND equiped = 'Y' LIMIT 1"); //kaspir added. Must first turn N, before it turns the new Y
		$mark_Y = ("UPDATE phaos_char_inventory SET equiped = 'Y' WHERE username = '$_COOKIE[PHP_PHAOS_USER]' AND item_id = '$item_id' AND type = '$item_type' AND equiped = 'N' LIMIT 1"); //kaspir added.
		
		$req = $db->query("UPDATE phaos_characters SET $item_type = '$item_id' WHERE id = '".$this->id."'");
		if (!$req) { showError(__FILE__,__LINE__,__FUNCTION__); return 0; exit;}
		if ($item_type == "armor")	{$this->armor = $item_id; $db->query($mark_N); $db->query($mark_Y); }
		if ($item_type == "weapon")	{$this->weapon = $item_id; $db->query($mark_N); $db->query($mark_Y); }
		if ($item_type == "gloves")	{$this->gloves = $item_id; $db->query($mark_N); $db->query($mark_Y); }
		if ($item_type == "helm")	{$this->helm = $item_id; $db->query($mark_N); $db->query($mark_Y); }
		if ($item_type == "shield")	{$this->shield = $item_id; $db->query($mark_N); $db->query($mark_Y); }
		if ($item_type == "boots")	{$this->boots = $item_id; $db->query($mark_N); $db->query($mark_Y); }
		return 1;
	}

	/**
	* @param (string)$item_type - Choose the item type as string
	* @return Returns 0 on failure and 1 on all done successfully. (mainly SQL-errors!!)
	*/
	function unequipt($item_type,$item_id) {
		global $db;
		
		$mark_N = ("UPDATE phaos_char_inventory SET equiped = 'N' WHERE username = '$_COOKIE[PHP_PHAOS_USER]' AND item_id = '$item_id' AND type = '$item_type' AND equiped = 'Y' LIMIT 1");
		switch($item_type) {
            case "armor":
                $this->armor = '';
				$db->query($mark_N);
                break;
            case "weapon":
                $this->weapon = '';
				$db->query($mark_N);
                break;
            case "gloves":
                $this->gloves = '';
				$db->query($mark_N);
                break;
            case "helm":
                $this->helm = '';
				$db->query($mark_N);
                break;
            case "shield":
                $this->shield = '';
				$db->query($mark_N);
                break;
            case "boots":
                $this->boots = '';
				$db->query($mark_N);
                break;
            default:
                return 0;
        }
		$req = $db->query("UPDATE phaos_characters SET $item_type = '' WHERE id = '".$this->id."'");
		if (!$req)  {showError(__FILE__,__LINE__,__FUNCTION__); return 0; exit;}
		return 1;
	}

	/**
    * Check whether the player still owns the items he has equipped
	* @param (string)$item_type - choose the item type as string
	* @param (string)$item_id   - choose the item id  as string
	* @return 1 on failure and 0 on all done successfully. (mainly SQL-errors!!)
	* system function to check the equipped items (do not use in public)
	*/
	function checkequipped($item_type,$item_id) {
		global $db;
		
		if ($item_id == '' OR $item_id == '0' OR $item_id == 'N/A') {
			return(0);
		}

		$res = $db->query("SELECT * FROM phaos_char_inventory WHERE username = '".$this->user."' AND item_id = '$item_id' AND type = '$item_type' AND equiped = 'Y' ");
		if ($row = $res->fetch_assoc()) {
			return 0;
		} else {
            $this->unequipt($item_type,$item_id);
			return 1;
		}
	}

	/**
	* @return the number of failures during check if "0" then all done correctly
	* Purpose: Usercalled function to check the equipped items
	*/
	function checkequipment() {
		$c1 = $this->checkequipped("armor",$this->armor);
		$c1 += $this->checkequipped("weapon",$this->weapon);

		$c1 += $this->checkequipped("boots",$this->boots);
		$c1 += $this->checkequipped("shield",$this->shield);
		$c1 += $this->checkequipped("helm",$this->helm);
		$c1 += $this->checkequipped("gloves",$this->gloves);
		return $c1;
	}

	function get_eq_item_name($item_type) {
		global $db;
		
		if ($item_type == "armor")		{ $result = $db->query("SELECT name FROM phaos_armor WHERE id = '".$this->armor."'"); }
		if ($item_type == "weapon")		{ $result = $db->query("SELECT name FROM phaos_weapons WHERE id = '".$this->weapon."'"); }
		if ($item_type == "gloves")		{ $result = $db->query("SELECT name FROM phaos_gloves WHERE id = '".$this->gloves."'"); }
		if ($item_type == "helm")		{ $result = $db->query("SELECT name FROM phaos_helmets WHERE id = '".$this->helm."'"); }
		if ($item_type == "shield")		{ $result = $db->query("SELECT name FROM phaos_shields WHERE id = '".$this->shield."'"); }
		if ($item_type == "boots")		{ $result = $db->query("SELECT name FROM phaos_boots WHERE id = '".$this->boots."'"); }
		if ($row = $result->fetch_assoc()) {
			return ($row["name"]);
		} else {return false;}
	}
	
	function get_eq_item_pic($item_type) {
		global $db;
		
		if ($item_type == "armor")		{ $result = $db->query("SELECT image_path FROM phaos_armor WHERE id = '".$this->armor."'"); }
		if ($item_type == "weapons") 	{ $result = $db->query("SELECT image_path FROM phaos_weapons WHERE id = '".$this->weapon."'"); }
		if ($item_type == "gloves")		{ $result = $db->query("SELECT image_path FROM phaos_gloves WHERE id = '".$this->gloves."'"); }
		if ($item_type == "helms")		{ $result = $db->query("SELECT image_path FROM phaos_helmets WHERE id = '".$this->helm."'"); }
		if ($item_type == "shields") 	{ $result = $db->query("SELECT image_path FROM phaos_shields WHERE id = '".$this->shield."'"); }
		if ($item_type == "boots")		{ $result = $db->query("SELECT image_path FROM phaos_boots WHERE id = '".$this->boots."'"); }
		if ($row = $result->fetch_assoc()) {
			return ($row["image_path"]);
		} else {
			return 'images/icons/'.$item_type.'/na.gif';
		}

	}
	
	function get_eq_armor_ac($item_type) {
		global $db;

		if ($item_type == "armor")		{ $result = $db->query("SELECT armor_class FROM phaos_armor WHERE id = '".$this->armor."'"); }
		if ($item_type == "gloves")		{ $result = $db->query("SELECT armor_class FROM phaos_gloves WHERE id = '".$this->gloves."'"); }
		if ($item_type == "helms")		{ $result = $db->query("SELECT armor_class FROM phaos_helmets WHERE id = '".$this->helm."'"); }
		if ($item_type == "shields") 	{ $result = $db->query("SELECT armor_class FROM phaos_shields WHERE id = '".$this->shield."'"); }
		if ($item_type == "boots")		{ $result = $db->query("SELECT armor_class FROM phaos_boots WHERE id = '".$this->boots."'"); }
		if ($row = $result->fetch_assoc()) {
			return ($row["armor_class"]);
		} else { return false; }

	}
	
	function get_eq_weapon_mindam($item_type) {
		global $db;

		if ($item_type == "weapons") {$result = $db->query("SELECT min_damage FROM phaos_weapons WHERE id = '".$this->weapon."'"); }
		if ($row = $result->fetch_assoc()) {
			return ($row["min_damage"]);
		} else { return false; }
	}
		
	function get_eq_weapon_maxdam($item_type) {
		global $db;
		
		if ($item_type == "weapons") { $result = $db->query("SELECT max_damage FROM phaos_weapons WHERE id = '".$this->weapon."'"); }
		if ($row = $result->fetch_assoc()) {
			return ($row["max_damage"]);
		} else { return false; }
	}

	
	function reduce_stamina($ammount) {
		global $db;
		
		$def = $this->stamina_points;
		$this->stamina_points = $def - $ammount;
		$db->query("UPDATE phaos_characters SET stamina = '".$this->stamina_points."' WHERE id = '".$this->id."'");
	}

	// Shop functions of Character
	function pay($amount) {
		global $db, $lang_gp, $lang_glo;
		
		// FIXME: this function should also take a payee character ID and add gold to them
		if ($amount <= $this->gold) {
			echo "<div class='center'><h3 class='b msgbox'>$lang_glo[paid] <span class='gold'>$amount</span> $lang_gp</h3></div>";
			$this->gold = $this->gold - $amount;
			$req = $db->query("UPDATE phaos_characters SET gold = ".$this->gold." WHERE id = ".$this->id);
			if (!$req) {showError(__FILE__,__LINE__,__FUNCTION__); exit;}
			return 1;
		} else {
			return 0;
		}
	}

	function invent_count() {
		global $db;
		
		$result = $db->query("SELECT * FROM phaos_char_inventory WHERE username = '".$this->user."' AND equiped = 'N' AND type != 'spell_items'");
		$inv_count = $result->num_rows;
		return $inv_count;
	}

	function add_item($item_id,$item_type) {
		global $db, $PHP_PHAOS_USER;

		$req = $db->query("INSERT INTO phaos_char_inventory (username,item_id,type) VALUES ('$PHP_PHAOS_USER','$item_id','$item_type')");
		if (!$req) {showError(__FILE__,__LINE__,__FUNCTION__); exit;}
		return 1;
	}

	function remove_item($item_id,$item_type) {
		global $db;
		
		$query = "SELECT id FROM phaos_char_inventory WHERE username='$this->user' AND item_id='$item_id' AND type='$item_type'";
        $id = fetch_value($query,__FILE__,__LINE__,__FUNCTION__);
        if ($id) {
    		$req = $db->query("DELETE FROM phaos_char_inventory WHERE id=$id");
    		if (!$req) {showError(__FILE__,__LINE__,__FUNCTION__); exit;}
    		return 1;
        } else {
            return 0;
        }
	}

    // Pickup one or more items from the ground, includes gold.
    // Note: The actual change of the ground happens elsewhere
    function pickup_item($item) {
		global $db;
		
        $pickedup = 0;
        if ($item['type'] == "gold") {
            $req = $db->query("UPDATE phaos_characters SET gold=gold+$item[number] WHERE id='$this->id'");
            if (!$req) {showError(__FILE__,__LINE__,__FUNCTION__); exit;}
            $pickedup += 1;
        } else {
            for($i = 0; $i < $item['number']; ++$i) {
                $pickedup += $this->add_item($item['id'],$item['type']);                
            }
        }
        return $pickedup;
    }

    /*
    // Drop one or more items to the ground, includes gold.
    // Note: The actual change of the ground happens elsewhere
    function drop_item($item) {
		global $db;
		
        $location = $this->location;
        $dropped = 0;
        if ($item['type'] == "gold") {
            $req = $db->query("UPDATE phaos_characters SET gold=gold-$item[number] WHERE id='$this->id'");
            if (!$req) {showError(__FILE__,__LINE__,__FUNCTION__); exit;}
            $dropped += $item['number'];
        } else {
            for($i = 0; $i < $item['number']; ++$i) {
                $dropped += $this->remove_item($item['id'],$item['type']);
            }
        }
        return $dropped;
    }
*/
	// Skill Raising and Lowering
	function skillup($skillname) {
		global $db;
		
        if ($skillname == 'wisdom') {
    		$total = $this->fight + $this->defence;
            $wisdomoffset =- 4; //lizardfolk get one near lvl3, I might be able to do something dex/str here.
        } else {
    		$total = ($this->fight + $this->defence) + $this->wisdom; //kaspir made it so if you are placing wisdom for casting, you earn less!! // +
            $wisdomoffset = 0;  //Original
		}

        $chance = 10; //Chance will be used against the rand roll below. higher # equals higher chance! //kaspir

        if ($this->race == 'Lizardfolk' && $skillname == 'wisdom') { //kaspir TRYME
            $total -= 1;
            $chance = 50;
        }

		if ($total < (15 + $this->level + $wisdomoffset)) { //kaspir, now melee chars earn firstpoint near lvl3 if wis =6
			$rnd = rand(1,100); // Normaly set to 100 so $chance equals a %
			if ($rnd < $chance) {
				$db->query("UPDATE phaos_characters SET $skillname=$skillname+1 WHERE id='".$this->id."'");
				return(1);
			}
		} else {
			return(0);
		}
	}
	
	function skilldown() { // There REALLY is a lower skill function!
		global $db;
		
		$rnd = rand(1,100); // Normaly set to 100!
		if ($rnd < 5 + ($this->level / 10)) {
			$rnd2 = rand(1,2); 
			switch ($rnd2) {
				case 1 : $skillname = "fight";
									if ($this->fight <= 1) {$exec = 0;} // Makes sure it isn't already 1 or 0 first.
									else {$exec = 1;}
									break;
				case 2 : $skillname = "defence";
									if ($this->defence <= 1) {$exec = 0;}
									else {$exec = 1;}
									break;
				}
				if ($exec == 1) {
					$db->query("UPDATE phaos_characters SET $skillname=$skillname-1 WHERE id='".$this->id."'");
					return(1);
				}
		} else {
			return(0);
		}
	}
	
	function all_skillsup($action,$lang_fun) {
    	if ($action == "magic_attack") {
			$ret = $this->skillup("wisdom");
			if ($ret == 1) { $_SESSION['disp_msg'][] = "<span class='b green'>".$lang_fun["gai_wis"]."</span>"; }
		} else {
			if ($this->weapon == 0) {
				$ret = $this->skillup("weaponless");
				if ($ret == 1) { $_SESSION['disp_msg'][] = "<span class='b green'>".$lang_fun["gai_wep"]."</span>"; }
			} else {
				$rnd2 = rand(1,100);
				//kaspirs random choice, working so far?
				/***try use/make a shuffle function?
					$numbers = range(1, 20);
					shuffle($numbers);
					foreach ($numbers as $number) {
						echo "$number ";
					}
				*/
				if ($rnd2 >= 50) {
					$ret = $this->skillup("fight");
					if ($ret == 1) { $_SESSION['disp_msg'][] = "<span class='b green'>".$lang_fun["gai_att"]."</span>"; }
				} else {
					$ret = $this->skillup("defence");
					if ($ret == 1) { $_SESSION['disp_msg'][] = "<span class='b green'>".$lang_fun["gai_def"]."</span>"; }
				}
			}
		}
	}
	
	function inv_skillmatch() {
		$error = 0;
		$wstr = $this->weapon_min + $this->weapon_max;
		if ($wstr > ($this->fight * 5.33)) {
			$this->unequipt("weapon");
			$error++;
		}
		if ($this->armor_ac > ($this->defence * 2) + 8) {
			$this->unequipt("armor");
			$error++;
		}
		if ($this->helm_ac > ($this->defence - 1)) {
			$this->unequipt("helm");
			$error++;
		}
		if ($this->boots_ac > ($this->defence)) {
			$this->unequipt("boots");
			$error++;
		}
		if ($this->gloves_ac > ($this->defence)) {
			$this->unequipt("gloves");
			$error++;
		}
		if ($this->shield_ac > ($this->defence - 2)) {
			$this->unequipt("shield");
			$error++;
		}
		return($error);
	}

    // Place a Character on the Map
    // @optional $locationid the new location of the character
    // no argument = just save character information using the current location.
    function place($locationid =- 1) {
		global $db;
		
        if ($this->id > 0) {
            $idkey = 'id,';
            $idvalue = "$this->id,";
            $auto_increment_id = false;
        } else {
            $idkey = "";
            $idvalue = "";
            // We use mysqls AUTO_INCREMENT feature
            $auto_increment_id = true;
        }
        if ($locationid >= 0) {
            $this->location = $locationid;
        }
        $query = "REPLACE INTO phaos_characters
        (  $idkey location,image_path,username,name,strength,dexterity,wisdom,constitution,hit_points,race,class,sex,gold,fight,defence,weaponless,
		weapon,xp,level,armor,stat_points,boots,gloves,helm,shield,regen_time,stamina,stamina_time,rep_time,rep_points,rep_helpfull,rep_generious,rep_combat
        )
        VALUES
        (
           $idvalue '$this->location','$this->image','$this->user','$this->name','$this->strength','$this->dexterity','$this->wisdom','$this->constitution','$this->hit_points','$this->race','$this->cclass','$this->sex','$this->gold'
         , '$this->fight','$this->defence','$this->weaponless','$this->weapon','$this->xp','$this->level','$this->armor','$this->stat_points','$this->boots','$this->gloves','$this->helm','$this->shield','$this->regen_time','$this->stamina_points','$this->stamina_time','$this->rep_time','$this->rep_points','$this->rep_helpfull','$this->rep_generious','$this->rep_combat'
        )";

        $req = $db->query($query);
        if (!$req) {showError(__FILE__,__LINE__,__FUNCTION__); exit;}

        if ($auto_increment_id) {
            $req = $db->query("SELECT id FROM phaos_characters WHERE location='$this->location' ORDER BY id DESC LIMIT 1");
            if (!$req) {showError(__FILE__,__LINE__,__FUNCTION__); exit;}
           $row = $req->fetch_assoc();
            if ($row) {
                $this->id = $row['id'];
            }
        }
    }



    // FIXME: there should be a separate field in the database for characterrole, since right now it abuses the username
    function setRole($role) {
        $this->username = $role;
    }

    function is_npc() {
        return strpos($this->user,'phaos_') === 0 ; //CAN I SHOW NPCS guys HERE? OR HOSTILE MODE!//kaspir
    }

    // TODO: ghosts, and flying creatures might be able to pass where others cannot
    function sql_may_pass() {
		global $db;
        // HACK
        // Determine whether the npc is somewhere we he is not supposed to be and maybe walled in. Allow him to move to fix that.
        $condition_pass = "pass='y'";
		$result = $db->query("SELECT id, buildings, pass FROM phaos_locations WHERE id=$this->location AND $condition_pass");
        $position_ok = $result->num_rows > 0;
        if (!$position_ok) {
            return '1=1';
        }
        return $this->real_sql_may_pass();
    }

    // TODO: ghosts, and flying creatures might be able to pass where others cannot
    function real_sql_may_pass() {
        if ( $this->is_npc() ) {
            return "( buildings='n' AND pass='y' )";
        } else {
            return "pass='y'";
        }
    }

    function relocate($direction) {
		global $db, $npc_dir_map;

        $condition_pass = $this->sql_may_pass();

		// Select one of the surround squares as direction
		$npc_dir = $npc_dir_map[intval($direction)];

		$res = $db->query("SELECT $npc_dir FROM phaos_locations WHERE id='".$this->location."'");
		//if ( (@list($newloc) = $res->fetch_assoc()) && $newloc ) {
		if ($row = $res->fetch_assoc()) {
			$newloc = $row["$npc_dir"];
			// Get and Set the New Location for a NPC
			$result = $db->query("SELECT id, buildings, pass FROM phaos_locations WHERE id=$newloc AND $condition_pass");
			if ($row = $result->fetch_assoc() ) {
					$this->location = $newloc;
					// Now we can update the DB
					$result = $db->query("UPDATE phaos_characters SET location	= ".$this->location." WHERE id	= '".$this->id."'");
					if (!$result) {showError(__FILE__,__LINE__,__FUNCTION__); exit;}

					if (DEBUG) { $GLOBALS['debugmsgs'][] = ">> SUCCESS: moved to Loc.($newloc)"; }
					return true;
			} else {
				if (DEBUG) { $GLOBALS['debugmsgs'][] = ">> FAILED: The new Loc.($newloc) was <b>blocked</b>"; }
				return false;
			}
		} else {
			if (DEBUG) { $GLOBALS['debugmsgs'][] = ">> FAILED: No adjacent square in direction $npc_dir at $this->location"; }
			return false;
        }
    }
}// End Character class

// NPC Blueprint class
class np_character_from_blueprint extends character {
	
	public function __construct($blueprint,$level=1,$username='phaos_npc') {	
		$this->level = intval($level);
		if ($level < 0) { $level = 1; }

		// Define Main vars
		$this->id = -1;
		$this->name = $blueprint["name"];
		$this->user = $username;
		$this->cclass = $blueprint["class"];
		$this->race = $blueprint["race"];
		$this->sex = rand(0,1)?'Female':'Male'; // ADDME: Would be funny to add 'hermaphrodite' for certain race!!
		$this->image = $blueprint["image_path"];
		$this->location = $blueprint["location"];
		// Define Attribute vars
		$this->strength = (int)($blueprint["min_damage"] + 3 * ($this->level + 1));
		$this->dexterity = (int)( (($blueprint["max_damage"] - $blueprint["min_damage"]) + 2) * ($this->level + 2) ) * 0.75; // no more than 15
        $this->wisdom = (int)($blueprint["xp_given"] / 2 + $this->level);
        // Define Changeable vars ( well except constitution )
        $this->hit_points = (int)($blueprint["hit_points"] * $this->level);
		$this->constitution = (int)($this->hit_points / $this->level);
		$this->max_stamina = ($this->constitution + $this->strength) * 5;
		$this->level = $this->level;

        $this->xp = $blueprint["xp_given"];
        $this->gold = $blueprint["gold_given"];
        $this->stat_points = 0;

        // Skills for Monsters
        $this->fight = 4 + $this->level;
        $this->defence = (int)($blueprint['AC'] / 4 + $this->level - 1);

        // Define Equipment vars; ##ADDME: Future drops, these are set to randomized lower-end gears for drops
        $this->weapon = rand(1,45);
        $this->armor = rand(10,16); //7max
        $this->boots = rand(97,102); //8max
        $this->shield = rand(73,77); //5max
        $this->gloves = rand(97,102); //8max
        $this->helm = rand(68,71); //11max

        // Calculated Stuff
		$this->armor = $blueprint["AC"];
		$this->weaponless = (int)($blueprint["max_damage"] + $blueprint["min_damage"] + $this->level);
        $this->available_points = $this->strength + $this->dexterity + $this->wisdom + $this->constitution;
		$this->max_hp2 = $this->constitution * $this->level; // max_hp2 is for mobs, while max_hp is for chars
		$this->max_stamina = ($this->constitution + $this->strength) * 10;
        $this->max_rep = 7;

		if ($this->stamina_points > $this->max_stamina) {
			$this->max_stamina = $this->stamina_points;
		}

		// Other Stuff
		$actTime = time();
		$this->regen_time = $actTime;
		$this->stamina_time = $actTime;
		$this->rep_time = $actTime;
		$this->no_regen_hp = $blueprint["hit_points"];
		// Regeneration
		$this->time_since_regen = $actTime - $this->regen_time;
		$this->stamina_time_since_regen = $actTime - $this->stamina_time;
		$this->rep_time_since_regen = $actTime - $this->rep_time;
		// Reputation
		$this->rep_points = rand(0,$this->level - 1);
		$this->rep_helpfull = rand(0,$this->level - 1);
		$this->rep_generious = rand(0,$this->level - 1);
		$this->rep_combat = rand(0,$this->level - 1);

		$this->max_inventory = $this->strength * 5;
		
		// Default image if not defined
		if (!$this->image) {
			$this->image = "images/monster/forest_troll.gif";
		}
	}
} // End NPC Blueprint class

/**
* @param: none
* return: none
* purpose: generate new NPC/monster and add to database
*/
function npcgen() {
	global $db;
	
	$res = $db->query("SELECT * FROM phaos_opponents WHERE name NOT LIKE '%Champion%' AND location='0' ORDER BY RAND() LIMIT 1") or die(mysqli_error());
	if ($blueprint = $res->fetch_assoc()) {
		$race = $blueprint["race"]; //kaspirs, to set spawn regions by race
        // Create 50% level 1 characters, and not more than 37,5% characters with level>3
        $level = 1 + (int)( rand(0,1) * (pow(1 + rand(0,10) * rand(0,10) * 0.01,4) + rand(0,99) * 0.01) );
        $npc = new np_character_from_blueprint($blueprint, $level);

			// Identify spawn location based on race & lvl
			if ( ($level <= 3) || (($level <= 9) && (empty($race))) ) { // Set base Allied realm				
				$rnd = rand(1,3); 
				switch ($rnd) {
					case 1 : $min = 4; $max = 10000;
								break;					
					case 2 : $min = 4; $max = 10000; // We do twice so majority spawns stay above ground in Allied.
								break;
					case 3 : $min = 100001; $max = 100675;
								break;
				} 
			} else {$min = 10001; $max = 103185;} // Default high lvls mobs allowed anywhere but Allied.
			// Unless...
			if ($race == "Humanoid") 	{ $min = 10001; $max = 20000; } //Illandry
			if ($race == "Vampire") 	{ $min = 20001; $max = 30000; } //Lanus
			if ($race == "Orc") 		{ $min = 30001; $max = 40000; } //Thanium
			if ($race == "Undead") 		{ $min = 40001; $max = 50000; } //Wath
			if ($race == "Gnome") 		{ $min = 50001; $max = 60000; } //Kjelk
			if ($race == "Elf") 		{ $min = 60001; $max = 70000; } //Tel-khaliid
			if ($race == "Snowy") 		{ $min = 80001; $max = 90000; } //Gilanthia
			if ($race == "Elemental") {
				$rnd2 = rand(1,2); 
				switch ($rnd2) { //Qu_nai
					case 1 : $min = 70001; $max = 80000;
								break;
					case 2 : $min = 100001; $max = 103185;
								break;
				} 
			} 
			if (($race == "Dragon") || ($race == "Griffon") ) { $min = 100001; $max = 103185; } // All Dungeons		
		
        $condition_passable= $npc->real_sql_may_pass();

        $tries = 10;
        while ($tries-->0) {
			$res = null;
			if (!@$res) {
				$sql = "SELECT id FROM phaos_locations WHERE id>='$min' AND id<='$max' AND (name LIKE 'Wilderness' OR name LIKE 'Woodlands' OR name LIKE 'Dungeon') AND $condition_passable ORDER BY RAND() LIMIT 1";
				$res = $db->query($sql) or die(mysqli_error());
			}
			list($locationid) = $res->fetch_row();	

			// Check whether location is crowded
			$res = $db->query("SELECT count(*) FROM phaos_characters WHERE location='$locationid' AND username='phaos_npc'") or die(mysqli_error());
			list($count) = $res->fetch_assoc() > 0;
			if ($count > 6) {
				$GLOBALS['debugmsgs'][]= " location $locationid is <b>crowded</b>, not placing here ($count npcs)";
				// Trying to fix
				$res = $db->query("SELECT id FROM phaos_characters WHERE location='$locationid' AND username='phaos_npc'") or die(mysqli_error());
				while (list($id) = $res->fetch_assoc()) {
					$crowd = new character($id);
					$crowd->relocate( (int)rand(1,8) );
				}
			} else {
				break; // Stop while loop
			}
		}
	} else {
		die("cant find valid mob in DB: ".mysqli_error());
	}
    $npc->place($locationid);
	
	if (DEBUG) { $GLOBALS['debugmsgs'][] = "**DEBUG: Generated a new Lvl($npc->level)<span class='b'>$npc->name</span>, id($npc->id) and placed at Loc.($locationid)"; }
	return 1;
}
