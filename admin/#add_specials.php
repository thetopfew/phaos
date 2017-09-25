<?php 
include("../config.php");
include("aup.php");
?>
<html>
<head>
<title>WoP Admin Panel - Add Specials</title>
<link rel="stylesheet" type="text/css" href="../styles/phaos.css">
</head>
<body>
<table width="600" border="1" cellspacing="0" cellpadding="3" align="center">
  <tr style=background:#006600;> 
    <td colspan="2"> 
      <div align="center"><b>Check Specials</b></div>
    </td>
  </tr>
  <tr> 
    <td><font color="#FFFFFF">
	<?php
	for ($x=0; $x<=5; $x++){
		$randqry = mysqli_query($db, "SELECT * FROM phaos_locations");
		$rndmax = mysqli_num_rows($randqry);

		$res = mysqli_query($db, "SELECT * FROM phaos_specials WHERE type='random' ORDER BY rand() LIMIT 1");
		$rand_id = rand(1,$rndmax);
		$specialid = mysqli_fetch_array($res);
		echo "Selected special_ID:".$specialid["id"];

		$sql = "UPDATE phaos_locations set special='".$specialid["id"]."' WHERE id=$rand_id";
		mysqli_query($db, $sql);
		if (!mysqli_query($db, $sql)){
			echo mysqli_error();echo "an error has occured<br>";
		}
		else {
			echo " - Area $rand_id has been updated<br>";
		}
	}
?>
	<br><br><center><form><input type='button' onClick="parent.location='admin_map_Check_Specials.php'" value='Return to overview menu'>
	<input type='button' onClick="parent.location='index.php'" value='Back to Admin Panel'></form></center>
	</font></td>
  </tr>
</table>
</body>
</html>
