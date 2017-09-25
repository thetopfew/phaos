<?php
require_once "header.php";

// If no character created
if ($character->location == "") {
	jsChangeLocation("create_character.php", 0);
}
// Get Slapped! If in combat and clicked travel
if(isset($_SESSION['opponent_id'])) {
	jsChangeLocation("combat.php?comb_act=npc_attack", 0);
}
// EXTRA SECURITY Check: Make sure this user exists or die
$db->query("SELECT * FROM phaos_users WHERE username='$PHP_PHAOS_USER' LIMIT 1") or die (" ".$lang_mssg["cnat_touch_this"]);

// added by dragzone---
//if (!$to) { $to = $HTTP_GET_VARS['to']; }
//---------------------
require_once "class_character.php";
require_once "message_functions.php";

$character = new character($PHP_PHAOS_CHARID);

// Did we click search user?
if (isset($_POST['searchnow'])) {
	$res = $db->query("SELECT username FROM phaos_characters WHERE name='$player_name' LIMIT 1") or die (" ".$lang_mssg["cnat_touch_this"]);
	if ($row = $res->fetch_assoc()) { $player_name = $row["username"]; jsChangeLocation("player_info.php?player_name=$player_name", 0); }
}

?>

<div class="center">
	<h3><?php echo $lang_mssg["m_cent"]; ?> <?php echo $PHP_PHAOS_USER; ?></h3>
	
	<div id="actionbar" class="bgcolor">
		<input class="big button" type="submit" value="<?php echo $lang_mssg["comp"];?>" onClick="location='message.php?action=compose'">
		<input class="big button" type="submit" value="<?php echo $lang_mssg["inbox"];?>" onClick="location='message.php?action=inbox'">
	</div>
	<hr>
	<div>
		<form enctype="multipart/form-data" method="post" action="player_info.php?player_name=<?php echo $player_name;?>">
			<input type="text" name="player_name" size="20" maxlength="20" placeholder="<?php echo $lang_f_names;?>">
			<input class="button" type="submit" name="searchnow" value="<?php echo $lang_search;?>" onClick="this.value='<?php echo $lang_searching;?>'">
		</form>
	</div>
	<hr>
</div>
<?php

if (@$action == 'inbox') {
	$res = $db->query("SELECT * FROM phaos_mail WHERE UserTo='$PHP_PHAOS_USER' ORDER BY mail_id DESC");
	if ($res->num_rows) {
		?>
		<table class="fullsize" style="background:#000000;border-collapse: collapse;" cellpadding=2 cellspacing=0 valign="top" border=1>
			<tr class="b bgcolor">
				<td width=160><?php echo $lang_mssg["subj"];?></td>
				<td width=70><?php echo $lang_mssg["frm"];?></td>
				<td width=45 class="center"><?php echo $lang_mssg["s_date"];?></td>
				<td width=10 class="center"><?php echo $lang_mssg["delmail"];?></td>
			</tr>
		<?php
		while ($row = $res->fetch_assoc()) { // List each piece of mail ?>
			  
			<tr width="30">
				<td width="160">
					<img src="images/<?php echo $row["STATUS"];?>.gif" border=0 width="22px">	  
					<a href="message.php?action=veiw&mail_id=<?php echo $row["mail_id"];?>"><?php echo $row["Subject"];?></a>
				</td>
				<td width=70 style="padding-left:5px">
					<?php echo $row["UserFrom"];?>
				</td>
				<td width=45 class="center">
					<?php echo $row["SentDate"];?>
				</td>
				<td width=10 class="center">
					<input class="button" type="button" value="<?php echo $lang_mssg["delt"];?>" onClick="location='message.php?action=delete&SentDate=<?php echo $row["SentDate"];?>&id=<?php echo $row["mail_id"];?>'">
				</td>
			</tr>
			
			<?php
		}
		echo "</table>"; // Close table after last mail piece.
	} else {
		echo "<div class='center'><h3 class='b msgbox'>".$lang_mssg["no_msg_l"]."</h3></div>";
	}
}

