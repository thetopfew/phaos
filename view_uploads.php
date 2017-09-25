<?php require_once "header.php";

// If no character created
if ($character->location == "") {
	jsChangeLocation("create_character.php", 0);
}
#ADDME: Perhaps make file for admins only.
?>

<table class="fullsize" border=1 cellspacing=2 cellpadding=2>
	<?php
	// Get a list of images
	$dirname = "images/uploads/";
	$images = glob($dirname."*.*");

	foreach($images as $image) {
		$neatname = str_replace($dirname,'',$image);
		
		echo '<tr><td><img id="clan_image" src="'.$image.'" title="'.$neatname.'" /></td>';
	}
	?>
</table>
<?php require_once "footer.php";
