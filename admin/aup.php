<?php
include_once "../config.php";
include_once "../include_lang.php";

$admin_auth = false; // Assume admin is not authenticated

$row = null;
if ( (@$PHP_ADMIN_USER) && ((@$PHP_ADMIN_PW)||(@$PHP_ADMIN_MD5PW) )) {

    if(@$PHP_ADMIN_MD5PW){
	   $result = mysqli_query($db, "SELECT * FROM phaos_admin WHERE admin_user = '$PHP_ADMIN_USER' AND admin_pass = '$PHP_ADMIN_MD5PW'");
	   $row = mysqli_fetch_array($result);
    }

    if(!@$row){
       $PHP_ADMIN_MD5PW = md5(@$PHP_ADMIN_PW);
	   $result = mysqli_query($db, "SELECT * FROM phaos_admin WHERE admin_user = '$PHP_ADMIN_USER' AND admin_pass = '$PHP_ADMIN_MD5PW'");
	   $row = mysqli_fetch_array($result);
    }

    if ( $row ) {
        // A matching row was found - the admin is authenticated.
        $admin_auth = true;

        setcookie("PHP_ADMIN_USER",$PHP_ADMIN_USER,time()+1728000); // ( REMEMBERS USER NAME FOR 20 DAYS )
        setcookie("PHP_ADMIN_MD5PW",$PHP_ADMIN_MD5PW,time()+172800); // ( REMEMBERS USER PASSWORD FOR 2 DAYS )
    }

    setcookie("PHP_ADMIN_PW","",time()-3600); // remove old cookie
}


if ( !$admin_auth ) { ?>

<html>
<head>
	<title>WoP Administration Panel</title>
	<link href="../styles/phaos.css" rel="stylesheet">
	
	<link rel="shortcut icon" href="../favicon.ico" type="image/x-icon" />
	<link rel="icon" href="../favicon.ico" type="image/x-icon" />
</head>
<body>
	<div id="login-box">
		<div class="center"> <br><br><br>
			<form method="post" action="<?php echo basename($_SERVER['PHP_SELF']); ?>">
				<img src="../images/login_logo.gif" alt="World of Phaos">	
				<p class="user-login">
					<?php echo $lang_aup["user"]; ?><input name="PHP_ADMIN_USER" type="text" maxlength="20" placeholder="Phaos Admins Only!">
				</p>
				<p class="user-pass">
					<?php echo $lang_aup["pass"]; ?><input name="PHP_ADMIN_PW" type="password">
				</p>
				<input class="button" type="submit" value="<?php echo $lang_aup["login"]; ?>">
			</form>
			
		</div>
	</div>
	
	<div class="center">
		<form action="../index.php" method="get">
			<input class="button" type="submit" value="Leave ADMIN Area"></input>
		</form> 
		<hr class="quartersize">
		<br><?php echo $version;?>
		<br><?php echo $lang_aup["phpversion"], phpversion(); ?><br><br>
		<hr class="quartersize">
		Copyright 2004-<?php echo date("Y",time()); ?><br><br>
		<a href="http://worldofphaos.com/index.php?site=online_rpg&id=7" target="_blank"><?php echo $lang_aup["license"]; ?></a>
	</div>
	
</body>
</html>

<?php
exit;
}
?>