if (@$action == 'compose') {
	?>
	<form name="inputform" action="message.php?action=send" method="post">
		<table class="fullsize b" cellpadding=1 cellspacing=1>
		<tr>
			<td style="padding-bottom:14px;" class="right"><?php echo $lang_mssg["222"];?></td>
			<td><input type="text" name="to" size="20" maxlength="20" value="<?php echo @$to;?>"><br><b><?php echo $lang_mssg["must_username"];?></b></td>
		</tr>
		<tr>
			<td class="right"><?php echo $lang_mssg["subj"];?></td>
			<td><input type="text" name="subject" size="40" maxlength="40" value="<?php echo @$subject;?>" ></td>
		</tr>
		<tr>
			<td class="center" colspan=2><textarea rows="16" cols="80" maxlength="1000" name="message"></textarea></td>
		</tr>
		<tr>
			<td class="center" colspan=2>
				<input class="button" type="button" value="b" onClick="addText('message', '[b]', '[/b]');">
				<input class="button" type="button" value="i" onClick="addText('message', '[i]', '[/i]');">
				<input class="button" type="button" value="u" onClick="addText('message', '[u]', '[/u]');">
				
				<input class="button" type="button" value="<?php echo $lang_bbcode["url"];?>" onClick="addText('message', '[url]', '[/url]');">
				<input class="button" type="button" value="<?php echo $lang_bbcode["mail"];?>" onClick="addText('message', '[mail]', '[/mail]');">
				<input class="button" type="button" value="<?php echo $lang_bbcode["img"];?>" onClick="addText('message', '[img]', '[/img]');">
				<input class="button" type="button" value="<?php echo $lang_bbcode["center"];?>" onClick="addText('message', '[center]', '[/center]');">
				<input class="button" type="button" value="<?php echo $lang_bbcode["small"];?>" onClick="addText('message', '[small]', '[/small]');">
				<input class="button" type="button" value="<?php echo $lang_bbcode["code"];?>" onClick="addText('message', '[code]', '[/code]');">
				<input class="button" type="button" value="<?php echo $lang_bbcode["quote"];?>" onClick="addText('message', '[quote]', '[/quote]');">
			</td>
		</tr>
		<tr>
			<td>
				<?php
				// Addin the BBcodes & Smileys
				echo "".$lang_bbcode["cl_selc"].": 
				<select name='bbcolor' onChange=\"addText('message', '[color=' + this.options[this.selectedIndex].value + ']', '[/color]');this.selectedIndex=0;\">
					<option value=''>".$lang_bbcode["Default"]."</option>
					<option value='maroon' style='color:maroon;'>".$lang_bbcode["Maroon"]."</option>
					<option value='red' style='color:red;'>".$lang_bbcode["Red"]."</option>
					<option value='orange' style='color:orange;'>".$lang_bbcode["Orange"]."</option>
					<option value='brown' style='color:brown;'>".$lang_bbcode["Brown"]."</option>
					<option value='yellow' style='color:yellow;'>".$lang_bbcode["Yellow"]."</option>
					<option value='green' style='color:green;'>".$lang_bbcode["Green"]."</option>
					<option value='lime' style='color:lime;'>".$lang_bbcode["Lime"]."</option>
					<option value='olive' style='color:olive;'>".$lang_bbcode["Olive"]."</option>
					<option value='cyan' style='color:cyan;'>".$lang_bbcode["Cyan"]."</option>
					<option value='blue' style='color:blue;'>".$lang_bbcode["Blue"]."</option>
					<option value='navy' style='color:navy;'>".$lang_bbcode["Navy Blue"]."</option>
					<option value='purple' style='color:purple;'>".$lang_bbcode["Purple"]."</option>
					<option value='violet' style='color:violet;'>".$lang_bbcode["Violet"]."</option>
					<option value='black' style='color:black;'>".$lang_bbcode["Black"]."</option>
					<option value='gray' style='color:gray;'>".$lang_bbcode["Gray"]."</option>
					<option value='silver' style='color:silver;'>".$lang_bbcode["Silver"]."</option>
					<option value='white' style='color:white;'>".$lang_bbcode["White"]."</option>";
				?>
				</select>
			</td>
			<td>
				<?php echo displaysmileys("message");?><button class="inline right button" type="submit"><?php echo $lang_mssg["send_mssg"];?></button>
			</td>
		</tr>
		</table>
	</form>
	<?php
}

   
if (@$action == 'send') {
	// Fail if any fields are empty
	if (!@$subject) {
		echo "<div class='center'><h3 class='b red msgbox'>".$lang_mssg["_blank1"]."</h3></div>";
		jsChangeLocation("message.php?action=compose", 3);
	}
	if (!@$message) {
		echo "<div class='center'><h3 class='b red msgbox'>".$lang_mssg["_blank2"]."</h3></div>";
		jsChangeLocation("message.php?action=compose", 3);
	}
	if (!@$to) {
		echo "<div class='center'><h3 class='b red msgbox'>".$lang_mssg["_blank3"]."</h3></div>";
		jsChangeLocation("message.php?action=compose", 3);
	}

	// Cannot mail yourself, otherwise process to sending
	if ($to == $PHP_PHAOS_USER) {
		echo "<div class='center'><h3 class='b red msgbox'>".$lang_mssg["ad_msg-yourself"]."</h3></div>";
		jsChangeLocation("message.php?action=inbox", 3);
	} else {
		// Prevent Junkmail: Check if username exists first!
		$result = $db->query("SELECT username FROM phaos_users WHERE username = '$to' LIMIT 1");
		if ($row = $result->fetch_assoc()) {
			$user = $row["username"];

			if ($user) {
				$date = date("m/d/Y h:i");
				// Create mail piece
				$create = "INSERT INTO phaos_mail (UserTo, UserFrom, Subject, Message, SentDate, status)
				VALUES ('$to','$PHP_PHAOS_USER','$subject','$message','$date','unread')";
				// OR Fail using create query
				$db->query($create) or die($lang_mssg["snd22"]." $to");
				echo "<div class='center'><h3 class='b msgbox'>".$lang_mssg["snd_2"]." ".$to."</h3></div>";
				jsChangeLocation("message.php?action=inbox", 3);
			} 
		} else { 
			echo "<div class='center'><h3 class='b red msgbox'>".$lang_err["fail_serch"]."</h3></div>";
			jsChangeLocation("message.php?action=compose", 3);
		}
	}
}
   

