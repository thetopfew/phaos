<?php 
include("../config.php");
include("aup.php");
?>
<html>
<head>
<title>WoP Admin Panel - Check Treasures</title>
<link href="../styles/phaos.css" rel="stylesheet" type="text/css">
</head>

<body>
<table width="600" border="1" cellspacing="0" cellpadding="3" align="center">
  <tr style=background:#006600;> 
    <td colspan="2"> 
		<div align="center"><b>Check Treasures</b></div>
    </td>
  </tr>
  <tr>
  	<td colspan=2 align=center>
  		<form><input type='button' onClick="parent.location='index.php'" value='Back to Admin Panel'></form>
  	</td>
  </tr>
  <tr> 
    <td><font color="#FFFFFF">
	
	<?php //rewritten by kaspir
	$self = mysqli_query($db, "SELECT * FROM phaos_ground");
	$count = mysqli_num_rows($self);
	echo "<br>There are a total of $count treasures existing in the entire world!<br>";
	
	echo "<br>The following have treasures:<br>[Location #] - [Item ID#] - [Item Type] - [Count]<br><hr>";
	
	while ($row = mysqli_fetch_array($self)) {
		$loc = $row["location"];
		$desc = $row["item_id"];
		$type = $row["item_type"];
		$num = $row["number"];
		
		echo "<b>$loc - $desc - $type - $num</b><br>";
	} 
	?>
	
	</font></td>
  </tr>
</table>
</body>
</html>
