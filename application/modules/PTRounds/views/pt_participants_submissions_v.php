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
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <a href = "<?= @$qa_unresponsive; ?>"><div class="card-block">
                <div class="h4 m-0"><?= @$qa_unresponsive_count; ?></div>
                <div class="pb-1">Pending QA review</div>
            </div></a>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-block">
                <div class="h4 m-0">200</div>
                <div class="pb-1">Yet to Submit Response</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-block">
                <div class="h4 m-0">200</div>
                <div class="pb-1">Completed and Reviewed</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-block">
                <div class="h4 m-0">200</div>
                <div class="pb-1">Not completed and Reviewed</div>
            </div>
        </div>
    </div>
</div>

        

            
<div class = "row">
    <div class="col-md-12">
        <div class = "card">
            <div class="card-header col-4">
                <i class = "icon-wrench"></i>
                &nbsp; Ready Participants
                <div class = "pull-right">
                
                    <a href = "<?= @$back_link; ?>"> <button class = "btn btn-primary btn-sm"><i class = "fa fa-arrow-left"></i> <?= @$back_name; ?></button></a><br /><br />
                
                </div>
            </div>
            <div class = "card-block">
                <?= @$table_view; ?>
            </div>
        </div>
    </div>
</div>