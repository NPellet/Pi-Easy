function findImages(content) {			
	$(content).find("a[rel=box]").colorbox({ photo: 1 });
}

function findVideos(content) {
	$(content).find(".build_video").each(function() {
		$(this).buildVideo({ platform: $(this).data('video-platform'), id: $(this).data('video-id') });								
	});	
}

function findTrees(content, callback, mime, type) {

	$(content).find(".Tree").each(function() {
		var root = this;
	
		$(this).find(".Filetree.Media").
		fileTree({ script: './libs/ajax/filetree.php', root: './', mime: mime, type: type, post: 'media' }, 
			function(file, folder, totalFile) {	
				$.getJSON('./libs/ajax/infofile.php', {file: file, folder: folder}, function(data) {
					if($.isFunction(callback))
						callback.call(this, data)
				});
		});	
		
		$(this).find(".Filetree.FTP").
		fileTree({ script: './libs/ajax/filetree.php', root: './', mime: mime, type: type, post: 'ftp' }, 
			function(file, folder, totalFile) {	
				$.getJSON('./libs/ajax/infofile.php', {file: file, folder: folder}, function(data) {
					if($.isFunction(callback))
						callback.call(this, data)
				});
		});	
	});		
}


function buildInfosFile(data, buttons) {

	var mime = data.mime;
	if(data.getThumb != undefined)
		var logo = data.getThumb;
	else if(data.getLogo != undefined)
		var logo = data.getLogo;
	else
		var logo = '';
	
	var file = data.get_file;	
	var buttonsHtml = '';
		
	if(buttons !== undefined)
		buttonsHtml = $(buttons.join(''));

	$(buttonsHtml).attr('rel', data.url);

	var html = 
		'<table cellpadding="0" cellspacing="0">'
	+	'<tr><td colspan="2" class="Logo"><a href="' + file + '"' + (data.type == 'picture' ? ' rel="box"' : ' target="_blank"') + '><img src="' + logo + '" /></a></td></tr>'
	+	'<tr><td colspan="2" class="Filename"><a href="' + file + '"' + (data.type == 'picture' ? ' rel="box"' : ' target="_blank"') + '>' + data.name + '</a></td></tr>'
	+	'<tr><td class="Label">Type MIME : </td><td class="Infos">' + mime + '</td></tr>'
	+	'<tr><td class="Label">Poids : </td><td class="Infos">' + $.formatSize(data.size) + '</td></tr>'
	+	'<tr><td class="Label">Uploadé le :</td><td class="Infos">' + $.formatDate(data.uploaded) + '</td></tr>'
	+	'<tr><td colspan="2" class="Buttons"></td></tr>'
	+	'</table>';
		
	return $(html).find('.Buttons').html(buttonsHtml).end();
}



