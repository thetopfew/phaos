<?php 

/*################################################################################################
bank_functions.php as been added to WoP, by kaspir. v.1.0BETA. Last updated for v2.0 7/24/17
This file is now included in bank.php along with a new deposit/withdraw table that allows
conversion and transfer of WoP in-game gold to & from another platform/database point system. 
I currently have phpBB forums software (v3.1.9) with Ultimate Points Extention(v1.1.5) installed.
- NOTE update: 7/24/17, still working on phpBB 3.2.0 and point ext v 1.1.8 & now on PHP7.1
##################################################################################################*/

// Retreive users forum points
function getpoints() {
	global $phpbb_db, $phpbb_dbprefix, $PHP_PHAOS_USER, $lang_bank;

	$get_user = $phpbb_db->query("SELECT user_points FROM ".$phpbb_dbprefix."_users WHERE username='$PHP_PHAOS_USER'");
	if ($row = $get_user->fetch_assoc()) {
		$gold_points = $row["user_points"];
		return $gold_points;	
	} else {
		die("$lang_bank[die_nouser]");
	} 
}		

// Exchange forum points for more game gold
function xfer_deposit($amount) {
	global $db, $phpbb_db, $phpbb_dbprefix, $PHP_PHAOS_USER, $lang_bank;

	$get_user = $phpbb_db->query("SELECT user_points FROM ".$phpbb_dbprefix."_users WHERE username='$PHP_PHAOS_USER'");
	if ($row = $get_user->fetch_assoc()) { 
		$gold_points = $row["user_points"];
		
		$ratio = 0.25; // Adjust current deposit ratio here
		$fixed_amt = $amount * $ratio;
				
		if ($gold_points >= $fixed_amt) {
			// Update both tables
			$phpbb_db->query("UPDATE ".$phpbb_dbprefix."_users SET user_points = (user_points - $fixed_amt) WHERE username='$PHP_PHAOS_USER'");
			$db->query("UPDATE phaos_characters SET gold = (gold + $amount) WHERE username='$PHP_PHAOS_USER'");
			
			echo "<div class='center'><h3 class='b msgbox'>$lang_bank[x_sucss]</h3></div>";
		} else { 
			echo "<div class='center'><h3 class='b red msgbox'>$lang_bank[x_fail]</h3></div>";
		}
	} else {
		die("$lang_bank[die_nouser]");
	}	
}		

// Exchange game gold for forum points
function xfer_withdraw($amount) {
	global $db, $phpbb_db, $phpbb_dbprefix, $PHP_PHAOS_USER, $lang_gold, $lang_bank;
	
	$get_gold = $db->query("SELECT gold FROM phaos_characters WHERE username='$PHP_PHAOS_USER'");
	if ($row = $get_gold->fetch_assoc()) { 
		$game_gold = $row["gold"];
		
		if ($amount <= $game_gold) {
			$db->query("UPDATE phaos_characters SET gold = (gold - $amount) WHERE username='$PHP_PHAOS_USER'");
			
			$get_user = $phpbb_db->query("SELECT user_points FROM ".$phpbb_dbprefix."_users WHERE username='$PHP_PHAOS_USER'");
			if ($row = $get_user->fetch_assoc()) {
				$gold_points = $row["user_points"];
				
				// Adjust withdraw ratio here
				$ratio = 0.10;
				$fixed_amt = $amount * $ratio;

				$phpbb_db->query("UPDATE ".$phpbb_dbprefix."_users SET user_points = (user_points + $fixed_amt) WHERE username='$PHP_PHAOS_USER'");
			} else {
				die("$lang_bank[die_nouser]");
			}
			echo "<div class='center'><h3 class='b msgbox'>$lang_bank[x_sucss]</h3></div>";
		} else {
			echo "<div class='center'><h3 class='b red msgbox'></h3>$lang_bank[t_fail]</div>";
		}
	} else {
		die("$lang_bank[die_nochar]");
	}
}			
