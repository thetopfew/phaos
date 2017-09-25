<?php
require_once "header.php";

// If no character created
if ($character->location == "") {
	jsChangeLocation("create_character.php", 0);
}

// INITIAL SETUP
//if(!isset($_SESSION['opponent_id'])) { session_start(); }

$comb_refsidebar = false;

require_once "class_character.php";
require_once "combat_functions.php";

$character = new character($PHP_PHAOS_CHARID);

$_SESSION['disp_msg'] = array();
if (isset($_GET['charfrom'])) {
	$_SESSION['charfrom'] = $_GET['charfrom'];
}

// OPPONENT INFORMATION
if (@$_SESSION['charfrom'] != "arena") {
	// Retreives all mobs from combat location
	setcombatlocation($character->location);
	$list = whos_here($_SESSION['combatlocation'],'phaos_npc');
	
	if (DEBUG) { $GLOBALS['debugmsgs'][] = "**DEBUG: normal combat"; }
} else {
	// ARENA OPPONENT INFORMATION
	$list = array();
	if (@$_SESSION['combatlocation']){
 		$list = whos_here($_SESSION['combatlocation'],'phaos_arena_fighting');
		if (DEBUG) { $GLOBALS['debugmsgs'][] = "**DEBUG: Trying to locate previous opponents, found ".count($list); }
	} else {
		// Place new monsters in arena, this code has to be moved/adapted into the arena.php, I guess, since otherwise the arena cannot properly set the level of the monster
		if (DEBUG) { $GLOBALS['debugmsgs'][] = "**DEBUG: arena initial setup"; }

		// Set opponents level
		$opponent_level= intval(@$_GET['opponent_level']);
		if ($opponent_level > 0){
			$_SESSION['opp_level'] = $opponent_level;
		} else {
			$_SESSION['opp_level'] = (int)rand((int)($character->level / 5),($character->level));
		}
		$other_opp_level= $_SESSION['opp_level'];

		// Set number of opponents
		$_SESSION['num_of_opps'] = rand(1, ceil(sqrt($opponent_level)) );

		$opponent_id = intval(@$_GET['opponent_id']);
		if (DEBUG) { $GLOBALS['debugmsgs'][] = "**DEBUG: choosing opponent $opponent_id, lvl $_SESSION[opp_level] , $_SESSION[num_of_opps] foes"; }

		// Find or create an arena location #FIXME
		// find out whether there is a city or special here, and use the name
		$res = $db->query("SELECT name FROM phaos_locations WHERE id=$character->location LIMIT 1");
		//@list($locationname) = mysqli_fetch_row($result);
		list($locationname) = $res->fetch_row();

		$arenaat = isset($locationname)? $locationname : "Unknown ".rand(0,99);
		$arenaname = "Arena ".rand(1,3)." at $arenaat";

		// Check whether this arena exists already
		$res = $db->query("SELECT id FROM phaos_locations WHERE name='$arenaname' ORDER BY id DESC");
		//@list($arenalocation) = mysqli_fetch_row($result);
		list($arenalocation) = $res->fetch_row();
		if (!@$arenalocation) { //KASPIR, apparently, if I turn this off, we might stay with one arena damn it!
			if (DEBUG) { $_SESSION['disp_msg'][] = "DEBUG: adding new arena "; }
			$arenalocation= nextLocationIdFromRange('arena_fighting_location',__FILE__,__LINE__);
			// Insert if not exists
			$query = "INSERT INTO phaos_locations
					(id, name, image_path, special, buildings, pass, explore)
	                VALUES
					($arenalocation, '$arenaname','images/arena.gif',1,0,1,0)";
			$req = $db->query($query);
			if (!$req) { showError(__FILE__,__LINE__,__FUNCTION__); exit;}

			$res = $db->query("SELECT id FROM phaos_locations WHERE name='$arenaname' ORDER BY id DESC");
			//@list($arenalocation) = mysqli_fetch_row($result);
			list($arenalocation) = $res->fetch_row();

			// Make arena point to itself so that champs don't wander off
			$query = "UPDATE phaos_locations
			SET
			`above_left`= $arenalocation,
			`above`= $arenalocation,
			`above_right`= $arenalocation,
			`leftside`= $arenalocation,
			`rightside`= $arenalocation,
			`below_left`= $arenalocation,
			`below`= $arenalocation,
			`below_right`= $arenalocation
			WHERE id = $arenalocation";
			$req = $db->query($query);
			if (!$req) { showError(__FILE__,__LINE__,__FUNCTION__); exit;}
		}

		(@$arenalocation) or die('Must have a special location for arena');

		$_SESSION['arenalocation'] = $arenalocation;
		setcombatlocation($_SESSION['arenalocation']);

		$opplocation = $character->location;

		// Empty arena of old combatants
		#FIXME: check whether arena is in use
		$query = "UPDATE phaos_characters
		SET location= '$opplocation',
		username= 'phaos_npc_arena'
		WHERE location= '$arenalocation'";
		$req = $db->query($query);
		if (!$req) { showError(__FILE__,__LINE__,__FUNCTION__); exit;}

		$oppsneeded = $_SESSION['num_of_opps'];

		if (@$opponent_id) {
			// Try to find requested opponent
			$res = $db->query("SELECT id FROM phaos_characters WHERE $opplocation AND id=$opponent_id AND username NOT LIKE 'phaos_%'");
			// Place requested opponent
			if ($res->num_rows) {
				$oppcharacter = new character($opponent_id);
				$oppcharacter->user = 'phaos_arena_fighting';
				$oppcharacter->place($arenalocation);
				$opponent_id = $oppcharacter->id; //paranoia
				$list[] = $opponent_id;
				$_SESSION['opponent_id'] = $opponent_id;
				--$oppsneeded;
				if (--$other_opp_level < 1) {
					$other_opp_level = 1;
				}
			}
		}

		// Place new monsters in the arena
		$query = "SELECT * FROM phaos_opponents WHERE location='$opplocation=' ORDER BY RAND() LIMIT $oppsneeded";
		$blueprints = fetch_all($query);
		foreach($blueprints as $blueprint) {
			$npc = new np_character_from_blueprint($blueprint,$other_opp_level,'phaos_arena_fighting');
			$npc->place($arenalocation);
			$list[] = $npc->id;
			if (DEBUG) { $GLOBALS['debugmsgs'][] = "**DEBUG: placing $npc->name $npc->level $npc->username"; }
		}
		//$list = whos_here($_SESSION['combatlocation'],'phaos_arena_fighting');
	} // set up arena
}

