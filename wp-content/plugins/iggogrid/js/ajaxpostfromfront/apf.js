function getWeekselector(date){
	var startdate=date[0],enddate=date[1];
//	console.log(startdate);
//	console.log(enddate);
	jQuery.ajax({
        type: 'POST',
        url: apfajax.ajaxurl,
        data: {
            action: 'apf_weekselector',
            startdate: startdate,
            enddate: enddate
        },
        success: function(data, textStatus, XMLHttpRequest) {
        	var data = $.trim(data);
        	data = $.parseJSON(data);
        	var html = '';
        	$(".spinner-loading").hide();
        	setDataByWeek(date,data,'list_html_hidden');
//        	console.log($('.list_html_hidden .single-item').length);
//    		$('.list_html_hidden .single-item').filter(function(index) {
//    			var value = $(this).find(".item_id").val();
//    			value = parseInt(value);
////    			console.log(value);
////    			console.log(data);
//    			if( $.inArray(value, data) !== -1){
//    				html += '<div class="col-xs-12 single-item">'+$(this).html()+'</div>';
//    				return true;
//    			}
//    			return false;
//    		}); 
    		
//    		$(".data_list").html(html);
    		var filter = {
				'area' : $("#iggo_area").val(),
				'type' : $("#iggo_type").val(),
				'person' : $("#iggo_person").val(),
				'rooms' : $("#iggo_rooms").val(),
				'internet' : $("#iggo_internet").val(),
				'animals' : $("#iggo_animals").val(),
				'washing' : $("#iggo_washing").val(),
				'acctype' : $("#iggo_acctype").val(),
				'distance' : $("#iggo_distance").val(),
			}
			filter = checkFilter(filter);
			filterSingleItem(filter);
    		
//    		console.log(html);
        },
        error: function(MLHttpRequest, textStatus, errorThrown) {
            alert(errorThrown);
        }
 
    });
}