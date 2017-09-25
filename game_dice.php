<?php
require_once "header.php";
require_once "class_character.php";

$character = new character($PHP_PHAOS_CHARID);
char_intown($character->location); //check for inn?

$gold_o = $character->gold;
$stamina_o = $character->stamina_points;
$dexterity_o = $character->dexterity;

if (!isset($jackpot)) {
	$jackpot = rand(100,300);
}

// Don't allow game if zero gp
if ($gold_o <= 0) {
	echo "<h3 class='center b red'>".$lang_game1["not_enough"]."</h3>";
	echo "<a href='inn.php'>".$lang_clan["back"]."</a>";
	exit;
}

if ($stamina_o <= 0) {
	echo "<h3 class='center b red'>".$lang_game1["2_tired"]."</h3>";
	echo "<a href='inn.php'>".$lang_clan["back"]."</a>";
	exit;
}

//if ($jackpot > $gold_o) {
//$jackpot = "";

//$rollgo= "";

function xrand($lo,$hi,$skill) {
	global $db, $stamina_o, $dexterity_o, $gold_o;
	
    $x = rand($lo,$hi);
    if ((1 + $skill) * $dexterity_o * 83 > $gold_o-$stamina_o) {
        $y = rand($lo,$hi);
        if ($y > $x) {
            $x = $y;
        }
    }
    return $x;
}

// Limit Jackpot
if ($jackpot < "1") {$jackpot = "";}
if ($jackpot > "100") {$jackpot = "100";}

$roller = $lang_game1["rollll"];
if ((@$_REQUEST['roll']) && $jackpot > 0) {
    $rollgo = "yes";
    srand ((double)microtime() * 1000000);
    $dice_opp_1 = rand(1,6);
    $dice_opp_2 = rand(1,6);
    $dice_opp_3 = rand(1,6);
    $dice_opp_4 = rand(1,6);
    $dice_opp_5 = rand(1,6);
    $dice_opp_all = $dice_opp_1 + $dice_opp_2 + $dice_opp_3 + $dice_opp_4 + $dice_opp_5;
    $dice_user_1 = rand(1,6);
    $dice_user_2 = rand(1,6);
    $dice_user_3 = $character->race=='Gnome'? xrand(1,6,2) : rand(1,6);
    $dice_user_4 = rand(1,6);
    $dice_user_5 = rand(1,6);
    $dice_user_all = $dice_user_1 + $dice_user_2 + $dice_user_3 + $dice_user_4 + $dice_user_5;
    	$stamina_reduce = $stamina_o - 10;
    	mysqli_query($db, "UPDATE phaos_characters SET stamina = '$stamina_reduce' WHERE username = '$PHP_PHAOS_USER'");
} else { // Set default rolls just in case.
    $rollgo = "no";
    $dice_user_1 = 1; $dice_user_2 = 1; $dice_user_3 = 1; $dice_user_4 = 1; $dice_user_5 = 1;
    $dice_user_all = 6;
    $dice_opp_1 = 1; $dice_opp_2 = 1; $dice_opp_3 = 1; $dice_opp_4 = 1; $dice_opp_5 = 1;
    $dice_opp_all= 6;
}