$_SESSION['num_of_opps'] = count($list);

//TODO: for spells
//$opponentList= makeList($list);

$oppcharacter = null;
if (!count($list)) {
	$skip_actions = true;
	$comb_act = 'endfight';
	
	// Get link via function
	$link = endfight();
	jsChangeLocation($link, 0);	
} else {
	$skip_actions = false;

	if (!@$_SESSION['opponent_id']) {
		$_SESSION['opponent_id'] = $list[0];
	}

	// Load opponents
	$opponents = array();
	foreach($list as $id) {
		$opponents[$id] = new character($id);
	}

	if (@$_SESSION['opponent_id']) {
		$oppcharacter = &$opponents[$_SESSION['opponent_id']];
	}
	if (!@$oppcharacter || $oppcharacter->hit_points <= 0 ) {
		$_SESSION['opponent_id'] = $list[0];
		$oppcharacter = &$opponents[$_SESSION['opponent_id']];
	}
}

if (DEBUG) { $GLOBALS['debugmsgs'][] = "**DEBUG: combat location: ".@$_SESSION['combatlocation']; }

# NOTES #
# $_SESSION['opponent_id']: the an opponent to attack
# $_SESSION['endcombat']: whether combat skips the monster attack, maybe if monster is dead

$_SESSION['endcombat'] = false;

