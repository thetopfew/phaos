<?php 
include("../config.php");
?>
<html>
<head>
<title>WoP - Shields</title>
<link href="../styles/phaos.css" rel="stylesheet" type="text/css">
</head>
<body>
<table width="90%" border="1" cellspacing="0" cellpadding="3" align="center">
  <tr style=background:#006600;> 
    <td colspan="2"> 
      <div align="center"><b>WoP - Shields List</b></div>
    </td>
  </tr>

  <tr> 
	<?php
	$self = mysqli_query($db, "SELECT * FROM phaos_shields ORDER BY buy_price ASC");
	print ("<table border=0 cellpadding=10 align='center' valign='top' width='90%'>
	<tr>");
	$line = 0;
    while ($row = mysqli_fetch_array($self)) {
        //$id = $row["id"];
        $desc = $row["name"];
        $ac = $row['armor_class'];
        $buy = $row['buy_price'];
		$image = $row['image_path'];
        //$sell = $row['sell_price'];

		print ("<td align=center style=\"background-color: rgba(95, 95, 95, .7); padding:5px;\"><hr>
			<b>$row[name]</b><br>
			<img style='padding-top:5px;width:35px;height:auto;' src='/$image'><br>
			<b>AC: +$ac</b><br>
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
