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
                                        SUMMARY: PARTICIPANTS
                                        <div class="card-actions">
                                        </div>
                                    </div>
                                    <div class="card-block">
                                        Total # site enrolled :<strong id="enrolled"> 0 </strong><br/>
                                        # of participants :<strong id="partno"> 0 </strong><br/>
                                        Disqualified :<strong id="disqualified"> 0 </strong><br/>
                                        Unable to report :<strong id="unable"> 0 </strong><br/>
                                        Non-Responsive :<strong id="nonresp"> 0 </strong><br/>
                                        Responded :<strong id="resp"> 0 </strong>
                                        <br/><br/>
                                        <div class="chart-wrapper">
                                            <canvas id="graph-1"></canvas>
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-header">
                                        SUMMARY OUTCOMES: PASS VS FAILED
                                        <div class="card-actions">
                                        </div>
                                    </div>
                                    <div class="card-block">
                                        # of responded participants :<strong id="part"> 0 </strong><br/>
                                        # of passed :<strong id="pass"> 0 </strong><br/>
                                        # of failed :<strong id="fail"> 0 </strong>
                                        <br/><br/>
                                        <div class="chart-wrapper">
                                            <canvas id="graph-2"></canvas>
                                        </div>
                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 container-fluid">
                        <div class="animated fadeIn">
                            <!-- <div class="card-columns col-2"> -->


                                <div class="card" style="width:100%";>
                                    <div class="card-header">
                                        SUMMARY OUTCOME: <strong id="roundname1">Round Name</strong>
                                        <div class="card-actions">
                                        </div>
                                    </div>
                                    <div class="card-block">
                                        <div class="chart-wrapper">
                                            <canvas id="graph-3"></canvas>
                                        </div>
                                    </div>
                                </div>


                            <!-- </div> -->

                            
                        </div>
                    </div>


                    <div class="col-md-12 container-fluid">
                        <div class="animated fadeIn">
                            <div class="card" style="width:100%";>
                                <div class="card-header">
                                    DISQUALIFIED PARTICIPANTS: <strong id="roundname2">Round Name</strong>
                                    <div class="card-actions">
                                    </div>
                                </div>
                                <div class="card-block">
                                    EQUIPMENT BREAKDOWN :<strong id="equip"> 0 </strong><br/>
                                    REAGENT STOCK-OUT :<strong id="reag"> 0 </strong><br/>
                                    ANALYST UNAVAILABLE :<strong id="anal"> 0 </strong><br/>
                                    PENDING CAPA :<strong id="pend"> 0 </strong>
                                    <br/><br/>
                                    <div class="chart-wrapper">
                                        <canvas id="graph-4"></canvas>
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
                                        PARTICIPANT PASS / FAIL TREND
                                        <div class="card-actions">
                                        </div>
                                    </div>
                                    <div class="card-block">
                                        <div class="chart-wrapper">
                                            <canvas id="graph-5"></canvas>
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-header">
                                        PARTICIPANT RESPONDENT / NON-RESPONDENT TREND
                                        <div class="card-actions">
                                        </div>
                                    </div>
                                    <div class="card-block">
                                        <div class="chart-wrapper">
                                            <canvas id="graph-6"></canvas>
                                        </div>
                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>


                    <div class="col-md-12 container-fluid">
                        <div class="animated fadeIn">
                            <div class="card" style="width:100%";>
                                <div class="card-header">
                                    COUNTY OUTCOMES: <strong id="roundname3">Round</strong>
                                    <div class="card-actions">
                                    </div>
                                </div>
                                <div class="card-block">
                                    <div class="chart-wrapper">
                                        <canvas id="graph-7"></canvas>
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