?>
<div class="center">
    <img src="./lang/<?php echo $lang;?>_images/inn.png">
	<br>
	<table align=center border=1 cellpadding=0 cellspacing=3 background="./images/body_background.jpg" width="70%">
		<tr>
			<td class="bgcolor">
				<form method="post" action="game_dice.php">
					<div>
						<table border=1 cellpadding=0 cellspacing=0 id="AutoNumber2" width="100%">
							<tr>
								<td class="center fullsize bgcolor" colspan=6>
									<?php echo $lang_game1["host_roll"];?>
								</td>
							</tr>
							<tr>
								<td class="center fullsize bgcolor" colspan=6>
									<?php echo $dice_opp_all;?>
								</td>
							</tr>
							<tr>
								<td width="20%" class="center bgcolor">
									<img border="0" src="./images/games/000<?php echo $dice_opp_1;?>.gif" width="32" height="32">
								</td>
								<td width="20%" class="center bgcolor">
									<img border="0" src="./images/games/000<?php echo $dice_opp_2;?>.gif" width="32" height="32">
								</td>
								<td width="20%" class="center bgcolor" colspan=2>
									<img border="0" src="./images/games/000<?php echo $dice_opp_3;?>.gif" width="32" height="32">
								</td>
								<td width="20%" class="center bgcolor">
									<img border="0" src="./images/games/000<?php echo $dice_opp_4;?>.gif" width="32" height="32">
								</td>
								<td width="20%" class="center bgcolor">
									<img border="0" src="./images/games/000<?php echo $dice_opp_5;?>.gif" width="32" height="32">
								</td>
							</tr>
							<tr>
								<td width="20%" class="center bgcolor">
									<?php echo $dice_opp_1;?>
									</td>
								<td width="20%" class="center bgcolor">
									<?php echo $dice_opp_2;?>
								</td>
								<td width="20%" class="center bgcolor" colspan=2>
									<?php echo $dice_opp_3;?>
								</td>
								<td width="20%" class="center bgcolor">
									<?php echo $dice_opp_4;?>
								</td>
								<td width="20%" class="center bgcolor">
									<?php echo $dice_opp_5;?>
								</td>
							</tr>
							<tr>
								<td class="center fullsize bgcolor" colspan=6>
									<?php echo $lang_game1["ur_roll"];?>
								</td>
							</tr>
							<tr>
								<td class="center fullsize bgcolor" colspan=6>
									<?php echo $dice_user_all;?>
								</td>
							</tr>
							<tr>
								<td class="center halfsize bgcolor" colspan=3>
									<?php echo $lang_added["ad_u-bet"];?>
								</td>
								<td class="center halfsize bgcolor" colspan=3>
									<input type="text" name="jackpot" size="5" value="<?php echo $jackpot;?>"><?php echo $lang_gold;?>
								</td>
							</tr>
							<tr>
								<td width="20%" class="center bgcolor">
									<img border="0" src="./images/games/000<?php echo $dice_user_1;?>.gif" width="32" height="32">
								</td>
								<td width="20%" class="center bgcolor">
									<img border="0" src="./images/games/000<?php echo $dice_user_2;?>.gif" width="32" height="32">
								</td>
								<td width="20%" class="center bgcolor" colspan=2>
									<img border="0" src="./images/games/000<?php echo $dice_user_3;?>.gif" width="32" height="32">
								</td>
								<td width="20%" class="center bgcolor">
									<img border="0" src="./images/games/000<?php echo $dice_user_4;?>.gif" width="32" height="32">
								</td>
								<td width="20%" class="center bgcolor">
									<img border="0" src="./images/games/000<?php echo $dice_user_5;?>.gif" width="32" height="32">
								</td>
							</tr>
							<tr>
								<td width="20%" class="center bgcolor">
									<?php echo $dice_user_1;?>
								</td>
								<td width="20%" class="center bgcolor">
									<?php echo $dice_user_2;?>
								</td>
								<td width="20%" class="center bgcolor" colspan=2>
									<?php echo $dice_user_3;?>
								</td>
								<td width="20%" class="center bgcolor">
									<?php echo $dice_user_4;?>
								</td>
								<td width="20%" class="center bgcolor">
									<?php echo $dice_user_5;?>
								</td>
							</tr>
							<?php
							if ($rollgo == "yes" ) {
								if ($dice_user_all > $dice_opp_all) {
									// Win
									$wingold = $jackpot;

									$result = $db->query("SELECT * FROM phaos_characters WHERE username = '$PHP_PHAOS_USER'");
									if ($row = $result->fetch_assoc()) {
										$gold_o = $row["gold"];
									}
									$gold_o = $gold_o + $wingold;

									$db->query("UPDATE phaos_characters SET gold = '$gold_o' WHERE username = '$PHP_PHAOS_USER'");

									comb_refsidebar(); // refreshes on 2nd click
									
									echo 	"<tr>
												<td width='100%' bgcolor='#00a000' colspan='6' align='center'>
													<font color='#000000' size='4'><b>".$lang_game1["u_won"]."!!</b></font>
												</td>
											</tr>";
								}

								if ($dice_user_all < $dice_opp_all) {
									// Lose
									$result = $db->query("SELECT * FROM phaos_characters WHERE username = '$PHP_PHAOS_USER'");
									if ($row = $result->fetch_assoc()) {
										$gold_o = $row["gold"];
									}
									$gold_o = $gold_o - $jackpot;

									$db->query("UPDATE phaos_characters SET gold = '$gold_o' WHERE username = '$PHP_PHAOS_USER'");

									echo 	"<script>
												javascript:parent.side_bar.location.reload();
											</script>";
									echo 	"<tr>
												<td width='100%' bgcolor='#a00000' colspan='6' align='center'>
													<font color='#000000' size='4'><b>".$lang_game1["u_lost"].".</b></fot>
												</td>
											</tr>";
								}
								// Added by dragzone---
								if ( $dice_user_all == $dice_opp_all) {
									echo 	"<tr>
												<td width='100%' bgcolor='#FF7F2A' colspan='6' align='center'>
													<font color='#000000' size='4'><b>".$lang_added["ad_g2-drew"].".</b></font>
												</td>
											</tr>";
								}
							} else {
								// Pass
								echo 	"<tr>
											<td width='100%' bgcolor='#008000' colspan='6' align='center'>
												<font color='#000000' size='4'><b>...</b></font>
											</td>
										</tr>";
							}
							?>
						</table>
					</div>
					<input class="button" type="submit" value="<?php echo $lang_game1["roll"];?>" name="roll">
				</form>
			</td>
		</tr>
	</table>
</div>

<div class="center">
	<form style="padding:10px;"><input class="button" type="button" value="<?php echo $lang_backtown;?>" onClick="location='town.php';this.value='<?php echo $lang_leaving;?>'"></form>
</div>
<?php
require_once "footer.php";
