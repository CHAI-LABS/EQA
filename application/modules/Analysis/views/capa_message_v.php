
<div class = "pull-left">
    <a href = "<?= @base_url('Analysis/Results/'); ?><?= @$round_uuid?>"> <button class = "btn btn-primary btn-sm"><i class = "fa fa-arrow-left"></i>  Back to Analysis Results</button></a>     
</div>


<form method="POST" action="<?= @base_url('Analysis/sendCAPAMessage/'); ?><?= @$round_uuid?>" class="p-a-4" id="capa-message">

<div class="row">
    <div class="col-sm-12">

        <div class="card">
            <div class="card-header">
                <strong>Send CAPA Message</strong>


            </div>
            <div class="card-block">

                  <!-- <h4 class="m-t-0 m-b-4 text-xs-center font-weight-semibold">PARTICIPANT DETAILS</h4> -->

                  <fieldset class="page-signup-form-group form-group form-group-lg">
                   <div class = "form-group">
                        <label class = "control-label col-md-3">Email</label>
                        <div class="col-md-9">
                          <input type = "hidden" name = "facility_code" value="<?= @$facility_code; ?>" class = "form-control"/>
                            <input type = "text" name = "email" value="<?= @$email; ?>" class = "form-control"/>
                        </div>
                    </div>
                  </fieldset>

                  <fieldset class="page-signup-form-group form-group form-group-lg">
                    <div class = "form-group">
                        <label class = "control-label col-md-3">Subject</label>
                        <div class="col-md-9">
                            <input type = "text" name = "subject" class = "form-control"/>
                        </div>
                    </div>
                  </fieldset>

                  <fieldset class="page-signup-form-group form-group form-group-lg">
                    <div class = "form-group">
                        <label class = "control-label col-md-3">Message</label>
                        <div class="col-md-9">
                            <textarea id="message" name="message" rows="8" maxlength="500" class="form-control" placeholder="Enter Message Here"></textarea>
                        </div>
                    </div>
                  </fieldset>

                  

                  <button type="submit" class="btn btn-block btn-lg btn-primary m-t-3">Send Comment and CAPA link</button>

                </div>   
            </div>
        </div>
    </div>

</form>