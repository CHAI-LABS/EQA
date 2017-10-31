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

                    Graph Analysis

                <div class = "pull-right">
                    <a href = "<?= @base_url('Analysis/'); ?>"> <button class = "btn btn-primary btn-sm"><i class = "fa fa-arrow-left"></i>  Back to PT Analysis</button></a><br /><br />
                </div>
            </div>

            <div class = "card-block">
            	<div class = "row">
				<div id="round" data-type="<?= @$round; ?>"></div>

					<div class="col-md-12 container-fluid">
			            <div class="animated fadeIn">
			                <div class="card-columns col-2">


			                    <div class="card">
			                        <div class="card-header">
			                            PARTICIPATION STATISTICS
			                            <div class="card-actions">
			                                <!-- <a href="http://www.chartjs.org/">
			                                    <small class="text-muted">docs</small>
			                                </a> -->
			                            </div>
			                        </div>
			                        <div class="card-block">
			                            <div class="chart-wrapper">
			                                <canvas id="participation"></canvas>
			                            </div>
			                        </div>
			                    </div>
			                

			                    <div class="card">
			                        <div class="card-header">
			                            DISQUALIFIED PARTICIPANTS
			                            <div class="card-actions">
			                                <!-- <a href="http://www.chartjs.org/">
			                                    <small class="text-muted">docs</small>
			                                </a> -->
			                            </div>
			                        </div>
			                        <div class="card-block">
			                            <div class="chart-wrapper">
			                                <canvas id="disqualified"></canvas>
			                            </div>
			                        </div>
			                    </div>
			               


			                    <div class="card">
			                        <div class="card-header">
			                            JUSTIFICATION FOR RESULTS NOT RETURNED
			                            <div class="card-actions">
			                                <!-- <a href="http://www.chartjs.org/">
			                                    <small class="text-muted">docs</small>
			                                </a> -->
			                            </div>
			                        </div>
			                        <div class="card-block">
			                            <div class="chart-wrapper">
			                                <canvas id="justification"></canvas>
			                            </div>
			                        </div>
			                    </div>
			                

			                    <div class="card">
			                        <div class="card-header">
			                            REMEDIAL ACTION PER PLATFORM
			                            <div class="card-actions">
			                                <!-- <a href="http://www.chartjs.org/">
			                                    <small class="text-muted">docs</small>
			                                </a> -->
			                            </div>
			                        </div>
			                        <div class="card-block">
			                            <div class="chart-wrapper">
			                                <canvas id="remedial"></canvas>
			                            </div>
			                        </div>
			                    </div>


			                </div>
			            </div>
			        </div>

			        <div class="col-md-12 container-fluid">
			            <div class="animated fadeIn">
			                <div class="card-columns col-2">			                

			       
			                    <div class="card">
			                        <div class="card-header">
			                        	OVERALL HISTORICAL PERFORMANCE
			                            <div class="card-actions">
			                                <!-- <a href="http://www.chartjs.org/">
			                                    <small class="text-muted">docs</small>
			                                </a> -->
			                            </div>
			                        </div>
			                        <div class="card-block">
			                            <div class="chart-wrapper">
			                                <canvas id="historical"></canvas>
			                            </div>
			                        </div>
			                    </div>




			                </div>
			            </div>
			        </div>


			        <!-- <div class="col-md-12 container-fluid pt-2">
			            <div class="animated fadeIn">
			                <div class="card-columns col-2">
			                    <div class="card">
			                        <div class="card-header">
			                            BAR GRAPH
			                            <div class="card-actions">
			                                <a href="http://www.chartjs.org/">
			                                    <small class="text-muted">docs</small>
			                                </a>
			                            </div>
			                        </div>
			                        <div class="card-block">
			                            <div class="chart-wrapper">
			                                <canvas id="test"></canvas>
			                            </div>
			                        </div>
			                    </div>
			                </div>
			            </div>
			        </div> -->


				</div>
            </div>
        </div>
    </div>
</div>






