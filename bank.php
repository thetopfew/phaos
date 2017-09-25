<?php
require_once "header.php";
require_once "shop_functions.php"; // also loads class_character.php  (USE $this->character($gold) or something?
require_once "bank_functions.php";

$character = new character($PHP_PHAOS_CHARID);
shop_valid($character->location, $shop_id);
$reload = false;

// Set bank withdrawl fee
$bank_fee = .10; // 10%

// Return percentage function
$percent = new NumberFormatter('en_US', NumberFormatter::PERCENT);

// Get players gold amounts.
$res = $db->query("SELECT gold,bankgold FROM phaos_characters WHERE id='$PHP_PHAOS_CHARID'");
$row = $res->fetch_assoc();

if(isset($_POST['transfer'])) {
	// Process Bank
	switch($_POST['R1']) {
		case "deposit":
		if ($_POST['amount'] < 10) {
			echo "<div class='center'><h3 class='b red msgbox'>$lang_bank[e_dep_amt]</h3></div>";
		} else if ($_POST['amount'] > $row['gold']) {
			echo "<div class='center'><h3 class='b red msgbox'>$lang_bank[e_not_gp]</h3>";
		} else {
			$newgold = $row['gold'] - $_POST['amount'];
			$newbank = $row['bankgold'] + $_POST['amount'];
			$db->query("UPDATE phaos_characters SET gold='$newgold', bankgold='$newbank' WHERE id='$PHP_PHAOS_CHARID'");
			echo "<div class='center'><h3 class='b msgbox'>$lang_bank[u_dep1] <span class='gold'>".($_POST['amount'])."</span> $lang_gold $lang_bank[u_dep2]</h3></div>";
			$reload = true;
		}
		break;
		case "withdraw":
		if ($_POST['amount'] < 10) {
			echo "<div class='center'><h3 class='b red msgbox'></h3></div>";
		} else if ($_POST['amount'] > $row['bankgold']) {
			echo "<div class='center'><h3 class='b red msgbox'>$lang_bank[e_not_gp]</h3></div>";
		} else {
			$newgold = $row['gold'] + ($_POST['amount'] - ($_POST['amount'] * $bank_fee));
			$newbank = $row['bankgold'] - $_POST['amount'];
			$db->query("UPDATE phaos_characters SET gold='$newgold', bankgold='$newbank' WHERE id='$PHP_PHAOS_CHARID'");
			echo "<div class='center'><h3 class='b msgbox'>$lang_bank[u_wdl1] <span class='gold'>".($_POST['amount'] - ($_POST['amount'] * $bank_fee))."</span> $lang_bank[u_wdl2]</h3></div>";
			$reload = true;
		}
		break;
	}
}

if(isset($_POST['exchange'])) {
	// Process Forum Exchange
	switch($_POST['R1']) {
		case "deposit":
		if ($_POST['xferamount'] < 0.01) {
			echo "<div class='center'><h3 class='b red msgbox'>$lang_bank[e_amount]</h3></div>";
		} else {
			$amount = ($_POST['xferamount']);
			xfer_deposit($amount);			
			$reload = true;
		}
		break;
		case "withdraw":
		if ($_POST['xferamount'] < 0.01) {
			echo "<div class='center'><h3 class='b red msgbox'>$lang_bank[e_amount]</h3></div>";
		} else {
			$amount = ($_POST['xferamount']);
			xfer_withdraw($amount);
			$reload = true;
		}
		break;
	}
}

if ($reload) {
	$reload = false; // Reset status
	jsChangeLocation("bank.php?shop_id=$shop_id", 2);
}
?>

<div class="center">
	<form method="POST" action="bank.php?shop_id=<?php echo $shop_id;?>">
		<h2 class="b"><?php echo $lang_bank["welcome"];?></h2><br>
		<p class="big"><?php echo $lang_gold, $lang_bank["acct_gold"];?><span class="b gold"><?php echo $row["bankgold"];?></span><img class="bottom" src="images/icons/gold_side.gif"></p>
		<div>
			<input type="radio" value="deposit" checked name="R1"><?php echo $lang_bank["deposit"];?>
			<input type="radio" value="withdraw" name="R1"><?php echo $lang_bank["withdraw"];?>
		</div>
		<input type="text" name="amount" size="10" placeholder="<?php echo $lang_bank["ent_amt"];?>">
		<input type="hidden" name="shop_id" value="<?php echo $shop_id;?>">
		<input class="button" type="submit" value="<?php echo $lang_bank["xfer"];?>" name="transfer">
	</form>
</div>

<div class="center msgbox">
	<span class="u"><?php echo $lang_bank["term_title"];?> </span><br>
	<p class="small"><?php echo $lang_bank["bankterms1"];?><span class="b red"><?php print $percent->format($bank_fee);?></span><?php echo $lang_bank["bankterms2"];?></p>
</div>

<br><hr><br>

<div class="center">
	<form method="POST" action="bank.php?shop_id=<?php echo $shop_id;?>">
		<h2 class="b"><?php echo "$lang_bank[forum]<br>$lang_bank[ex_booth]";?></h2><br>
		<p class="big"><?php echo $lang_bank["forum_coin"];?>: <span class="b gold"><?php echo getpoints();?></span><img class="middle" src="/images/icons/goldcoins.png"></p>
		<div>
			<input type="radio" value="deposit" checked name="R1"><?php echo $lang_bank["gp2gc"];?>
			<input type="radio" value="withdraw" name="R1"><?php echo $lang_bank["gc2gp"];?>
		</div>
		<input type="text" name="xferamount" size="10" placeholder="<?php echo $lang_bank["ent_amt"];?>">
		<input type="hidden" name="shop_id" value="<?php echo $shop_id;?>">
		<input class="button" type="submit" value="<?php echo $lang_bank["exchange"];?>" name="exchange">
	</form>
</div>

<div class="center fullsize msgbox">
	<span class="u"><?php echo $lang_bank["xchg_title"];?></span>
	<p class="small">
		<?php echo $lang_bank["exch_terms"];?>
	</p>
</div>

<br><hr><br>

<div class="center">
	<form style="padding:10px;"><input class="button" type="button" value="<?php echo $lang_backtown;?>" onClick="location='town.php';this.value='<?php echo $lang_leaving;?>'"></form>
</div>

<?php include "footer.php";