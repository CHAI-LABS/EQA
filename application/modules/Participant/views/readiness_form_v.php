<div class="row">
	<div class="col-sm-12">

	<div class = 'alert alert-warning'>
        <h5>Refreshing the page will cause a loss of some data, and would require another login attempt, through your email</h5>
    </div>


	<div class="col-sm-12">

	    <div class="card">
	        <div class="card-header">
	            <strong>Questionnaire</strong>
	        </div>
	        <div class="card-block">
	          <form method = "post" action="<?php echo base_url('Participant/Readiness/submitReadiness');?>" class="p-a-4" id="page-signin-form">
				<input type="hidden" class="page-signin-form-control form-control" value="<?= @$pt_uuid; ?>" id="ptround-form" name="ptround">

                <?= @$questionnair; ?>

                <button id="submit-readiness" type="submit" class="btn btn-block btn-primary">Submit PT Readiness Checklist</button>
          	</form>
	            
	        </div>
	    </div>
	</div>


</div>