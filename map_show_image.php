<?php 
require_once "config.php";
require_once 'include_lang.php';

$imgsizew = 30;
$imgsizeh = 30;
$border = 0;
$begin_id = $_POST[begin_id];
$end_id = $_POST[end_id];

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
if ($begin_id == 100001) {$realm = $lang_home["map_dung1"];}
if ($begin_id == 100226) {$realm = $lang_home["map_dung2"];}
if ($begin_id == 100451) {$realm = $lang_home["map_dung3"];}
if ($begin_id == 100686) {$realm = $lang_home["map_mines"];}

$result = $db->query("SELECT id FROM phaos_locations WHERE above_left = '0' AND above = '0' AND leftside = '0' AND id >= '$begin_id' AND id < '$end_id'");
if ($row = $result->fetch_assoc()) {
	$start_id = $row["id"];
}

$max_width_x = "SELECT * FROM phaos_locations WHERE above = '0' AND id >= '$begin_id' AND id < '$end_id'";
$numresults = $db->query($max_width_x);
$max_width = $numresults->num_rows;
//echo $max_width."<br>";

$max_height_y = "SELECT * FROM phaos_locations WHERE leftside = '0' AND id >= '$begin_id' AND id < '$end_id'";
$numresults = $db->query($max_height_y);
$max_height = $numresults->num_rows;
//echo $max_height."<br>";

$image = imagecreatetruecolor ($max_width * $imgsizew,$max_height * $imgsizeh);
$bgcolor = ImageColorAllocate ($image, 0, 0, 0);

$N_below = 0;
for ($y= 1; $y <= $max_height; $y++) {
    for ($x= 1; $x <= $max_width; $x++) {
        $S_result = $db->query("SELECT * FROM phaos_locations WHERE id  = '".$start_id."'");
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
		}

		$tileimage = $S_image;
		$image2 = imagecreatefrompng($tileimage);
		imagecopymerge ( $image, $image2, ($x-1) * $imgsizew, ($y-1) * $imgsizeh, 0, 0, $imgsizew, $imgsizeh, 100);

        if ($S_building == "n") {
				echo "<img border='".$border."' src='".$S_image."' width='".$imgsizew."' height='".$imgsizeh."' title='".$S_name."' >";
			} else {
				echo "<img class='help' border='".$border."' src='".$S_image."' width='".$imgsizew."' height='".$imgsizeh."' title='".$S_name."' >";					
		}
		
        if ($x == $max_width) {
			$N_below == 0;
			$start_id = $N_start_id;
		}
        if ($x == 1 AND $N_below == 0) {
			$N_start_id = $S_below;
			$N_below == 1;
		}
    }
}

header("Content-Type: image/png");
$tempimage = imagepng($image);
