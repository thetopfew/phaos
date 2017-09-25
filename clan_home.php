<?php
require_once "header.php";
require_once "class_character.php";

// If no character created
if ($character->location == "") {
	jsChangeLocation("create_character.php", 0);
}
// SECURE: Check URL if = a valid clanname for user, redirect if not
if ($clan == "" OR $clan != get_clanname($character->name)) {
	echo "<div class='center'><h3 class='center red msgbox'>$lang_err[clan_page]</h3></div>";
	jsChangeLocation("travel.php", 2);
}
// Still here? Get all clan info at once and still call by it's returned var
list($clanid,$clanname,$clanleader,$clanleader_1,$clanbanner,$clansig,$clanlocation,$clanslogan,$clancashbox,$clanmembers,$clancreatedate,$clanrank_1,$clanrank_2,
	$clanrank_3,$clanrank_4,$clanrank_5,$clanrank_6,$clanrank_7,$clanrank_8,$clanrank_9,$clanrank_10,$clan_sig) = getall_claninfo($clan);
?>


<div class="center">
	<?php 
	if ($clan_sig) {
		echo "<img id='clanlogo' src='$clan_sig'>";
	}
	?>
	<img src="lang/<? echo $lang;?>_images/clan_home.png">
	<?php 
	if ($clan_sig) {
		echo "<img id='clanlogo' src='$clan_sig'>";
	}
	?>
</div>

