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

                    Criteria Selection
            </div>

            <div class = "card-block">
                <div class = "card-block col-md-3">
                    <fieldset class="form-group">
                        <label>Round</label>
                        <?= @$round_option; ?>
                    </fieldset>
                </div>

                <div class = "card-block col-md-3">
                    <fieldset class="form-group">
                        <label>County</label>
                        <?= @$county_option; ?>
                    </fieldset>
                </div>

                <div class = "card-block col-md-3">
                    <fieldset class="form-group">
                        <label>Facility</label>
                        <select id="facility-select" class="form-control select2-single">
                            <?= @$facility_option; ?>
                        </select>
                    </fieldset>
                </div>
            </div>

        </div>
    </div>
</div>



            
<div class = "row">
    <div class="col-md-12">
        <div class = "card">
            <div class="card-header col-4">
                <i class = "icon-chart"></i>
                &nbsp;

                    Program Graphs
            </div>




            <div class = "card-block">
                <div class = "row">
                <div id="round" data-type="<?= @$round; ?>"></div>

                    <div class="col-md-12 container-fluid">
                        <div class="animated fadeIn">
                            <div class="card-columns col-2">


                                <div class="card">
                                    <div class="card-header">
                                        GRAPH 1
                                        <div class="card-actions">
                                        </div>
                                    </div>
                                    <div class="card-block">
                                        <div class="chart-wrapper">
                                            <canvas id="graph-1"></canvas>
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-header">
                                        GRAPH 2
                                        <div class="card-actions">
                                        </div>
                                    </div>
                                    <div class="card-block">
                                        <div class="chart-wrapper">
                                            <canvas id="graph-2"></canvas>
                                        </div>
                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>


                </div>
            </div>

        </div>
    </div>
</div>