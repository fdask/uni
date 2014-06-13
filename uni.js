// from http://davidwalsh.name/fullscreen
function launchFullscreen(element) {
	if (element.requestFullscreen) {
		element.requestFullscreen();
	} else if (element.mozRequestFullScreen) {
		element.mozRequestFullScreen();
	} else if (element.webkitRequestFullscreen) {
		element.webkitRequestFullscreen();
	} else if (element.msRequestFullscreen) {
		element.msRequestFullscreen();
	}
}

function isFullScreen(element) {
	if (document.mozFullscreen) {
		return document.mozFullscreen;
	} else if (document.webkitIsFullscreen) {
		return document.webkitIsFullscreen;
	}
}

$.fn.scrollView = function () {
	return this.each(function () {
		$('html, body').animate({
			scrollTop: $(this).offset().top
		}, 1000);
	});
}

function server(data, completeFunc) {
	completeFunc = completeFunc || function (data) {};

	$.ajax({
		url: "ajax.php",
		type: "POST",
		data: JSON.stringify(data),
		contentType: 'application/json; charset=utf-8',
		dataType: 'json',
		async: true,
		error: function(httpRequest, textStatus, errorThrown) {
			console.log("status=" + textStatus + ",error=" + errorThrown);
		},
		complete: completeFunc
	});
}

$(document).ready(function() {
	$("#video").on('ended', function(e) {
		var sendme = {
			videoid: $("#videoid").html(),
			action: 'watched'
		};

		server(sendme, function (data) {
			if ($("#playToModuleEnd").prop("checked") && $("#lastInModule").val() == "0") {
				setTimeout(test1, 4000);
			}
		});
	});

	$(".navbutton").on('click', function(e) {
		var cp = 0;

		if ($("#playToModuleEnd").prop("checked")) {
			cp = 1;
		}

		// is full screen?  ideally when someone is playing automatically to the next video, keep them fullscreen!
		// TODO
		var fs = 0;

		document.location.href = "video.php?vid=" + $(this).data('vid') + "&cp=" + cp;
	});

	$("#saveNote").on('click', function(e) {
		var videoid = $(this).data('vid');
		var note = $("#note").val();

		var sendme = {
			videoid: $(this).data('vid'),
			action: 'saveNote',
			note: $("#note").val()
		};

		server(sendme);
	});

	$("#unwatch").on('click', function(e) {
		var sendme = {
			videoid: $("#videoid").html(),
			action: 'unwatch'
		};

		server(sendme);
	});

	$("#clearBookmark").on('click', function(e) {
		var sendme = {
			videoid: $("#videoid").html(),
			action: 'clearBookmark'
		};

		server(sendme);
	});

	$("#jumpToBookmark").on('click', function(e) {
		$("#video").get(0).currentTime = $(this).data('time');
	});

	$("#addBookmark").on('click', function(e) {
		$('#video').get(0).pause();

		var sendme = {
			videoid: $("#videoid").html(),
			time: $('#video').get(0).currentTime,
			action: 'addBookmark'
		};

		server(sendme);
	});

	$("#container").accordion({
		active: false,
		collapsible: true,
		header: "h2",
		heightStyle: "content",
	});

	$(".modules").accordion({
		active: false,
		collapsible: true,
		header: "h3",
		heightStyle: "content",
		beforeActivate: function (e, ui) {
			console.log("scrolling");
			$(this).scrollView();
		}
	});

	if (window.location.hash) {
		var hash = window.location.hash;
		var type = hash.substring(1, 2);

		// find the element that has this 
		var el = null;

		if (type == "c") {
			el = $("body").find(hash).closest('h2');
			el.trigger('click'); //.scrollView();
		} else if (type == "m") {
			// we have to hit the parent first
			el = $("body").find(hash).closest('h3');

			var p = el.parents();
			$(p[2].childNodes[0]).trigger('click');

			el.trigger('click');
			//el.scrollView();
		}
	}

	$(".fa-file-o").click(function () {
		event.stopPropagation();
	});
});
