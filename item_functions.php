<?php
//require_once "config.php";
//require_once "global.php";
//require_once "class_character.php";
//require_once 'include_lang.php';

/*
 * This file contains functions related to item handling.
 */

$armorItems = array("armor","boots","shield","helm","gloves");
$dummycharacter = new character(0);

// Check the ground, if necessary drop new items
function auto_ground() {
    if ( rand(0,9) == 0 || DEBUG ) {
        fix_ground();
    }
    $nlocations = fetch_value("SELECT count(*) FROM phaos_locations",__FILE__,__LINE__,__FUNCTION__);
    //CAVEAT: this includes items stored in shops
    $nground = fetch_value("SELECT count(DISTINCT(location)) FROM phaos_ground WHERE number>0 AND location>'0'",__FILE__,__LINE__,__FUNCTION__);

    $proportion = 25;
    $missingitems = (int)($nlocations / $proportion) - $nground;
    if (DEBUG) { $GLOBALS['debugmsgs'][] = "**DEBUG: Ground: missing $missingitems items ($nground/$nlocations)."; }


    if ($missingitems > 0) {
        $add = $missingitems < 12?$missingitems:12;
        while($add-->0) {
            $location = random_location("item_drop");
            $item_cluster_limit = 4;
            $items_here = fetch_items_for_location($location);
            if (count($items_here) >= $item_cluster_limit) {
                continue;
            }

            $minvalue = 1;
            $maxvalue = powrand(2.75,0.83,5) * powrand(2.75,0.5,8); //kaspir, up the drop quality here.
            $item = random_item($minvalue,$maxvalue);
            if ($item) {
                item_drop($location,$item);
            }
        }
    }
}

// Return a random item or items with a value between minvalue and maxvalue
function random_item($minvalue,$maxvalue,$fixedtype=null,$name_like=null) {
    global $validDropItems, $dummycharacter;

    if ($name_like && $name_like != '%') {
        $condition_name = "name LIKE '$name_like'";
    } else {
        $condition_name = '1=1';
    }

    $found = false;
    $tries_left = 33;
	while($tries_left-->0 && !$found) {
        $type = $fixedtype ? $fixedtype : $validDropItems[rand(0,count($validDropItems)-1)];
        switch($type) {
			case "potion":
				$rnd = rand(1,100); //kaspir added chance roll for high potions
				if ($rnd >= 90) {
					$id = 5; // half rejuv
					$number = 1;
				}elseif ($rnd > 95) {
					$id = 6; // full rejuv
					$number = 1;
				} else {
					$id = rand(1,4); 
					$number = rand(1,3);
				}
                $found = true;
                break;
            case "gold":
                $id = 1;
                //$number = rand($minvalue,$maxvalue); //Original, dropped 1 gold often..
                $number = rand(23,377); //kaspirs gold min/max decision
                $found = true;
                break;
            case "armor":
            case "weapon":
            case "boots":
            case "shield":
            case "helm":
            case "gloves":

            case "spell_items":
                $table = $GLOBALS['tableForField'][$type];
                //HINT: to stop special items from being selected, a trick would be to set buy_value=0
                $query = "SELECT id FROM phaos_$table WHERE sell_price<$maxvalue AND buy_price>$minvalue AND $condition_name ORDER BY rand() LIMIT 1";
                $id = fetch_value($query,__FILE__,__LINE__,__FUNCTION__);
                //$info= fetch_item_additional_info($item,$dummycharacter);
                if ($id) {
                    $number = 1;
                    $found = true;
                }
                break;
            default:
                echo "<p>Unknown item type $type in ".__FUNCTION__."</p>";
                $tries_left= 0;
        }
    }
    if ($found) {
        return array('id'=>$id,'type'=>$type,'number'=>$number);
    } else {
        return null;
    }
}

function random_location($whatfor) {
    $where = "id<>0 AND special=0 AND buildings='n' AND pass='y'";
    $whereid = speedup_random_row('id','phaos_locations',$where);
    return fetch_value("SELECT id FROM phaos_locations WHERE $where AND $whereid ORDER BY rand() LIMIT 1");
}

