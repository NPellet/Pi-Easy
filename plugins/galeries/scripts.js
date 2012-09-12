// JavaScript Document
$(document).ready(function() {
	
	$(".Synchro").bind("click", function() {
			
		$("#FBSynch").html('<div class="Message Orange"><div class="Image"><img src="design/images/ajax-loader-orange.gif" /></div> Le Pi-Easy recherche la liste de vos albums Facebook. Cette opération peut prendre plusieurs minutes. Merci de ne pas quitter la page.</div>');
		$.get("./plugins/galeries/ajax/fbsynchro.ajax.php", {}, function(data) {
			$("#FBSynch").html(data);
		});
	});
	
	$("tr.imgs .UploadZone").each(function() {
				
		var that = this;
		var domRoot = $(that);
		var mime;
		
		mime = 'image/*';
		
		$(domRoot).uploadZone({
			mime: mime,
			nbFiles: 0,
			uploaded: function(file, zone) {
				
				var domRoot = this;
				uploaded.call(this, file);
				return true;
			},
			
			terminated: function() {
				
			}
		});
		

		function uploaded(file) {
			
			$.getJSON('./plugins/galeries/ajax/upload.php', { file: file.serverName, filename: file.name, album: $.nav.album }, function(obj) {
			
			var image = $('\
				<div class="Image Galerie" rel="' + obj.file + '">\
						<img src="./plugins/galeries/get_file.php?file=' + obj.file + '&album=' + obj.album + '&thb" />\
				</div>');
				
				findImages(image);
				$("#ListImages").append(image);
			});
			
			/*
			var $dom = $domRoot.parent().parent().parent().find('.CurrentFile.Hidden:first');
			if(type == 'Picture')
				$dom.find('img:first').attr('src', 'get_file.php?file=' + file.serverName);
			else
				$dom.find('img:first').attr('src', $.logoFromMime(file.type));
			
			$dom.find('a:first').attr('href', 'get_file.php?file=' + file.serverName);
			$dom.find('input[type=hidden]').val(file.serverName);
			var reg = new RegExp('^([^/\?%\*:\|"<>\.]*)\.([a-zA-Z]{0,4})$');
			var parsed = reg.exec(file.name);
			$dom.find('input.Filename').val(parsed[1]);
			$dom.find('.Extension span').html(parsed[2]);
			$dom.find('.Extension input').val(parsed[2]);
			
			$dom.removeClass('Hidden').hide().fadeIn('slow');*/
		};
	});

	$('#PluginGalerie .DataForm').find('tr').not('.imgs').find('.UploadZone').each(function(i, element) {
		
		var that = this;
		var domRoot = $(that);
		var mime;
		
		var options = [];
		var opts = $(that).parent().find('span[rel=Config]').html().split(';');
		for(var i = 0; i < opts.length; i++) {
			var opt = opts[i].split(':');
			options[opt[0]] = opt[1];
		}
		options.type = options.type.slice(',');	
		
		$(that).data('type', $(that).parent().parent().hasClass('Picture') ? 'Picture' : 'File');
		$(that).data('options', options);
		
		mime = $(that).data('type') == 'Picture' ? 'image/*' : '*/*';
		
		$(domRoot).uploadZone({
			mime: mime,
			nbFiles: options.nbFiles,
			uploaded: function(file, zone) {
		
				var domRoot = this;
				uploaded.call(this, file);
				return checkFiles.call(this, domRoot);
			},
			
			terminated: function() {
				$(this).parent().hide('slow');
				$(this).parent().parent().find('.Button.Add').parent().removeClass('Hidden').show('slow');
			}
		});
		
		var checkFiles = function() {
			
			return true;
		}
		
		var uploaded = function(file) {
			
			var $dom = $(this).parent().parent().parent().parent().find('.CurrentFile.Hidden:first');
		
			if($(this).data('type') == 'Picture')
				$dom.find('img:first').attr('src', 'get_file.php?file=' + file.serverName);
			else
				$dom.find('img:first').attr('src', $.logoFromMime(file.type));
			
			$dom.find('a:first').attr('href', 'get_file.php?file=' + file.serverName);
			$dom.find('input[type=hidden]').val(file.serverName);

			$dom.find('input.Filename').val(file.basename);
			$dom.find('.Extension span').html("." + file.extension);
			$dom.find('.Extension input').val(file.extension);
			$dom.removeClass('Hidden').hide().fadeIn('slow');
		};
		
		checkFiles.call(this);

		$(this).parent().parent().find('.Remove').bind('click', function() {
			
			$(this).parent().parent().parent().parent().hide('slow', function() {
				$(this).addClass('Hidden');
				$(this).find('input[type=hidden]').val('');
			});
			
			$(this).parent().parent().parent().parent().parent().find('.UploadZone').parent().overlay('destroy');
		});
		
		$(this).parent().parent().find('.Add').bind('click', function() {
			$(this).parent().parent().parent().find('.New').show('slow');		
			$(this).parent().removeClass('Hidden').hide('slow');
		});
	});



	/*
	$("# .UploadZone").each(function() {
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
	*/

	$(".Buttons .Remove[rel=album]").buttonRemove('Cet album sera supprimé', './plugins/galeries/ajax/remove.php', [$.nav.galerie, $.nav.album]);
	$(".Buttons .Remove[rel=galerie]").buttonRemove('Cette galerie sera supprimée', './plugins/galeries/ajax/remove.php', [$.nav.galerie, $.nav.album]);
	$(".Buttons .Remove[rel=image]").buttonRemove('Cette image sera supprimée', './plugins/galeries/ajax/remove.php', [$.nav.galerie, $.nav.album], ".Image");
	
	$(".Button.Images, .Button.Albums").data('onSelect', function(button, mode, delegated) {

		if($(this).hasClass('Disabled'))
			return;
				
		if(mode == true) {
			if(delegated !== true)
				$(".Button.Edit").pieasybutton('select', true);
			
			$("table.Data tr[rel]").bind('click', function(event) {
				
				if($(button).hasClass('Disabled'))
					return;
			
				event.stopPropagation();
				event.preventDefault();
				var url = {};
				url['mode'] = $(button).attr('rel');
				if($(button).hasClass('Images'))
					url['album'] = $(this).attr('rel');
				else
					url['galerie'] = $(this).attr('rel');
				url['action'] = 'show';
				
				$.redirect(url);
			});
		} else {
			$('table tr').unbind('click');
			if(delegated !== true) {
				$(".Button.Edit").pieasybutton('select', false);
			}
		}
	});
	
	$(".DirectGalerieRemove").bind('click', function() {
		var galId = $(this).attr('rel');
		$.get('./plugins/galeries/ajax/remove.php', {type: 'galerie', toremove: galId}, function() {
			$.redirect({mode: false, action: false, galerie: false, album: false, plugin: 1});
		});
		
	});
});

