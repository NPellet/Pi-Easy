// JavaScript Document
$(document).ready(function() {
						   
	$("#PluginGalerie .UploadZone").each(function() {
		var domRoot = $(this);
		var $uploadRoot = $(domRoot).parent();
		var $list = $uploadRoot.children('#ListImages');
		
		var options = [];
		var opts = $(this).find('span[rel=Config]').html().split(';');
		for(var i = 0; i < opts.length; i++) {
			var opt = opts[i].split(':');
			options[opt[0]] = opt[1];
		}
		
		options.type = options.type.slice(',');		
		options.imageUploaded = function(file) {
			
			$.getJSON('./plugins/galeries/ajax/upload.php', { file: file.serverName, album: $("#album").val(), galerie: $("#galerie").val() }, function(obj) {
				$list.append('<div class="Image Galerie">\
				<a href="./plugins/galeries/get_file.php?file=' + obj.file + '&album=' + obj.album + '&galerie=' + obj.galerie + '" rel="imageBox">\
					<img src="./plugins/galeries/get_file.php?file=' + obj.file + '&album=' + obj.album + '&galerie=' + obj.galerie + '&thb" />\
				</a>\
				<div class="Remove">\
					<a>Supprimer</a>\
				</div>\
			</div>');
				
			});
			
			var $imgDom = $(domRoot).parent().parent().parent().find('.CurrentFile.Hidden:first');
			$imgDom.find('img:first').attr('src', 'get_file.php?file=' + file.serverName);
			$imgDom.find('a:first').attr('href', 'get_file.php?file=' + file.serverName);
			$imgDom.find('input').val(file.serverName);
			$imgDom.removeClass('Hidden').hide().fadeIn('slow');
		};
				
		$(this).find('.DragnDrop td').unbind('dragenter').unbind('drop').unbind('dragenter').bind('dragenter', function(event) {
			event.originalEvent.dataTransfer.dropEffect = 'copy';																	   
		}).bind('drop', function(event) {
			
			event.preventDefault();
			var box = $(this).next();
			var files = event.originalEvent.dataTransfer.files;
			var uploader = new Uploader(files, domRoot, options);
			uploader.start();
		});
	});	
	
	$("#PluginGalerie .Remove a").Remove('./plugins/galeries/ajax/remove.php');
});

