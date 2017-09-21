<script>
    $(document).ready(function(){

        $('table').dataTable({
        	"createdRow": function( row, data, dataIndex){
                if( data[10] ==  `Unsatisfactory Performance`){
                    $(row).addClass('red');
                }else if(data[10] ==  `Incomplete Submission`){
                	$(row).addClass('orange');
                }
            }
        });
                   
});    
</script>