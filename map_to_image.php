<?php
require_once "config.php";
require_once 'include_lang.php';

$imgsizew = 30;
$imgsizeh = 30;
$border = 0;
$begin_id = @$_POST[begin_id];
$end_id = @$_POST[end_id];

$css = "";
$realm = "You're lost...";
if ($begin_id == 0) 	 {$realm = $lang_home["map_allied"];}
if ($begin_id == 10000)  {$realm = $lang_home["map_illandry"];}
if ($begin_id == 20000)  {$realm = $lang_home["map_lanus"];}
if ($begin_id == 30000)  {$realm = $lang_home["map_thanium"];}
if ($begin_id == 40000)  {$realm = $lang_home["map_wath"];}
if ($begin_id == 50000)  {$realm = $lang_home["map_kjelk"];}
if ($begin_id == 60000)  {$realm = $lang_home["map_tel-khaliid"];}
if ($begin_id == 70000)  {$realm = $lang_home["map_qu-nai"];}
if ($begin_id == 80000)  {$realm = $lang_home["map_gilanthia"];}
if ($begin_id == 100001) {$realm = $lang_home["map_dung1"]; $css = "page-center";}
if ($begin_id == 100226) {$realm = $lang_home["map_dung2"]; $css = "page-center";}
if ($begin_id == 100451) {$realm = $lang_home["map_dung3"]; $css = "page-center";}
if ($begin_id == 100686) {$realm = $lang_home["map_mines"];}
?>

<html>
<head>
	<title><? echo $realm;?></title>
	<link rel="stylesheet" type="text/css" href="styles/phaos.css" />
</head>

<body>
	<?php
	$result = $db->query("SELECT id FROM phaos_locations WHERE above_left = '0' AND above = '0' AND leftside = '0' AND id >= '$begin_id' AND id < '$end_id'");
	if ($row = $result->fetch_assoc()) {
		$start_id = $row["id"];
	}

	$max_height_y = "SELECT * FROM phaos_locations WHERE leftside = '0' AND id >= '$begin_id' AND id < '$end_id'";
	$numresults = $db->query($max_height_y);
	$max_height = $numresults->num_rows;
	//echo "Total rows: ".$max_height."<br>";

	$max_width_x = "SELECT * FROM phaos_locations WHERE above = '0' AND id >= '$begin_id' AND id < '$end_id'";
	$numresults = $db->query($max_width_x);
	$max_width = $numresults->num_rows;
	//echo "Total columns: ".$max_width."<br>";

	// COMMENTED OUT SO PEOPLE DON'T WASTE MY BANDWIDTH GENERATING IMAGES -Zeke
	// IF YOU UNCOMMENT THIS THERE IS A BUTTON TO TURN THE MAP INTO A SINGLE IMAGE
	/*echo "
	<form method='POST' action='map_show_image.php'>
		<input type='hidden' name='max_width' value='".$max_width."'>
		<input type='hidden' name='imgsizew' value='".$imgsizew."'>
		<input type='hidden' name='max_height' value='".$max_height."'>
		<input type='hidden' name='imgsizeh' value='".$imgsizeh."'>
		<input type='hidden' name='begin_id' value='".$begin_id."'>
		<input type='hidden' name='end_id' value='".$end_id."'>
		<p><input class='button' type='submit' value='Show Image' name='B1'></p>
	</form>";
	*/

	echo "<table id='$css'>
			<tr><td>";

			$N_below = 0;
			for ($y = 1; $y <= $max_height; $y++) {
				for ($x = 1; $x <= $max_width; $x++) {
					$S_result = $db->query("SELECT * FROM phaos_locations WHERE id = '".$start_id."'");
					if ($S_row = $S_result->fetch_assoc()) {
						$S_id 			= $S_row["id"];
						$S_name			= $S_row["name"];
						$S_above_left 	= $S_row["above_left"];
						$S_above 		= $S_row["above"];
						$S_above_right 	= $S_row["above_right"];
						$S_left 		= $S_row["leftside"];
						$S_right 		= $S_row["rightside"];
						$S_below_left 	= $S_row["below_left"];
						$S_below 		= $S_row["below"];
						$S_below_right 	= $S_row["below_right"];
						$S_image 		= $S_row["image_path"];
						$S_building 	= $S_row["buildings"];
						
						$start_id = $S_right;
					
						if ($S_building == "n") {
							echo "<img border='".$border."' src='".$S_image."' width='".$imgsizew."' height='".$imgsizeh."' title='".$S_id."' >";
						} else {
							echo "<img class='help' border='".$border."' src='".$S_image."' width='".$imgsizew."' height='".$imgsizeh."' title='".$S_name."' >";					
						}
					}
					
					if ($x == $max_width) {
						echo "<br>";
						$N_below == 0;
						$start_id = $N_start_id;
					}
					if ($x == 1 AND $N_below == 0) {
						$N_start_id = $S_below;
						$N_below == 1;
					}
				}
			}
			?>
		</td></tr>
	</table>
</body>
</html>
