<?php
	ob_start();
	session_start();
	$filepath = "../";

	include($filepath . "autoload.php");
	autoload($filepath);

	include($filepath . "session-status.php");
	include($filepath . "session-player-redirect.php");

	if(isset($_GET['ac'])) {
		$ac = true;
	} else {
		$ac = false;
	}

	try {
		include($filepath . "connect-to-db.php");

		# Get data to populate availability table with slots
		$dsql = "SELECT DISTINCT DayOfWeek FROM slots";
		$dstatement = $pdo->prepare($dsql);
		$dstatement->execute();

		$tsql = "SELECT SlotTime FROM slots WHERE DayOfWeek='Sunday'";
		$tstatement = $pdo->prepare($tsql);
		$tstatement->execute();

		# If the update availability button has been submitted, update the availability records for the player
		if(isset($_POST['asubmit'])) {
			$slot_status = array();
			for ($i = 1; $i <= SLOTS; $i++) {
				if(isset($_POST['slot' . $i]) and $_POST['slot' . $i]=='Yes') {
					$slot_status[$i] = 1;
				} else {
					$slot_status[$i] = 0;
				}
			}

			$update_sql = "UPDATE playeravailability SET Available=:slot_status WHERE AvailabilityID=:availabilityid AND PlayerID=:playerid;";
			$update_statement = $pdo->prepare($update_sql);
			$update_statement->bindValue(':playerid', $playerid);

			for ($j = 1; $j <= SLOTS; $j++) {
				$update_statement->bindValue(':availabilityid', $j);
				$update_statement->bindValue(':slot_status', $slot_status[$j]);
				$update_statement->execute();
			}

		}


		# Get availability data for the player
		$pl_sql = "SELECT Available FROM playeravailability AS pa INNER JOIN availability AS a ON pa.AvailabilityID=a.AvailabilityID WHERE PlayerID=:playerid ORDER BY SlotID;";
		$pl_statement = $pdo->prepare($pl_sql);
		$pl_statement->bindValue(':playerid', $playerid);
		$pl_statement->execute();
		
		# Populate an array with slot values
		$playerslots = array();
		$i = 1;
		while ($pl_row = $pl_statement->fetch()) {
			$playerslots[$i] = $pl_row['Available'];
			$i = $i + 1;
		}

		$pdo = null;
	}
	catch (PDOException $e) {
		die($e->getMessage());
	}
?>

<!DOCTYPE html>
<html>

<?php
	head("Availability",$filepath);
?>

<body>
	<div class="container-fluid">
		<?php navbar('player',$filepath,$filepath . 'index.php', $playerfirst);?>
	</div>
	<div class="container page-content">
		<?php if(isset($_POST['asubmit'])){?>
		<div class="row">
			<div class="col-md-12">
				<div class="alert alert-dismissible alert-info pull-left form-alert">
		  			<button type="button" class="close" data-dismiss="alert">
						<span aria-hidden="true">&times;</span>
						<span class="sr-only">Close</span>
					</button>
		  			<p>Availability updated.</p>
				</div>
			</div>
		</div>
		<?php }?>
		<?php if($ac) {?>
		<div class="alert alert-dismissible alert-info">
  			<button type="button" class="close" data-dismiss="alert">
				<span aria-hidden="true">&times;</span>
				<span class="sr-only">Close</span>
			</button>
  			<p>Congratulations <?php echo $playerfirst;?>! Your player account has been successfully created. Please indicate your availability below. You may come back and update this at any time.</p>
		</div>
		<?php }
		
		playerpills($playerfirst . ' ' . $playerlast);
		?>

		<form method="post" action="availability.php">
			<fieldset>
				<div class="row">
					<div class="col-md-6">
						<h2><strong>Availability</strong></h2>
						<p>Check each of the time slots that you are available for.</p>
					</div>
					<div class="col-md-6">
						<div class="form-group pull-right menu-button">
							<button type="submit" class="btn btn-primary" id="asubmit" name="asubmit">
								<span class="glyphicon glyphicon-upload" aria-hidden="true"></span>&nbsp;
								Update Availability
							</button>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<table class="table table-striped table-hover table-responsive" id="atable">
							<thead>
								<tr>
									<?php
									while ($row = $dstatement->fetch()) {
										?><th><?php echo $row['DayOfWeek'];?></th>
									<?php }
									?>
								</tr>
							</thead>
							<tbody>
								<?php
								$j = 1;
								while ($row = $tstatement->fetch()) {
									$k = $j;?>
									<tr>
									<?php 
									for ($i = 1; $i < 8; $i++) {?>
										<td class="<?php if($playerslots[$k]==1){echo 'success';}?>">
											<div class="checkbox av">
												<label><input type="checkbox" name="slot<?php echo $k;?>" id="slot<?php echo $k;?>" value="Yes" <?php if($playerslots[$k]==1){echo 'checked="checked"';}?>><?php echo $row['SlotTime'];?></label>
											</div>
										</td>
										<?php $k = $k + 16;
									}?>
									</tr>
								<?php $j = $j + 1;
								}
								?>
							</tbody>
						</table>
					</div>
				</div>
			</fieldset>
		</form>
	</div>
</body>