if ($skip_actions) {
	if (DEBUG) { $GLOBALS['debugmsgs'][] = "**DEBUG: Skipping actions"; }
} else {
	// Do this if we have an opponent ID  (only time we shouldn't have one is when we FIRST enter combat)
	if (isset($healed)) {
		if ($_SESSION['no_heal'] == 1) {
			$_SESSION['disp_msg'][] = $lang_comb["not_heal_this"];
		} else {
			$_SESSION['disp_msg'][] = "<span class='b'>".$lang_comb["drink"]."</span>".$_SESSION['heal_points']."";
			comb_refsidebar();
		}
		unset($healed);
	}

	// COMBAT ACTIONS
	if (!isset($comb_act)) {
		$comb_act= 'travel';
	}
	
	// Flee Code
	if ($comb_act == 'flee') {
		$char_flee_roll = ($character->dexterity + $character->level + diceroll());
		$opp_flee_roll = ($oppcharacter->dexterity + $oppcharacter->level + diceroll());

		if (DEBUG) { $GLOBALS['debugmsgs'][] = "**DEBUG: Flee roll $char_flee_roll > $opp_flee_roll?"; }

		if ($char_flee_roll > $opp_flee_roll OR rand(1,100) < 50 ) {
			// Move character to random adjacent location
			for($i = 0; $i < 24; ++$i) {
				if ($character->relocate(rand(1,8))) {
					break;
				}
			}
			// Character Flees
			$link = endfight();
			jsChangeLocation($link, 0);
		} else {
			jsChangeLocation("combat.php?comb_act=npc_attack&fleefail=1", 0);
		}
	}

	// BEGIN ATTACK CODE -- ##FOR GOD MODE## use: $comb_act == 'char_attack'
	if ($comb_act == 'both_attack' OR $comb_act == 'magic_attack') {
		
		// Check Stamina points
		if ($character->stamina_points <= 0) {
			$_SESSION['disp_msg'][] = "<span class='b red'>$lang_comb[stam_noo]</span>";
		} else {
			// CHARACTER ATTACKS

			// Using up a scroll/magic?
				if ($comb_act == 'magic_attack') {
					$res = $db->query("SELECT name,min_damage,max_damage,damage_mess,req_skill FROM phaos_spells_items WHERE id = $spellid");
					list($name,$min_damage,$max_damage,$damage_mess,$req_skill) = $res->fetch_row();

					// Remove scroll from inventory
					$db->query("DELETE FROM phaos_char_inventory WHERE id = '$invid'");

				if ($character->wisdom + rand(1,$character->wisdom) < $req_skill) {
					$defenders = array();
					$_SESSION['disp_msg'][] = "<span class='b red'>$lang_comb[spell_fumble]</span>";
				} else {
					// set area effect
					$numdefenders = $damage_mess * (1 + (int)($character->wisdom / 9 + rand(0,99) * 0.01));
					if ($numdefenders > 0) {
						if ($numdefenders >= count($opponents)) {
							$defenders = &$opponents;
						} else {
							$defenders = array_rand_assoc_array($opponents, $numdefenders);
						}
					}
					// Always attack the one the character is engaged with
					$defenders[$oppcharacter->id] = &$oppcharacter;
				}
			} else {
				$defenders = array( $oppcharacter->id => &$oppcharacter );
			}

			foreach(array_keys($defenders) as $defenderkey) {
				$defender = &$defenders[$defenderkey];
				if ($defender->hit_points <= 0) {
					if (DEBUG) { $GLOBALS['debugmsgs'][] = "**DEBUG: defender[$defenderkey]=".print_r($defender,true); }
				} else {
					// Determine char attack value
					$_SESSION['char_attack']= $character->attack_roll($comb_act);
					
					// Determine opponent defense value
					$_SESSION['opp_defence']= $defender->defence_roll($comb_act);

					//Check hit to opponent
					$damage_multiplier = $_SESSION['char_attack'] - $_SESSION['opp_defence']; // PERHAPS we can add dex factor in here... kaspirs doesnt use.
					$lucky_hit = rand(0,20);

					if ($lucky_hit == 0) { // Missed //kaspir doesnt use $damage_multiplier. currently, you miss 1/20 times.
						$_SESSION['disp_msg'][] = "<span class='b'>$lang_comb[u_miss]</span>";
					}

					if (($lucky_hit == 1) OR ($lucky_hit == 2)) { // Tie (missed) //kaspir, a random dodge added in for mob, currently dodges 2/20
						$_SESSION['disp_msg'][] = "<span class='b'>$defender->name $lang_comb[def_ur_att]</span>";
					}

					if (DEBUG) { $GLOBALS['debugmsgs'][] = "**DEBUG: damage multiplier = ".$damage_multiplier;}

					if ($lucky_hit >= 3) {	// Hit
						// Do damage to Opponent
						if ($comb_act == "magic_attack") {	// magic damage
							if ($character->race = "Lizardfolk" ) {
								$_SESSION['dmg_to_opp'] = roll_damage(root_damage($min_damage*($character->wisdom * 0.10)),root_damage($max_damage*($character->wisdom * 0.10)));
							} else {
								$_SESSION['dmg_to_opp'] = roll_damage(root_damage($min_damage*($character->wisdom * 0.05)),root_damage($max_damage*($character->wisdom * 0.05)));
							}
						} else {	// normal damage
							//$_SESSION['dmg_to_opp'] = roll_damage(root_damage($character->attack_min()*$damage_multiplier)-$defender->defense_min(),root_damage($character->attack_max()*$damage_multiplier)-$defender->defense_min()); //Original
							//Kaspir's DMG_TO_OPP string. Takes dam_mulitplier out, because dex is going to be the new determining factor for critical high hits, instead of a random chance. See class_character.php for specifics @ min $ max.
							$_SESSION['dmg_to_opp'] = roll_damage(root_damage($character->attack_min() / $defender->mob_defense_min() ),root_damage($character->attack_max() / $defender->mob_defense_max() ));
							
							if ($character->race == 'Vampire' AND $lucky_hit <= 4) {
								$_SESSION['disp_msg'][] = "<span class='b pink'>$lang_comb[suck_opp_blood]</span>";
								$character->hit_points += rand(1,3); // THIS WORKS, gives +1 to 3 hitpoints on lucky hit.                      
							}
							if ($character->race == 'Undead') {
								$_SESSION['disp_msg'][] = "<span class='b pink'>$lang_comb[gain_stam]</span>";
								$character->stamina_points += rand(1,3);
							}
						}
						
						if ($_SESSION['dmg_to_opp'] <= 0) { $_SESSION['dmg_to_opp'] = 1; }

						$defender->hit_points = $defender->hit_points - $_SESSION['dmg_to_opp'];
						$_SESSION['disp_msg'][] = "<span class='b'>$lang_comb[att_hit_foor] <span class='big red'>$_SESSION[dmg_to_opp]</span> . . .</span>";

						// Update Opponent Hit Points in the DataBase
						$res = $db->query("UPDATE phaos_characters SET hit_points = ".$defender->hit_points." WHERE id = ".$defender->id."");
						if (!$res){ showError(__FILE__,__LINE__,__FUNCTION__); exit; };

						// Update opponent character
						#FIXME: when   ($damage_mess == 1)  this whole section needs to loop
						if ($defender->hit_points <= 0) {	// An opponent has been killed
							if ($defender->hit_points < 0) { $defender->hit_points = 0; }
							$_SESSION['disp_msg'][] = "<span class='big b red'>" .$lang_comb["kill_a"]." ".$defender->name. "</span>";

							if ($defender->id == $_SESSION['opponent_id']){
								$_SESSION['opponent_id'] = 0;
								unset($_SESSION['opponent_id']);	// clear current opp
							}

							$ret = $defender->kill_characterid();
							if (DEBUG) { $GLOBALS['debugmsgs'][] = "**DEBUG: killing opponent"; }

							// Add 2 monsters to replace each dead one, population control happen elsewhere.
							for($i = 1; $i <= 2; $i++) {
								npcgen();
							}
							
							// GOLD_BONUS: easy way to bump gold drop.
							$_SESSION['goldbonus'] = rand(5,30);
							
							// Receive Gold
							if ($character->race != "Gnome") {
								$_SESSION['gold_rec'] = (int)( ($defender->gold) * ($defender->level) );
							} else {
								$_SESSION['gold_rec'] = (int)( ($defender->gold) + rand(10, 50) * ($defender->level) ); // Gnome ability!
							}
	
							$fair_gp = (int)(($defender->max_hp2 * 0.20) * ($defender->level)); // FAIR LVL GOLD BONUS!
							$_SESSION['gold_rec'] += $_SESSION['goldbonus'] + $fair_gp; // add it in
							$character->gold += $_SESSION['gold_rec'];
							$_SESSION['disp_msg'][] = "<span class='b'>$lang_fun[gai_gold] <span class='gold big'>$_SESSION[gold_rec]</span></span>";

							// Receive Experience
							//if ($character->level < 10) { $_SESSION['xp_rec'] = (int)($defender->max_hp+$character->wisdom); } else { $_SESSION['xp_rec'] = (int)($defender->max_hp+$character->wisdom); }
							//$_SESSION['xp_rec'] = (int)(($defender->max_hp/5)*($character->wisdom/3)); //kaspirs, my hp are based on level so i use hp here.
							
							if (@$arenalocation) { // Arena Bonus: 10% xp gain
								$_SESSION['xp_rec'] == (int)($_SESSION['xp_rec'] * 0.10); 
							} else {
								if ($character->level < 10) {
									$_SESSION['xp_rec'] = (int)( ($defender->xp) * ($character->wisdom * 0.25) );
								} else {
									$_SESSION['xp_rec'] = (int)( ($defender->xp) * ($character->wisdom * 0.15) );
								} 
								
								$fair_xp = (int)(($defender->max_hp2 * 0.25) * ($defender->level)); // FAIR LVL EXP BONUS!
								$_SESSION['xp_rec'] += $_SESSION['xp_rec'] + $fair_xp; // add it in
								$character->xp += $_SESSION['xp_rec'];
								$_SESSION['disp_msg'][] = "<span class='b'>$lang_fun[gai_xp] <span class='big green'>$_SESSION[xp_rec]</span></span>";
							}
							
							//kaspir added for quest2
							/*	
							$sql="SELECT * FROM phaos_questhunters WHERE charid='".$character->id."' AND questid=2 LIMIT 1";
							$res=$db->query($sql);
							//$hunterrow=@mysqli_fetch_array($res);
							if ($res) {
								$sql="SELECT * FROM phaos_quests WHERE questid=2 LIMIT 1";
								$res=$db->query($sql);
								$questrow=@mysqli_fetch_array($res);
								
								if (($defender->race == $questrow["killrace"]) && ($questrow["killtype"])) {
									
								}
							}
							*/
							
							// Update Character rewards to the DataBase & new Kill Counter
							$res = $db->query("UPDATE phaos_characters SET gold=".$character->gold.", xp=".$character->xp.", kills=kills+1 WHERE id='$PHP_PHAOS_CHARID'");
							if (!$res) { showError(__FILE__,__LINE__,__FUNCTION__); exit; };

							$character->all_skillsup($comb_act,$lang_fun); //This is where is runs the function (from class_char.php, line:983) if a chance to get any points.

							$list = whos_here($character->location);

							if ($defender->id == $oppcharacter->id) {
								// The opponent we are engaged with has been killed
								$_SESSION['endcombat']= true;
							}
							comb_refsidebar();
						} //killed opponent
					} //hit opponent
				} //living defender
			} //foreach defender
		} //end "has enough stamina"

		$_SESSION['disp_msg'][] = "&nbsp";

		$character->update_stamina();
	
	} // end all char attack code
	
	

	if ( ($comb_act == 'both_attack' OR $comb_act == 'npc_attack' OR $comb_act == 'magic_attack') AND $_SESSION['endcombat'] == false) {

		// OPPONENT ATTACKS
		if (isset($npcfirstatt)) {
			$_SESSION['disp_msg'][] = "<span class='big red'>$lang_comb[under_att]</span>";
			unset($npcfirstatt);
		}

		if (isset($fleefail)) {
			$_SESSION['disp_msg'][] = "<span class='b big'>$lang_comb[fail_flee] ".sayOpponents()."</span>";
			unset($fleefail);
			if (DEBUG) { $GLOBALS['debugmsgs'][] = "**DEBUG: Flee = Char: ".($character->dexterity+$character->level)."+(2-30)  /  MOB: ".($oppcharacter->dexterity+$oppcharacter->level)."+(2-30)"; }
		}

		// Let each opponent attack
		foreach(array_keys($opponents) as $opponentskey) {
			$attackingcharacter= &$opponents[$opponentskey];
			if ($attackingcharacter->hit_points <= 0) {
				if (DEBUG) { $GLOBALS['debugmsgs'][] = "**DEBUG: $attackingcharacter->name #$attackingcharacter->id is dead"; }
					unset($opponents[$opponentskey]);
			} else {
				if (DEBUG) { $GLOBALS['debugmsgs'][] = "**DEBUG: <span class='u'>$attackingcharacter->name #$attackingcharacter->id</span>"; }

				$_SESSION['opp_attack'] = $attackingcharacter->attack_roll($comb_act);

				//Set characters defence
				$_SESSION['char_def'] = $character->defence_roll($comb_act);

				$damage_multiplier = ($_SESSION['opp_attack'] * 2) - fairInt($_SESSION['char_def']);
				//$damage_multiplier = rand(1,10); //kaspir

				if ($damage_multiplier < 0) { // Missed
					$_SESSION['disp_msg'][] = $attackingcharacter->name." misses you!";
				}

				// Check hit to Character
				if (!$damage_multiplier) { // Defended
					$_SESSION['disp_msg'][] = "<span class='b big'>$lang_comb[def_en] $attackingcharacter->name</span>";
				}

				if (DEBUG) { $GLOBALS['debugmsgs'][] = "**DEBUG: damage multiplier = ".$damage_multiplier; }
				
				
				if ($damage_multiplier > 0) {
					// Do and show damage to Character
					$_SESSION['dmg_to_char'] = roll_damage(root_damage($attackingcharacter->attack_min_mob() / $character->defense_min() ), root_damage($attackingcharacter->attack_max_mob() / $character->defense_max()) );

					//if ($_SESSION['dmg_to_char'] <= 1) {$_SESSION['dmg_to_char'] = rand(1,3); } //random low dam booster // kaspir
					if ($_SESSION['dmg_to_char'] <= 0) {$_SESSION['dmg_to_char'] = 1; }

					$character->hit_points = $character->hit_points - $_SESSION['dmg_to_char'];

					$_SESSION['disp_msg'][] = "<span class='b'>".$attackingcharacter->name." ".$lang_comb['hit_for_u']." <span class='big red'>".$_SESSION['dmg_to_char']."</span> !!</span>";

					if ($character->hit_points <= 0) {
						$character->hit_points = 0;
						$_SESSION['disp_msg'][] = "<span class='b big red'>".$attackingcharacter->name." ".$lang_comb["kill_u_man"]."!!</span>";

						// Let NPC Receive Gold
						$attackingcharacter->gold += (int)($character->gold * 0.0005);
						$character->gold -= (int)($character->gold * 0.30);  // Kaspirs, Players looses 30%

						//$attackingcharacter->xp += ((int)($character->max_hp/1)+$attackingcharacter->wisdom)*$_SESSION['fightbonus'];	//   (max_hp/1) for now (NPC's need to gain faster) //Original
						$cur_levxp = $db->query("SELECT * FROM phaos_level_chart WHERE level = '".$character->level()."'");
						//if ($row = mysqli_fetch_array($cur_levxp)) {
						if ($row = $cur_levxp->fetch_assoc()) {
							$make_xp = $row["xp_needed"];
							$math = ($character->xp - $make_xp)  *0.50;	}
						$attackingcharacter->xp += (int)($character->xp * 0.00005); //mob gets nothing almost!
						$character->xp -= (int)($math);		// kaspirs

						// Update database with win/loss for player/NPC
						$req = $db->query("UPDATE phaos_characters SET gold = ".$character->gold.", xp = ".$character->xp." WHERE id = '".$character->id."'");
						if (!$req) { showError(__FILE__,__LINE__,__FUNCTION__); exit;}
						
						// kaspir added -- the kill counter for NPC, on player death
						$req = $db->query("UPDATE phaos_characters SET gold = ".$attackingcharacter->gold.", xp = ".$attackingcharacter->xp.", kills = kills+1 WHERE id = '".$attackingcharacter->id."'");
						if (!$req) { showError(__FILE__,__LINE__,__FUNCTION__); exit;}

						// Winning mob gains skills and gets stronger!
						$attackingcharacter->all_skillsup("",$lang_fun_opp); 

						unset($_SESSION['opponent_id']);

						// when dead, go to Gornath (easy city of undead) to start over AND total death count.
						if (! $db->query("UPDATE phaos_characters SET location = 4072, deaths = deaths+1 WHERE id = '$PHP_PHAOS_CHARID'") ) {  showError(__FILE__,__LINE__,__FUNCTION__); die; }
							//kaspir added -- deaths column to phaos_characters to count/record # of char deaths.				
						$break_loop = true;
					}

					// Update Character Hit Points in the DataBase
					$req = $db->query("UPDATE phaos_characters SET hit_points = ".$character->hit_points." WHERE id = '$PHP_PHAOS_CHARID'");
					if (!$req) { showError(__FILE__,__LINE__,__FUNCTION__); exit; }

					// REFRESH SIDEBAR INFO
					$comb_refsidebar = true;

					$attackingcharacter->update_stamina();

					if (@$break_loop) {
						break;
					}
				}
			}//hit points>0	
		}//end foreach opponents
	}

   $character->update_stamina();
   if ($character->stamina_points < (0.25 * $character->stamina_points) ){
        $comb_refsidebar = true;
   }

   // DRINK POTIONS
   if ($comb_act == 'drink_potion') {
         $_SESSION['heal_points'] = $character->drink_potion($invid);
         $_SESSION['no_heal'] = 0;
         $comb_refsidebar = true;

       	// Still in combat?
        if ($_SESSION['endcombat'] == false) {	// "false" means you drink and OPP attacks  --- set to true by dragzone
            jsChangeLocation("combat.php?comb_act=npc_attack&healed", 0);
        } else {					// "true" means you drink, no OPP attack
            jsChangeLocation("combat.php?healed", 0);
        }
   }

    if ($comb_refsidebar) {
        comb_refsidebar();
		$comb_refsidebar = false;
    }
	
} //end skip actions, starts on #201
?>


