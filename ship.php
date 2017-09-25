<?php require_once "header.php"; ?>
<!--
you create a ship.php page with this code, you insert a shop called ship where you want your port so when you explore you can see the it.
In location you should add (Port) to the end of the name so you know where other ports are
-->
<?php
// If no character created
if ($character->location == "") {
	jsChangeLocation("create_character.php", 0);
}

$character = new character($PHP_PHAOS_CHARID);

function travel ($chid, $gol, $locid, $price) {
	global $db, $lang_shop;
	
	if ($gol >= $price) {
		$gol = $gol - $price;

		$db->query("UPDATE phaos_characters SET location='$locid', gold='$gol' WHERE id = '$chid'");

		//header ('Location: travel.php');
		?>
		<script>
			self.location="town.php"
		</script>
		<?php
	} else {
		echo "<div class='center'><h3 class='b msgbox'>$lang_shop[sorry]</h3></div>";
	}
}

if (@$_POST['travel']) {
	$id = $_POST['travel'];
	$price = (int)($id / 10); // Ticket Price per location id.
	
	travel($character->id, $character->gold, $id, $price);
}

?>
<table border=1 cellspacing=0 cellpadding=3 align=center>
	<tr style=background:#006600;>
		<td align="center" colspan=5>
			<h2 class="b"><?php echo $lang_ship["welcome"];?></h2>
		</td>
	</tr>
	<tr>
	<?php
	$line = 0;
	$result = $db->query("SELECT location FROM phaos_buildings WHERE name='Stable' OR name='Ship Travel'");
	while ($row = $result->fetch_assoc()) {
		$loc = $row["location"];
		$res = $db->query("SELECT id,name FROM phaos_locations WHERE id='$loc' AND id != '".$character->location."'");
		while ($row = $res->fetch_assoc()) {
			$id = $row["id"];
			$name = $row["name"];
			$price = (int)($loc / 10); // Ticket Price per location id.
			
			if ($id != $character->location) {
				echo "<td class='center'><form action='ship.php' method='post'>
					<input type='hidden' name='travel' value='$id'>
					<input class='button' type='submit' value='$name'>
					</form>";
				echo "<span class='b gold'>$price</span> $lang_shop[gp]";
				
				$line ++;
				if ($line == 5) {
					echo "</td></tr>";
					$line = 0;
				} else { 
					echo "</td>";
				}
			}
		}
	}
	?>
</table>

<div class="center">
	<form style="padding:10px;"><input class="button" type="button" value="<?php echo $lang_backtown;?>" onClick="location='town.php';this.value='<?php echo $lang_leaving;?>'"></form>
</div>
<br>
<?php require_once "footer.php";