$(document).ready(function() {

	$(window).bind('dragexit', function() {
		$('div.Selected').trigger('dragexit');								  
	});
	
	findImages(document);
	findVideos(document);
	
	$("textarea.Standard").growfield();
	
	$('textarea.WysiwygExtended').tinymce({
		script_url : $.baseUrl + '/libs/jquery/tinymce/tiny_mce.js',
		theme : "advanced",
		skin: 'o2k7',
		plugins : "pagebreak,style,layer,table,save,advhr,advimage,advlist,emotions,iespell,insertdatetime,preview,media,searchreplace,print,paste,fullscreen,visualchars,nonbreaking,xhtmlxtras",
		theme_advanced_buttons1 : "pastetext, removeformat,|,bold,italic,underline,strikethrough,|,forecolor,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,|,formatselect,link,unlink,|,sub,sup,|,code,fullscreen,",
		theme_advanced_buttons2 : "",
		theme_advanced_buttons3 : "",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true,
		width: '100%',
		height: '300px',
		convert_urls: false,
		paste_remove_styles: true,
		theme_advanced_path: false,
		theme_advanced_statusbar_location: 'none',
		theme_advanced_resizing : true
	});

	$('textarea.WysiwygSimple').tinymce({
		script_url : $.baseUrl + '/libs/jquery/tinymce/tiny_mce.js',
		theme : "advanced",
		skin: 'o2k7',
		plugins : "paste,fullscreen",
		theme_advanced_buttons1 : "pastetext,removeformat, | , bold,italic,underline,forecolor, | ,bullist,numlist, sub,sup, | ,codef,fullscreen ",
		theme_advanced_buttons2 : "",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_resizing : false,
		width: '100%',
		height: '200px',
		convert_urls: false,
		convert_urls: false,
		paste_remove_styles: true,
		theme_advanced_path: false,
		theme_advanced_statusbar_location: 'none',
		theme_advanced_resizing : true
	});
	
    $("select[multiple]").asmSelect({
        sortable: true,
        animate: true,
        addItemTarget: 'top'
    });
	
	$.datepicker.setDefaults($.datepicker.regional['fr']);
	$("input.Date").datepicker({
		changeMonth: true,
		changeYear: true,
		dateFormat: 'dd.mm.yy'
	});
	
	$(".Button").pieasybutton();
	
	$(".Mp3Player").each(function() {
		$(this).flash({
			swf: './libs/dewplayer/dewplayer-rect.swf',
			wmode: 'transparent',
			flashvars: {
				mp3: $(this).text()	
			}
		})
	});
	
	findTrees(document, function(file) {
		$(".InfosFile").html(buildInfosFile(file));	
		findImages($(".InfosFile"));
	}, '*/*', 'image');
	
	$(".UploadMethods li").bind('click', function() {
		var cl = $(this).attr('class');
		$(this).parent().parent().children().not('.UploadMethods').hide('slow').filter('.' + cl).show('slow');
	});
	
	$(".loadVideo").bind('click', function(event) {
		event.preventDefault();
		
		var wrapper = $(this).parent();
		var videoDiv = wrapper.children('.video');
		var videoPlatform = wrapper.children('.video_platform');
		var videoId = wrapper.children('.video_id');
		var url = wrapper.children('.video_url').val();
		
		var exprs = {
			youtube: [/youtube\.com\/v\/([a-zA-Z0-9_-]*)/gi, /youtube\.com\/watch\?v=([a-zA-Z0-9_-]*)/gi],
			dailymotion: [/dailymotion\.com\/video\/v\/([a-zA-Z0-9_]*)/gi, /dailymotion\.com\/video\/([a-zA-Z0-9_]*)/gi, /dailymotion\.com\/swf\/video\/([a-zA-Z0-9_]*)/gi],
			vimeo: [/vimeo\.com\/([0-9]*)/gi, /vimeo\.com\/moogaloop.swf?clip_id=([0-9]*)/gi],
			wat: [/wat\.tv\/swf2\/([a-zA-Z0-9]*)/gi, /wat.tv\/video\/[a-zA-Z0-9_-]*-([a-zA-Z0-9_]*).html/]
		};
		
		function findVideo(url, exprs, platform) {
			
			var id = false;
			for(var j = 0; j < exprs.length; j++) {
				retour = exprs[j].exec(url);
				
				if(retour) {
					var id = retour[1];
					break;
				}
			}
			
			if(!id)
				return false;
				
			return { platform: platform, id: id };
		}
		
		for(var i in exprs) {
			
			if(video = findVideo(url, exprs[i], i)) {
				$(videoId).val(video['id']);
				$(videoPlatform).val(video['platform']);
				$(videoDiv).buildVideo(video);	
			}
		}

	});
	
	
	$(".FieldMap").each(function() {
		var div = this;

		var $name = $(div).find('.Name');
		var $address = $(div).find('.Address');
		var $zip = $(div).find('.ZIP');
		var $city = $(div).find('.City');

		$(this).find('.getMap').bind('click', function(event) {
			
			event.preventDefault();
			event.stopPropagation();
			
			if($(this).data('map') == undefined && !generateMap())
				return false;
			try {
				geocoder = new google.maps.Geocoder();
				geocoder.geocode( { 'address': $address.val() + ", " + $zip.val() + " " + $city.val()}, function(results, status) {
				  if (status == google.maps.GeocoderStatus.OK) {
					var gmap = $(div).data('map');
					gmap.setCenter(results[0].geometry.location);
					gmap.setZoom(13);
					var marker = new google.maps.Marker({
						map: gmap, 
						position: results[0].geometry.location
					});
				  } else {
				  }
				});
			} catch(e) {
				console.log(e);
			}
		});

		if($address.val() != '' || $zip.val() != '' || $city.val() != '') {
			$(div).find('.getMap').trigger('click');	
		}

		function generateMap() {
			
			try {
				if($address.val() != '' || $zip.val() != '' || $city.val() != '') {
			
					var myLatlng = new google.maps.LatLng(-34.397, 150.644);
					var myOptions = {
					  zoom: 8,
					  center: myLatlng,
					  mapTypeId: google.maps.MapTypeId.ROADMAP
					};
					
					var map = new google.maps.Map($(div).children('.GoogleMap').get(0), myOptions);
					$(div).data('map', map);
					return true;
				} else
					return false;
			} catch(e) {
				console.log(e);
			}
		}
	});
});