<div id="combat-block" class="fullsize">
	<dl class="bgcolor center big"><?php echo sayOpponents();?></dl><br><br>
	<div id="player-block" class="left quartersize">
		<img src="<?php echo $character->image;?>" height="80px"><br>
		<span class="b"><?php echo $lang_comb['ur_heea']."<br>".$character->hit_points." / ".$character->max_hp;?></span>
		<meter id="hp-bar" class="fullsize" min="0" max="<?php echo $character->max_hp;?>" low="<?php echo ($character->max_hp * 0.30);?>" high="<?php echo ($character->max_hp * 0.75);?>" optimum="<?php echo $character->max_hp;?>" value="<?php echo $character->hit_points;?>">
			<span id="hp-progress">
				<?php print ($character->max_hp - $character->hit_points); ?>
			</span>
		</meter>
		
		<?php
		
		// Display potions in inventory
		echo "
		<table width='100%' border=0 cellspacing=0 cellpadding=5 align=center><tr>";
		$result = $db->query("SELECT id,item_id,count(item_id) FROM phaos_char_inventory WHERE username = '".$character->user."' AND type='potion' GROUP BY item_id ");
		if ($result->num_rows) {
			// Build invo potions & details
			while (list($id,$item_id,$count) = $result->fetch_row()) {
				$res = $db->query("SELECT name,image_path,heal_amount FROM phaos_potion WHERE id='$item_id' ");
				$row = $res->fetch_assoc();
				
				list($description,$image_path,$heal_amount) = [$row["name"],$row["image_path"],$row["heal_amount"]];

				echo "<td><input class='icon' type='image' src='$image_path' title='$description: $lang_heal +$heal_amount'  onClick='self.location=\"combat.php?comb_act=drink_potion&item_id=$item_id&invid=$id\"'><br>($count)</td>";
			}
		} else {
			echo "<td align=center>".$lang_comb["no_pot"]."</td>";
		}
		echo "
		</tr>
		</table>";

		// Display magic in inventory		
		echo "
		<table width='100%' border=0 cellspacing=0 cellpadding=5 align=center><tr>";
		$result = $db->query("SELECT id,item_id,count(item_id) FROM phaos_char_inventory WHERE username = '".$character->user."' AND type='spell_items' GROUP BY item_id ");
		if ($result->num_rows) {
			
			while (list($id,$item_id,$count) = $result->fetch_row()) {
				$res = $db->query("SELECT name,image_path,damage_mess,min_damage,max_damage,req_skill FROM phaos_spells_items WHERE id=$item_id ");
				$row = $res->fetch_assoc();
				
				list($description,$image_path,$damage_mess,$min_damage,$max_damage,$req_skill) = 
					[$row["name"],$row["image_path"],$row["damage_mess"],$row["min_damage"],$row["max_damage"],$row["req_skill"]];

				if ($character->wisdom >= $req_skill) {
					if ($damage_mess == 0) {
						$damage_mess = $lang_shop["mgc_eff1"];} else {$damage_mess = $lang_shop["mgc_eff2"];
					}
					echo "<td><input class='icon' type='image' src='$image_path' title='$description $damage_mess $min_damage-$max_damage $lang_dam'  onClick='self.location=\"combat.php?comb_act=magic_attack&spellid=$item_id&invid=$id\"'><br>($count)</td>";
				} else {
					if ($damage_mess == 0) {
						$damage_mess = $lang_shop["mgc_eff1"];} else {$damage_mess = $lang_shop["mgc_eff2"];
					}
					echo "<td align=center class='red'><input class='icon' type='image' src='$image_path' title='$description $damage_mess $min_damage-$max_damage $lang_dam'  onClick='self.location=\"combat.php?comb_act=magic_attack&spellid=$item_id&invid=$id\"'><br>($count)</td>";
				}
			}
		} else {
			echo "<td align=center><span class='i'>".$lang_comb["no_mag"]."</span></td>";
		}
		echo "
		</tr>
		</table>";
		?>
	</div>
	
	<div id="combat_msgs" class="inline center halfsize">
		<br><br>
		<?php
		//if ( (strpos($_SERVER['REQUEST_URI'], 'opp_type=roammonst') !== false) OR (strpos($_SERVER['REQUEST_URI'], 'comb_act=nextfight') !== false) ) {
		if ( (strpos($this_URI, 'opp_type=roammonst') !== false) OR (strpos($this_URI, 'comb_act=nextfight') !== false) ) {
			
			$char_first_attack = $character->dexterity + diceroll();
			$opp_first_attack = $oppcharacter->dexterity + diceroll();

			if ($char_first_attack <= $opp_first_attack) {
				jsChangeLocation("/combat.php?comb_act=npc_attack&npcfirstatt=1", 0);
			} else {
				// You attack first
				echo "<span class='big'>".$lang_comb["sight_enn"]."</span>";
			}
		}
		
		// Display combat session actions
		print_msgs(@$_SESSION['disp_msg']);
		unset($_SESSION['disp_msg']);
		?>
	</div>
	
	<div id="monster-block" class="right quartersize">
		<img class="comb-mob" src="<?php echo $oppcharacter->image;?>" onClick="self.location='combat.php?comb_act=both_attack'"><br>
		<span class="b"><?php echo $lang_comb['opp_heea']."<br>".$oppcharacter->hit_points." / ".$oppcharacter->max_hp2;?></span><br>
		<meter id="hp-bar" class="fullsize" min="0" max="<?php echo $oppcharacter->max_hp2;?>" low="<?php echo ($oppcharacter->max_hp2 * 0.30);?>" high="<?php echo ($oppcharacter->max_hp2 * 0.75);?>" optimum="<?php echo $oppcharacter->max_hp2;?>" value="<?php echo $oppcharacter->hit_points;?>">
			<span id="hp-progress">
				<?php print ($oppcharacter->max_hp2 - $oppcharacter->hit_points); ?>
			</span>
		</meter>
	</div>
</div>

<div id="combat-actions" class="inline center halfsize">
	<?php
	// Still Alive
	if ($character->hit_points > 0) {
		if ( !(@$_SESSION['opponent_id']&& !@$_SESSION['endcombat']) ) {
			?>
			<input class="button" type="button" onClick="self.location='combat.php?comb_act=nextfight'" value="<?php echo $lang_comb["_conti"];?>">
			<?php
		}
	} else { // Player died
		unset($_SESSION['opponent_id']);
		?>
		<input class="button" type="button" onClick="self.location='travel.php'" value="<?php echo $lang_comb["_conti"];?>">
		<?php
	}
	
	if (@$_SESSION['opponent_id']&& !@$_SESSION['endcombat']) {
		?>
		<div class="center">
			<br><br>
			<input class="button" type="button" onClick="self.location='combat.php?comb_act=flee'" value="<?php echo $lang_comb["_flee"];?>">
			<input class="button" type="button" onClick="self.location='combat.php?comb_act=both_attack'" value="<?php echo $lang_comb["_attt"];?>">
		</div>
		<?php
	}
	?>
</div>


<?php
require_once "footer.php";
