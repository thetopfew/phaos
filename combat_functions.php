<?php
function comb_refsidebar() { # FIXME: Only refreshes on second load.. :/
	?>
	<script>
		parent.side_bar.location.reload();
	</script>
	<?php
}

function setcombatlocation($combatlocation) {
	global $db;
	
	$_SESSION['combatlocation'] = $combatlocation;
	$res = $db->query("SELECT name FROM phaos_locations WHERE id=$_SESSION[combatlocation] LIMIT 1");
	list($_SESSION['locationname']) = $res->fetch_row();
}

function endfight() {
	//$disp_msg = $_SESSION['disp_msg'];
	//$charfrom = @$_SESSION['charfrom'];

	session_unset();
	session_destroy();

	flush();
	ob_flush();
	
	/*
	//if (@$_SESSION['charfrom'] == "arena"){ //Original didn't work, it's kicked you out of town after a win.
	if ($charfrom == "arena"){ //fixed by kaspir	
		return "travel.php";
	} else if ($charfrom == "dungeon"){
		return "travel_dungeon.php?finish";
	} else {
		return "travel.php";
	} */
	return "travel.php";
}

function sayOpponents() {
	global $oppcharacter;
	
	$more = (@$_SESSION['num_of_opps'] > 1)? ' and '.($_SESSION['num_of_opps'] - 1).' more':"";
	return "$oppcharacter->name (Lvl$oppcharacter->level) $more";
}

function root_damage($damage) {
	//$exp = 0.50; //Original
	$exp = 1; //kaspirs
	//return (int)(pow($damage,$exp)+rand(0,99)*0.01); //Original
	return (int)(pow($damage,$exp));
	//return $damage; //kaspir wants everyone equal.
}

/*
 * return a random damage between min and max
 * Adding this kind of randomness should make combat more exciting, while keeping damage in the lower range most of the time.
 * FIXME: This function should somehow make root_damage superfluous, but that requires making npcs more player like
 */
function roll_damage($mindamage,$maxdamage) {
	$delta = $maxdamage - $mindamage;
	if ($delta < 1) {
		$delta = 1;
	}
// IF THIS IS LINE 361:  $damage_multiplier= $_SESSION['char_attack']-$_SESSION['opp_defence'];
// EX: your weap has 3-10damage, you roll char_attack=5; you're opponent rolls opp_def=7; thus this = -2 on the dam multiplier
// making the damg_to_opp = -55 !!  Now you only hit for 1, since it's negative.
	$damage = floor($mindamage + exp(rand(0,floor(137.0 * log($delta))) / 137.0) + rand(0,99) * 0.01);
	//if (DEBUG) { $_SESSION['disp_msg'][] = "**DEBUG: possible damage between $mindamage - $maxdamage = $damage"; }
	if (DEBUG) { $GLOBALS['debugmsgs'][] = "**DEBUG: possible damage between $mindamage - $maxdamage = $damage"; }

	return $damage;
}