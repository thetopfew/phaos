<?php
require_once "header.php";

// If no character created
if ($character->location == "") {
	jsChangeLocation("create_character.php", 0);
}
// Boot if not you are not a leader or coleader
if (ru_clanleader($character->name) == false) {
	echo "<div class='center'><h3 class='red msgbox'>$lang_err[clan_leader]</h3></div>";
	jsChangeLocation("travel.php", 2);
}
// SECURE: Check URL if = a valid clanname for user, redirect if not
if ($clan == "" OR $clan != get_clanname($character->name)) {
	echo "<div class='center'><h3 class='center red msgbox'>$lang_err[clan_page]</h3></div>";
	jsChangeLocation("travel.php", 2);
}


// BEGIN UPLOAD
$target_dir = "images/uploads/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);

// Check if image file is a actual image or fake image
if(isset($_POST["logo"]) OR isset($_POST["banner"])) {
    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
    if($check !== false) {
        if (DEBUG) { $GLOBALS['debugmsgs'][] = "**DEBUG: File type - " . $check["mime"] . " is supported!"; }
        $uploadOk = 1;
    } else {
        echo "<div class='center'><h3 class='red msgbox'>$lang_upload[e_ftype]</h3></div>";
        $uploadOk = 0;
    }
}

// Limit file size
$maxlogo = 15360; // must be set in bytes #15kb
$maxbanner = 102400; // must be set in bytes #100kb

if ($_POST["logo"] && $_FILES["fileToUpload"]["size"] > $maxlogo) {
	$total_kb = (int)($maxlogo / 1024);
    echo "<div class='center'><h3 class='red msgbox'>$lang_upload[e_2bigL] $total_kb kb.</h3></div>";
    $uploadOk = 0;
}
if ($_POST["banner"] && $_FILES["fileToUpload"]["size"] > $maxbanner) {
	$total_kb = (int)($maxbanner / 1024);
    echo "<div class='center'><h3 class='red msgbox'>$lang_upload[e_2big] $total_kb kb.</h3></div>";
    $uploadOk = 0;
}

// Limit to these file formats
if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
    echo "<div class='center'><h3 class='red msgbox'>$lang_upload[e_ext]</h3></div>";
    $uploadOk = 0;
}
// If no errors, try and upload then rename or deliver the error and reload leader page.
if ($uploadOk == 1) {
	$brand = "";
	if(isset($_POST["logo"])) {
		$brand = "_L";
	} 
	if(isset($_POST["banner"])) {$brand = "";}
	
	$newfilename = "$target_dir$clanname$brand.$imageFileType";
	
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
		rename($target_file, $newfilename);
		
		if ($_POST['logo']) 	{$db->query("UPDATE phaos_clan_admin SET clan_sig='$newfilename' WHERE clanname='$clanname'");}
		if ($_POST['banner']) 	{$db->query("UPDATE phaos_clan_admin SET clanbanner='$newfilename' WHERE clanname='$clanname'");}
		
		 if (DEBUG) { $GLOBALS['debugmsgs'][] = "**DEBUG: File name has been renamed to: $clanname$brand.$imageFileType"; }
        echo "<div class='center'><h3 class='msgbox'>$lang_upload[sccess]</h3></div>";
		jsChangeLocation("clan_leader.php?clan=$clanname", 5);
    }
} else { // Error displays and now reload.
	jsChangeLocation("clan_leader.php?clan=$clanname", 5);
}

require_once "footer.php";