<?php
// Are you depositing gold?
if (isset($_POST['n_gold'])) {
	$gold = $character->gold;
	
	// Did you try a negative number?
	if ($givegold_n < 0) {
		echo "<div class='center'><h3 class='b red msgbox'>".$lang_clan["no_neg_g"]."</h3></div>";
		jsChangeLocation("clan_home.php?clan=$clan", 2);
	} else {
		// Process deposit
		if ($givegold_n <= $gold) {
			echo "<div class='center'><h3 class='b msgbox'><span class='gold'>$givegold_n</span> ".$lang_clan["gold_tr"]."</h3></div>";

			$new_clancashbox = $clancashbox + $givegold_n;
			$ur_new_gold = $gold - $givegold_n;
	
			$db->query("UPDATE phaos_clan_admin SET clancashbox='$new_clancashbox' WHERE clanname='$clanname'");
			$db->query("UPDATE phaos_characters SET gold='$ur_new_gold' WHERE name='$character->name'");
			$db->query("UPDATE phaos_clan_in SET givegold=givegold+$givegold_n WHERE clanmember='$character->name'");
			jsChangeLocation("clan_home.php?clan=$clan", 2);
		} else {
			// OR, error you don't have enough gold.
			echo "<div class='center'><h3 class='b red msgbox'>".$lang_clan["not_hav"].".</h3></div>";
			jsChangeLocation("clan_home.php?clan=$clan", 2);
		}
	}
}
?>
<div class="fullsize">
	<h2 class="center bgcolor"><?php echo $clanname;?></h2>
	<?php
	if ($clanbanner) {
		echo "<img id='clanbanner' src='$clanbanner'>";
	}
	?>
	<table class="b center fullsize" border=2 cellpadding=0 cellspacing=0>
		<tr>
			<td><?php if ($clanslogan == "") { echo $lang_clan["our_slog"]; }?></td>
		</tr>
		<tr>
			<td style='word-wrap:break-word;'><?php if ($clanslogan != "") { echo $clanslogan; }?></td>
		</tr>
	</table>
	<div class="bgcolor">
		<form method="post" action="clan_home.php?clan=<?php echo $clan;?>&gold=<?php echo $gold;?>&clancashbox=<?php echo $clancashbox;?>">
			<span class="big b">
				&nbsp;<?php echo "$lang_clan[g_reserve] <span class='gold'>$clancashbox</span> <img class='sideicon' src='images/icons/gold_side.gif'>";?>
			</span>
			<input type="text" name="givegold_n" size="4">
			<input class="button" type="submit" value="<?php echo $lang_clan["dep_golds"];?>" name="n_gold">
		
		<?php
		// Display leader page button OR Leave clan button?
		if (ru_clanleader($character->name)) {
			?><input class="right button" type="button" value="<?php echo $lang_clan["leaderarea"];?>" onClick="location='clan_leader.php?clan=<?php echo $clanname;?>'"><?php
		} else {
			?><input class="right button" type="button" value="<?php echo $lang_clan["clk_leave"];?>" onClick="location='clan_leave.php?clan=<?php echo $clan;?>&clan_user_name=<?php echo $character->name;?>&clanmembers=<?php echo $clanmembers;?>'"><?php
		}
		?>
		</form>
	</div>
		
	
	<?php // Start member charter  -----------------------------> ?>
		<table class="fullsize" cellpadding=5 cellspacing=0 style="border-collapse: collapse">
			<tr>
				<td class="fullsize center" bgcolor="#003300" colspan="5">
					<span class="b big"><?php echo $lang_clan["mem_chart"];?></span>
				</td>
			</tr>
			<tr class="b" bgcolor="#002300" style="border-bottom:solid 1px #4286f4;">
				<td width="35%"><?php echo $lang_clan["mem_name"];?></td>
				<td width="25%"><?php echo $lang_clan["gu_rank"];?></td>
				<td width="15%"><?php echo $lang_clan["gld_depo"];?> <img class="sideicon" src="images/icons/gold_side.gif"></td>
				<td width="15%"><?php echo $lang_clan["gld_rec"];?> <img class="sideicon" src="images/icons/gold_side.gif"></td>
				<td width="20%"><?php echo $lang_clan["enlist_on"];?></td>
			</tr>
		<?php
		// Build list of members & sort by rank
		$res = $db->query("SELECT * FROM phaos_clan_in WHERE clanname = '$clanname' ORDER BY clanrank DESC");
		while ($row = $res->fetch_assoc()) {
			$clanmember = $row["clanmember"];
			$clanindate = $row["clanindate"];
			$givegold = $row["givegold"];
			$recgold = $row["rec_gold"];
			$clanrank = $row["clanrank"];
			
			// Get player_info username
			$get_username = $db->query("SELECT username FROM phaos_characters WHERE name = '$clanmember'");
			$row = $get_username->fetch_assoc();
			$username = $row["username"];

			if ($clanrank == "1"):
				$n_clanrank = $clanrank_1;
			elseif ($clanrank == "2"):
				$n_clanrank = $clanrank_2;
			elseif ($clanrank == "3"):
				$n_clanrank = $clanrank_3;
			elseif ($clanrank == "4"):
				$n_clanrank = $clanrank_4;
			elseif ($clanrank == "5"):
				$n_clanrank = $clanrank_5;
			elseif ($clanrank == "6"):
				$n_clanrank = $clanrank_6;
			elseif ($clanrank == "7"):
				$n_clanrank = $clanrank_7;
			elseif ($clanrank == "8"):
				$n_clanrank = $clanrank_8;
			elseif ($clanrank == "9"):
				$n_clanrank = $clanrank_9;
			elseif ($clanrank == "10"):
				$n_clanrank = $clanrank_10;
			elseif ($clanrank == "98"):
				$n_clanrank = $lang_clan["assist"];
			elseif ($clanrank == "99"):
				$n_clanrank = $lang_clan["leader"];
			endif;
			
			echo "<tr style='border-bottom:solid 1px #4286f4;'>
					<td width='35%' bgcolor='#141414'><a href='player_info.php?player_name=$username'>$clanmember</a></td>
					<td width='25%' bgcolor='#141414'>$n_clanrank</td>
					<td width='15%' bgcolor='#141414'>$givegold</td>
					<td width='15%' bgcolor='#141414'>$recgold</td>
					<td width='20%' bgcolor='#141414'>$clanindate</td>
				</tr>";
		} ?>
	</table>

	<div id="actionbar" class="center fullsize bgcolor">
		<input class="button" type="button" onClick="location='travel.php';this.value='<?php echo $lang_leaving;?>'" value="<?php echo $lang_goback;?>">
	</div>

</div>

<?php require_once "footer.php";
