// Javascript file

$(document).ready(function() {
	$("#AffinitesTree").dynatree({
		
		onActivate: function(node) {
			alert("You activated " + node.data.title);
		},
		  
		initAjax: {
			url: "./plugins/affinites_handler/json/get_events.json.php"
		},
		
		onActivate: function(node) {
			
			var id = node.data.key;
			console.log(id);
			if(id.indexOf("subscription") < 0)
				return;
				
			$.ajax({
				url: "./plugins/affinites_handler/html/details_subscription.php",
				type: "get",
				dataType: "text",
				data: { 
					subscription_id: id.replace("subscription_", "")
				},
				
				success: function(data) {
					$("#AffinitesDetailsSubscription").html(data);
				}
			});
		},
		
		onLazyRead: function(node) {
			
			var id = node.data.key;
			if(id.indexOf("event_") > -1) {
				
				file = 'get_dates.json.php';
				data = {
					eventid: id.replace("event_", "")
				}
			} else if(id.indexOf('date_') > -1) {
				
				file = 'get_subscriptions.json.php';
				data = {
					dateid: id.replace("date_", "")
				}
			}
			
			node.appendAjax({
				url: "./plugins/affinites_handler/json/" + file,
				data: data
			});
		},
		debugLevel: 2
	});


});