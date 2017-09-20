<script>   
    $(document).ready(function(){
        $('#participantsTable').DataTable({
            serverSide: true,
            processing: true,
            ajax: {
                url: "<?= @base_url('API/Users/participants'); ?>",
                type: "POST",
                error: function(){
                    alert("No data found in server");
                }
            }
        });

        $('#participantsTable tbody').on('click', 'a.approval',function(e){
            e.preventDefault();
            url = $(this).attr('href');
            swal({
                title: "APPROVE?!",
                text: "Are you sure you want to approve this user?",
                type: "info",
                showCancelButton: true,
                closeOnConfirm: false,
                showLoaderOnConfirm: true
            }, function(){
                $.get(url, function(data){

                    if(data.status == true){
                        // alert('true');
                        window.location = "<?= @base_url('Users/Participants/listing'); ?>";
                    }else{
                        // alert('false');
                        sweetAlert("Oops...", data.message, "error");
                    }
                });
            });
        });
    });
</script>