// due to map restructuring, ground items may become inaccessible - remove these
// CAVEAT: this will wreak havoc if you ever remove the dummy storage locations for shops
// locations have to be marked with 'special', or have to be accessible
// so if you make locations with varying accessibility, make them special
function fix_ground() {
	global $db;
    //bad locations - be careful, missing braces mean trouble
    $where = "( id IS NULL or id=0 or (special<>1 AND NOT (pass LIKE 'y') ) )";

    $noks = fetch_all("SELECT $where AS ok, count(*) AS n FROM phaos_ground LEFT JOIN phaos_locations ON (phaos_ground.location=phaos_locations.id) GROUP BY ok");
    $ok = 0;
    $nok = 0;
    foreach($noks as $k) {
        if (!$k['ok']) {
           $ok = $k['n'];
        } else {
           $nok = $k['n'];
        }
    }

    if (DEBUG) { $GLOBALS['debugmsgs'][] = "**DEBUG: ".__FUNCTION__.". inaccessible items: ".$nok."/".($ok + $nok); }
    if ($nok > $ok) {
        print (__FUNCTION__.': The number of items in inaccessible locations is unusally high, not executing automatic cleanup - please inform a wizard.');
    } else if ($nok > 0) {
        //this does a similar query as above, so if it happens regularly that $nok>0, it is extra work
        $query = "SELECT location FROM phaos_ground LEFT JOIN phaos_locations ON (phaos_ground.location=phaos_locations.id) WHERE $where";
        $noks = fetch_all($query);
        $locations = makeList(project($noks,'location'));
        $query = "DELETE FROM phaos_ground WHERE location IN $locations";
        if (DEBUG) { $GLOBALS['debugmsgs'][] = "**DEBUG: sql for cleaning: ".$query; }
		
        $req = $db->query($query);
        if (!$req) {showError(__FILE__,__LINE__,__FUNCTION__); exit;}
    }
}


// Get all items on the ground. allows a spotting value to be used so that not all is visible
function fetch_items_for_location($location,$xof100=100) {
	if ($xof100 < 100) {
		//first make the finder pass a find-at-all check
		//FIXME: this somehow depends on close locations having the same sqrt
		$seed = $xof100 + $location + date('d');
		if (DEBUG) { $GLOBALS['debugmsgs'][] = "**DEBUG: seed=$seed for $location,$xof100"; }
		
		srand($seed);
		if (rand(1,100) > $xof100) {
			return array();
		}
		srand(time());
    }
	//$query = "SELECT item_id AS id, item_type AS type, number FROM phaos_ground WHERE location='$location' AND RAND()*100<=$xof100";
	$query = "SELECT item_id AS id, item_type AS type, number FROM phaos_ground WHERE location='$location'";    
	//if (DEBUG) { $GLOBALS['debugmsgs'][] = "**DEBUG: ".__FUNCTION__.":". $query; }
	return fetch_all($query,__FILE__,__LINE__,__FUNCTION__);
}

// Select a random item from the ground
function fetch_random_item_for_location($location) {
	$query = "SELECT item_id AS id, item_type AS type, number FROM phaos_ground WHERE location='$location' ORDER BY RAND() LIMIT 1"; 	
    //if (DEBUG) { $GLOBALS['debugmsgs'][] = "**DEBUG: ".__FUNCTION__.":". $query; }
    return fetch_first($query,__FILE__,__LINE__,__FUNCTION__);
}

