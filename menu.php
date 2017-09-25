<table border="1" bordercolor="#003300" width="100%">
	<tr>
		<td class="center" width="15%">
			<a href="character.php"><?php echo $lang_menu["char"]; ?></a>
			<br>
		</td>
		<td class="center" width="15%">
			<a href="town.php"><?php echo $lang_menu["expl"]; ?></a>
		</td>
		<td class="center" width="15%">
			<a href="travel.php"><?php echo $lang_menu["trav"]; ?></a>
		</td>
		<td class="center" width="15%">
			<?php
			echo "<a href='prefs.php?username=$PHP_PHAOS_USER'>".$lang_menu['prefs']."</a>";
			?>
			<br>
			<a href="logout.php" ><?php echo $lang_menu["logo"].' ('.$PHP_PHAOS_USER.')'; ?></a>
		</td>
	</tr>
</table>
