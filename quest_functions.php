<?php
function candoquest($char, $quest)	{
	global $db;
	//global $PHP_PHAOS_USER;
	
	$cando = 1;
	
	$res = $db->query("SELECT xp FROM phaos_characters WHERE id=$char LIMIT 1");
	//$charrow = @mysqli_fetch_array($res, MYSQLI_BOTH);
	$charrow = $res->fetch_assoc();
	
	$res = $db->query("SELECT * FROM phaos_quests WHERE questid=$quest LIMIT 1");
	$questrow = @mysqli_fetch_array($res, MYSQLI_BOTH);
	
	$res = $db->query("SELECT * FROM phaos_questhunters WHERE charid=$char AND questid=$quest LIMIT 1");
	$hunterrow = @mysqli_fetch_array($res, MYSQLI_BOTH);
	
	if ($hunterrow["complete"] == 1) {$cando =- 1;} //no if have already completed
	if ($questrow["reqexp"] >= $charrow["xp"]) {$cando =- 2;} //no if not enough req exp
	if ($questrow["hunters"] >= $questrow["maxhunters"]) {$cando =- 3;} //no if max hunters is reached
	if (($hunterrow["questid"] == $questrow["questid"]) && ($hunterrow["complete"] == 0)) {$cando =- 4;} //no if incomplete
	
	return $cando;
}

### QUEST SECTION ###
function addquest($char, $quest) {
	global $db;
	//global $PHP_PHAOS_USER;
	
	$res = $db->query("SELECT * FROM phaos_characters WHERE id=$char LIMIT 1");
	$charrow = @mysqli_fetch_array($res, MYSQLI_BOTH);
	
	$res = $db->query("SELECT * FROM phaos_quests WHERE questid=$quest LIMIT 1");
	$questrow = @mysqli_fetch_array($res, MYSQLI_BOTH);
	
	$query = "INSERT INTO phaos_questhunters (charid, questid, username, starttime, startexp, startgold, startkills)
			  VALUES
			 ($char, $quest, '".$charrow["username"]."', '".date("Y-m-d H:i:s")."', '".$charrow["xp"]."', '".$charrow["gold"]."', '".$charrow["kills"]."')";
	$db->query($query);
	
    $res = $db->query("UPDATE phaos_quests SET hunters=hunters+1 WHERE questid=$quest");
	
	$echothis = print("<big><b>".$questrow["tracemsg"]."</b></big>\n");
    return $echothis;
}

function questkillcount() { //not used yet. kaspir
	global $db;
	
	$res = $db->query("SELECT * FROM phaos_quests WHERE questid=$quest LIMIT 1");
	$questrow = @mysqli_fetch_array($res, MYSQLI_BOTH);
}

function not_complete() {
	$echothis = "<big><b>You have not finished this quest yet.</b></big>";
    print($echothis);
}

function completequest($char, $quest) {
	global $db;

	$res = $db->query("SELECT * FROM phaos_characters WHERE id=$char LIMIT 1");
	$charrow = @mysqli_fetch_array($res);
	
	$res = $db->query("SELECT * FROM phaos_quests WHERE questid=$quest LIMIT 1");
	$questrow = @mysqli_fetch_array($res);

	$sql = "UPDATE phaos_questhunters 
			SET endtime='".date("Y-m-d H:i:s")."', endexp='".$charrow["xp"]."', endgold='".$charrow["gold"]."', endkills='".$charrow["kills"]."', complete=1 
			WHERE charid=$char AND questid=$quest 
			LIMIT 1";
	$res = $db->query($sql);
	
	$res = $db->query("UPDATE phaos_quests SET hunters=hunters-1, numcompleted=numcompleted+1 WHERE questid=$quest LIMIT 1");

	$res = $db->query("UPDATE phaos_characters SET gold=gold+'".$questrow["rewardgold"]."', xp=xp+'".$questrow["rewardwexp"]."' WHERE id=$char LIMIT 1");

	$echothis = print ("<big><b>".$questrow["completemsg"]."</b></big>");
    return ($echothis);
}

function checkquest($char, $quest) {
	global $db;
	//global $PHP_PHAOS_USER;
	
	$res = $db->query("SELECT * FROM phaos_characters WHERE id=$char LIMIT 1");
	$charrow = @mysqli_fetch_array($res);
	
	$res = $db->query("SELECT * FROM phaos_quests WHERE questid=$quest LIMIT 1");
	$questrow = @mysqli_fetch_array($res);
	
	if ($quest == 1) {
		$result = $db->query("SELECT id FROM phaos_char_inventory WHERE username='".$charrow["username"]."' AND item_id='".$questrow["haveitemid"]."' AND type='".$questrow["haveitemtype"]."' LIMIT 1");		
		if (mysqli_fetch_row($result)) {
			$sql = "DELETE FROM phaos_char_inventory 
					WHERE username='".$charrow["username"]."' 
					AND item_id='".$questrow["haveitemid"]."' 
					AND type='".$questrow["haveitemtype"]."' 
					LIMIT 1";
			$res = $db->query($sql);
			
			completequest($char, $quest);
			
		} else {
			return not_complete();
		}
	}
	if ($quest == 2) {
		$echothis = print ("<br><b>WHY IS THIS SHOWING</b>\n");
		return $echothis;
	}
	if ($quest == 3) {
		$result_a = $db->query("SELECT 1 FROM phaos_char_inventory WHERE username='".$charrow["username"]."' AND item_id='".$questrow["haveitemid"]."' AND type='".$questrow["haveitemtype"]."' LIMIT 1");		
		$result_b = $db->query("SELECT 1 FROM phaos_char_inventory WHERE username='".$charrow["username"]."' AND item_id='".$questrow["paygold"]."' AND type='".$questrow["haveitemtype"]."' LIMIT 1");
		if (mysqli_fetch_row($result_a)) {
			$sql = "DELETE FROM phaos_char_inventory 
					WHERE username='".$charrow["username"]."' 
					AND item_id='".$questrow["haveitemid"]."' 
					AND type='".$questrow["haveitemtype"]."' 
					LIMIT 1";
			$res = $db->query($sql);

			completequest($char, $quest);
		}			
		elseif (mysqli_fetch_row($result_b)) {
			$sql = "DELETE FROM phaos_char_inventory 
					WHERE username='".$charrow["username"]."' 
					AND item_id='".$questrow["paygold"]."' 
					AND type='".$questrow["haveitemtype"]."' 
					LIMIT 1";
			$res = $db->query($sql);

			completequest($char, $quest);
		} else {
			return not_complete();
		}
	}
}
