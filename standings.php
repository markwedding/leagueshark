<?php
	ob_start();
	session_start();
	$filepath = "";

	include($filepath . "autoload.php");
	autoload($filepath);

	# Redirect the user since this page is not part of the initial release
	header("Location: ". $filepath . "index.php");

	include($filepath . "session-status.php");
?>

<!DOCTYPE html>
<html>

<?php
	head("Standings",$filepath);
?>

<body>
	<div class="container-fluid">
		<?php
			if($schoolaccount) {
				navbar('school',$filepath,$filepath . 'index.php',$schoolname);
			} elseif($playeraccount) {
				navbar('player',$filepath,$filepath . 'index.php',$playerfirst);
			} elseif($refaccount) {
				navbar('referee',$filepath,$filepath . 'index.php',$reffirst);
			}
		?>
	</div>
	<div class="container page-content">
		<div class="row">
			<div class="col-md-6">
				<h2><strong>Standings</strong></h2>
			</div>
			<div class="col-md-6">
				<form class="form-horizontal pull-right">
					<fieldset>
						<div class="form-group menu-form-group">
							<label class="control-label col-md-4" for="league">League</label>
							<div class="col-md-8">
								<select class="form-control" id="league" name="league">
									<option>Men's 3 on 3</option>
									<option>CoRec Flag Football</option>
									<option>Men's Ultimate Frisbee</option>
								</select>
							</div>
						</div>
					</fieldset>
				</form>
			</div>
		</div>
		<hr>
		<div class="row">
			<div class="col-md-12">
				<table class="table table-striped table-hover table-responsive">
					<thead>
						<tr>
							<th></th>
							<th>Team</th>
							<th>Wins</th>
							<th>Losses</th>
							<th>Percentage</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>1</td>
							<td>Swag Monkeys</td>
							<td>8</td>
							<td>0</td>
							<td>1.000</td>
						</tr>
						<tr>
							<td>2</td>
							<td>Purple Cobras</td>
							<td>5</td>
							<td>3</td>
							<td>0.675</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</body>