<?php
require_once "config.php";

if ($auth == false) { ?>

<!DOCTYPE html>
<html lang="<?php echo $lang;?>">
<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
	<title><?php echo $GAME_TITLE, $version;?></title>
	<meta name="application-name" content="<?php echo $GAME_TITLE;?>">
	<meta name="description" content="Play World of Phaos RPG in it’s newest edition! Making it’s glorious come back through a new developer — www.thetopfew.com." />
	<meta name="keywords" content="phaos,world of phaos,wop,mmorpg,browser game,rpg,online game" />
	<meta name="distribution" content="global" />
	<meta name="robots" content="index,follow" />
	<meta name="revisit-after" content="7 Days" />

	<!-- <link href="http://fonts.googleapis.com/css?family=Droid+Sans" rel="stylesheet" type="text/css"> USE LATER FOR MOBILE FONT -->
	
	<link rel="stylesheet" type="text/css" href="<?php echo auto_version('/styles/phaos.css'); ?>">
	<!-- <link rel="stylesheet" type="text/css" href="<?php #echo auto_version('/styles/responsive.css'); ?>" media="all and (max-width: 700px)" /> -->
	<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
	
	<!--<script> 
		document.onmousedown=disableclick;
		status="Right Click Disabled";
		function disableclick(event)
		{
		  if(event.button==2)
		   {
			 alert(status);
			 return false;    
		   }
		}
	</script>-->
</head>

<body id="page-center">
	<div class="center">
		<div id="login-box" class="center">
			<br><br><br>
			<form method="POST" action="index.php">
				<img src="images/login_logo.gif" alt="<?php echo $GAME_TITLE, $version; ?>"><br>				
				<label for="<?php echo @$_COOKIE['PHP_PHAOS_USER'];?>" id="user-login" class="b"><?php echo $lang_aup["user"]; ?></label>
				<input name="PHP_PHAOS_USER" type="text" maxlength="20" value="<?php echo @$_COOKIE['PHP_PHAOS_USER'];?>" placeholder="<?php echo $lang_aup["uselogin"]; ?>">
				<br><br>
				<label for="PHP_PHAOS_PW" id="user-pass" class="b"><?php echo $lang_aup["pass"]; ?></label>
				<input name="PHP_PHAOS_PW" type="password" maxlength="20">
				<div class="center">
					<input class="button" type="button" value="<?php echo $lang_aup["register"]; ?>" onClick="location='register.php'">
					<input class="button" type="submit" value="<?php echo $lang_aup["login"]; ?>" onClick="this.value='<?php echo $lang_aup["auth"];?>'"><br>
					<input class="button" type="button" value="<?php echo $lang_aup["resetpass"]; ?>" onClick="location='pass_lost.php'">
				</div>
			</form>
		</div>
		
		<hr class="quartersize">
		<a href="<?php echo $phpbb_url;?>/ucp.php?mode=register" title="<?php echo $phpbb_sitename;?>"><?php echo $lang_aup["no_account"]; ?></a><br><br>
		<a href="<?php echo $phpbb_url;?>/viewforum.php?f=4"><?php echo $lang_aup["leave"]; ?></a>
		<hr class="quartersize">
	</div>
	
	<div class="center"><br>
		<?php echo $version;?><br>
		<?php echo $lang_copyright;?> 2004-<?php echo date("Y",time()); ?><br><br>
		<a href="http://worldofphaos.com/index.php?site=online_rpg&id=7" target="_blank"><?php echo $lang_aup["license"]; ?></a><br>
		<br>
		<a href="<?php echo $phpbb_url;?>/feed.php"><img title="<?php echo $lang_aup["feeds"];?>" alt="<?php echo $lang_aup["feeds"];?>" src="/images/social_icons/rss-20.png"></a>
		<a href="https://plus.google.com/<?php echo $googleplus_id;?>"><img title="<?php echo $lang_aup["google"];?>" alt="<?php echo $lang_aup["google"];?>" src="/images/social_icons/googleplus.png"></a>
		<a href="https://twitter.com/<?php echo $twitter_id;?>"><img title="<?php echo $lang_aup["tweet"];?>" alt="<?php echo $lang_aup["tweet"];?>" src="/images/social_icons/twitter-20.png"></a>
	</div>

</body>
</html>
<?php exit; } ?>
