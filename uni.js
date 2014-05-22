$.fn.scrollView = function () {
	return this.each(function () {
		$('html, body').animate({
			scrollTop: $(this).offset().top
		}, 1000);
	});
}

function server(data) {
	$.ajax({
		url: "ajax.php",
		type: "POST",
		data: JSON.stringify(data),
		contentType: 'application/json; charset=utf-8',
		dataType: 'json',
		async: true,
		success: function (data) {
		},
		error: function(httpRequest, textStatus, errorThrown) {
			console.log("status=" + textStatus + ",error=" + errorThrown);
		}
	});
}

$(document).ready(function() {
	$("#video").on('ended', function(e) {
		var sendme = {
			videoid: $("#videoid").html(),
			action: 'watched'
		};

		server(sendme);
	});

	$(".navbutton").on('click', function(e) {
		document.location.href = "video.php?vid=" + $(this).data('vid');
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

	$("#container").accordion({
		active: false,
		collapsible: true,
		header: "h2",
		heightStyle: "content"
	});

	$(".modules").accordion({
		active: false,
		collapsible: true,
		header: "h3",
		heightStyle: "content"
	});

	if (window.location.hash) {
		var hash = window.location.hash;
		var type = hash.substring(1, 2);

		// find the element that has this 
		var el = null;

		if (type == "c") {
			el = $("body").find(hash).closest('h2');
			el.trigger('click').scrollView();
		} else if (type == "m") {
			// we have to hit the parent first
			el = $("body").find(hash).closest('h3');

			var p = el.parents();
			$(p[2].childNodes[0]).trigger('click');

			el.trigger('click');
			el.scrollView();
		}
	}
});
