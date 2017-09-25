<?php 
include("../config.php");
?>
<html>
<head>
<title>WoP - Spells</title>
<link href="../styles/phaos.css" rel="stylesheet" type="text/css">
</head>
<body>
<table width="90%" border="1" cellspacing="0" cellpadding="3" align="center">
  <tr style=background:#006600;> 
    <td colspan="2"> 
      <div align="center"><b>WoP - Spells List</b></div>
    </td>
  </tr>

  <tr> 
	<?php
	$self = mysqli_query($db, "SELECT * FROM phaos_spells_items ORDER BY id ASC");
	print ("<table border=0 cellpadding=20 align='center' valign='top'>
	<tr>");
	$line = 0;
    while ($row = mysqli_fetch_array($self)) {
        //$id = $row["id"];
        $desc = $row["name"];
        $min = $row['min_damage'];
        $max = $row['max_damage'];
        $buy = $row['buy_price'];
		$image = $row['image_path'];
		$req_wis = $row['req_skill'];
		$mess = $row['damage_mess'];
        //$sell = $row['sell_price'];
		
		if($mess == 0){ $mess = "[Single Effect]";} else {$mess = "[Mass Effect]";}
		print ("<td align=center style=\"background-color: rgba(95, 95, 95, .7); padding:5px;\"><hr>
				<b>$desc</b><br>
				<img style='padding-top:5px;width:35px;height:auto;' align='center' src='/$image'><br>
				<b>Dam: $min-$max</b><br>
				$mess<br>
				Req Wis: <b>$req_wis</b><br>
				Price: $buy<br>
		<hr>"); //Sell Price: $sell gp<br>
		$line ++;
		if($line==5){
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
