<?php

function diceroll() {
	return rand(1,15) + rand(1,15);
	// odds, rolling 2 15-side dice
	//   2=0.4%      3=0.9%      4=1.3%      5=1.8%      6=2.2%      7=2.7%      8=3.1%      9=3.5%     10=4.0%     11=4.5%
	//  12=4.9%     13=5.4%     14=5.7%     15=6.2%     16=6.6%     17=6.1%     18=5.8%     19=5.4%     20=4.9%     21=4.4%
	//  22=4.0%     23=3.6%     24=3.1%     25=2.7%     26=2.2%     27=1.8%     28=1.3%     29=0.9%     30=0.4%
}

function fairInt($f) {
    return (int)($f + rand(0,99) * 0.01);
}

// Get user time (uses regen_time from phaos_characters table)
function smsTime() {
    $start = microtime();
    $start = explode(" ", $start);
    $start = (float)$start[1] + (float)$start[0];
    return $start;
}

// Sets global var for start time
function beginTiming() {
    global $timestart;
    $timestart = smsTime();
}

// Return the timer since beginTiming() was executed
function endTiming() {
    global $timestart;
    return smsTime() - $timestart;
}

// Returns a character player_info.php?=$username
function get_username($charname) {
	global $db;
	
	$res = $db->query("SELECT username FROM phaos_characters WHERE name = '$charname' LIMIT 1");
	if ($row = $res->fetch_assoc()) {
		$s_user = $row["username"];
		return $s_user;
	} else {
		echo "Failure!";
	}
}

// Returns all clan info as vars (for clan_home.php & clan_leader.php mainly.)
function getall_claninfo($clan) {
	global $db;
	
	$res = $db->query("SELECT * FROM phaos_clan_admin WHERE clanname = '$clan'");
	if ($res->num_rows) {
		list($clanid,$clanname,$clanleader,$clanleader_1,$clanbanner,$clansig,$clanlocation,$clanslogan,$clancashbox,$clanmembers,$clancreatedate,$clanrank_1,$clanrank_2,
		$clanrank_3,$clanrank_4,$clanrank_5,$clanrank_6,$clanrank_7,$clanrank_8,$clanrank_9,$clanrank_10,$clan_sig) = $res->fetch_row();
		
		return array($clanid,$clanname,$clanleader,$clanleader_1,$clanbanner,$clansig,$clanlocation,$clanslogan,$clancashbox,$clanmembers,$clancreatedate,$clanrank_1,$clanrank_2,
		$clanrank_3,$clanrank_4,$clanrank_5,$clanrank_6,$clanrank_7,$clanrank_8,$clanrank_9,$clanrank_10,$clan_sig);
		
	} else {
		echo "GUILD DOES NOT EXIST";
	}
}

// Return only one line of clan info
function claninfo($info,$clan) {
	global $db;
	
	$res = $db->query("SELECT * FROM phaos_clan_admin WHERE clanname = '$clan'");
	if ($row = $res->fetch_assoc()) {
		
		$claninfo["id"] = $row["id"];
		$claninfo["name"] = $row["clanname"];
		$claninfo["leader"] = $row["clanleader"];
		$claninfo["coleader"] = $row["clanleader_1"];
		$claninfo["banner"] = $row["clanbanner"];
		$claninfo["tag"] = $row["clansig"];
		$claninfo["location"] = $row["clanlocation"];
		$claninfo["slogan"] = $row["clanslogan"];
		$claninfo["gold"] = $row["clancashbox"];
		$claninfo["members"] = $row["clanmembers"];
		$claninfo["created"] = $row["clancreatedate"];
		$claninfo["rank1"] = $row["clanrank_1"];
		$claninfo["rank2"] = $row["clanrank_2"];
		$claninfo["rank3"] = $row["clanrank_3"];
		$claninfo["rank4"] = $row["clanrank_4"];
		$claninfo["rank5"] = $row["clanrank_5"];
		$claninfo["rank6"] = $row["clanrank_6"];
		$claninfo["rank7"] = $row["clanrank_7"];
		$claninfo["rank8"] = $row["clanrank_8"];
		$claninfo["rank9"] = $row["clanrank_9"];
		$claninfo["rank10"] = $row["clanrank_10"];
		$claninfo["image"] = $row["clan_sig"];
	}
	// Return only requested
	return $claninfo[$info];
}

