<?php 
include("../config.php");
include("aup.php");
?>
<html>
<head>
<title>WoP Admin Panel - Create Opponent</title>
<link href="../styles/phaos.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php
if($addme == "yes")
{
	$location = 0; //added by kaspir
	$class = "";
	//$spawn = mysqli_query($db, "SELECT race FROM phaos_opponents WHERE race != "" ");
	mysqli_query($db, "INSERT INTO phaos_opponents (name, hit_points, race, class, min_damage, max_damage, AC, xp_given, gold_given, image_path, location) 
		VALUES ('$name', '$hit_points', '$race', '$class', '$min_damage', '$max_damage', '$AC', '$xp_given', '$gold_given', 'images/monster/$image_path', '$location')");
?>
	<table width="100%" height="100%" border="0" cellpadding="2" cellspacing="0">
	  <tr>
	  <td align=center valign=middle height="100%" width="100%">
		  <table border="0" cellspacing="1" cellpadding="0">
		  <tr>
		  <td colspan="2" align=center>
		  	<b>New Opponent has been created!</b>
		  </td>
		  </tr>
		  <tr>
		  <td colspan="2" align=center>
		  	<br><form><input type="button" onClick="parent.location='admin_create_Opponent.php'" value="Create more Opponents">
		  	<input type="button" onClick="parent.location='index.php'" value="Back to Admin Panel"></form>
		  </td>
		  </tr>
		  </table>
		</td>
		</tr>
	</table>
	<?php
}
else
{
	?>
	<form action="admin_create_Opponent.php?addme=yes" method=post>
	  <table width="600" border="1" cellspacing="0" cellpadding="3" align="center">
		<tr style=background:#006600;> 
		  <td colspan="2"> 
			<div align="center"><b>Create New Opponent<br><small>(HP, damages, AC, XP & gold should all be based on a level 1)</small></b></div>
		  </td>
		</tr>
		<tr> 
		  <td width="50%"><b><font color="#FFFFFF">Name</font></b></td>
		  <td width="50%"> <b><font color="#FFFFFF">
			<input type="text" name="name">
			</font></b></td>
		</tr>
		<tr> 
		  <td width="50%"><b><font color="#FFFFFF">Race <small>(determines spawn location)</small></font></b></td>
		  <td width="50%"><b><font color="#FFFFFF">
			<select name="race">
		  <option value="">Select</option>
		  <?php
		  $result = mysqli_query ($db, "SELECT race FROM phaos_opponents WHERE race !='' GROUP BY race HAVING COUNT(*) > 1 ORDER BY race ASC");
		  if ($row = mysqli_fetch_array($result)) {
			  do {
				  $race = $row["race"];
				  print ("<option value=\"$race\">$race</option>");
			  } 
			  while ($row = mysqli_fetch_array($result));
		  }
		  ?>
		  </select>
			</font></b></td>
		</tr>
		<tr> 
		  <td width="50%"><b><font color="#FFFFFF">Hit Points</font></b></td>
		  <td width="50%"> <b><font color="#FFFFFF">
			<input type="text" name="hit_points">
			</font></b></td>
		</tr>
		<tr> 
		  <td width="50%"><b><font color="#FFFFFF">Min Damage</font></b></td>
		  <td width="50%"> <b><font color="#FFFFFF">
			<input type="text" name="min_damage">
			</font></b></td>
		</tr>
		<tr> 
		  <td width="50%"><b><font color="#FFFFFF">Max Damage</font></b></td>
		  <td width="50%"> <b><font color="#FFFFFF">
			<input type="text" name="max_damage">
			</font></b></td>
		</tr>
		<tr> 
		  <td width="50%"><b><font color="#FFFFFF">Armor Class (AC)</font></b></td>
		  <td width="50%"><b><font color="#FFFFFF">
			<input type="text" name="AC">
			</font></b></td>
		</tr>
		<tr> 
		  <td width="50%"><b><font color="#FFFFFF">XP Given</font></b></td>
		  <td width="50%"><b><font color="#FFFFFF">
			<input type="text" name="xp_given">
			</font></b></td>
		</tr>
		<tr> 
		  <td width="50%"><b><font color="#FFFFFF">Gold Given</font></b></td>
		  <td width="50%"><b><font color="#FFFFFF">
			<input type="text" name="gold_given">
			</font></b></td>
		</tr>
		<tr> 
		  <td width="50%"><b><font color="#FFFFFF">Image Filename</font></b></td>
		  <td width="50%"><b><font color="#FFFFFF">
			<input type="text" name="image_path" value="forest_troll.gif">
			</font></b></td>
		</tr>
		
	   <tr> 
		  <td colspan="2"> 
			<div align="center"> 
			  <input type="submit" name="Submit" value="Create">
			  <input type='button' onClick="parent.location='index.php'" value='Back to Admin Panel'>
			</div>
		  </td>
		</tr>
	  </table>
	</form>
<?php
}
?>
</body>
</html>
