/***************************************************************************************
**	Uploader.js, v2.1
**	Last update: 27 Feb. 2011
**	Upload des fichiers sur un serveur par partie
**	Utilise jQuery (last)
**	© 2010 - 2011 Norman Pellet, norman.pellet@gmail.com
**	Toute reproduction partielle ou complète est soumise à autorisation de l'auteur	
****************************************************************************************/

(function($) {
	
$.fn.upload = function(options) {
	
	return this.each(function() {
		
		var that = this;
		
		if(typeof FileReader == undefined)
			return;
		
		if(options.url == undefined)
			return;
		
		var queue = [], paused = false, reader = new FileReader();
		var sliceSize = 8388608;
		
		// Iterated vars
		var currentFileId = 0, uploaded = 0, start = 0;
		
		var start = function() {
			nextQueue();
		}

		function stackQueue(file) {
			queue.push(file);
			file.id = queue.length - 1;		
			
			if($.isFunction(options.fileQueue))
				options.fileQueue.call(that, file);
		}
			
		function nextQueue() {
			var file = queue[currentFileId];
			if(file) {
				if($.isFunction(options.fileStart))
					options.fileStart.call(that, file);

				currentFileId++;
				read(file);
			} else {
				if($.isFunction(options.terminated)) 
					options.terminated.call(that);
				return;
			}
		}
			
		function read(file) {
			uploaded = 0;	
			initial = 0;
			initiateFile(file);
		}
			
		function readSlice(file, initial) {
			
			if(paused == true)
				return;
			if(initial > file.size)
				 terminateFile(file);	
			else {
				if(file.slice !== undefined)		
					var slice = file.slice(initial, initial + sliceSize);
				else if(file.mozSlice !== undefined)
					var slice = file.mozSlice(initial, initial + sliceSize);
				else
					var slice = file;
					
				reader.readAsBinaryString(slice);
				reader.onloadend = function(event) {
					upload(file, event.target.result);
				}
			}
		}
			
		function upload(file, content) {
					
			var xhr = new XMLHttpRequest();
			xhr.timeout = 6000000;
			var self = this;
			
			xhr.upload.addEventListener("progress", function(evt) { 
				if (evt.lengthComputable) {
					var percentage = Math.round((evt.loaded * 100) / evt.total);   
					var onServer = uploaded + evt.loaded;
					progress(file, percentage, onServer, evt.total);
				}
			}, false);
			
			
			xhr.onreadystatechange = function() { 
				if(xhr.status == 200) {
					if(xhr.readyState == '4') { 
						uploaded += sliceSize;
						initial += sliceSize;
						readSlice(file, initial);
					}
				} else {
					// Gestion de l'erreur
				}
			};
	
			xhr.open("POST", options.url);
			xhr.sendAsBinary(content);
		}
			
		function initiateFile(file) {
			$.getJSON(options.url, {newfile: true, filesize: file.size, filename: file.name}, function(data) {
				file['serverName'] = data.filename;
				file['basename'] = data.basename;
				file['extension'] = data.extension;
				readSlice(file, initial);												 
			});
		}
			
		function terminateFile(file) {
			if($.isFunction(options.fileUploaded)) {
				var next = options.fileUploaded.call(that, file);
				
				if(next === false || next === undefined) {
					if($.isFunction(options.terminated)) 
						options.terminated.call(that);
					return;
				}
			}
			nextQueue();
		}
		
		function progress(file, percentage, onServer, total) {
			if($.isFunction(options.fileProgress))
				options.fileProgress.call(that, file, percentage, onServer, total);
		}
			
		function init(files) {
			var first = true;
			var j = 0;
			var fileType, mime;
			
			if(options.mime instanceof Array)
				options.mime = options.mime;
			else
				options.mime = [options.mime];
			
			for(var i in files) {
				var file = files[i];	
				if(parseInt(i) == i) {
					
					for(var k = 0; k < options.mime.length; k++) {
						fileType = file.type.split('/');
						mime = options.mime[k].split('/');
						if((fileType[0] == mime[0] || mime[0] == '*') && (fileType[1] == mime[1] || mime[1] == '*'))
							break;
					}
					
					if(k == options.mime.length)
						continue;

					j++;
					if(options.nbFiles != undefined && options.nbFiles > 0 && j > options.nbFiles)
						return;
						
					stackQueue(file);
				}
			}
		}
		
		$(document).unbind('dragenter').unbind('dragleave').unbind('dragover').bind('dragleave', function(event) {
			return false;
		}).bind('dragenter', function(event) {
			event.preventDefault();
			return true;
		}).bind('dragover', function(event) {
			event.originalEvent.returnValue = false;
			return false;
		});

		if($(this).filter('input[type=file]').length == 1)
			type = 'file';
		else if($(this).filter('input[type=text]').length == 1)
			type = 'external';
		else if($(this).attr('rel') == 'box_filetree')
			type = 'filetree';
		else
			type = 'zone';
		
		switch(type) {
			
			case 'file':
				$(that).bind('change', function(event) {
					event.preventDefault();
					var files = $(this).get(0).files;	
					init(files);
					start();
				});
			break;
			
			case 'zone':
				
				var td = $(this).find('td');
				$(that)
				.bind('dragenter', function() { $(this).addClass('Selected'); })
				.bind('dragexit', function() { $(this).removeClass('Selected'); })
				.bind('drop', function(event) {	
					event.preventDefault();
					var files = event.originalEvent.dataTransfer.files;
					init(files);
					start();
					$(this).trigger('dragexit');
				});

			break;
			
			case 'external':
			
				var but = $('<input type="submit" value="Ok">').bind('click', function(event) {
					event.preventDefault();
					event.stopPropagation();
					var input = $(this).prev();
					$.getJSON('./libs/ajax/getexternal.php', {url: input.val() }, function(file) {
						terminateFile(file);
						$(input).val('');
					});
				}).hide();
				$(that).after(but);
				
				var zone = this;
				$(that).bind('focus', function() {
					var input = this;
					but.fadeIn();
					
				}).bind('blur', function() {
					$(that).next().fadeOut();				
				});
			
			break;
			
			case 'filetree':
		
				function lookForTrees(content) {
					findTrees(content, function(data) {
						var html = $(buildInfosFile(data, ['<input type="submit" class="Select" value="Sélectionner" />']));
						$(html).find('.Select').bind('click', function() {
							var fileUrl = $(this).attr('rel');
							$.getJSON('./libs/ajax/getinternal.php', {url: fileUrl }, function(file) {
								terminateFile(file);
								$.colorbox.close();
							});																   
						}, options.mime);
						
						$(content).find('.InfosFile').html(html);				
						findImages(content);
					});
				}

				$(that).colorbox({	  
					height: '80%',
					width: '60%',
					onComplete: function() {
						lookForTrees(document);
					}
				});
			
			break;
		}
	});
}
}) (jQuery);
	

