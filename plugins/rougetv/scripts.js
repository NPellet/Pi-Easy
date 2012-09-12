// JavaScript Document

$(document).ready(function() {

	$("#PluginRougeTV input[type=submit]").bind('click', function(event) {
		
		event.preventDefault();
		event.stopPropagation();
		
		var id = $("#PluginRougeTV .idVideo").val();
		var url = $("#PluginRougeTV .urlVideo").val();
		var dl = $("#PluginRougeTV .dlVideo").is(':checked') ? true : false;
		
		if(id != '') {
			get_file({id: id, dl: dl});
		} else if(url != '')
			get_file({url: url, dl: dl});
	});
});

function get_file(d) {

	var loading = $('<div class="Message Ok">Chargement du fichier en cours.' + (d.dl ? 'Cette opération peut prendre plusieurs minutes. Merci de patienter et ne pas actualiser la page' : '') + '</div>').hide().fadeIn('slow');
	
	$("#PluginRougeTV table.DataForm").after(loading);

	$.ajax({
		url: './plugins/rougetv/ajax/get.php',
		data: d,
		type: 'get',
		timeout: 6000000,
		success: function(data) {
		
			$(loading).fadeOut(function() {
				if(d.dl == false) {
					$(loading).html('Le fichier a été trouvé sur le serveur de Rouge TV. L\'url directe est : ' + data).fadeIn('slow');
				} else {
					$(loading).html('Le fichier a été correctement téléchargé dans votre médiathèque. Son nom est : ' + data).fadeIn('slow');
				}
			});
		}
	})
}