// Is player in a clan
function ru_inclan($charname) {
	global $db;
	
	$res = $db->query("SELECT * FROM phaos_clan_in WHERE clanmember = '$charname' LIMIT 1");
	if ($res->num_rows) {
		return true;
	}
}

// Get character's clan name
function get_clanname($charname) {
	global $db;
	
	$res = $db->query("SELECT clanname FROM phaos_clan_in WHERE clanmember = '$charname' LIMIT 1");
	if ($row = $res->fetch_assoc()) {
		return $row["clanname"];
	} else {
		return false;
	}
}

// Is player a clan leader or coleader
function ru_clanleader($charname) {
	global $db;
	
	$res = $db->query("SELECT * FROM phaos_clan_admin WHERE clanleader = '$charname' OR clanleader_1 = '$charname' LIMIT 1");
	if ($res->num_rows) {
		return true;
	} else {
		return false;
	}
}

// Still waiting for response in clan search
function ru_inclansearch($charname) {
	global $db;
	
	$res = $db->query("SELECT * FROM phaos_clan_search WHERE charname = '$charname' LIMIT 1");
	if ($res->num_rows) {
		return true;
	}
}

// Fetch & display custom guild logo for any player
function getclan_logo($plname) {
	global $db;
	
    $result = $db->query("SELECT clanname FROM phaos_clan_in WHERE clanmember = '$plname'");
    if ($row = $result->fetch_assoc()) {
    	$mem_clan = $row["clanname"];
    } else {return false;}

    $result = $db->query("SELECT clan_sig FROM phaos_clan_admin WHERE clanname = '$mem_clan'");
    if ($row = $result->fetch_assoc()) {
		$clan_sig = $row["clan_sig"];
    }
    if ($clan_sig == "") {
		echo "";
	} else { ?>
		<img width="15px" height="15px" style="vertical-align:middle;" src="<?php echo $clan_sig; ?>" title="<?php echo $mem_clan; ?>"><?php
    }
}

// Change page using JS, pause timer on seconds
function jsChangeLocation($link,$time) {
	?>
	<script>
		setTimeout(function(){
		   window.location.href = '<?php echo $link; ?>';
		}, <?php echo ($time * 1000); ?>);
	</script>
	<?php
	exit;
}

// Another way of calling $_SERVER[PHP_SELF], but WITH the ?vars=$var
function jsRefreshURL() {
	?>
	<script>
		window.location = document.referrer
	</script>
	<?php
	exit;
}

function refsidebar() {
	?>
	<script>
		parent.side_bar.location.reload();
	</script>
	<?php
	exit;
}

// Is player in a town
function char_intown($location) {
	global $db;
	
	$result = $db->query("SELECT * FROM phaos_buildings WHERE location = '$location'");
	if ($result->num_rows == 0) {
		return false;
	} else {
		return true;
	}
}

// Check if this map location has a specific shop
function shop_valid($location, $shop_id) {
	global $db, $lang_err;

	$result = $db->query("SELECT * FROM phaos_buildings WHERE location = '$location' AND shop_id='$shop_id' ");
	if ($result->num_rows == 0) {
		die ("<h3 class='center b message'>".$lang_err["no_shop"]."</h3>");
	}
	if(@ !list($shop_id,$shop_location,$shop_name,$shop_type,$shop_ownerid,$shop_capacity) = $result->fetch_assoc() ) {
		return false;
	}
    return true;
}

// Print an array of messages
function print_msgs($msgs,$left='<p>',$right='</p>') {
    if (is_array(@$msgs)) {
        foreach ($msgs as $i => $value) {
            echo $left.$value.$right;
        }
    }
}


function who_is_online($location = '') {
	global $db, $lang_glo, $PHP_PHAOS_USER;
	
	if ($location != '') { 
		$loc = 'location = ' . $location . ' AND ';
	} else { $loc = ''; }
	
	$current_time = time();
	$active_min = $current_time - 300;
	$active_max = $current_time + 300;
	
	$i = 0;
	$result = $db->query("SELECT username,name FROM phaos_characters WHERE $loc regen_time >= '$active_min' AND regen_time <= '$active_max' AND username != '$PHP_PHAOS_USER' AND username NOT LIKE  'phaos_%' ORDER by name ASC");
	if ($result->num_rows) {
		while (list($username,$name) = $result->fetch_row()) { 
			echo "<span class='green'>| </span><a href='player_info.php?player_name=". $username . "'>" . $name .  "</a><span class='green'> |</span>";
			$i++;
		}
	} else {
		echo "<span class='green'>| </span>".$lang_glo["n_else"]."<span class='green'> |</span>";
	}
}

