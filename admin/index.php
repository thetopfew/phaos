<?php
include_once("../config.php");
include "aup.php";
?>
<html>
<head>
	<title>WoP Administration Panel</title>
	<link href="../styles/phaos.css" rel="stylesheet" type="text/css">
</head>

<body>
	<table border="1" bordercolor="#CCCCCC" cellspacing="0" cellpadding="5" align="center">
		<tr>
			<td align="center"><b><font size="6">WoP Administration Panel</font></b></td>
		</tr>
<?php
	// This script will search for files with the name "admin_<section>_<function>.php
	// To add an admin-file, name the function file admin_<section>_<function>.php
	// To give the function a .gif icon, name the function icon exactly the same as the
	// function file but, instead of .php use .gif and place in the phaos/admin/images
	// folder. Make sure you use the same upper and lower case letters and spellings.
	// eg. function admin_create_Weapon.php has icon admin_create_Weapon.gif

function printit($element){
	print($element);
}

function buildit($section){ //kaspir rewrote this function.
	$title = strtoupper($section);
	echo "<tr style=background:#006600;>
			<td align='center'><b>$title</b></td>
		  </tr>
				<tr>
				<td>
				<table width='100%' border='0' cellspacing='5' cellpadding='5' align='center'>
					<tr>";

	if ($dir = opendir('.')) {
		$i = 0;
		while (false !== ($entry = readdir($dir))) {
			if (mb_substr($entry, 0, 5) == "admin") {
				if (preg_replace("/$section/"," ",$entry)) {
					//take out admin_<section>_ and .php from sring
					$picture_name = preg_replace("/.php/",".gif",$entry);
					
					$display_name = preg_replace("/admin/"," ",$entry);
					$display_name = preg_replace("/.php/"," ",$display_name);
					$display_name = preg_replace("/_/"," ",$display_name);
					
					if (mb_strstr($display_name, $section)) {
						$display_name = preg_replace("/$section/"," ",$display_name);
						$output[$i] = '<td align="center"><a href="'.$entry.'"><img src="images/'.$picture_name.'" width="32" height="32" alt="'.$display_name.'"><br>'.$display_name.'</a></td>';
						$i++;
					}
				}
			}
		}
		closedir($dir);
	}
	
	$outArr = asort($output);
	array_walk($output,'printit');
	
	echo "</tr></table></td></tr>";
}


// ----------------
// BUILD THE SCREEN
// ----------------
// If you want another section in the Admin Panel then all
// you have to do is place the name of the section between
// the peranthesis () of buildit();
// eg. If you have created a 'Maps' section and you want to
//     call the section 'Maps' with the file names in the format of
//       admin_Maps_<function>.php
//     then you would use:
// buildit(Maps);

// Case sensitive.
buildit('create');
buildit('edit');
buildit('map');
buildit('users');
//buildit('help'); //kaspir, no longer uses JS help menu.
buildit('options');

echo "</table>
</body>
</html>";
?>
