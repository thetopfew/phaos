<?php 
include("../config.php");
?>
<html>
<head>
<title>WoP - Characters</title>
<link href="../styles/phaos.css" rel="stylesheet" type="text/css">
</head>
<body>
<table width="90%" border="1" cellspacing="0" cellpadding="3" align="center">
  <tr style=background:#006600;> 
    <td colspan="2"> 
      <div align="center"><b>WoP - Character Races</b></div>
    </td>
  </tr>
  <tr> 
	<?php
	$self = mysqli_query($db, "SELECT * FROM phaos_races ORDER BY name ASC");
	print ("<table border=0 cellpadding=10 align='center' valign='top' width='90%'>
	<tr>");
	$line = 0;
    while ($row = mysqli_fetch_array($self)) {
        $id = $row["id"];
        $desc = $row["name"];
        $str = $row['str'];
        $dex = $row['dex'];
		$wis = $row['wis'];
        $vit = $row['con'];
		$stam_time = $row['stamina_regen_time'];
		$stam_rate = $row['stamina_regen_rate'];
		$heal_time = $row['healing_time'];
		$heal_rate = $row['healing_rate'];
		$special = $row['special_desc'];
		
		print ("<td align=center style=\"background-color: rgba(95, 95, 95, .7); padding:5px;\"><hr>
			<big><b><u>$row[name]</u></b></big><br>
			<b>Str:</b> $str<br>
			<b>Dex:</b> $dex<br>
			<b>Wis:</b> $wis<br>
			<b>Vit:</b> $vit<br>
			<b>Stam Regen/Time:</b><br>
			$stam_rate every $stam_time sec<br>			
			<b>Heal Regen/Time:</b><br>
			$heal_rate every $heal_time sec<br>	
			<b>Special Ability:</b><br>	
			$special<br>
		<hr>"); 
		$line ++;
		if($line==4){
			echo "</td></tr><tr>";
			$line = 0;
		}else{ 
			echo "</td>";
		}
} 	
	?>
	</table></td>
  </tr>
  <br>
  <br>
<table width="90%" border="1" cellspacing="0" cellpadding="3" align="center">
  <tr style=background:#006600;> 
    <td colspan="2"> 
      <div align="center"><b>WoP - Character Classes</b></div>
    </td>
  </tr>
   <tr> 
	<?php
	$self=mysqli_query($db, "SELECT * FROM phaos_classes ORDER BY name ASC");
	print ("<table border=0 cellpadding=10 align='center' valign='top' width='90%'>
	<tr>");
	$line = 0;
    while ($row = mysqli_fetch_array($self)) {
        $id = $row["id"];
        $desc = $row["name"];
        $fight = $row['fight'];
        $defense = $row['defence'];
		//$hands = $row['weaponless'];
		
		print ("<td align=center style=\"background-color: rgba(95, 95, 95, .7); padding:5px;\"><hr>
			<big><b><u>$row[name]</u></b></big><br>
			<b>Fight:</b> $fight<br>
			<b>Defense:</b> $defense<br>	
		<hr>"); 
		$line ++;
		if($line==4){
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
