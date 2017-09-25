<?php
require_once "config.php";
?>
<html>
<head>
	<title><?php echo $lang_reg["sub"];?></title>
	<link rel="stylesheet" href="<?php echo auto_version('/styles/phaos.css'); ?>" type="text/css" />

	<script>
	function match_pw() {
		var themessage = "<?php echo $lang_reg["msg1"]; ?> ";
		/*
		if (document.form.username.value=="") {
			themessage = themessage + " - <?php echo $lang_reg["user"]; ?>";
		}
		if (document.form.email_address.value=="") {
			themessage = themessage + " - <?php echo $lang_reg["mail"]; ?>";
		}
		if (document.form.password_1.value=="") {
			themessage = themessage + " - <?php echo $lang_reg["pass1"]; ?>";
		}
		if (document.form.password_2.value=="") {
			themessage = themessage + " - <?php echo $lang_reg["pass2"]; ?>";
		}
		*/
		if (document.form.password_2.value != document.form.password_1.value) {
			themessage = themessage + " - <?php echo $lang_reg["err1"]; ?>";
		}
		if (themessage == "<?php echo $lang_reg["msg1"]; ?> ") {
			document.form.submit();
			return true;
		} else {
			alert(themessage);
			return false;
		}
	}
	</script>
	
</head>

<body>
	<div id="page-center">
		<img src="lang/<?php echo $lang ?>_images/register.png">
		
		<form method="post" name="form" action="register_complete.php" onSubmit="return match_pw();" >
		<table align="center" cellspacing=5>
			<tr>
				<td align="right">
					<?php echo $lang_reg["user"];?>
				</td>
				<td align="left">
					<input type="text" name="username" size="20" maxlength="20" placeholder="<?php echo $lang_mustuse;?>" required>
				</td>
			</tr>
			<tr>
				<td align="right">
					<?php echo $lang_reg["mail"];?>
				</td>
				<td align="left">
					<input type="email" name="email_address" size="20" maxlength="50" placeholder="<?php echo $lang_mustuse;?>" required >
				</td>
			</tr> 
			<tr>
				<td align="right">
					<?php echo $lang_reg["pass1"];?>
				</td>
				<td align="left">
					<input type="password" name="password_1" size="20" maxlength="20" required>
				</td>
			</tr>
			<tr>
				<td align="right">
					<?php echo $lang_reg["pass2"];?>
				</td>
				<td align="left">
					<input type="password" name="password_2" size="20" maxlength="20" required>
				</td>
			</tr>
		</table>
		<input class="button" type="submit" value="<?php echo $lang_reg["sub"];?>">
		</form>	
		
		<span class="msgbox halfsize"><?php echo $lang_reg["note"];?></span>				
		<br>
		<input class="button" type="button" value="<?php echo $lang_goback;?>" onClick="location='index.php';this.value='<?php echo $lang_leaving;?>'">
	</div>
</body>
</html>
