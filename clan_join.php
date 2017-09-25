<?php
require_once "header.php";

// If no character created, bu-bye!
if ($character->location == "") {
	jsChangeLocation("create_character.php", 0);
}
?>

<div class="center">
	<img src="lang/<? echo $lang;?>_images/clan_join.png">
	<div class="big b bgcolor"><?php echo $lang_clan["lk_fr_gu"];?></div>
	
	<table class="fullsize" border=1 cellpadding=0 cellspacing=0>
		<tr class="b bgcolor">
			<td><?php echo $lang_clan["gname"];?></td>
			<td><?php echo $lang_clan["gmaster"];?></td>
			<td><?php echo $lang_clan["clangold"];?></td>
			<td><?php echo $lang_clan["members"];?></td>
			<td><?php echo $lang_clan["join_g"];?></td>
		</tr>
	
		<?php		
		// Are you in a clan already?
		if (ru_inclan($character->name)) {
			$inclan = "yes";
		}
		
		// Waiting on a leader response?
		if (ru_inclansearch($character->name)) {
			$insearch = "yes";
		}

		// Get & List all clan info
		$res = $db->query("SELECT clanname,clanleader,clancashbox,clan_sig,clanmembers,clancreatedate FROM phaos_clan_admin ORDER BY clanmembers DESC");
		while ($row = $res->fetch_assoc()) {
			$clanname = $row["clanname"];
			$clanleader = $row["clanleader"];
			$clangold = $row["clancashbox"]; //not used yet
			$clan_sig = $row["clan_sig"];
			$clanmembers = $row["clanmembers"];
			$clancreatedate = $row["clancreatedate"];
			?>
			<tr class="center b graybg">
				<td>
					<?php
					if ($clan_sig != "") {
						echo "<img class='left clanicon' src='$clan_sig'>";
					} else { echo " "; }
					echo "<div class='left clanicon'>$clanname</div>
				</td>
				<td><font color='#666699'>$clanleader</font></td>
				<td class='gold'>$clangold</td>
				<td>$clanmembers</td>";
				
				// Process or show errors
				if ($inclan == "yes") {
					echo "<td width='20%'>".$lang_clan["al_in_g"]."</td>";
				} else {
					if ($insearch == "yes") {
						echo "<td width='20%'>".$lang_clan["wait_rply"]."</td>";
					} else {
						echo "<td>
							<form method='post' action='clan_send.php?clanname_ask=".$clanname."'>
								<input class='button' type='submit' value='".$lang_clan["request"]."'>
							</form>
						</td>";
					}
				}
			echo "</tr>
				<tr class='small graybg'>
					<td colspan=5>".$lang_clan["createdon"]." $clancreatedate</td>
				</tr>
				<tr>
					<td colspan='5'> <hr> </td>
				</tr>";
		}
		?>
	</table>
</div>

<?php require_once "footer.php";