function who_is_offline($location = '') {
	global $db, $lang_glo, $PHP_PHAOS_USER;
	
	if ($location != '') { 
		$loc = 'location = ' . $location . ' AND ';
	}
	
	$current_time = time();
	$active_min = $current_time - 300;
	$active_max = $current_time + 300;
	
	$i = 0;
	$result = $db->query("SELECT username,name FROM phaos_characters WHERE $loc regen_time < '$active_min' AND username != '$PHP_PHAOS_USER' AND username NOT LIKE 'phaos_%' ORDER by name ASC");
	if ($result->num_rows) {
		while (list($username, $name) = $result->fetch_row()) { 
			echo "<span class='green'>| </span><a href='player_info.php?player_name=". $username . "'>" . $name .  "</a><span class='green'> |</span>";
			$i++;
		}
	} else {
		echo "<span class='green'>| </span>".$lang_glo["n_else"]."<span class='green'> |</span>";
	}
}

// Set Global var for all connecting squares (used in class_character functions for npc movement)
$npc_dir_map = array(
    1=>"above_left",
    2=>"above",
    3=>"above_right",
    4=>"leftside",
    5=>"rightside",
    6=>"below_left",
    7=>"below",
    8=>"below_right"
);

// ABOVE FUNCTIONS ARE CONFIRMED, and have been patched by kaspir in v2.0 //



/*
 *  Get random elements from an associative array (from www.php.net)
 *  shuffle() does not maintain key associations. This is how to shuffle an associative array without losing key associations:
 */
function array_rand_assoc_array($shuffle_me,$num_req,$byreference=true) {
   $randomized_keys = array_rand($shuffle_me, $num_req);
   foreach($randomized_keys as $current_key) {
       if ($byreference) {
           $shuffled_me[$current_key] = &$shuffle_me[$current_key];
       } else {
           $shuffled_me[$current_key] = $shuffle_me[$current_key];
       }
   }
   return $shuffled_me;
}

// Return the plural of a (monster)name
function namePlural($name,$number = 1) {
    return "".$name.($number > 1?'s':'');
}

// Create a list text from an array for use in SQL: .. where id IN $list ..
function makeList($ids) {
    $idlist = "(-1";
    foreach($ids as $id) {
        $iid= intval($id);
        if ($iid || $id===0 || $id==="0") {
            $idlist.= ",$iid";
        }
    }
    $idlist.= ')';
    return $idlist;
}

// Project an list of associative arrays onto a list containing just one field
function project($list,$fieldname) {
    if (!is_array($list)) {
        if ($list){
            die("bad list in project($list,'$fieldname')");
        }
        return;
    }
    $single = array();
    foreach($list as $key=>$array) {
        $single[$key] = $array[$fieldname];
    }
    return $single;
}

// Show a database access error
function showError($FILE=__FILE__,$LINE=__LINE__,$FUNCTION=__FUNCTION__,$sql="") {
    echo "<B> $FILE #$LINE $FUNCTION(): Error ".mysqli_errno()." :</B> ".mysqli_error().($sql?" <br> SQL:$sql":"");
    flush();
}

// Fetch all results for an SQL query into an associative array
function fetch_all($query,$FILE=__FILE__,$LINE=__LINE__,$FUNCTION=__FUNCTION__) {
	global $db;
	
	$result = $db->query($query);
    if (!$result) {
        showError($FILE,$LINE,$FUNCTION,$query);
        return array();
    }

    $list = array();
	if ($result->num_rows != 0) {
		while ($row = $result->fetch_assoc()) {
			$list[] =  $row;
		}
	}
	
    return $list;
}

