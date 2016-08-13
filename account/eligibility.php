<?php
	ob_start();
	session_start();
	$filepath = "../";

	include($filepath . "autoload.php");
	autoload($filepath);

	include($filepath . "session-status.php");

	if(isset($_GET['ac'])) {
		$ac = true;
	} else {
		$ac = false;
	}

	if(isset($_POST['esubmit'])) {
		if(empty($_FILES['efile']['name'])) {
			$empty_file = true;
		} elseif($_FILES['efile']['error'] != UPLOAD_ERR_OK) {
			$file_upload_error = true;	
		} else {
			$tmp_name = $_FILES['efile']['tmp_name'];
			$name = $schoolid . '_' . date('Y-d-m_H-i-s');
			move_uploaded_file($tmp_name, $filepath . 'csv_uploads/' . $name . '.csv');

			$participants = file($filepath . 'csv_uploads/' . $name . '.csv') or die('ERROR: Cannot find file');
			$delimiter = ',';
			
			try {
				include($filepath . "connect-to-db.php");

				try {
					#begin the Insert transaction
					$pdo->beginTransaction();

					# Insert new records into eligibility table
					$insert_ep_sql = "INSERT INTO eligibility (SchoolID, StudentID, StudentEmail) VALUES (:schoolid, :studentid, :studentemail)";
					$insert_ep_statement = $pdo->prepare($insert_ep_sql);
					$insert_ep_statement->bindValue(':schoolid', $schoolid);
					
					foreach($participants as $participant) {
						$participantFields = explode($delimiter, $participant);

						$studentid = trim($participantFields[0]);
						$studentemail = trim($participantFields[1]);

						if($studentid!='StudentID' and $studentemail!='StudentEmail') {
							$insert_ep_statement->bindValue(':studentid', $studentid);
							$insert_ep_statement->bindValue(':studentemail', $studentemail);
							$insert_ep_statement->execute();
						}
					}

					#commit the transaction
					$pdo->commit();
					$update_successful = true;
				} catch (Exception $e) {
					#rollback if there were any failures
					$pdo->rollback();
				}

				$pdo = null;
			}
			catch (PDOException $e) {
				die($e->getMessage());
			}
		}
	}

	if(isset($_POST['ac']) and isset($update_successful)) {
		header("Location: payment.php?ac=1");
	}
?>

<!DOCTYPE html>
<html>

<?php
	if($ac) {
		head("LeagueShark: Sign Up",$filepath);
	} else {
		head("Account: Eligibility",$filepath);
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
		<?php if(isset($update_successful)) {?>
		<div class="row">
			<div class="alert alert-dismissible alert-info pull-left form-alert">
	  			<button type="button" class="close" data-dismiss="alert">
					<span aria-hidden="true">&times;</span>
					<span class="sr-only">Close</span>
				</button>
	  			<p>Update successful.</p>
			</div>
		</div>
		<?php }?>
		<?php if($ac) {?>
		<div class="alert alert-dismissible alert-info">
  			<button type="button" class="close" data-dismiss="alert">
				<span aria-hidden="true">&times;</span>
				<span class="sr-only">Close</span>
			</button>
  			<p>Congratulations! Your account for <strong><?php echo $schoolname;?></strong> has been successfully created. At this time, please upload a CSV file of all of the currently eligible students for your school's intramural program. Without this information, students and other eligible participants will be unable to sign up for your leagues and teams. You may skip this step and do it later.</p>
		</div>
		<?php } else {
			schoolpills($schoolname);
		}?>

		<h2><strong>Eligibility</strong></h2>
		<br>
		<form method="post" action="eligibility.php" enctype="multipart/form-data">
			<fieldset>
				<div class="form-group">
					<p>Upload a <abbr title="Comma-Separated Values">CSV</abbr> file containing the following fields: Student ID, Student Email
						<span class="glyphicon glyphicon-info-sign pointer" aria-hidden="true" data-toggle="modal" data-target="#einfo"></span>
					</p>
					<input type="file" id="efile" name="efile">

					<?php if(isset($empty_file)) {?>
					<p class="text-danger">*Please select a file.</p>
					<?php } elseif(isset($file_error)) {?>
					<p class="text-danger">*There was an error uploading the file. Please try again.</p>
					<?php }?>

				</div>
				<br>
				<div class="form-group">
					<button type="submit" class="btn btn-primary" id="esubmit" name="esubmit">
						<span class="glyphicon glyphicon-upload" aria-hidden="true"></span>&nbsp;
						Update Eligibility Data
					</button>
				</div>
				<?php if($ac) {?>
				<input type="hidden" id="ac" name="ac" value="true">
				<?php }?>
			</fieldset>
		</form>

		<?php if($ac) {?>

		<br>
		<a href="payment.php?ac=1">Skip this step and do it later &raquo;</a>

		<?php }?>
		<div id="einfo" class="modal fade">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">
							<span aria-hidden="true">&times;</span>
							<span class="sr-only">Close</span>
						</button>
						<h4 class="modal-title">Eligibility Data</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-10 col-xs-push-1 col-sm-10 col-sm-push-1 col-md-10 col-md-push-1">
								<p>LeagueShark uses both a school ID and a school email address to validate all eligible participants. In order to get this data into LeagueShark, please upload a CSV file contaning these two pieces of information for each of your eligible participants.  Please name the first field Student ID and the second field Student Email. Once you upload this file, you can always come back and update the list of eligible participants.</p>
								<br>
								<p>The beginning of your CSV file should look like this:</p>
								<img src="<?php echo $filepath;?>images/csvexample.PNG" class="img-responsive img-thumbnail"/>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
