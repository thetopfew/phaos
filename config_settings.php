<?php
// Game title in browser tab.
$GAME_TITLE 	= 'DEV - World of Phaos';

// Define Server Globals
$this_url = basename(htmlspecialchars($_SERVER['PHP_SELF']));
$this_URI = basename(htmlspecialchars( $_SERVER['REQUEST_URI'], ENT_QUOTES | ENT_HTML5, 'UTF-8' ));

function isSecure() {
	if (isset($_SERVER["HTTPS"])) {
		return true;
	} else {
		return false;
	}
}


// Cookie Settings
$cookie_domain 	= '.example.com'; // Will keep cookie in this domain (AND subdomains as long as it starts with a .)
$secure 		= isSecure();


// Phaos MySQLi Settings
$game_host 		= 'localhost';
$game_db 		= 'db_name_here';
$game_dbuser 	= 'db_user_here';
$game_dbpasswd 	= 'db_pass_here';


// phpBB Database Information: required for Points Exchange /w Ultimate Points extension by dmzx.
$phpbb_host 	= 'localhost';
$phpbb_dbname 	= 'phpbb_dbname';
$phpbb_dbuser 	= 'phpbb_dbuser';
$phpbb_dbpasswd = 'phpbb_dbpasswd';
$phpbb_dbprefix = 'phpbb_dbprefix';
$phpbb_sitename = 'Example.com';
$phpbb_url		= 'https://Example.com/forum';

// Other Specific data
$googleplus_id	= '11181#YOUR_ID##54215';
$twitter_id		= 'YOUR_ID';