(function($) {
	$.Plugins = {};
}) (jQuery);

(function($) {
	
	$.fn.buildVideo = function(opts) {
		
		var width = (opts.width == undefined || !opts.width) ? 540 : opts.width;
		var height = (opts.height == undefined || !opts.height) ? 400 : opts.height;
		
		return this.each(function() {
			if(!opts.platform || !opts.id)
				return false;
			
			switch(opts.platform) {
				
				case 'youtube':
					$(this).flash({
						swf: 'http://www.youtube.com/v/' + opts.id,
						flashvars: {
							enablejsapi: 1,
							playerapiid: 'ytplayer'
						},
						width: width,
						height: height,
						hasVersion: 8
					});
				break;
				
				case 'vimeo':
				
					$(this).flash({
						swf: 'http://vimeo.com/moogaloop.swf',
						flashvars: {
							clip_id: opts.id,
							show_portrait: 1,
							show_byline: 1,
							show_title: 1,
							js_api: 1,
							js_onLoad: null,
							js_swf_id: 'Video'
						},
						width: width,
						height: height,
						allowfullscreen: true
					});
				
				break;
				
				case 'wat':
				
					$(this).flash({
						swf: 'http://wat.tv/swf2/' + opts.id,
						flashvars: {
						},
						width: width,
						height: height,
						
						allowfullscreen: true
					});
					
					
				break;
				
				case 'dailymotion':
				
					$(this).flash({
						swf: 'http://www.dailymotion.com/swf/' + opts.id,
						flashvars: {
							enableApi: 1,
							playerapiid: 'dmplayer'
						},
						width: width,
						height: height,
						allowfullscreen: true
					});
					
				break;
				
			}
		});
	}
	
	$.fn.Remove = function(url) {

		$(this).bind('click', function(event) {

			event.preventDefault();
			var tr = $(this).parent().parent();
			var data = {remove: $(this).attr('rel')};
			$(tr).overlay({
				
				mode: 'td',
				message: 'Êtes-vous certain de vouloir supprimer cet élément ?',
				button: 'Supprimer définitivement',
				color: 'Red',
				onButtonClick: function() {
					$.post(url, data, function() {
						$(tr).overlay({
							mode: 'td',
							message: 'Cet élément a correctement été supprimé',
							color: 'Green'
						});
					});
				}
			});
		});
	}
	
	$.redirect = function(newNav) {
		
		$.nav = $.extend($.nav, newNav);
		$.nav.mess = undefined;
		
		var url = '';
		for(var i in $.nav)
			url += $.nav[i] != undefined ? '/' + i + '-' + $.nav[i] : '';
		
		url += '.html';
		document.location.href = $.baseUrl + url;
	}
	
	$.setConfig = function(key, value) {
		$.post('./libs/ajax/setcfg.php', {key: key, value: value});
	}
	
}) (jQuery);
