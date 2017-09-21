<script>
    $(document).ready(function(){

        $('table').dataTable({
        	"createdRow": function( row, data, dataIndex){
                if( data[8] ==  `Unsatisfactory Performance`){
                    $(row).addClass('red');
                }else if(data[8] ==  `Satisfactory Performance`){
                	$(row).addClass('green');
                }
            }
        });
                   
});    
</script>