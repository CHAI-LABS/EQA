<?php if($this->session->flashdata('success')){?>
	<div class="container">
		<div class="card">
			<div class="card-block" style="text-align: center;">
				<i class = "icon-check" style="font-size: 6em;color: #66BB6A;"></i>
				<h3 class = "mt-1">SUCCESS!</h3>
				<p><?= @$this->session->flashdata('success');?></p>
				<div class="alert alert-danger">
					<p>Please close this tab or logout if you are done.</p>
				</div>
			</div>
		</div>
	</div>
<?php } else { ?>
<?php if($this->session->flashdata('error')){?>
	<div class="alert alert-danger alert-dismissable" role = "alert">
		<h4>Ooops! Something went wrong</h4>
		<p><?= @$this->session->flashdata('error');?></p>
	</div>
<?php } ?>
<div class="container">
	<div class="card">
		<div class="card-block">
			<h4>Confirm Receipt</h4>
			<hr>
			<form method="POST" class = "form-horizontal" action="<?= @base_url('Participant/PanelTracking/submitConfirmation/' . $panel_tracking_uuid); ?>">
				<div class="form-group row">
					<label class="col-sm-3" class = "control-label">Date of Receipt</label>
					<div class="col-sm-9">
						<input type="text" name="participant_received_date" class = "form-control" value="<?= @date('m/d/Y'); ?>" required>
						<span class="help-block">Format: month/day/year e.g. 12/31/2019</span>
					</div>
					
				</div>
				<div class="alert alert-warning" role = "alert">
					<p>Please fill in for all the recieved samples</p>
				</div>

				<legend>Condition of Panel</legend>

				<?= @$samples; ?>

				<!-- <div class="form-group row">
					<div class = "col-sm-6">
						<p>Are the sample tubes good to carry out this panel's tests ?</p>
					</div>
					<div class = "col-sm-6">
						<input type="radio" value = "1" name="acceptance" class = "" id="acceptanceyes" required />&nbsp;<label for = "acceptanceyes">Yes</label>&nbsp;
						<input type="radio" value = "0" name="acceptance" class = "" id="acceptanceno" required />&nbsp;<label for = "acceptanceno">No</label>&nbsp;
					</div>	
				</div> -->



				<!-- <div id="bad-samples" style="display: none;">

					<div class="form-group row">
			          <label class = "col-sm-6">Sample Tube</label>
			          <div class = "col-sm-6">

			          	<div class="">
				          	<label><strong>Broken</strong></label>
					            <input type="radio" name="tubes_broken" id = "tubes_broken_1" value = "1" /> <label for = "tubes_broken_1">Yes</label>&nbsp;
					            <input type="radio" name="tubes_broken" id = "tubes_broken_0" value = "0" /> <label for = "tubes_broken_0">No</label>&nbsp;
			          	</div>


			         	<div class="">
					          <label><strong>Leaking</strong></label>
					            <input type="radio" name="tubes_leaking" id = "tubes_leaking_1" value = "1" /> <label for = "tubes_leaking_1">Yes</label>&nbsp;
					            <input type="radio" name="tubes_leaking" id = "tubes_leaking_0" value = "0" /> <label for = "tubes_leaking_0">No</label>&nbsp;
			            </div>


			            <div class="">
				          	<label for = "sample_tubes_0"><strong>Cracked</strong></label>
					            <input type="radio" name="tubes_cracked" id = "tubes_cracked_1" value = "1" /> <label for = "tubes_cracked_1">Yes</label>&nbsp;
					            <input type="radio" name="tubes_cracked" id = "tubes_cracked_0" value = "0" /> <label for = "tubes_cracked_0">No</label>&nbsp;
				        </div>

				      </div>
			        </div>

			        <div class="form-group row">
			          <label class = "col-sm-6">Insufficient Volume</label>
			          <div class="col-sm-6">
			            <input type="radio" name="insufficient_volume" id = "insufficient_volume_yes" value = "1" required /> <label for = "insufficient_volume_yes">Yes</label>&nbsp;
			            <input type="radio" name="insufficient_volume" id = "insufficient_volume_no" value = "0" required /> <label for = "insufficient_volume_no">No</label>
			          </div>
			        </div>

			        <div class="form-group row">
			          <label class = "col-sm-6">Haemolysed sample</label>
			          <div class="col-sm-6">
			            <input type="radio" name="haemolysed_sample" id = "haemolysed_sample_yes" value = "1" required /> <label for = "haemolysed_sample_yes">Yes</label>&nbsp;
			            <input type="radio" name="haemolysed_sample" id = "haemolysed_sample_no" value = "0" required /> <label for = "haemolysed_sample_no">No</label>
			          </div>
			        </div>

			        <div class="form-group row">
			          <label class = "col-sm-6">Clotted sample</label>
			          <div class="col-sm-6">
			            <input type="radio" name="clotted_sample" id = "clotted_sample_yes" value = "1" required /> <label for = "clotted_sample_yes">Yes</label>&nbsp;
			            <input type="radio" name="clotted_sample" id = "clotted_sample_no" value = "0" required /> <label for = "clotted_sample_no">No</label>
			          </div>
			        </div>

			        <div class="form-group row">
			          <label class = "col-sm-6">Duplicate sample received</label>
			          <div class="col-sm-6">
			            <input type="radio" name="duplicate_sample" id = "duplicate_sample_yes" value = "1" required /> <label for = "duplicate_sample_yes">Yes</label>&nbsp;
			            <input type="radio" name="duplicate_sample" id = "duplicate_sample_no" value = "0" required /> <label for = "duplicate_sample_no">No</label>
			          </div>
			        </div>

			        <div class="form-group row">
			          <label class = "col-sm-6">Missing sample</label>
			          <div class="col-sm-6">
			            <input type="radio" name="missing_sample" id = "missing_sample_yes" value = "1" required /> <label for = "missing_sample_yes">Yes</label>&nbsp;
			            <input type="radio" name="missing_sample" id = "missing_sample_no" value = "0" required /> <label for = "missing_sample_no">No</label>
			          </div>
			        </div>

			        <div class="form-group row">
			          <label class = "col-sm-6">Mismatch of information details on introductory letter and sample tube</label>
			          <div class="col-sm-6">
			            <input type="radio" name="mismatch" id = "mismatch_yes" value = "1" required /> <label for = "mismatch_yes">Yes</label>&nbsp;
			            <input type="radio" name="mismatch" id = "mismatch_no" value = "0" required /> <label for = "mismatch_no">No</label>
			          </div>
			        </div>

					<div class="form-group">
						<label>Please provide a brief comment on why you chose the response above</label>
						<textarea class = 'form-control' name = 'condition_comment' rows = "8" required></textarea>
					</div>

				</div> -->

				

				<div class="alert alert-info" role = "alert">
					<p>Please note that if your panels are in good condition, then you will be allowed to start reporting on your findings. Otherwise, you may have to wait for an administrator to intervene</p>
				</div>
				
				<button class = "btn btn-primary btn-block">Submit Receipt</button>
			</form>
		</div>
	</div>
</div>
<?php } ?>