// Fetch one result for an SQL query into an associative array
function fetch_first($query,$FILE=__FILE__,$LINE=__LINE__,$FUNCTION=__FUNCTION__) {
	global $db;
	
    $limit = preg_match("/LIMIT (\d*,){0,1}\d*$/",$query)?"":" LIMIT 1";

	$result = $db->query($query.$limit);
    if (!$result) {
        showError($FILE,$LINE,$FUNCTION,$query);
        return array();
    }

    $list = array();
	if ($result->num_rows != 0) {
		$row = $result->fetch_assoc();
        return $row;
	}

    return null;
}

// Fetch one value for an SQL query, such as a count(*)
function fetch_value($query,$FILE=__FILE__,$LINE=__LINE__,$FUNCTION=__FUNCTION__) {
	global $db;
	
    $limit = preg_match("/LIMIT (\d*,){0,1}\d*$/",$query)?"":" LIMIT 1";

	$result = $db->query($query.$limit);
    if (!$result) {
        showError($FILE,$LINE,$FUNCTION,$query);
        return array();
    }

    $list = array();
	if ($result->num_rows != 0) {
		$row = $result->fetch_row();
        return @$row[0];
	}
    return null;
}

// args: map location or array of locations
// returns: list of npc/monster id numbers at that location (not including active player)
// the characterrole argument can be used to specify a subset of characters
//FIXME: there should be a separate field in the database for characterrole, since right now it abuses the username
function whos_here($locationids,$characterrole='phaos_npc') {
	global $db; //, $PHP_PHAOS_CHARID
	
    if (is_array($locationids)) {
        $locationset = makeList($locationids);
        $locwhere = "location IN $locationset";
    } else {
        $locationid = intval($locationids);
        $locwhere = "location=$locationid";
    }
	//$player_class = $db->query("SELECT * FROM phaos_classes WHERE name LIKE '%'"); //kaspirs, add a query soon.
    $order = 'ORDER by level DESC, wisdom DESC';
    $query = "SELECT id FROM phaos_characters WHERE $locwhere AND username LIKE '$characterrole' $order";
    $list = project(fetch_all($query), 'id');

	return $list;
}

// speed up selecting random rows by preselecting a subset for select by rand()
// this will only work fairly if the ids are distributed about evenly
// very low and very high ids will be selected less often
// this function is redundant for databases that support selecting a random row quickly
//FIXME: use this function more
function speedup_random_row($id,$table,$where) {
    $cia = fetch_first("SELECT count($id) AS c, min($id) AS i, max($id) AS a FROM $table WHERE $where",__FILE__,__LINE__,__FUNCTION__);
    $count = $cia['c'];
    $min = $cia['i'];
    $max = $cia['a'];
    if ($count > 0) {
        $range = $max-$min;
        $m = rand($min,$max);
        $root = ceil( sqrt($range) );
        $a = $m - $root;
        $b = $m + $root;
        $whereid = " $a<=$id AND $id<=$b ";
        $gotsome = fetch_value("SELECT $id FROM $table WHERE $where AND $whereid",__FILE__,__LINE__,__FUNCTION__);
        if ($gotsome) {
            return $whereid;
        } else { //play it safe
            return " 1=1 ";
        }
    } else {
        return " 1=1 ";
    }
}

/*
 * Check whether a string is a valid item type
 */
//these item type names are used as fields in the database
$validEquipFieldItems = array("armor","weapon","boots","shield","helm","gloves");
//these are valid items in the inventory
$validInvItems = array_merge($validEquipFieldItems,array("potion","spell_items"));
//these items are valid random drops
$validDropItems = array_merge($validInvItems,array("gold"));
//this is a fucking mess to need this trick at all
$tableForField = array(
    "armor"		=>"armor",
    "weapon"	=>"weapons",
    "boots"		=>"boots",
    "shield"	=>"shields",
    "helm"		=>"helmets",
    "gloves"	=>"gloves",
    "potion"	=>"potion",
    "spell_items"=>"spells_items",
    "gold"		=>"gold"
);

foreach ($tableForField as $field=>$table) {
    $fieldForTable[$table] = $field;
}

function isEqItemType($item_type) {
    global $validEquipFieldItems;
    return in_array($item_type,$validEquipFieldItems);
}

function isItemType($item_type) {
    global $validInvItems;
    return in_array($item_type,$validInvItems);
}


/*
 * This multiplies rand() such that there will be few, but some high numbers
 * FIXME: fit this into one formula solving
 * p(x<=exp(n*ln($growth)))=exp(n*ln($probability))
 * TODO: write testing function
 */
