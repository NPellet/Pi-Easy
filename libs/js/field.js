// JavaScript Document

//////////////////////////////////////////////////////////////////////////////////////////////////
/// PI-EASY (FRONTEND) ///////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////

$(document).ready(function() { 
		/*				   
	$(".MP3 .UploadZone").each(function() {
		
		var domRoot = $(this).data('root', $(this).parent().parent().parent().parent());
		
		$(this).uploadZone({
			mime: 'image/*',
			nbFiles: 1,
			uploaded: function(file) {
				uploaded(file);
				checkFiles();	
			}
		});
		
		function checkFiles() {
			var nbImages = $(domRoot).data('root').find('.CurrentFile').not('.Hidden').length;
			if(nbImages >= 1) {
	
				$(domRoot).overlay({
					mode: 'div',
					color: 'Red',
					message: 'Le nombre maximal de téléchargements est atteint'
				});
				return false;
			}
		}
	
		function uploaded(file) {
			var $imgDom = $(domRoot).parent().parent().parent().find('.CurrentFile.Hidden:first');
			$imgDom.find('.Mp3Player').html('get_file.php?file=' + file.serverName);
			$imgDom.find('input.mp3Name').val(file.serverName);
			
			$.getJSON('./libs/ajax/mp3infos.php', {file: file.serverName}, function(data) {
				$imgDom
					.find('input[name=id3_title]').val(data.title).end()
					.find('input[name=id3_artist]').val(data.artist).end()
					.find('input[name=id3_album]').val(data.album).end()
					.find('input[name=id3_year]').val(data.year).end()
					.find('input[name=id3_genre]').val(data.genre).end()
					.find('input[name=id3_fleName]').val(file.serverName).end();
			});
			
			$imgDom.removeClass('Hidden').hide().fadeIn('slow');
		};
		
		checkFiles();
		
		$(domRoot).data('root').find('.Remove').bind('click', function() {
			$(this).parent().fadeOut('slow', function() {
				$(this).addClass('Hidden');								  
			});
			$(domRoot).overlay('destroy');																					  
		});	
	
		$(domRoot).data('root').find('.Id3Tags .Form').find('input[type=submit]').bind('click', function(event) {
			
			event.preventDefault();
			event.stopPropagation();
			
			var $form = $(this).parent().parent();
			var $div = $form.parent();
			var serialized = $form.find('input, select').serializeArray();
			serialized.push({name: 'file', value: $form.find('input[name=id3_fleName]').val()});
			
			$.post('./libs/ajax/mp3infos.php', serialized, function(data) {
				$div.overlay({
					mode: 'div',
					color: 'Green',
					message: 'Les informations du fichier MP3 ont correctement été enregistrées',
					button: 'Ok', 
					onButtonClick: function(event, overlay, message) {
						$div.overlay('destroy');
					}			 
				})
			});
													  
		});
	});
	
*/
	$(".Entry .Picture .UploadZone, .Entry .File .UploadZone, .Entry .MP3 .UploadZone").each(function(i, element) {
		
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
			
			var nbImages = $(this).parent().parent().find('.CurrentFile').not('.Hidden').length;
			if(nbImages >= $(this).data('options').nbFiles) {
	
				$(this).parent().overlay({
					mode: 'div',
					color: 'Red',
					message: 'Le nombre maximal de téléchargements est atteint'
				});
				return false;
			}

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
			$(this).parent().parent().hide('slow', function() {
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
	
	
	$("#Mediatheque .UploadZone").each(function() {

		var domRoot = $(this);
		var $uploadRoot = $(domRoot).parent().parent().parent().parent();
		var mime;
		
		$(this).uploadZone({
			mime: '*/*',
			nbFiles: 0,
			uploaded: function(file, zone) {
				uploaded(file);
				return true;
			},
			
			terminated: function(zone) {
				$uploadRoot.find('.Button.Add').removeClass('Hidden').show('slow');
			}
		});
		
		function uploaded(file) {
			
			$.getJSON('./libs/ajax/media_upload.php', 
				{ file: file.serverName, fileName: file.name, folder: $("select[name=rootFolder]").val() }, 
				function(obj) {
			
			if(obj == null)
				return;
				
			if(file.type == 'image/jpeg' || file.type == 'image/gif' || file.type == 'image/png')
				var img = 'get_file.php?file=' + file.serverName + '&folder=' + obj.folder;
			else
				var img = $.logoFromMime(file.type);

				$("#ListFiles").append('\
				<div class="Image Galerie">\
					<a href="./get_file.php?file=' + obj.file + '&folder=' + obj.folder + '" rel="imageBox">\
						<img src="' + img + '" /><br />\
						' + file.name + '\
					</a>\
				</div>');

			});
		};
	});


});

//////////////////////////////////////////////////////////////////////////////////////////////////
/// ADMINISTRATION (BACKEND) /////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////

$(document).ready(function() {
						   
	$(".cfgEnum .Add a, .cfgIdx .Add a").bind('click', function() {
		var cloned = $(this).parent().parent().parent().find('p:first').clone();
		$(cloned).find('input,select').val('');
		$(this).parent().parent().parent().children('p:last').after(cloned);
	});
	
	$(".cfgEnum .Remove a, .cfgIdx .Remove a").bind('click', function() {
		var p = $(this).parent().parent().parent().find('p');
		if(p.length > 1)
			$(p).filter(':last').remove();
	});
	
	$("input[name=fieldMultilang]").bind('click', changeTypeEnum);
	$("input[name=fieldPictureThb]").bind('click', changeTypePicture);
	$("select[name=fieldType]").bind('change', changeType);
	changeType();
	
	
});


function changeTypeEnum() {
	
	if($("select[name=fieldType]").val() == 'enum') {
		$("table.cfgEnum").show();
		if($("input[name=fieldMultilang]").is(':checked')) {
			$(".cfgEnum .Multilang").show().find('input').removeAttr('disabled');
			$(".cfgEnum .Unilang").hide().find('input').attr('disabled', 'disabled');
		} else {
			$(".cfgEnum .Multilang").hide().find('input').attr('disabled', 'disabled');
			$(".cfgEnum .Unilang").show().find('input').removeAttr('disabled');
		}
	}
}

function changeTypePicture() {

	if($("input[name=fieldPictureThb]").is(':checked')) {
		$(".cfgThumb").show();
	} else {
		$(".cfgThumb").hide();
	}
	
	$(".cfgPicture").show();
}
			
function changeType(event) {
	
	var value = $("select[name=fieldType]").val();

	$('table[class^="cfg"]').hide();
	switch(value) {
		
		case 'textarea':
			$("table.cfgTextarea").show();
		break;
		
		case 'picture':
			changeTypePicture();
		break;
		
		case 'file':
			$("table.cfgFile").show();
		break;
		
		case 'numeric':
			$("table.cfgNumeric").show();
		break;
		
		case 'idx':
			$("table.cfgIdx").show();
		break;
		
		case 'fbpost':
			$("table.cfgFBPost").show();
		break;
		
		case 'fbevent':
			$("table.cfgFBEvent").show();
		break;
		
		case 'enum':
			changeTypeEnum();
		break;
	}
}