//
// Get name & other info about item
// @args $item array(id,type,number),
// @args const &$character class
// @return array( description, .., skill_req, ..
function fetch_item_additional_info($item,&$character) {
	global $db, $lang_dam;
	
    if (!$item) {
        echo "<p>no item handed over!</p>";
        return array();
    }

    $info['id'] = $item['id'];
    $info['number']= (@$item['number']) ? $item['number'] : 1;
    $info['skill_need'] = "#FFF";

    //FIXME: maybe move buy price and sell price into their own tables

    switch($item['type']) {
        case "gold":
            $info['description']= $GLOBALS['lang_gold'];
            $info['image_path'] = "images/icons/gold.gif";
            $info['effect'] = "";
            $info['sell_price'] = $item['number'];
            $info['buy_price'] = $item['number'];
            $info['skill_req'] = "";
            $info['skill_need'] = "#FFFFFF";
            $info['skill_type'] = "";
            break;

        // Add extra information
        case "weapon":
            $result = $db->query("SELECT * FROM phaos_weapons WHERE id = '$item[id]'");
            if ($row = $result->fetch_assoc()) {
				$info['description'] = $row["name"];
				$info['image_path'] = $row["image_path"];
				$info['min_damage'] = $row["min_damage"];
				$info['max_damage'] = $row["max_damage"];
				$info['effect'] = $lang_dam." ".$row["min_damage"]."-".$row["max_damage"];
				$info['sell_price'] = $row["sell_price"];
				$info['buy_price'] = $row["buy_price"];
				$info['skill_req'] = ceil(($row['min_damage'] + $row['max_damage']) / 5.33); //MUST ALSO CHANGE IN CLASS_CHARACTER.PHP ON LINE 933
				if ($info['skill_req'] <= 0) {$info['skill_req'] = 1; }
				if ($info['skill_req'] <= $character->fight) {$info['skill_need'] = "#FFFFFF";} else {$info['skill_need'] = "red";} 
				$info['skill_type'] =  $GLOBALS['lang_att'];
            }
            break;

        case "armor":
            $result = $db->query("SELECT * FROM phaos_armor WHERE id = '$item[id]'");
            if ($row = $result->fetch_assoc()) {
				$info['description'] = $row["name"];
				$info['image_path'] = $row["image_path"];
				$info['armor_class'] = $row["armor_class"];
				$info['effect'] = $GLOBALS['lang_shop']["ac"]." ".$row["armor_class"];
				$info['sell_price'] = $row["sell_price"];
				$info['buy_price'] = $row["buy_price"];
				$info['skill_req'] = ceil(($row["armor_class"] - 8) / 2);
				if ($info['skill_req'] <= 0) { $info['skill_req'] = 1; }
				if ($info['skill_req'] > $character->defence) {$info['skill_need'] = "red";} else {$info['skill_need'] = "#FFFFFF";}
				$info['skill_type'] =  $GLOBALS['lang_def'];
            }
            break;

        case "boots":
            $result = $db->query("SELECT * FROM phaos_boots WHERE id = '$item[id]'");
            if ($row = $result->fetch_assoc()) {
				$info['description'] = $row["name"];
				$info['image_path'] = $row["image_path"];
				$info['armor_class'] = $row["armor_class"];
				$info['effect'] = $GLOBALS['lang_shop']["ac"]." ".$row["armor_class"];
				$info['sell_price'] = $row["sell_price"];
				$info['buy_price'] = $row["buy_price"];
				$info['skill_req'] = ceil(($row["armor_class"]));
				if ($info['skill_req'] > $character->defence) {$info['skill_need'] = "red";} else {$info['skill_need'] = "#FFFFFF";}
				$info['skill_type'] =  $GLOBALS['lang_def'];
            }
            break;

        case "gloves":
            $result = $db->query("SELECT * FROM phaos_gloves WHERE id = '$item[id]'");
            if ($row = $result->fetch_assoc()) {
				$info['description'] = $row["name"];
				$info['image_path'] = $row["image_path"];
				$info['armor_class'] = $row["armor_class"];
				$info['effect'] = $GLOBALS['lang_shop']["ac"]." ".$row["armor_class"];
				$info['sell_price'] = $row["sell_price"];
				$info['buy_price'] = $row["buy_price"];
				$info['skill_req'] = ceil(($row["armor_class"]));
				if ($info['skill_req'] > $character->defence) {$info['skill_need'] = "red";} else {$info['skill_need'] = "#FFF";}
				$info['skill_type'] =  $GLOBALS['lang_def'];
            }
            break;

        case "helm":
            $result = $db->query("SELECT * FROM phaos_helmets WHERE id = '$item[id]'");
            if ($row = $result->fetch_assoc()) {
				$info['description'] = $row["name"];
				$info['image_path'] = $row["image_path"];
				$info['armor_class'] = $row["armor_class"];
				$info['effect'] = $GLOBALS['lang_shop']["ac"]." ".$row["armor_class"];
				$info['sell_price'] = $row["sell_price"];
				$info['buy_price'] = $row["buy_price"];
				//$info['skill_req'] = ceil($row["armor_class"]); //Original
				$info['skill_req'] = ceil($row["armor_class"]+1); //kaspir
				if ($info['skill_req'] > $character->defence) {$info['skill_need'] = "red";} else {$info['skill_need'] = "#FFF";}
				$info['skill_type'] =  $GLOBALS['lang_def'];
            }
            break;

        case "shield":
            $result = $db->query("SELECT * FROM phaos_shields WHERE id = '$item[id]'");
            if ($row = $result->fetch_assoc()) {
				$info['description'] = $row["name"];
				$info['image_path'] = $row["image_path"];
				$info['armor_class'] = $row["armor_class"];
				$info['effect'] = $GLOBALS['lang_shop']["ac"]." ".$row["armor_class"];
				$info['sell_price'] = $row["sell_price"];
				$info['buy_price'] = $row["buy_price"];
				//$info['skill_req'] = ceil($row["armor_class"]); //Original
				$info['skill_req'] = ceil(($row["armor_class"]+2)); //kaspir
				if ($info['skill_req'] > $character->defence) {$info['skill_need'] = "red";} else {$info['skill_need'] = "#FFF";}
				$info['skill_type'] =  $GLOBALS['lang_def'];
            }
            break;

        case "potion":
        	$result = $db->query("SELECT * FROM phaos_potion WHERE id = '$item[id]'");
        	if ($row = $result->fetch_assoc()) {
        		$info['description'] = $row["name"];
        		$info['image_path'] = $row["image_path"];
        		// don't want to give away what potion might do  :)
        		//$effect = $GLOBALS['lang_shop']["heall"]." ".$row["heal_amount"];
        		$info['effect'] = $row["effect"];
                $info['sell_price'] = $row["sell_price"];
                $info['buy_price'] = $row["buy_price"];
        		//$skill_req = ceil($row["armor_class"]);
        		//if ($skill_req > $character->defence) {$skill_need = "red";} else {$skill_need = "#FFF";}
        		//$skill_type =  $GLOBALS['lang_def'];
                $info['skill_need'] = '';
                $info['skill_req'] = 0;
                $info['skill_type'] = '';
        	}
            break;

        case "spell_items":
            $result = $db->query("SELECT * FROM phaos_spells_items WHERE id = '$item[id]'");
            if ($row = $result->fetch_assoc()) {
				$info['description'] = $row["name"];
				$info['image_path'] = $row["image_path"];
				$info['min_damage'] = $row["min_damage"];
				$info['max_damage'] = $row["max_damage"];
				$info['sell_price'] = $row["sell_price"];
				$info['buy_price'] = $row["buy_price"];
				$info['skill_req'] = $row["req_skill"];
				$info['damage_mess'] = $row["damage_mess"] == 0?"<br>[Single Effect]":"[Mass Effect]" ; //Original
				//if ($info['damage_mess'] == 0){ $info['damage_mess'] = $lang_shop["mgc_eff1"];} else {$info['damage_mess'] = $lang_shop["mgc_eff2"];} //kaspir's
				if ($info['skill_req'] > $character->wisdom) {$info['skill_need'] = "red";} else {$info['skill_need'] = "#FFF";}
				$info['effect'] = $lang_dam." ".$row["min_damage"]."-".$row["max_damage"]." ".$info['damage_mess']."";
				$info['skill_type'] = $GLOBALS['lang_wis'];
            }
            break;

        default:
            echo "<p>Unknown item type $item[type] ($item[id]) in ".__FUNCTION__."</p>";
    }
    return $info;
}

