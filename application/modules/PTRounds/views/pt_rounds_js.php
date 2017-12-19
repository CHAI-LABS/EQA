<script>
    $(document).ready(function(){
        var rowCount = numberofrows();
        // alert(rowCount);
        if(rowCount == 0){
            $('#sample_table tbody').append(customizeRow(1));
        }
        <?php if($step == "information"){?>
            $('input[name="round_duration"]').daterangepicker({
                locale: {
                      format: 'DD-MM-YYYY'
                }
            });
            $('#rounds-form').validate({
                rules: {
                    "samples[]": "required",
                    round_duration : "required",
                    blood_unit_lab_id: "required",
                    no_testers: {
                        required : true,
                        number: true
                    },
                    no_labs: {
                        required : true,
                        number: true
                    }
                },
                messages: {
                    "samples[]" : "You must enter the sample name",
                    blood_unit_lab_id: "The blood unit lab id is required",
                    round_duration: "You have to pick the round duration",
                    no_testers: {
                        required: "Enter the number of testers",
                        number: "The value has to be a number"
                    },
                    no_labs: {
                        required: "Enter the number of labs",
                        number: "The value has to be a number"
                    }
                },
                errorElement: 'em',
                errorPlacement: function ( error, element ) {
                    // Add the `help-block` class to the error element
                    error.addClass( 'form-control-feedback' );
                    if ( element.prop( 'type' ) === 'checkbox' ) {
                        error.insertAfter( element.parent( 'label' ) );
                    } else {
                        error.insertAfter( element );
                    }
                },
                highlight: function ( element, errorClass, validClass ) {
                    $( element ).addClass( 'form-control-danger' ).removeClass( 'form-control-success' );
                    $( element ).parents( '.form-group' ).addClass( 'has-danger' ).removeClass( 'has-success' );
                },
                unhighlight: function (element, errorClass, validClass) {
                    $( element ).addClass( 'form-control-success' ).removeClass( 'form-control-danger' );
                    $( element ).parents( '.form-group' ).addClass( 'has-success' ).removeClass( 'has-danger' );
                }
            });
        <?php }elseif($step == "variables"){ ?>
            $('.layout-toggler').trigger('click');
            $('.accordion div:first div.card-header h5 a').trigger('click');
        <?php }elseif($step == "calendar"){ ?>
            $('input.daterange').daterangepicker({
                locale: {
                      format: 'DD-MM-YYYY'
                },
                "minDate"   : '<?= @date('m/d/Y', strtotime($duration_from));  ?>',
                "maxDate"   : '<?= @date('m/d/Y', strtotime($duration_to));  ?>'
            });
        <?php }elseif($step == "facilities"){ ?>
                $('#facilities').DataTable({
                    serverSide: true,
                    processing: true,
                    ajax: {
                        url: "<?= @base_url('PTRounds/getFacilitiesTable/'.$pt_details->uuid.'/'. $type); ?>",
                        type: "POST",
                        error: function(){
                            alert("No data found in server");
                        }
                    }
                });
        <?php } ?>
    });

    $('#add-sample').click(function(){
        var rowCount = numberofrows();
        var number = rowCount+1;
         $('#sample_table tbody tr:last').after(customizeRow(number));
    });

    $('#sample_table').on('click', 'a.remove-sample' , function(){
        if(numberofrows() > 1){
            $(this).parent().parent().parent().remove();
            $('#sample_table tbody tr').each(function(i, row){
                var $row = $(row);
                var row_no = $(row).children(":first");
                var sample_number = $(row).find('span.sample-no');

                sample_number.text(i+1);
                row_no.text(i+1);
            });
        }else{
            alert("You must have at least one sample");
        }
    });

    function numberofrows(){
        var rowCount = $('#sample_table tbody tr').length;
        return rowCount;
    }

    function customizeRow(number){
        return "<tr><td>"+number+"</td><td>Sample <span class = 'sample-no'>"+number+"</span></td><td><input type = 'text' name = 'samples[]' class = 'form-control'/></td><td><center><a class = 'remove-sample'><i class = 'fa fa-times'></i></a></center></td></tr>";
    }

    function calculateMean(){
        
    }

    
</script>