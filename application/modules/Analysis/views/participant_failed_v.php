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
        

            
<div class = "row">
    <div class="col-md-12">
        <div class = "card">
            <div class="card-header col-4">
                <i class = "icon-wrench"></i>
                &nbsp;
                
                    Failed Participant List

                <div class = "pull-right">
                    
                    <a href = "<?= @base_url('Analysis/Results/'); ?><?= @$round_uuid; ?>"><button class = "btn btn-primary btn-sm" id = "btn-create-equipment"><i class = "fa fa-arrow-left"></i> Back to Analysis Result</button></a><br /><br />

                </div>
            </div>
            <div class = "card-block">
                <?= @$table_view; ?>
            </div>
        </div>
    </div>
</div>