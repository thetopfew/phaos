<?php
include "header.php";

// If no character created
if ($character->location == "") {
	jsChangeLocation("create_character.php", 0);
}

// Boot if not you are not a leader or coleader
if (ru_clanleader($character->name) == false) {
	echo "<div class='center'><h3 class='red msgbox'>$lang_err[no_leader]</h3></div>";
	jsChangeLocation("travel.php", 2);
}

// SECURE: Check URL if = a valid clanname for user, redirect if not
if ($clan == "" OR $clan != get_clanname($character->name)) {
	echo "<div class='center'><h3 class='red msgbox'>$lang_err[no_lead_p]</h3></div>";
	jsChangeLocation("travel.php", 2);
}

// Still here? Get all clan info at once and still call by it's returned var
list($clanid,$clanname,$clanleader,$clanleader_1,$clanbanner,$clansig,$clanlocation,$clanslogan,$clancashbox,$clanmembers,$clancreatedate,$clanrank_1,$clanrank_2,
	$clanrank_3,$clanrank_4,$clanrank_5,$clanrank_6,$clanrank_7,$clanrank_8,$clanrank_9,$clanrank_10,$clan_sig) = getall_claninfo($clan);
	// Set for new assignment of ranks dropdown
	$guildrank_n[1] = "$clanrank_1";
	$guildrank_n[2] = "$clanrank_2";
	$guildrank_n[3] = "$clanrank_3";
	$guildrank_n[4] = "$clanrank_4";
	$guildrank_n[5] = "$clanrank_5";
	$guildrank_n[6] = "$clanrank_6";
	$guildrank_n[7] = "$clanrank_7";
	$guildrank_n[8] = "$clanrank_8";
	$guildrank_n[9] = "$clanrank_9";
	$guildrank_n[10] = "$clanrank_10";
	
	$showform = "yes";

?>
<div class="center">
	<?php 
	if ($clan_sig) {
		echo "<img id='clanlogo' src='$clan_sig'>";
	}
	?>
	<img src="lang/<?php echo $lang;?>_images/clan_home.png">
	<?php 
	if ($clan_sig) {
		echo "<img id='clanlogo' src='$clan_sig'>";
	}
	?>
</div>
<div class="center">
	<h3 class=""><?php echo $lang_clan["cl_lead_tit"];?></h3>
