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

	var myVideoPlayer = document.getElementById('video');

	myVideoPlayer.addEventListener('loadedmetadata', function(e) {
		//console.log(e);
	});
});