(function($) {
	
	$.fn.uploadZone = function(options) {
		
		var that = this;
		var options = jQuery.extend(true, {}, $.fn.uploadZone.defaults, options);
		var $fileList = $(this).find('.FileList').children('tbody');
		var $current = $(this).find('.CurrentFile');
		var reader = new FileReader();
		var root = this;
		
		$(this).find('input[type=file], .DnD div, input.External, a[rel=box_filetree]').upload({
			
			url: options.url,
			nbFiles: options.nbFiles,
			mime: options.mime,
			fileQueue: function(file) {
				$fileList.append('<tr rel="' + file.id + '"><td>' + file.name + '</td><td>En attente</td><td>' + $.formatSize(0) + '</td><td>' + $.formatSize(file.size) + '</td></tr>');
			},
			fileStart: function(file) {	
			
				switch(file.type) {
					case 'image/png':
					case 'image/jpeg':
					case 'image/gif':
						domPicture(file);
					break;
					
					default:
						domFile(file);
					break;
				}

				$fileList
					.children('tr[rel=' + file.id + ']').children(':eq(1)')
					.html('Téléchargement en cours');
			},
			fileUploaded: function(file) {
				$fileList
					.children('tr[rel=' + file.id + ']').children(':eq(1)')
					.html('Téléchargement terminé').end().children(':eq(2)').html($.formatSize(file.size));
					
				if($.isFunction(options.uploaded))
					var returned = options.uploaded.call(that, file);	
				else
					var returned = true;
				
				return returned;
			},
			fileProgress: function(file, partPerc, onServer, total) {
				$fileList
					.children('tr[rel=' + file.id + ']').children(':eq(2)')
					.html($.formatSize(onServer));
			},
			terminated: function() {
				if($.isFunction(options.terminated))
					return options.terminated.call(that, root);
			}
		});
		
				
		function domPicture(file) {
			reader.onloadend = function(event) {
				var Image = document.createElement('img').setAttribute('src', event.target.result);
				$(Image).css({ maxWidth: 200, maxHeight: 200 }).load(function() {
					$current.html(Image);					  
				});
			};
		}
		
		function domFile(file) {
			var logo = $.logoFromMime(file.type);
			var Image = document.createElement('img').setAttribute('src', logo);
			$(Image).load(function() {
				$current.html(Image);					  
			});
		}
	}

	$.fn.uploadZone.defaults = {
		url: './libs/ajax/putfile.php',
		nbFiles: 1,
		mime: '*/*'
	};
	
	
}) (jQuery);


(function($) {
	
	$.logoFromMime = function(mime) {
		
		var baseUrl = './design/images/';
		
		switch(mime) {
			case 'application/pdf':
				return baseUrl + 'pdf.gif';
			break;
			
			default:
				return baseUrl + 'file.gif';
			break;		
			
			
			case 'application/pdf':
				return baseUrl + 'pdf.gif';
			break;
			case 'audio/mpeg':
				return baseUrl + 'mp3.gif';
			break;
			
			/*case 'image/gif':
			case 'image/png':
			case 'image/jpeg':
				$strPath .= 'pic.png';
			break;*/
	/*		
			case 'text/html':
				$strPath .= 'html.png';
			break;
*/
			case 'text/plain':
				return baseUrl + 'txt.gif';
			break;

			case 'application/zip':
				return baseUrl + 'zip.gif';
			break;
			
			case 'application/msword':
			case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
				return baseUrl + 'doc.gif';
			break;
			
			case 'application/vnd.ms-excel':
			case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
				return baseUrl + 'xls.gif';
			break;		
		}
	}
	
	$.formatSize = function(size) {

		var i = 0;
		while(size > 1024) {
			size = size / 1024;	
			i++;
		}
		var units = ['o', 'Ko', 'Mo', 'Go', 'To'];
		return (Math.round(size * 10) / 10) + ' ' + units[i];	
	}
	
	$.formatDate = function(timestamp) {
		
		var date = new Date();
		date.setTime(timestamp * 1000);
		
		return date.getDate() + "/" + date.getMonth() + "/" + date.getFullYear();
	}



}) (jQuery);