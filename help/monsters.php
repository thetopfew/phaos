<?php 
include("../config.php");
?>
<html>
<head>
<title>WoP - Monster Index</title>
<link href="../styles/phaos.css" rel="stylesheet" type="text/css">
</head>
<body>
<table width="90%" border="1" cellspacing="0" cellpadding="3" align="center">
  <tr style=background:#006600;> 
    <td colspan="2"> 
      <div align="center"><b>WoP - Monsters Index</b></div>
    </td>
  </tr>
  <tr style=background:grey;> 
    <td colspan="2"> 
      <div align="center"><b>Displayed in order of difficulty. Stats are shown from base level one monsters only.</b></div>
    </td>
  </tr>

  <tr> 
	<?php
	$self = mysqli_query($db, "SELECT * FROM phaos_opponents WHERE location=0 ORDER BY ac ASC, hit_points ASC");
	print ("<table border=0 cellpadding=5 align='center' valign='top' width='90%'>
	<tr>");
	$line = 0;
    while ($row = mysqli_fetch_array($self)) {
        //$id = $row["id"];
        $desc = $row["name"];
        $ac = $row['AC'];
        $hp = $row['hit_points'];
		$image = $row['image_path'];
		$min = $row['min_damage'];
		$max = $row['max_damage'];
        //$sell = $row['sell_price'];
		
		print ("<td align=center style=\"background-color: rgba(95, 95, 95, .7); padding:5px; width='200px'\"><hr>
			<big><b>$row[name]</b></big><br>
			<img style='padding-top:5px;width:60px;height:60px;' align='center' src='/$image'><br>	
			AC: $ac<br>
			HP: $hp<br>
			Base Dmg: $min - $max<br>		
		<hr>");
		$line ++;
		if($line==7){
			echo "</td></tr><tr>";
			$line = 0;
		}else{ 
			echo "</td>";
		}
} 	
	?>
	</table></td>
  </tr>

</table>
</body>
</html>
