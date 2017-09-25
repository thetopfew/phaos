<?php 
include("../config.php");
include("aup.php");
?>
<html>
<head>
<title>WoP Admin Panel - Create Spell</title>
<link href="../styles/phaos.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php
if($addme == "yes")
{
	IF ($damage_mess) $damage_mess = 1;
	mysqli_query($db, "INSERT INTO phaos_spells_items (name, min_damage, max_damage, buy_price, sell_price, image_path, req_skill, damage_mess) VALUES ('$name','$min_damage', '$max_damage', '$buy_price', '$sell_price', '$image_path', '$req_skill', '$damage_mess')");
	?>
	<table width="100%" height="100%" border="0" cellpadding="2" cellspacing="0">
	  <tr>
	  <td align=center valign=middle height="100%" width="100%">
		  <table border="0" cellspacing="1" cellpadding="0">
		  <tr>
		  <td colspan="2" align=center>
		  	<b>New Spell has been created!</b>
		  </td>
		  </tr>
		  <tr>
		  <td colspan="2" align=center>
		  	<br><form><input type="button" onClick="parent.location='admin_create_Spell.php'" value="Create more Spells">
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
	<form action="admin_create_Spell.php?addme=yes" method=post>
	<table width="600" border="1" cellspacing="0" cellpadding="3" align="center">
	  <tr style=background:#006600;> 
		<td colspan="2"> 
		  <div align="center"><b>Create Spell</b></div>
		</td>
	  </tr>
	  <tr> 
		<td width="50%"><b><font color="#FFFFFF">Name</font></b></td>
		<td width="50%"> 
		  <input type="text" name="name">
		</td>
	  </tr>
	  <tr> 
		<td width="50%"><b><font color="#FFFFFF">Min Damage</font></b></td>
		<td width="50%"> 
		  <input type="text" name="min_damage">
		</td>
	  </tr>
	  <tr> 
		<td width="50%"><b><font color="#FFFFFF">Max Damage</font></b></td>
		<td width="50%"> 
		  <input type="text" name="max_damage">
		</td>
	  </tr>
	  <tr> 
		<td width="50%"><b><font color="#FFFFFF">Vendor Price</font></b></td>
		<td width="50%"> 
		  <input type="text" name="buy_price">
		</td>
	  </tr>
	  <tr> 
		<td width="50%"><b><font color="#FFFFFF">Sell Price</font></b></td>
		<td width="50%"> 
		  <input type="text" name="sell_price">
		</td>
	  </tr>
	  <tr> 
		<td width="50%"><b><font color="#FFFFFF">Select Image</font></b></td>
		<td width="50%"> 
	<table border=0 cellpadding=0 cellspacing=0>
	  <tr>
	   <td align=center>
	   <img src="../images/icons/spells/magic.gif" title="Magic">
	   <input CHECKED type="radio" name="image_path" value="images/icons/spells/magic.gif">
	   </td>
	   <td align=center>
	   <img src="../images/icons/spells/misc.gif" title="Misc">
	   <input type="radio" name="image_path" value="images/icons/spells/misc.gif">
	   </td>
	   <td align=center>
	   <img src="../images/icons/spells/fire.gif" title="Fire">
	   <input type="radio" name="image_path" value="images/icons/spells/fire.gif">
	   </td>
	   <td align=center>
	   <img src="../images/icons/spells/ice.gif" title="Ice">
	   <input type="radio" name="image_path" value="images/icons/spells/ice.gif">
	   </td>
	 </tr>
	 <tr>
	   <td align=center>
	   <img src="../images/icons/spells/light.gif" title="Lighting">
	   <input type="radio" name="image_path" value="images/icons/spells/light.gif">
	   </td>
	   <td align=center>
	   <img src="../images/icons/spells/energy.gif" title="Energy">
	   <input type="radio" name="image_path" value="images/icons/spells/energy.gif">
	   </td>
	   <td align=center>
	   <img src="../images/icons/spells/endgame.gif" title="End Game">
	   <input type="radio" name="image_path" value="images/icons/spells/endgame.gif">
	   </td>
	   <td align=center>
	   </td>
	 </tr>
	</table> 
		</td>
	  </tr>
	  <tr> 
		<td width="50%"><b><font color="#FFFFFF">Required Wisdom</font></b></td>
		<td width="50%"> 
		  <input type="text" name="req_skill">
		</td>
	  </tr>
	  <tr> 
		<td width="50%"><b><font color="#FFFFFF">Mass Effect?</font></b></td>
		<td width="50%"> 
		  <input type="checkbox" name="damage_mess">
		</td>
	  </tr>
	  <tr> 
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
