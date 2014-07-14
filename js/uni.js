$.fn.scrollView = function () {
	return this.each(function () {
		$('html, body').animate({
			scrollTop: $(this).offset().top
		}, 1000);
	});
}

$(document).ready(function() {
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

	$("#showCompleted").change(function () {
		if ($(this).prop('checked')) {
			$(".completed").show();
		} else {
			$(".completed").hide();
		}
	});

	$("#showStarted").change(function () {
		if ($(this).prop('checked')) {
			$(".started").show();
		} else {
			$(".started").hide();
		}
	});

	$("#showNew").change(function () {
		if ($(this).prop('checked')) {
			$(".new").show();
		} else {
			$(".new").hide();
		}
	});
});