//FIXME: actually, tables should be locked when doing this
function item_pickup($location,$item) {
	global $db;
	
    $query = "SELECT number FROM phaos_ground WHERE location='$location' AND item_id='$item[id]' AND item_type='$item[type]'";
    $number = fetch_value($query,__FILE__,__LINE__,__FUNCTION__);

    if ($item['number'] > $number){
        $item['number'] = $number;
    }
    $item['number'] = -$item['number'];
    item_drop($location,$item);

    //remove drops of zero size
    $req = $db->query("DELETE FROM phaos_ground WHERE number<=0");
    if (!$req) {showError(__FILE__,__LINE__,__FUNCTION__); exit;}

    return -$item['number'];
}

//FIXME: actually, tables should be locked when doing this
//FIXME: an update+= statement also would be more robust even with no locking
function item_drop($location,$item) {
	global $db;
	
    $query = "SELECT number FROM phaos_ground WHERE location='$location' AND item_id='$item[id]' AND item_type='$item[type]'";
    $here = fetch_value($query,__FILE__,__LINE__,__FUNCTION__);

    $number = $item['number'];
    if ( $here ){
        $number += $here;
    }

    $query = "REPLACE INTO phaos_ground (location,item_id,item_type,number)
                VALUES ('$location','$item[id]','$item[type]','$number')";
    $req = $db->query($query);
    if (!$req) {showError(__FILE__,__LINE__,__FUNCTION__); exit;}

    if (DEBUG) { $GLOBALS['debugmsgs'][] = "**DEBUG: Dropped item: ".print_r($item,true); }
}
