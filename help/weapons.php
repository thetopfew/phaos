<?php 
include("../config.php");
?>
<html>
<head>
<title>WoP - Weapons</title>
<link href="../styles/phaos.css" rel="stylesheet" type="text/css">
</head>
<body>
<table width="90%" border="1" cellspacing="0" cellpadding="3" align="center">
  <tr style=background:#006600;> 
    <td colspan="2"> 
      <div align="center"><b>WoP - Weapons List</b></div>
    </td>
  </tr>
<center><a href="character.php"><?php echo $lang_added["ad_all"]; ?></a> 
&nbsp;|&nbsp;<a href="/help.php?id=1/weapons.php?act=$weap_type#club"><?php echo "SORT (coming soon)" ?>&nbsp;|&nbsp;</a></center><br>
<br>
  <tr> 
	<?php
//$weap_type=mysqli_query($db, "SELECT * FROM phaos_weapons WHERE name LIKE Club ORDER BY id ASC");	

	
	$self = mysqli_query($db, "SELECT * FROM phaos_weapons ORDER BY id ASC");
	print ("<table border=0 cellpadding=10 align='center' valign='top' width='90%'>
	<tr>");
	$line = 0;
    while ($row = mysqli_fetch_array($self)) {
        //$id = $row["id"];
        $desc = $row["name"];
        $min = $row['min_damage'];
        $max = $row['max_damage'];
        $buy = $row['buy_price'];
		$image = $row['image_path'];
        //$sell = $row['sell_price'];
		
		//if (strpos($desc, 'Club') !== false) {
			//$desc = //Ill finish this later...
			print "<td align=left valign=top>";
				//print "<input type='button' title=\"$lang_char[sell_pr] $sell_price gold\" onClick=\"parent.location='character.php?item_id=$item_id&item_type=$item_type&id=$id&sell_id=Y'\" value='$lang_char[sell]'";
		print ("<td align=center style=\"background-color: rgba(95, 95, 95, .7); padding:5px;\"><hr>
				<b>$desc</b><br>
				<img style='padding-top:5px;width:35px;height:auto;' align='center' src='/$image'><br>
				<b>Dam: $min-$max</b><br>
				Price: $buy<br>
		<hr>"); //Sell Price: $sell gp<br>
		$line ++;
		if($line==8){
			echo "</td></tr><tr>";
			$line = 0;
		} else { 
				echo "</td>";
			}
		//}
	}
	 	
	?>
	</table></td>
  </tr>
<br>

</table>
</body>
</html>
