<?php
//session_start();
require_once "aup.php";
require_once "global.php";
require_once "Sajax.php";
// FYI: class_character.php is loaded once for all pages in side_bar.php, not here.
			
//date_default_timezone_set('UTC');
// ADDME: A user option for timezones.
date_default_timezone_set('America/New_York');
$ts = date("Y-m-d H:i:s");

// Mark user last active date/time.
$last_active = $db->query("SELECT last_active FROM phaos_users WHERE username = '$PHP_PHAOS_USER'");
    if ($ts != $last_active) {
		$db->query("UPDATE phaos_users SET last_active = '$ts' WHERE username = '$PHP_PHAOS_USER'");
}

function add_chat_line($text) {
	global $db, $PHP_PHAOS_USER;
	
	$text = strip_tags($text);
	$bb_replace =      array('[b]', '[B]', '[/b]', '[/B]', '[i]', '[I]', '[/i]', '[/I]', '[u]', '[U]', '[/u]', '[/U]');
	$bb_replacements = array('<b>', '<b>', '</b>', '</b>', '<i>', '<i>', '</i>', '</i>', '<u>', '<u>', '</u>', '</u>');
	$text = str_replace($bb_replace, $bb_replacements, $text);
	$result = $db->query("SELECT location, name FROM phaos_characters WHERE username = '$PHP_PHAOS_USER'");
	if ($row = $result->fetch_assoc()) {
		$db->query("INSERT INTO phaos_shout (location, postname, postdate, posttext)
				VALUES ('".$row['location']."', '".$row['name']."', '".time()."', '".$text."')");
	}
}

function refresh() {
	global $db, $PHP_PHAOS_USER;
	
	$result = $db->query("SELECT location, name FROM phaos_characters WHERE username = '$PHP_PHAOS_USER'");
	if ($row = $result->fetch_assoc()) {
		$char_location = $row["location"];
		$char_name = $row['name'];
	}
	//$result = $db->query('SELECT * FROM phaos_shout WHERE location = \'' . $char_location .'\' OR destname=\''.$char_name.'\' OR destname = \'admin\' ORDER BY postdate DESC LIMIT 0, 10');
	$result = $db->query('SELECT * FROM phaos_shout ORDER BY id DESC LIMIT 0, 10'); // made chat GLOBAL.
	while ($row = $result->fetch_assoc()) {
		$color = '';
		if($row['destname'] == 'admin') {
			$color = "red";
		}
		if($row['destname'] == $char_name) {
			$color = "yellow";
		}
		if ($color == '') {
			$color = "white";
		}
		//print '<hr><div align="left"><font color="'.$color.'"><b>' . $row['postname'] . '</b>, posted at ' . $row['postdate'] . '<br><br> '.$row['posttext'] .' <br></font>';
		print '<hr><div align="left"><font color="'.$color.'"><u><b>' . $row['postname'] . '</b> says</u>: '.$row['posttext'] .' <br></font>'; // HIDE date/time of post
	}

	$result = $db->query("SELECT id,postdate FROM phaos_shout");
	while ($row = $result->fetch_assoc()) {
		$current_time = time();
		$time_check = $current_time - $row['postdate'];
		if ($time_check > '600') { // Counts seconds. (86400=24hours) (300=5min)
			$delete_extras = $db->query('DELETE FROM phaos_shout WHERE id = \''.$row['id'].'\' ');
		}
	}
}

$sajax_request_type = "GET";
sajax_init();
sajax_export("add_chat_line", "refresh");
sajax_handle_client_request();
?>

<!DOCTYPE html>
<html lang="<?php echo $lang;?>" />
<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	
	<title><?php echo $GAME_TITLE, $version;?></title>
	<meta name="application-name" content="<?php echo $GAME_TITLE;?>">
	
	<!-- <link href="http://fonts.googleapis.com/css?family=Droid+Sans" rel="stylesheet" type="text/css"> USE LATER FOR MOBILE FONT -->
	
	<link rel="stylesheet" type="text/css" href="<?php echo auto_version('/styles/phaos.css'); ?>" />
	<!-- <link rel="stylesheet" type="text/css" href="<?php #echo auto_version('/styles/responsive.css'); ?>" media="all and (max-width: 700px)" /> -->
	<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
	
	<script>
		<?php sajax_show_javascript(); ?>
		function refresh_chat(new_data) {
			document.getElementById("chat_right_side").innerHTML = new_data;
		}

		function refresh() {
			x_refresh(refresh_chat);
		}

		function add_chat_line_cb() {
			refresh();
		}

		function add_chat_line() {
			var chat_text;
			chat_text = document.chat_form.chat_text.value;
			x_add_chat_line(chat_text,add_chat_line_cb);
			document.chat_form.chat_text.value="";
		}
	</script>
	<!-- 
    <script> 
    document.onmousedown=disableclick;
    status="Right Click Disabled";
    function disableclick(event)
    {
      if(event.button==2)
       {
         alert(status);
         return false;    
       }
    }
    </script>
	-->
</head>

<body onFocus="refresh();"> <!--  //this removes left click also:  oncontextmenu="return false" -->

<div id="wrap">
		<div id="side_bar" >
			<dt class="center"><a href="index.php"><img id="logo" src="images/top_logo.png" title="<?php echo $lang_welcome;?>"></a><dt>
			<?php include_once "side_bar.php"; ?>
		</div>
		<div id="header_menu">
			<?php include_once "menu.php"; ?>
		</div>
		<div id="right_side" class="responsive-hide">
			<?php include_once "right_side.php";?>
		</div>
	
		<div id="page_body">
