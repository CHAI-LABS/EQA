<div class="page-signin-modal modal">
	<div class="modal-dialog">
	  <div class="modal-content">
	    <div class="box m-a-0">
	      <div class="box-row">
			<div class="box-cell col-md-7">


			<?php if($type == 'readiness'){ ?>
				<form method = "post" action='<?php echo base_url('Participant/Readiness/authentication/');?>' class="p-a-4" id="readiness-login-form">
			<?php }else if($type == 'capa'){ ?>
				<form method = "post" action='<?php echo base_url('Participant/Participant/authentication/');?>' class="p-a-4" id="readiness-login-form">
			<?php } ?>

			<?php if($this->session->flashdata('success')){ ?>
                <div class = 'alert alert-success'>
                    <?= @$this->session->flashdata('success'); ?>
                </div>
            <?php }elseif($this->session->flashdata('error')){ ?>
                <div class = 'alert alert-danger'>
                    <?= @$this->session->flashdata('error'); ?>
                </div>
            <?php } ?>
				<!-- <form method = "post" action="./" class="p-a-4" id="readiness-login-form"> -->
				<h3 class="m-t-0 m-b-4 text-xs-center font-weight-semibold">Readiness Checklist</h3>
						<h4 class="m-t-0 m-b-4 text-xs-center font-weight-semibold">Log in to verify your information</h4>
						<input type="hidden" class="page-signin-form-control form-control" value="<?= @$pt_uuid; ?>" id="ptround" name="ptround">
						<fieldset class="page-signin-form-group form-group form-group-lg">
						  <div class="page-signin-icon text-muted"><i class="ion-person"></i></div>
						  <input type="text" class="page-signin-form-control form-control" placeholder="Username or Email" id="username" name="username">
						</fieldset>

						<fieldset class="page-signin-form-group form-group form-group-lg">
						  <div class="page-signin-icon text-muted"><i class="ion-asterisk"></i></div>
						  <input type="password" class="page-signin-form-control form-control" placeholder="Password" id="password" name="password">
						</fieldset>

						<div class="clearfix">
						  <label class="custom-control custom-checkbox pull-xs-left">
						    <input type="checkbox" class="custom-control-input">
						    <span class="custom-control-indicator"></span>
						    Remember me
						  </label>
						  <a href="#" class="font-size-12 text-muted pull-xs-right" id="page-signin-forgot-link">Forgot your password?</a>
						</div>

						<button id="loginready" type="submit" class="btn btn-block btn-lg btn-primary m-t-3">Log in</
				</form>

			</div>
	      </div>
	    </div>
	  </div>
	</div>
</div>
		<a href="<?php echo base_url('Auth/signin');?>">
			<h3 class="m-t-0 m-b-4 text-xs-center font-weight-semibold">Login to Dashboard</h3>
		</a>