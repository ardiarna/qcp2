if ($('.back-to-top').length){
	var scrollTrigger = 30,
	backToTop = function () {
		var scrollTop = $(window).scrollTop();
		if (scrollTop > scrollTrigger) {
			$('.back-to-top').show();
		} else {
			$('.back-to-top').hide();
		}
	};
	backToTop();
	
	$(window).on('scroll', function () {
		backToTop();
	});
	$('.back-to-top').on('click', function (e) {
		e.preventDefault();
		$('html,body').animate({scrollTop: 0}, 700);
	});
}

function checkTime(i) {
    if (i < 10) {i = "0" + i};  // add zero in front of numbers < 10
    return i;
}

function startTime() {
    var today = new Date();
    var h = today.getHours();
    var m = today.getMinutes();
    var s = today.getSeconds();
    h = checkTime(h);
    m = checkTime(m);
    s = checkTime(s);
    document.getElementById('txt').innerHTML = h + ":" + m + ":" + s;
    var t = setTimeout(startTime, 500);
}

function salinTabel(namatabel) {
    var out = '';
    $(namatabel).each(function() {
        // out += '<table>';
        $(this).find("tr").each(function() {
            out += '<tr>';
            $(this).find("th:visible, td:visible").each(function() {
                var text = $(this).text()
                text = text.trim();
                out += '<td>' + text + '</td>';
            });
            out += '</tr>';
        });
        // out += '</table>';
    });
    return out;
}

function salinDataTabel(namatabel) {
    var out = [];
    var a = 0;
    $(namatabel).each(function() {
        $(this).find("tr").each(function() {
            out[a] = {};
            var b = 0;
            $(this).find("th:visible, td:visible").each(function() {
                var text = $(this).text()
                text = text.trim();
                out[a][b] = text;
                b++;
            });
            a++;
        });
    });
    return out;
}

jQuery.fn.afDigitOnly = function() {
    return this.each(function() {
        $(this).keyup(function(e) {
			if (/\D/g.test(this.value)) { 
				this.value = this.value.replace(/\D/g, '');
        	}
        });
    });
};

jQuery.fn.afNumericOnly = function() {
    return this.each(function() {
        $(this).keyup(function(e) {
			if (/([^.0123456789]|)/g.test(this.value)) { 
				this.value = this.value.replace(/([^.0123456789])/g, '');
        	}
        });
    });
};

jQuery.fn.afWordOnly = function() {
    return this.each(function() {
        $(this).keyup(function(e) {
			if (/\W/g.test(this.value)) { 
				this.value = this.value.replace(/\W/g, '');
        	}
        });
    });
};

jQuery.fn.afInputVal = function() {
    return this.each(function() {
        $(this).keyup(function(e) {
			if (/(--|[,!@#$%^&*+='";])/g.test(this.value)) { 
				this.value = this.value.replace(/(--|[,!@#$%^&*+='";])/g, '');
        	}
        });
    });
};