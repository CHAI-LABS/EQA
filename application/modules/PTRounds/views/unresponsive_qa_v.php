<?php if($this->session->flashdata('success')){ ?>
        <div id="data-info" class = 'alert alert-success'>
            <?= @$this->session->flashdata('success'); ?>
        </div>
  <?php }elseif($this->session->flashdata('error')){ ?>
        <div class = 'alert alert-danger'>
            <?= @$this->session->flashdata('error'); ?>
        </div>
  <?php } ?>


    <div class = "row">
    <div class="col-md-12">
        <div class = "card">
            <div class="card-header col-4">
                <i class = "icon-wrench"></i>
                &nbsp; Unresponsive QA / Supervisors
                <div class = "pull-right">
                
                    <a href = "<?= @$back_link; ?>"> <button class = "btn btn-primary btn-sm"><i class = "fa fa-arrow-left"></i> Back to Ready Participants</button></a><br /><br />
                
                </div>
            </div>
            <div class = "card-block">
                <?= @$table_view; ?>
            </div>
        </div>
    </div>
</div>
