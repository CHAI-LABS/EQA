

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
                <i class = "icon-chart"></i>
                &nbsp;

                    Analysis Results

                <div class = "pull-right">
                    <a href = "<?= @base_url('Analysis/'); ?>"> <button class = "btn btn-primary btn-sm"><i class = "fa fa-arrow-left"></i>  Back to PT Analysis</button></a><br /><br />
                </div>
            </div>

            <div class = "card-block">

            	<?= @$sample_tabs; ?>
          
            </div>
        </div>
    </div>
</div>
        

