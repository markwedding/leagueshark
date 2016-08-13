<?php
	ob_start();
	session_start();
	$filepath = "../";

	include($filepath . "autoload.php");
	autoload($filepath);

	include($filepath . "session-status.php");

	#indicate whether the account is still in the creation phase
	if(isset($_POST['ac']) or isset($_GET['ac'])) {
		$ac = true;
		$formaction = $filepath . 'leagues.php';
	} else {
		$ac = false;
		$formaction = 'payment.php';
	}
?>

<!DOCTYPE html>
<html>

<?php
	if($ac) {
		head("LeagueShark: Sign Up",$filepath);
	} else {
		head("Account: Payment",$filepath);
	}
?>
<body>
	<div class="container-fluid">
		<?php if($ac) {
			navbar('plain',$filepath,'#','');
		} else {
			navbar('school',$filepath,$filepath . 'index.php',$schoolname);
		}?>
	</div>
	<div class="container page-content">
		<?php if($ac) {?>

		<div class="alert alert-dismissible alert-info">
  			<button type="button" class="close" data-dismiss="alert">
				<span aria-hidden="true">&times;</span>
				<span class="sr-only">Close</span>
			</button>
  			<p>Your account is almost ready! Please enter your PayPal account ID. You will be redirected to PayPal to complete your payment.</p>
		</div>

		<?php } else {
			schoolpills($schoolname);
		}?>
		<h2><strong>Payment</strong></h2>
		<br>
	<?php
	//Start PayPal Checkout Button<br>
	$pp_checkout_btn = '';
	$pp_checkout_btn .= '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
    <input type="hidden" name="cmd" value="_s-xclick">
	<input type="hidden" name="hosted_button_id" value="CSPRNHH9KYCGG">
    <input type="hidden" name="business" value="ekoomson101@gmail.com">';


		// Dynamic Checkout Btn Assembly
 $pp_checkout_btn .= '<input type="hidden">

	<input type="hidden" name="notify_url" value="https://www.leagueshark.net/storescripts/my_ipn.php">
	<input type="hidden" name="return" value="https://www.leagueshark.net/checkout_complete.php">
	<input type="hidden" name="rm" value="2">
	<input type="hidden" name="cbt" value="Return to The Store">
	<input type="hidden" name="cancel_return" value="https://www.leagueshark.net/paypal_cancel.php">
	<input type="hidden" name="lc" value="US">
	<input type="hidden" name="currency_code" value="USD">
	<table>
	<tr><td>You can be billed up to $400.00 USD</td></tr>
	</table>
	<table>
	</table>
	<table><tr><td align=center><i>Sign up for</i></td></tr><tr><td>
	<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_auto_billing_LG.gif" style="border-style: none;" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!"></td></tr></table>
	<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
	</form>';
?>
<?php echo $pp_checkout_btn; ?>
    <br />
    </div>
    <br />
</body>
</html>
