<?php
// Sensitive information file, also protected in .htaccess
require_once 'config_settings.php';

// Check for players lang cookie & load required lang file
if(@$_COOKIE['lang']) {
	$lang = @$_COOKIE['lang'];
} else {
	// or default to English
	$lang = "en";
}

// Required for 'bad username/password' error still.
require_once "include_lang.php";

// Current version
$version = " v2.0.0";

// Force Refresh of CSS file(s) for Development.
function auto_version($file) {
  if(strpos($file, '/styles/phaos.css') !== 0 || !file_exists($_SERVER['DOCUMENT_ROOT'] . $file))
    return $file;

  $mtime = filemtime($_SERVER['DOCUMENT_ROOT'] . $file);
  return preg_replace('{\\.([^./]+)$}', ".$mtime.\$1", $file);
}

// Show ALL PHP Errors, Initial Setup
define(DEBUG, false);
error_reporting(E_ALL);
ini_set('display_errors', DEBUG ? 'On' : 'Off');


// Phaos database
$db = new mysqli("$game_host", "$game_dbuser", "$game_dbpasswd", "$game_db") or die ("Unable to connect to requested database.");
// phpBB database
$phpbb_db = new mysqli("$phpbb_host", "$phpbb_dbuser", "$phpbb_dbpasswd", "$phpbb_dbname") or die ("Unable to connect to requested database.");


// Sanity check
$result = $db->query("SELECT 1 FROM phaos_characters LIMIT 1");
if (!$result->fetch_row()) {
	printf("<br>ERROR: Sanity check failed.");
}


// Set variables for cookies
$PHP_PHAOS_USER = @$_COOKIE["PHP_PHAOS_USER"];
$PHP_PHAOS_PASS = @$_COOKIE["PHP_PHAOS_PASS"];

$PHP_ADMIN_USER = @$_COOKIE["PHP_ADMIN_USER"];
$PHP_ADMIN_PW = @$_COOKIE["PHP_ADMIN_PW"]; // for compatibility with old accounts
$PHP_ADMIN_MD5PW = @$_COOKIE["PHP_ADMIN_MD5PW"];


// Database querys should all have words like: Don\'t instead of Don't to be safe.
foreach($_GET as $key=>$value) {
	if (!is_array($value)) {
		$$key = get_magic_quotes_gpc() ? $value : addslashes($value);
	} else {
		foreach ($value as $innerKey => $innerValue) {
			$$key[$innerKey] = get_magic_quotes_gpc() ? $value : addslashes($innerValue);
		}
	}
}
foreach($_POST as $key=>$value) {
	if (!is_array($value)) {
		$$key = get_magic_quotes_gpc() ? $value : addslashes($value);
	} else {
		foreach ($value as $innerKey => $innerValue) {
			$$key[$innerKey] = get_magic_quotes_gpc() ? $value : addslashes($innerValue);
		}
	}
}


// Additional Security Check
unset($PHP_PHAOS_CHARID);
unset($PHP_PHAOS_CHAR);


// Fetch entered account, or go register
$auth = false;
if ( (isset($PHP_PHAOS_USER)) AND ((isset($PHP_PHAOS_PASS)) OR (isset($_POST['PHP_PHAOS_PW']))) ) {
	if (@$PHP_PHAOS_PASS) { // got cookie?
		$result = $db->query("SELECT * FROM phaos_users WHERE username = '$PHP_PHAOS_USER' AND password = '$PHP_PHAOS_PASS'");
		$row = $result->fetch_assoc();
	}
	if (!@$row) { // if no cookie
		$PHP_PHAOS_PASS = md5($_POST['PHP_PHAOS_PW']);
		$result = $db->query("SELECT * FROM phaos_users WHERE username = '$PHP_PHAOS_USER' AND password = '$PHP_PHAOS_PASS'");
		$row = $result->fetch_assoc();
	}
	if ($row) { // success, got user
		$auth = true;
		$lang = $row['lang'];
		
		// Set global vars for character name & id
		$result = $db->query("SELECT id,name FROM phaos_characters WHERE username = '$PHP_PHAOS_USER'");
		if ($row = $result->fetch_assoc()) {
			$PHP_PHAOS_CHARID	= $row['id'];
			$PHP_PHAOS_CHAR		= $row['name'];
		} else {
			$PHP_PHAOS_CHARID = 0;
		}
	
		// Reset Cookie Timers on each click
		setcookie("PHP_PHAOS_USER", $PHP_PHAOS_USER, time() + 3600, "/", $cookie_domain, $secure);
		setcookie("PHP_PHAOS_PASS", $PHP_PHAOS_PASS, time() + 3600, "/", $cookie_domain, $secure);
		setcookie('lang', $lang, time() + (86400 * 365));
		
		/*if ($_GET[play_music] == "YES") {
				$play_music = $_GET[play_music];
				setcookie("play_music",$play_music,time()+17280000);
		} else if ($_GET[play_music] == "NO") {
				$play_music = $_GET[play_music];
				setcookie("play_music",$play_music,time()+17280000);
		} else if ($_GET[play_music] == "") {
				$play_music = $_COOKIE[play_music];
				setcookie("play_music",$play_music,time()+17280000);
		}*/
		
		//if (DEBUG) { print_r($_COOKIE); }

	} else {
		please_register(true);
	}
} else {
	please_register();
}


function please_register($badpass = false) {
	if ($badpass) {
		global $lang_fig, $phpbb_url, $phpbb_sitename;
		?>
		<div class="center">
			<hr width="10%"><h4 class="red"><?php echo $lang_fig["baduser"];?></h4><hr width="10%">
			<span class="b msgbox halfsize">
			<?php echo $lang_fig["badu_msg1"];?><a href="<?php echo $phpbb_url;?>/ucp.php?mode=register" title="<?php echo $lang_fig["badu_help"];?>"><span class="u"><?php echo $phpbb_sitename;?></span></a>,
			<?php echo $lang_fig["badu_msg2"];?>
			</span>
		</div>
		<?php
	}

	if (!defined('AUTH')) {
		//unset these values just in case someone decides to remove the 'exit'
		unset($_COOKIE["PHP_PHAOS_USER"]);
		unset($GLOBALS['PHP_PHAOS_USER']);
		unset($GLOBALS['PHP_PHAOS_CHAR']);
		unset($GLOBALS['PHP_PHAOS_CHARID']);
	}
}