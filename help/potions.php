<?php 
include("../config.php");
?>
<html>
<head>
<title>WoP - Potions</title>
<link href="../styles/phaos.css" rel="stylesheet" type="text/css">
</head>
<body>
<table width="90%" border="1" cellspacing="0" cellpadding="3" align="center">
  <tr style=background:#006600;> 
    <td colspan="2"> 
      <div align="center"><b>WoP - Potions List</b></div>
    </td>
  </tr>

  <tr> 
	<?php
	$self = mysqli_query($db, "SELECT * FROM phaos_potion ORDER BY id ASC");
	print ("<table border=0 cellpadding=10 align='center' valign='top'>
	<tr>");
	$line = 0;
    while ($row = mysqli_fetch_array($self)) {
        //$id = $row["id"];
        $desc = $row["name"];
        if ($row["heal_amount"]==9999) {$heal_amount = "MAX";} else {$heal_amount = $row["heal_amount"];}
        if ($row['buy_price']==0) {$buy = "<i>Find Only</i>";} else {$buy = $row['buy_price'];} //kaspir edited for rejuv potions, they are only found.
		$image = $row['image_path'];
        //$sell = $row['sell_price'];
		
		print ("<td align=center style=\"background-color: rgba(95, 95, 95, .7); padding:5px;\"><hr>
				<b>$desc</b><br>
				<img style='padding-top:5px;width:35px;height:auto;' align='center' src='/$image'><br>
				<b>Heals $heal_amount hp</b><br>
				Price: $buy<br>
		<hr>"); //Sell Price: $sell gp<br>
		$line ++;
		if($line==3){
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
