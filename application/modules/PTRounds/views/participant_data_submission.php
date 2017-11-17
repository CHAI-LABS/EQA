<?php if($this->session->flashdata('success')){ ?>
        <div id="data-info" class = 'alert alert-success'>
            <?= @$this->session->flashdata('success'); ?>
        </div>
<?php }elseif($this->session->flashdata('error')){ ?>
        <div class = 'alert alert-danger'>
            <?= @$this->session->flashdata('error'); ?>
        </div>
<?php } ?>

                <div id="round" data-type="<?= @$round; ?>"></div>


<?= @$back_link; ?>

            <div class = "block">
                <!-- <div class = "block col-md-3">
                    <fieldset class="form-group">
                        <label>Round</label>
                        <?= @$round_option; ?>
                    </fieldset>
                </div> -->

                <div class = "block col-md-3">
                    <fieldset class="form-group">
                        <label>County</label>
                        <?= @$county_option; ?>
                    </fieldset>
                </div>

                <div class = "block col-md-3">
                    <fieldset class="form-group">
                        <label>Facility</label>
                        <select id="facility-select" class="form-control select2-single">
                            <?= @$facility_option; ?>
                        </select>
                    </fieldset>
                </div>
            </div>

            <strong><center>METHODOLOGY</center></strong>

<?php if($this->session->flashdata('success')){ ?>
        <div id="data-info" class = 'alert alert-success'>
            <?= @$this->session->flashdata('success'); ?>
        </div>
  <?php }elseif($this->session->flashdata('error')){ ?>
        <div class = 'alert alert-danger'>
            <?= @$this->session->flashdata('error'); ?>
        </div>
  <?php } ?>


  	<div class = 'alert alert-warning'>
	    Form is to be submitted by <span class="text-danger"><?= @date('dS F, Y', strtotime($pt_round_to)); ?></span>
    </div>

		<div class="col-md-12 mb-2">
			<div id="content">Please select a Facility</div>
			<!-- <?= @$equipment_tabs; ?> -->

    	</div> 
