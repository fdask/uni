var Video = function (id, htmlObj, autoload) {
	this.name = null;
	this.sequence = null;
	this.moduleId = null;
	this.moduleName = null;
	this.courseId = null;
	this.courseName = null;
	this.lastInModule = null;
	this.vidLength = null;
	this.loaded = false;
	this.autoload = autoload || true; 
	this.testing = {test1: 'cheese'};

	// private variables
	var _id = null;
	var _nextId = null;
	var _els = null;
	var _note = null;
	var _nextId = null;
	var _prevId = null;
	var _bookmark = null;
	var _videoSrc = null;
	var _watched = null;

	Object.defineProperty(this, "id", {
		get: function () {
			return _id;
		},
		set: function (newVal) {
			_id = newVal;
			
			if (this.autoload) {
				this.load.call(this);
			}
		}
	});

	Object.defineProperty(this, "nextId", {
		get: function () {
			return _nextId;
		},
		set: function (newVal) {
			_nextId = parseInt(newVal);

			// enable the navNext button
			if ("navNext" in this.els) {
				if (_nextId != null) {
					$(this.els.navNext).removeAttr("disabled");
				} else {
					$(this.els.navNext).attr("disabled", "disabled");
				}
			}
		}
	});

	Object.defineProperty(this, "prevId", {
		get: function () {
			return _prevId;
		},
		set: function (newVal) {
			_prevId = parseInt(newVal);

			// enable the navPrev button
			if ("navPrev" in this.els) {
				if (_prevId != null) {
					$(this.els.navPrev).removeAttr("disabled");
				} else {
					$(this.els.navPrev).attr("disabled", "disabled");
				}
			}
		}
	});

	Object.defineProperty(this, "bookmark", {
		get: function () {
			return _bookmark;
		},
		set: function (newVal) {
			_bookmark = newVal;

			if (_bookmark != null) {
				if ("bookmarkClear" in this.els) {
					$(this.els.bookmarkClear).removeAttr("disabled");
				}

				if ("bookmarkJump" in this.els) {
					$(this.els.bookmarkJump).removeAttr("disabled");
				}
			} else {
				if ("bookmarkClear" in this.els) {
					$(this.els.bookmarkJump).attr("disabled", "disabled");
				}

				if ("bookmarkJump" in this.els) {
					$(this.els.bookmarkJump).attr("disabled", "disabled");
				}
			}
		}
	});

	Object.defineProperty(this, "note", {
		get: function () {
			return _note;
		},
		set: function (newVal) {
			_note = newVal;

			$(this.els.note).val(_note);
		}
	});

	Object.defineProperty(this, "videoSrc", {
		get: function () {
			return _videoSrc;
		},
		set: function (newVal) {
			_videoSrc = newVal;

			if (_videoSrc != null) {
				if ("video" in this.els) {
					$(this.els.video).attr("src", _videoSrc);	
				} 
			} 
		}
	});

	Object.defineProperty(this, "watched", {
		get: function () {
			return _watched;
		},
		set: function (newVal) {
			if (newVal) {
				_watched = true;
			} else {
				_watched = false;
			}

			if ("watchClear" in this.els) {
				if (_watched) {
					$(this.els.watchClear).removeAttr("disabled");
				} else {
					$(this.els.watchClear).attr("disabled", "disabled");
				}	
			}
		}
	});

	// parse out element names and assign callbacks
	Object.defineProperty(this, "els", {
		get: function () {
			return _els;
		},
		set: function (newVal) {
			_els = newVal;

			// assign the callbacks to the buttons
			for (el in this.els) {
				switch (el) {
					case 'bookmarkSet':
						$(this.els.el).on('click', this.bookmarkSet);
						break;
					case 'bookmarkClear':
						$(this.els.el).on('click', this.bookmarkClear);
						break;
					case 'bookmarkJump':
						$(this.els.el).on('click', this.bookmarkJump);
						break;
					case 'noteSet':
						$(this.els.el).on('click', this.noteSet);
						break;
					case 'watchClear':
						$(this.els.el).on('click', this.watchClear);
						break;
					case 'navNext':
						break;
					case 'navPrev':
						break;	
					default:
				}
			}
		}
	});

	// parse out the html element names and assign callbacks
	this.els = htmlObj;

	// autoload defaults to true
	this.id = id;
};

Video.prototype = function () {
	var watchClear = function () {
  		this.transmit.call(this, {
			videoid: this.id,
      	action: 'unwatch'
		}, function () {
			this.watched = false;
		});
	}, 
	watchSet = function () {
		this.transmit.call(this, {
			videoid: this.id,
	    	action: 'watched'
		}, function () {
			this.watched = true;
		});
	},
	noteSet = function () {
		var note = this.els.noteEl.value;
		
		this.transmit.call(this, {
			videoid: this.id,
			action: 'saveNote',
			note: note
		}, function () {
			this.note = note;
		});
	},
	bookmarkSet = function () {
		var curTime = this.els.videoEl.currentTime;
		
		this.transmit.call(this, {
			videoid: this.id,
			time: curTime,
			action: 'addBookmark'
		}, function () {
			this.bookmark = curTime;
		});
	},
	bookmarkClear = function () {
		this.transmit.call(this, {
			videoid: this.id,
			action: 'clearBookmark'
		}, function () {
			this.bookmark = null;
		});
	},
	bookmarkJump = function () {
		if (this.bookmark) {
			this.els.videoEl.currentTime = this.bookmark;
		}
	},
	transmit = function (data, successFunc) {
 		successFunc = successFunc || function (data) {};

		$.ajax({
			url: "ajax.php",
			type: "POST",
			data: JSON.stringify(data),
			contentType: 'application/json; charset=utf-8',
			dataType: 'json',
			async: true,
			context: this,
			error: function(httpRequest, textStatus, errorThrown) {
				console.log("status=" + textStatus + ", error=" + errorThrown);
			},
			success: successFunc
		});
	},
	load = function () {
		this.transmit.call(this, {
			videoid: this.id
		}, function (data, stat) {
			if (stat == "success") {
				// map the data
   			this.name = data.name;
				this.sequence = parseInt(data.sequence);
   			this.moduleId = parseInt(data.moduleid);
				this.moduleName = data.modulename;
   			this.courseId = parseInt(data.courseid);
				this.courseName = data.coursename;
				this.nextId = data.navNext;
				this.prevId = data.navPrev;
				this.bookmark = data.bookmark;
				this.note = data.note;
				this.lastInModule = data.last;
				this.videoSrc = data.loc;
				this.vidLength = data.length;
				this.watched = data.lastwatch;
				this.loaded = true;

				console.log(this);
			} else {
				// error
				console.log("error condition!");
				console.log(data, stat);
			}
		});
	};

	return {
		load: load,
		watchClear: watchClear,
		watchSet: watchSet,
		noteSet: noteSet,
		bookmarkSet: bookmarkSet,
		bookmarkClear: bookmarkClear,
		bookmarkJump: bookmarkJump,
		transmit: transmit
	};
}();
