<script>
    $(document).ready(function(){

        $('table').dataTable({
        	"createdRow": function( row, data, dataIndex){
                if( data[11] ==  `Unsatisfactory Performance`){
                    $(row).addClass('red');
                }else if(data[11] ==  `Satisfactory Performance`){
                	$(row).addClass('green');
                }
            }
        });
                   
});    
</script>