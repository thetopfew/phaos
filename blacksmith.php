<?php
// SECURE: Password Protected!!
require_once "../password_protect.php";

require_once "header.php";
require_once "items.php";
require_once "class_character.php";
?>

<h1>Updating.. Blacksmith</h1>
<?php //FIXME: Doesn't seem to do anything yet anyways, no vars are carried over when file passworded.
$query = "REPLACE INTO phaos_shop_basics
	    SELECT shop_id, 'blacksmith', 0, 0, 3600
	    FROM `phaos_buildings`
	    WHERE TYPE LIKE 'blacksmith.php'";

$req = $db->query($query);
if (!$req) { showError(__FILE__,__LINE__,__FUNCTION__,$query); exit;}

// Off chance to logout protected cookie
echo "<br><br><br><a href='$this_url?logout=1'>Logout</a>";

require_once "footer.php";

// Force refresh back to blacksmith we are updating...
jsChangeLocation("darksmith.php?shop_id=$shop_id", 5);
