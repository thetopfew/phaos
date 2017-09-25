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
if ((empty($clan)) OR $clan != get_clanname($character->name)) {
	echo "<div class='center'><h3 class='red msgbox'>$lang_err[no_lead_p]</h3></div>";
	jsChangeLocation("travel.php", 2);
}

// Still here? Get all clan info at once and still call by it's returned var
list($clanid,$clanname,$clanleader,$clanleader_1,$clanbanner,$clansig,$clanlocation,$clanslogan,$clancashbox,$clanmembers,$clancreatedate,$clanrank_1,$clanrank_2,
	$clanrank_3,$clanrank_4,$clanrank_5,$clanrank_6,$clanrank_7,$clanrank_8,$clanrank_9,$clanrank_10,$clan_sig) = getall_claninfo($clan);



// Updating Guild Ranks? ------------------------------------------------------------------------------------------------------------------->
//if ($newnames == $lang_clan["save_nranks"]) {
if (isset($_POST['newnames'])) {
	$showform = "no";
	
	if ($T1 == "" OR $T2 == "" OR $T3 == "" OR $T4 == "" OR $T5 == "" OR $T6 == "" OR $T7 == "" OR $T8 == "" OR $T9 == "" OR $T10 == "") {
		echo "<div class='center'><h3 class='b red msgbox'>$lang_clan[noemtpy]</h3></div>";
		jsChangeLocation("clan_leader_ranks.php?clan=$clan", 2);
	} else {
		echo "<div class='center'>
				<h3 class='b msgbox'>$lang_clan[saveranks]</h3>
			</div>";
		
		$db->query("UPDATE phaos_clan_admin SET clanrank_1='$T1',clanrank_2='$T2',clanrank_3='$T3',clanrank_4='$T4',clanrank_5='$T5',clanrank_6='$T6',clanrank_7='$T7',clanrank_8='$T8',clanrank_9='$T9',clanrank_10='$T10' WHERE id='$clanid'");
		jsChangeLocation("clan_leader_ranks.php?clan=$clan", 2);
	}
}
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
	<h3 class=""><?php echo $lang_clan["cl_leadr_tit"];?></h3>
</div>

<?php // Customize Guild Ranks ------------------------------------------------------------------------------------------------------------------->?>
<div class="center b bgcolor">
	<?php echo $lang_clan["cus_here"];?>
</div>
<form method="post" action="clan_leader_ranks.php?clan=<?php echo $clan;?>">
	<table class="fullsize b" cellpadding=5 cellspacing=0>
		<tr bgcolor="#141414">
			<td class="center" width="25%">1</td>
			<td width="75%"><input type="text" name="T1" size="25" maxlength="20" value="<?php echo $clanrank_1;?>"></td>
		</tr>
		<tr bgcolor="#282828">
			<td class="center" width="25%">2</td>
			<td width="75%"><input type="text" name="T2" size="25" maxlength="20" value="<?php echo $clanrank_2;?>"></td>
		</tr>
		<tr bgcolor="#141414">
			<td class="center" width="25%">3</td>
			<td width="75%"><input type="text" name="T3" size="25" maxlength="20" value="<?php echo $clanrank_3;?>"></td>
		</tr>
		<tr bgcolor="#282828">
			<td class="center" width="25%">4</td>
			<td width="75%"><input type="text" name="T4" size="25" maxlength="20" value="<?php echo $clanrank_4;?>"></td>
		</tr>
		<tr bgcolor="#141414">
			<td class="center" width="25%">5</td>
			<td width="75%"><input type="text" name="T5" size="25" maxlength="20" value="<?php echo $clanrank_5;?>"></td>
		</tr>
		<tr bgcolor="#282828">
			<td class="center" width="25%">6</td>
			<td width="75%"><input type="text" name="T6" size="25" maxlength="20" value="<?php echo $clanrank_6;?>"></td>
		</tr>
		<tr bgcolor="#141414">
			<td class="center" width="25%">7</td>
			<td width="75%"><input type="text" name="T7" size="25" maxlength="20" value="<?php echo $clanrank_7;?>"></td>
		</tr>
		<tr bgcolor="#282828">
			<td class="center" width="25%">8</td>
			<td width="75%"><input type="text" name="T8" size="25" maxlength="20" value="<?php echo $clanrank_8;?>"></td>
		</tr>
		<tr bgcolor="#141414">
			<td class="center" width="25%">9</td>
			<td width="75%"><input type="text" name="T9" size="25" maxlength="20" value="<?php echo $clanrank_9;?>"></td>
		</tr>
		<tr bgcolor="#282828">
			<td class="center" width="25%">10</td>
			<td width="75%"><input type="text" name="T10" size="25" maxlength="20" value="<?php echo $clanrank_10;?>"></td>
		</tr>
	</table>
	
	<div id="actionbar" class="center fullsize bgcolor">
		<input class="left button" type="button" onClick="location='clan_leader.php?clan=<?php echo $clan;?>'" value="<?php echo $lang_goback;?>">
		<input class="button" type="reset" value="<?php echo $lang_reset;?>" name="B2">
		<input class="right button" type="submit" value="<?php echo $lang_save;?>" name="newnames">
	</div>
</form>


<?php include "footer.php";