function powrand($growth,$probability,$n) {
/*  left here for your entertainment the fun magic structural constant 1/137 */
    $powar = 1.0;
    $power = 1.0;
    while($n-->0 && rand(1,137) <= 137 * $probability) {
        $powar = $power;
        $power *= $growth;
        if (DEBUG) { $GLOBALS['debugmsgs'][] = "**DEBUG: powrand($growth,$probability,$n) = ($powar,$power)"; }
    }
    $fudge = rand(0,136) / 137;
    return rand( (int)(137.0 * ($powar + $fudge)), (int)(137.0 * ($power + $fudge)) ) / 137.0;
}

/* Display a hidden field, but recurse if the field is an array */
function hiddenFields($name,$value) {
    if (is_array($value)) {
        foreach($value as $k=>$v) {
            hiddenFields($name.'['.$k.']',$v);
        } //foreach
    } else {
        ?><input type="hidden" name="<?php echo $name; ?>" value="<?php echo htmlspecialchars($value); ?>"><?php
    }
}

/*
function htmlSelect($name,$options,$selected,$vk=0,$ok=1,$size="",$null="",$defaulttext="------") {
	?>
	<select name="<?php echo $name; ?>" <?php echo $size?"size=\"$size\"":""; ?> onChange="if (this.value!='<?php echo $null; ?>')this.form.submit();">
		<option value="<?php echo $null; ?>"><?php echo $defaulttext; ?></option>
		<?php
		foreach($options as $array){
			$value= $array[$vk];
			$text= $array[$ok];
			?>
		<option value="<?php echo $value; ?>" <?php echo ($selected==$value)?"SELECTED":""; ?> ><?php echo $text; ?></option>
			<?php
		}
		?>
	</select>
	<?php
}
*/

/*
 * Select id from 90001-100000
 *  for use as
 *  arena_fighting_location
 *  item_storage_location
 * FIXME: this is not thread-safe at all without table locking
 * when you add more ranges, you can split the old ranges
 */
function nextLocationIdFromRange($location_type,$file=__FILE__,$line=__LINE__) {
	global $db;
    switch($location_type) {
        case 'arena_fighting_location':
            $min =  90001;
            $max =  95001;
            break;
        case 'item_storage_location':
            $min =  95001;
            $max = 100001;
            break;
        default:
            die("Invalid location range($location_type:$min-$max) requested in call to ".__FUNCTION__."() by ".$file."#".$line);
    }
    $query = "SELECT MAX(id) FROM phaos_locations WHERE id>=$min AND id<$max";
    $id = fetch_value($query);
    if (!$id) {
        //die("No id found(bad sql?) in location range($location_type) requested in call to ".__FUNCTION__."() by ".$file."#".$line);
        $query = "INSERT INTO phaos_locations (id, name, image_path, special, buildings, pass, explore)
					VALUES ($min, 'BEGIN location range($location_type:$min-$max)','images/special.gif',1,0,1,0)";
        $req = $db->query($query);
        if (!$req) { showError(__FILE__,__LINE__,__FUNCTION__); exit;}

        $id = $min;
    }
    ++$id;
    if ($id >= $max) {
            die("No ids left in location range($location_type:$min-$max) requested in call to ".__FUNCTION__."() by ".$file."#".$line."<br> SQL:".$query);
    }
    if (DEBUG) { $GLOBALS['debugmsgs'][] = "**DEBUG: issued id $id as $location_type"; }
	
    return $id;
}


/* Display a hidden field, but recurse if the field is an array */
function urlFields($name,$value) {
    if (is_array($value)) {
        $rval = '';
        foreach($value as $k=>$v){
           $rval .= urlFields($name.'['.$k.']',$v);
        } //foreach
        return $rval;
    } else {
        return htmlentities($name).'='.rawurlencode($value).'&';
    }
}


## NOT USED ANYMORE ##
/* Format Image for use in table cells 
function makeImg($image_path) {
    if (@$image_path) {
        return "<img src=\"".$image_path."\">";
    } else {
        return "&nbsp;";
    }
}
*/
/* this could replace require_once command
function get_page($fileName) {
	if (is_readable($fileName)) {
		include($fileName);
	} else {
		include("404.html");
	}
}
*/