// In the view I have removed viewing in the textbox to allow correct output of the codes now in use.
// This should have no effect on the overall standard display abilitys since I have also made use of nl2br to get the returns etc. (at least i think it will work :)

if (@$action == 'veiw') {
	// Get selected mail details
	$res = $db->query("SELECT * FROM phaos_mail WHERE UserTo='$PHP_PHAOS_USER' AND mail_id=$mail_id") or die (" ".$lang_mssg["cnat_touch_this"]);
	$row = $res->fetch_assoc();
	
	// Parse the Smileys & BBcodes
	$message = $row['Message'];
	$message = parsesmileys($message);
	$message = parseubb($message);
	$message = nl2br($message);// make returns
	
	// SECURE: Make sure this mail belongs to you!
	if ($row["UserTo"] != $PHP_PHAOS_USER) {
		echo "<div class='center'><h3 class='b red msgbox'>".$lang_mssg["not_ur_ma"]."</h3>,/div>";
		jsChangeLocation("message.php?action=inbox", 3);
	}
	
	// Mark this mail as read
	if ($row["STATUS"] == 'unread') { $db->query("UPDATE phaos_mail SET status='read' WHERE mail_id='$mail_id'"); }
	
	// Display mail piece ?>
	<table class="fullsize" border=1 cellpadding=5 cellspacing=0 style="border-collapse:collapse;background:#000000;" height="201">
		<tr>
			<td>
				<span class="b"><?php echo $lang_mssg["frm"];?></span>
				<a href="player_info.php?player_name=<?php echo $row["UserFrom"];?>"><?php echo $row["UserFrom"];?></a>
				<span class="right"><?php echo $row["SentDate"];?></span>
			</td>
		</tr>
		<tr>
			<td>
				<span class="b"><?php echo $lang_mssg["subj"];?></span> <?php echo $row["Subject"];?>
			</td>
		</tr>
		<tr>
			<td height="141"><?php echo $message;?></td>
		</tr>
	</table>
	<hr>
	<div id="actionbar" class="center fullsize bgcolor">
		<input class="button" type="button" value="<?php echo $lang_mssg["repply"];?>" onClick="location='message.php?action=compose&to=<?php echo $row["UserFrom"];?>&subject=RE:<?php echo $row["Subject"];?>'">
		
		<input class="button" type="button" value="<?php echo $lang_mssg["delt"];?>" onClick="location='message.php?action=delete&SentDate=<?php echo $row["SentDate"];?>&id=<?php echo $row["mail_id"];?>'">
	</div>
	<?php
}

if (@$action == 'delete') {
	## ADDME: A JS confirm popup box here upon deletion.
	$delthis = $db->query("DELETE FROM phaos_mail WHERE mail_id='$id' LIMIT 1");
	if ($delthis) {
		echo "<div class='center'><h3 class='b msgbox'>".$lang_mssg["msg_dellt"]."</h3></div>";
	} else {
		echo "<div class='center'><h3 class='b red msgbox'>".$lang_mssg["mssg_wasn"]."</h3></div>";
	}
	jsChangeLocation("message.php?action=inbox", 1);
}
?>

<?php require_once "footer.php";
