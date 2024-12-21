@include('includes.utilities.js')


<style>

.table > :not(caption) > * > *{ padding: .5rem 0; }
</style>
{{--
<script>
    
    function searchTable(tag, value){
        
        $('.rows_orders').css('display', 'none');
        if(value.length == 0){
            $('.main_rows_orders').css('display', 'table-row');
        }else{
            $('.main_rows_orders').css('display', 'none');
            $('[class*="' + tag + value.toUpperCase() + '"]').css('display', 'table-row');
        }
    }
    
	function setRowAsChecked(panel, var_1, var_2, var_3, var_4){

	    $.ajax({
            type: 'POST',
            async: true,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            url: "{{ route('dashboard.post') }}",
            data: {
                action: 'addException',
                panel: panel,
                var_1: var_1,
                var_2: var_2,
                var_3: var_3,
            },
            success: function(data) {
                
                $('table tr#row_exception_' + var_1).remove();
                
                let quantity = $('#' + var_4 + '_quantity').text()
                $('#' + var_4 + '_quantity').text( parseInt(quantity-1) );
                
            }
        });
        
	}

	function keyPressHandler(e) {
        var evtobj = window.event ? window.event : e;
        
        if (evtobj.ctrlKey && evtobj.shiftKey && evtobj.keyCode == 70) {
            $('#extraMenu').css('display', 'block');
            document.getElementById("extraSearchInput").focus();

            document.getElementById("extraSearchInput").addEventListener("keyup", function(event) {
                event.preventDefault();
                if (event.keyCode === 13) { globalSearch(); }
            });
            
        }
    }

    function globalSearch(){

        $.ajax({
            type: 'POST',
            url: "{{route('products.globalSearch')}}",
            data: {
                tag: $('#extraSearchInput').val(),
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                $('#mainContentView').css('display', 'none');
                
                html =  '<div id="globalSearchResult" style="width: calc(100% - 20px);margin: 10px;display: block;border: 3px solid orange;border-radius: 10px;">';
                    html += '<div style="background-color: #bbb;color: #fff;display: inline-table;width: 100%;border-radius: 5px 5px 0 0;">';
                        html += '<div style="float: left; width: calc( 100% - 60px );padding: 5px;font-size: 24px;font-weight: bold;color: #000">SEARCHING FOR: <span style="color: white;">' + $('#extraSearchInput').val() + '</span></div>';
                        html += '<div style="float: left; width: 60px;padding: 5px;">';
                            html += '<button class="btn btn-warning"><i class="fa-solid fa-eraser" style="font-size: 20px;" onclick="cleanGlobalFilter()"></i></button>';
                        html += '</div>';
                    html += '</div>';
                    html += response.html;
                html += '</div>';
                
                $('#globalSearchResult').replaceWith(html);
            }       
        });
        
    }
    
    function cleanGlobalFilter(){
        $('#mainContentView').css('display', 'block');
        $('#globalSearchResult').replaceWith('<div id="globalSearchResult" style="width: calc(100% - 100px);margin: 50px;display: none;"></div>');
    }
    
    window.addEventListener('keydown', keyPressHandler);
</script>
--}}