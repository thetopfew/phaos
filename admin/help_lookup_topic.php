<?php
include "aup.php";
?>
<html>
<head>
<title>WoP Admin Panel - Lookup Help Topic</title>
<link rel=stylesheet type="text/css" href="../styles/phaos.css">
</head>
<body bgcolor="#000000" text="#FFFFFF" link="#FFFFFF" alink="#FFFFFF" vlink="#FFFFFF">
<br><div align=center>
<hr>
<img src="../images/top_logo.png">
<br>
<font size="4">Help System Admin</font>
<br>
<a href="index.php">[Back to Admin Panel]</a>
<hr>
<?php
$db = mysqli_connect("$game_host", "$game_dbuser", "$game_dbpasswd", "$game_db") or die ("Unable to connect to MySQL server.");
//$db = mysqli_select_db($db, "$game_db") or die ("Unable to select requested database.");

print  "<table border=0 cellspacing=0 cellpadding=0 class=default>
	<tr class=trhead>
	<td>ID #:&nbsp;</td>
	<td>Subject:&nbsp;</td>
	<td>File Name:&nbsp;</td>
	</tr>";

if($number == "") {$number = 0;}

$result = mysqli_query($db, "SELECT id FROM phaos_help WHERE $hlpfield LIKE '$hlpvalue%'");
$count_rows = mysqli_num_rows($result);

$result = mysqli_query($db, "SELECT title,file,id FROM phaos_help WHERE $hlpfield LIKE '$hlpvalue%' ORDER BY title ASC limit $number,30");
if ($row = mysqli_fetch_array($result)) {
	do {
		print "<form action=\"admin_help_modify_topic.php\">";
		print "<tr><td><b>";
		print "<input type=\"hidden\" name=\"topic\" value=\"$row[id]\"><input type=submit class=lookup_button value=\"&nbsp $row[id] &nbsp\">";
		print "</b>&nbsp;</td><td><b>";
		print htmlspecialchars(stripslashes($row[title]));
		print "</b>&nbsp;</td><td><b>";
		print htmlspecialchars(stripslashes($row[file]));
		print "</b>&nbsp;</td></tr></form>";
	} while($row = mysqli_fetch_array($result));
} else {print "<tr><td colspan=7 align=center><br>Sorry, no records were found!</td></tr>";}

print ("</table>");
$number_prev = $number-30;
$number_next = $number+30;
?> 
<p>
<?php
if($number != '0') {
	?>
	<a href="<?php print $PHP_SELF; ?>?number=<?php print $number_prev; ?>&hlpfield=<?php print $hlpfield; ?>&value=<?php print $hlpvalue; ?>">Previous</a>
	&nbsp;
	<?php
}
if($count_rows >= $number_next) {
	?>
	<a href="<?php print $PHP_SELF; ?>?number=<?php print $number_next; ?>&hlpfield=<?php print $hlpfield; ?>&value=<?php print $hlpvalue; ?>">Next</a>
	<?php
}
?>
<p>
<a href="admin_help_Modify_Topic.php">Back</a>
</div>
</body>
</html>