</div>
<div>
	<?php
	// Did we click to update something?
	//if ($adjustment == $lang_clan["update"] ) {
	if (isset($_POST['adjustment'])) {
		// Was it slogan?
		if ($slogan == "") {
			echo "";
		} else {
			$showform = "no";
			echo "<div class='center'><h3 class='b msgbox'>".$lang_clan["slogan_chg"]."</h3></div>";
			
			$db->query("UPDATE phaos_clan_admin SET clanslogan='$slogan' WHERE id='$clanid'");
			jsChangeLocation("clan_leader.php?clan=$clan", 2);
		}
		
		// Kicking a member?
		if ($kickmember == $lang_clan["none"]) {
			echo "";
		} else {
			$showform = "no";
			$res = $db->query("SELECT clanname,oldname FROM phaos_clan_in WHERE clanmember = '$kickmember'");
			if ($row = $res->fetch_assoc()) {
				$clanname = $row["clanname"];
				$oldname = $row["oldname"];
			}			
			echo "<div class='center'><h3 class='b msgbox'>$oldname".$lang_clan["mem_removed"]."</h3></div>";
				
			$db->query("UPDATE phaos_clan_admin SET clanmembers=clanmembers-1 WHERE id='$clanid'");
			$db->query("DELETE FROM phaos_clan_in WHERE clanmember='$kickmember'");
			$db->query("UPDATE phaos_characters SET name='$oldname' WHERE name='$kickmember'");
			
			jsChangeLocation("clan_leader.php?clan=$clan", 2);
		}
		
		// Assign a new rank to member
		if ($newrank == $lang_clan["none"]) {
			echo "";
		} else {
			$showform = "no";
			
			if ($newrank == $clanleader_1) {
				$db->query("UPDATE phaos_clan_admin SET clanleader_1='' WHERE id='$clanid'");
				$db->query("UPDATE phaos_clan_in SET clanrank='$gonewrank' WHERE clanmember='$newrank'");
				echo "<div class='center'>
					<h3 class='b msgbox'>
						<span class='red'>$newrank</span>".$lang_clan["ass_demote"]."<span class='red'>$guildrank_n[$gonewrank]</span>.
					</h3>
				</div>";
				jsChangeLocation("clan_leader.php?clan=$clan", 2);
			} elseif ($gonewrank == 11) {
				if ($clanleader_1 == "") {
					$db->query("UPDATE phaos_clan_admin SET clanleader_1='$newrank' WHERE id='$clanid'");
					$db->query("UPDATE phaos_clan_in SET clanrank='98' WHERE clanmember='$newrank'");
				} else {
					$db->query("UPDATE phaos_clan_admin SET clanleader_1='$newrank' WHERE id='$clanid'");
					$db->query("UPDATE phaos_clan_in SET clanrank='1' WHERE clanmember='$clanleader_1'");
					$db->query("UPDATE phaos_clan_in SET clanrank='98' WHERE clanmember='$newrank'");
				}
				echo "<div class='center'>
					<h3 class='b msgbox'><span class='red'>$newrank</span> ".$lang_clan["ass_new"]." <span class='red'>".$lang_clan["assist"]."</span>.</h3>
				</div>";
				jsChangeLocation("clan_leader.php?clan=$clan", 2);
			} elseif (!$guildrank_n[$gonewrank]) { 
				echo "<div class='center'><h3 class='b red msgbox'>".$lang_clan["select_rank"]."</h3></div>";
					jsChangeLocation("clan_leader.php?clan=$clan", 3);
			} else {
				echo "<div class='center'>
					<h3 class='b msgbox'><span class='red'>$newrank</span> ".$lang_clan["ass_new"]." <span class='red'>$guildrank_n[$gonewrank]</span>.</h3>
				</div>";			
				
				$db->query("UPDATE phaos_clan_in SET clanrank='$gonewrank' WHERE clanmember='$newrank'");
				jsChangeLocation("clan_leader.php?clan=$clan", 2);
			}
		}
		
		// Give gold to member from reserves
		if ($goldtomember == $lang_clan["none"]) {
			echo "";
		} else {
			$showform = "no";
			
			if ($clancashbox >= $goldto_n AND $goldto_n >= 1) {
				echo "<div class='center'>
					<h3 class='b msgbox'><span class='gold'>$goldto_n</span> ".$lang_clan["giv_gold"]." $goldtomember.</h3>
				</div>";
		
				$res = $db->query("SELECT gold FROM phaos_characters WHERE name = '$goldtomember'");
				if ($row = $res->fetch_assoc()) {
					$gold_o = $row["gold"];
				}

				$gold_o = $gold_o + $goldto_n;
				$clancashbox = $clancashbox - $goldto_n;
				$res = $db->query("SELECT rec_gold FROM phaos_clan_in WHERE clanmember = '$goldtomember'");
				if ($row = $res->fetch_assoc()) {
					$recgold = $row["rec_gold"];
				}
				$recgold = $recgold + $goldto_n;

				$db->query("UPDATE phaos_clan_admin SET clancashbox='$clancashbox' WHERE id='$clanid'");
				$db->query("UPDATE phaos_characters SET gold='$gold_o' WHERE name='$goldtomember'");
				$db->query("UPDATE phaos_clan_in SET rec_gold='$recgold' WHERE clanmember='$goldtomember'");
				
				jsChangeLocation("clan_leader.php?clan=$clan", 2);
			} else {
				echo "<div class='center'>
					<h3 class='b red msgbox'>".$lang_clan["not_en_go"]." $goldto_n ".$lang_gold.".</h3>
				</div>";
				jsChangeLocation("clan_leader.php?clan=$clan", 2);
			}
		}
	}

	// Accept new member request ------------------------------------------------------------------------------------------------------------------->
	//if ($charname_n != "" AND $toaccept == "yes") {
	if (isset($_POST['charname_n']) AND $toaccept == "yes") {
		$showform = "no";
		
		$res = $db->query("SELECT * FROM phaos_clan_in WHERE oldname = '$charname_n'");
		if ($res->num_rows) { // Fail if dupe player in this clan
			echo "<div class='center'><h3 class='b msgbox'> $charname_n ".$lang_clan["err_dupe"]."</h3></div>";
			jsChangeLocation("clan_leader.php?clan=$clan", 2);
		} else {
			$date_h = date('m/d/Y');
			$query = "INSERT INTO phaos_clan_in (clanname,clanmember,oldname,clanindate,givegold,rec_gold,clanrank)
			VALUES
			('$clanname','[$clansig]$charname_n','$charname_n','$date_h','0','0','1')";
			$req = $db->query($query);
			if (!$req) {echo "<B>Error ".mysqli_errno()." :</B> ".mysqli_error().""; exit;}
		}
		echo "<div class='center'><h3 class='b msgbox'> $charname_n ".$lang_clan["acc_new"]."</h3></div>";
		
		// Get username of clanleader for mail
		$res = $db->query("SELECT username FROM phaos_characters WHERE name = '$charname_n' LIMIT 1");
		if ($row = $res->fetch_assoc()) {
			$sendto_usr = $row["username"];
		}
		
		// Send the notice mail
		$subject = $lang_clan["mail_accept"];
		$message = $lang_clan["mail_acc_msg"].$clanname;
		$date = date("m/d/Y h:i");
		$db->query("INSERT INTO phaos_mail (UserTo,UserFrom,Subject,Message,STATUS,SentDate) VALUES ('$sendto_usr','$PHP_PHAOS_USER','$subject','$message','unread','$date')");
		
		// Update everything else.
		$db->query("DELETE FROM phaos_clan_search WHERE charname='$charname_n' LIMIT 1");
		$db->query("UPDATE phaos_clan_admin SET clanmembers=clanmembers+1 WHERE id='$clanid'");
		$db->query("UPDATE phaos_characters SET name='[$clansig]$charname_n' WHERE name='$charname_n'");
		jsChangeLocation("clan_leader.php?clan=$clan", 2);
	}

	// Reject new member request ------------------------------------------------------------------------------------------------------------------->
	//if ($charname_n > "" AND $toaccept != "yes") {
	if (isset($_POST['charname_n']) AND $toaccept == "no") {
		$showform = "no";
		
		echo "<div class='center'><h3 class='b msgbox'>".$lang_clan["user_rej"]."</h3></div>";
		
		// Get username of applicant for mail
		$res = $db->query("SELECT username FROM phaos_characters WHERE name = '$charname_n' LIMIT 1");
		if ($row = $res->fetch_assoc()) {
			$sendto_usr = $row["username"];
		}
		
		// Send the notice mail
		$subject = $lang_clan["mail_reject"];
		$message = $lang_clan["mail_rej_msg"].$clanname;
		$date = date("m/d/Y h:i");
		$db->query("INSERT INTO phaos_mail (UserTo,UserFrom,Subject,Message,STATUS,SentDate) VALUES ('$sendto_usr','$PHP_PHAOS_USER','$subject','$message','unread','$date')");
		
		// Remove from searching
		$db->query("DELETE FROM phaos_clan_search WHERE charname = '$charname_n'");
		jsChangeLocation("clan_leader.php?clan=$clan", 2);
	}
	
	#################################
	// Default Display Screen form ------------------------------------------------------------------------------------------------------------------->
	if ($showform == "yes") {
		// Display player applicants fields, only if any
		$res = $db->query("SELECT charname,description FROM phaos_clan_search WHERE clanname = '$clan'");
		if ($row = $res->fetch_assoc()) {
			do {
				$charname = $row["charname"];
				$description = $row["description"];
				
				// Get username for player_info link
				$res = $db->query("SELECT username FROM phaos_characters WHERE name = '$charname'");
				$row = $res->fetch_assoc();
				$new_username = $row["username"];
				?>
				<table class="fullsize" border=1 cellpadding=5 cellspacing=0>
					<tr class="b bgcolor">
						<td class="quartersize"><?php echo $lang_clan["app_li"];?></td>
						<td class="halfsize"><?php echo $lang_clan["txt_li"];?></td>
						<td class="center" colspan=2><?php echo $lang_clan["t_action"];?></td>
						
					</tr>
					<tr bgcolor="#141414">
						<td class="quartersize"><a href="player_info.php?player_name=<?php echo $new_username;?>"><?php echo $charname;?></a></td>
						<td class="halfsize"><?php echo $description;?></td>
						<td class="center">
							<form method="post" action="clan_leader.php?clan=<?php echo $clan;?>&charname_n=<?php echo $charname;?>&toaccept=yes">
								<input class="button" type="submit" value="<?php echo $lang_clan["acpt"];?>">
							</form>
						</td>
						<td class="center">
							<form method="post" action="clan_leader.php?clan=<?php echo $clan;?>&charname_n=<?php echo $charname;?>&toaccept=no">
								<input class="button" type="submit" value="<?php echo $lang_clan["rej"];?>">
							</form>
						</td>
					</tr>
				</table>
				<br>
				<?php
			} while ($row = $res->fetch_assoc());
		}
		?>
		<div class="big b fullsize bgcolor">
			&nbsp;<?php echo "$clanname, $lang_gold $lang_clan[g_reserve] <span class='gold'>$clancashbox</span> <img class='sideicon' src='images/icons/gold_side.gif'>";?>
		</div>
		<form method="post" action="clan_leader.php?clan=<?php echo $clan;?>">
			<table class="fullsize" border=0 cellpadding=5 cellspacing=0>
				<tr>
					<td width="33%" bgcolor="#141414"><?php echo $lang_clan["giv_gold2"];?></td>
					<td width="33%" bgcolor="#141414">
						<select size="1" name="goldtomember">
							<option selected value="<?php echo $lang_clan["none"];?>"><?php echo $lang_clan["none"];?></option>
							<?php
							// Build dropdown of clan members
							$res = $db->query("SELECT * FROM phaos_clan_in WHERE clanname = '$clanname'");
							while ($row = $res->fetch_assoc()) {
								$clanmember = $row["clanmember"];
								echo "<option value='$clanmember'>$clanmember</option>";
							}
							//  End member dropdown ----->>>
							?>
						</select>&nbsp;&nbsp;&nbsp;&nbsp;
						<?php echo $lang_clan["giv_gold_amt"];?><input type="text" name="goldto_n" size="10">
					</td>
				</tr>
				<tr>
					<td width="33%" bgcolor="#333333"><?php echo $lang_clan["ass_a_gu"];?></td>
					<td width="33%" bgcolor="#333333">
						<select size="1" name="newrank">
							<option selected value="<?php echo $lang_clan["none"];?>"><?php echo $lang_clan["none"];?></option>
							<?php
							$res = $db->query("SELECT clanmember,clanrank FROM phaos_clan_in WHERE clanname = '$clanname'");
							while ($row = $res->fetch_assoc()) {
								$clanmember = $row["clanmember"];
								$clanrank = $row["clanrank"];
								if ($clanrank < "99") {
									echo "<option value='$clanmember'>$clanmember</option>";
								}
							}
							?>
						</select>&nbsp&nbsp&nbsp&nbsp
						<select size="1" name="gonewrank">
							<option selected value="<?php echo $lang_clan["none"];?>"><?php echo $lang_clan["none"];?></option>
							<?php
							echo "<option value='1'>$clanrank_1</option>
								<option value='2'>$clanrank_2</option>
								<option value='3'>$clanrank_3</option>
								<option value='4'>$clanrank_4</option>
								<option value='5'>$clanrank_5</option>
								<option value='6'>$clanrank_6</option>
								<option value='7'>$clanrank_7</option>
								<option value='8'>$clanrank_8</option>
								<option value='9'>$clanrank_9</option>
								<option value='10'>$clanrank_10</option>
								<option class='red' value='11'>".$lang_clan["sel_newass"]."</option>";
								?>
						</select>
					</td>
				</tr>
				<tr>
					<td width="20%" bgcolor="#141414">
						<?php echo $lang_clan["gu_slogan"];?><br>
						<small><?php echo $lang_clan["slo_max_char"];?></small>
					</td>
					<td width="80%" bgcolor="#141414">
						<textarea id="txtArea" wrap="hard" rows="1" cols="40" maxlength="300" name="slogan" placeholder="<?php echo $clanslogan;?>"></textarea>
					</td>
				</tr>
				<tr>
					<td width="20%" bgcolor="#333333" valign="top"><?php echo $lang_clan["kickmember"];?></td>
					<td width="80%" bgcolor="#333333">
						<select size="1" name="kickmember">
							<option selected value="<?php echo $lang_clan["none"];?>"><?php echo $lang_clan["none"];?></option>
							<?php
							$res = $db->query("SELECT clanmember FROM phaos_clan_in WHERE clanname = '$clanname' AND clanrank < 98");
							while ($row = $res->fetch_assoc()) {
								$clanmember = $row["clanmember"];
								echo "<option value='$clanmember'>$clanmember</option>";
							} ?>
						</select>			
					</td>
				</tr>
				<tr>
					<td colspan=2 bgcolor="#141414">
						<input class="right button" type="submit" value="<?php echo $lang_clan["update"];?>" name="adjustment">
					</td>
				</tr>
			</table>
		</form>"; 
		
		<?php // Upload image area & delete guild option ------------------------------------------------------------------------------------------------------------------->?>
		<div class="fullsize b bgcolor">
			<form action="upload.php?clan=<?php echo $clan;?>" method="post" enctype="multipart/form-data" style="padding-left:10px;">
				<?php echo $lang_clan["u_newimg"];?>
				<input type="file" name="fileToUpload" id="fileToUpload">
				<input class="button" type="submit" value="<?php echo $lang_clan["up_m_logo"];?>" name="logo">
				<input class="button" type="submit" value="<?php echo $lang_clan["up_m_banr"];?>" name="banner">
			</form>
		</div> 
		
		<div class="fullsize">
			<div id="button-box" class="inline left">
				<?php echo $lang_clan["edit_rank"];?>
				<form method="post" action="clan_leader_ranks.php?clan=<?php echo $clan;?>">
					<input class="button" type="submit" value="<?php echo $lang_custom;?>">
				</form>
			</div>
			<div id="button-box" class="inline right">
				<?php echo $lang_clan["can_del"];?>
				<form method="post" action="clan_delete.php?clan=<?php echo $clan;?>">
					<input class="button" type="submit" value="<?php echo $lang_verify;?>">
				</form>
			</div>
		</div>
		
		<div class="fullsize center bgcolor inline">
			<input class="button" type="button" onClick="location='clan_home.php?clan=<?php echo $clan;?>'" value="<?php echo $lang_goback;?>">
		</div>
	<?php
	}
	?>
	
</div>



<?php include "footer.php";
