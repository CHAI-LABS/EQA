<div class = "card-block">
    <?php if($this->session->flashdata('success')){ ?>
        <div class = 'alert alert-success'>
            <?= @$this->session->flashdata('success'); ?>
        </div>
    <?php }elseif($this->session->flashdata('error')){ ?>
        <div class = 'alert alert-danger'>
            <?= @$this->session->flashdata('error'); ?>
        </div>
    <?php } ?>
</div>


<div class="row">
	<div class="col-sm-12">

	<div class = "pull-right">
        <a href = "<?= @base_url('Participant/PTRound/Round/'.$round_uuid); ?>"> <button class = "btn btn-primary btn-sm"><i class = "fa fa-arrow-left"></i>  Back to Data Submission</button></a><br /><br />
    </div>


	<div class="col-sm-12">

	    <div class="card">
	        <div class="card-header">
	            <strong>Unable to Respond</strong>
	        </div>
	        <div class="card-block">
	          <form method = "post" action="<?php echo base_url('Participant/PTRound/submitReason');?>" class="p-a-4" id="page-signin-form">
				<input type="hidden" class="page-signin-form-control form-control" value="<?= @$round_uuid; ?>"  name="round_uuid">
				<input type="hidden" class="page-signin-form-control form-control" value="<?= @$equipment_id; ?>" name="equipment_id">

				<div class = "row">
				    <div class="col-md-12">
				        <div class = "card">
				            <div class="card-header col-4">
				                <i class = "icon-chart"></i>
				                &nbsp;

			                    <strong>Classification of Reason</strong>

				            </div>

				            <div class = "card-block">
				            	<div class="col-md-3">
				            		Please select one
			                	</div> 

			                	<div class="col-md-9">
				                    <div class = "form-group">
				                        <select name="reason">
				                        	<option value="Management">Management</option>
				                        	<option value="Personnel">Personnel</option>
				                        	<option value="Equipment">Equipment</option>
				                        	<option value="Reagent">Reagent</option>
				                        	<option value="Other">Other</option>
				                        </select>
				                    </div>

				                    <textarea id="detail" name="detail" rows="8" maxlength="500" class="form-control" placeholder="Enter detail of reason here..."></textarea>
			                    </div>

				            </div>
				        </div>
				    </div>
				</div>


                <button id="submit-reason" type="submit" class="btn btn-block btn-primary">Submit Unable Reason</button>
          	</form>
	            
	        </div>
	    </div>
	</div>


</div>