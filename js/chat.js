// ==ClosureCompiler==
// @output_file_name default.js
// @compilation_level SIMPLE_OPTIMIZATIONS
// ==/ClosureCompiler==

// This is not the source code you are looking for!
// http://closure-compiler.appspot.com/home to minify.

var apiRegion = 1;
var showPub = true;
var showEn = false;
var showRe = false;
var refreshInterval = 30; // in seconds
var maxIdleTime = 5; // in minutes

var apiKey = getURLParam('key');
var apiUrl = 'http://ingress.writhem.com/api/?table=chat&key='+apiKey;

// dont change below this... unless your name is Michael Writhe!

jQuery(function($){
    // handle async links
    $('a.asynclink').click(
        function (event)
        {
            event.preventDefault();
            
            var anchor = $(this);
            $.get(anchor.attr('href'),
                  function (data)
                  {
                      refresh();
                  }
            );
        }
    );

    // idle handle bars mustaches.
    $('body').mousemove(idleReset).keypress(idleReset);

    $('#chat').scroll(function() {
        var t = $(this);
        if(t.data('ignoreNextScroll')) return t.data('ignoreNextScroll', false);
        if(t.scrollTop() === 0) loadJSON(false);
        if(scrollBottom(t) === 0) loadJSON(true);
    });
});

var last = 0;
var first = new Date().getTime()/1000;
var loaded = false;
$(document).ready(function() {
    refresh();
    start_info_refresh();
    
    window.addResumeFunction(loadJSON);
    window.addResumeFunction(start_info_refresh);

});

var info_refresh = null;
var start_info_refresh = function() {
	if (info_refresh == null)
	{
		info_refresh = setInterval(refresh, refreshInterval * 1000);
	}
}
var stop_info_refresh = function() {
    info_refresh = null;
}

var refresh = function() {
    if (isIdle()) {
        $('#status').html('Idle, not updating');
    } else {
        loadJSON(false);
        applyFilters();
    }
}

var loadJSON = function(old) {
    var jsonUrl = apiUrl + '&after=' + last;
    if (old) jsonUrl = apiUrl + '&before=' + first;
    console.log(jsonUrl);
    $('#loading').show();
    $('#status').html('Loading...');
    var request = $.getJSON(jsonUrl, function(json) {
        if (json && !old) {
            jQuery.each(json.reverse(), function() {
                $("#chat").prepend(buildLog(this));
            });      
        } else if (json) {
            jQuery.each(json, function() {
                $("#chat").append(buildLog(this));
            });      
        }
    });
    $.when(request).done(function() {
        $('#loading').hide();
        $('#status').html('Up to date.');
        loaded = true;
        applyFilters();
    });
}

var buildLog = function(elem) {
    var ts = new Date(elem.datetime * 1000);
    //console.log("first:"+first+"; last:"+last+"; elem:"+elem.datetime);
    if (elem.datetime > last) last = elem.datetime + 1;
    if (elem.datetime < first) first = elem.datetime -1;
    var channel = 0;
    if (elem.channel == "RESISTANCE") {
        channel = 1;
    } else if (elem.channel == "ENLIGHTENED") {
        channel = 2;
    }
    var htmlString = "  <p class=\"region-"+elem.region[0].guid+" channel-"+channel+"\"  style=\"display: none;\">\n";
    htmlString += "    <time class=\"timestamp\" title=\"";
    htmlString += ts.toString();
    //htmlString += ts.getYear() + "-" + ts.getMonth() + "-" + ts.getDate() + " " + ts.getH.2013-2-24 7:06:24 PM
    htmlString += "\" data-timestamp=\""+elem.datetime+"\">"+ts.getHours()+":"+ts.getMinutes()+"</time>\n";
    htmlString += "    <span class=\"invisibleseparator\"> &lt;</span> \n";
    htmlString += "    <span class=\""+elem.user[0].faction+"\">"+elem.user[0].name+"</span> \n";
    htmlString += "    <span class=\"invisibleseparator\">&gt; </span> \n";
    htmlString += "    <span class=\""+elem.channel+"\">"+elem.text+"</span> \n";
    htmlString += "  </p>";
    return htmlString;
}

var applyFilters = function() {
    if (apiRegion == 1) {
        $('#Calgary').addClass('active');
        $('#Edmonton').removeClass('active');
    } else {
        $('#Edmonton').addClass('active');
        $('#Calgary').removeClass('active');
    }
    $("#chat > p").each(function() {
        // cycle every entry... check it
        if ($(this).hasClass('region-'+apiRegion)) {
            // region matches, but does the channel?
            if (showPub) {
                // region matches, public matches! SHOW!
                $('#showPub').addClass('active');
                if ($(this).hasClass('channel-0')) $(this).show();
            } else {
                $('#showPub').removeClass('active');
                if ($(this).hasClass('channel-0')) $(this).hide();
            }
            if (showRe) {
                $('#showRe').addClass('active');
                if ($(this).hasClass('channel-1')) $(this).show();
            } else {
                $('#showRe').removeClass('active');
                if ($(this).hasClass('channel-1')) $(this).hide();
            }
            if (showEn) {
                $('#showEn').addClass('active');
                if ($(this).hasClass('channel-2')) $(this).show();
            } else {
                $('#showEn').removeClass('active');
                if ($(this).hasClass('channel-2')) $(this).hide();
            }
        } else { 
            $(this).hide();
        }
    });   
    needMoreMessages();
}

var toggleChannel = function(elem) {
    if (elem == 'Pub') {
        if (showPub) showPub = false;
        else showPub = true;
    } else if (elem == 'Re') {
        if (showRe) showRe = false;
        else showRe = true;
    } else if (elem == 'En') {
        if (showEn) showEn = false;
        else showEn = true;
    }
    applyFilters();
}

var toggleRegion = function(elem) {
    apiRegion = elem;
    applyFilters();
}

// returns number of pixels left to scroll down before reaching the
// bottom. Works similar to the native scrollTop function.
function scrollBottom (elm) {
  if(typeof elm === 'string') elm = $(elm);
  return elm.get(0).scrollHeight - elm.innerHeight() - elm.scrollTop();
}

// retrieves parameter from the URL?query=string.
function getURLParam (param) {
  var v = document.URL;
  var i = v.indexOf(param);
  if(i <= -1) return '';
  v = v.substr(i);
  i = v.indexOf("&");
  if(i >= 0) v = v.substr(0, i);
  return v.replace(param+"=","");
}

// checks if there are enough messages in the selected chat tab and
// loads more if not.
function needMoreMessages() {
  var activeChat = $('#chat');
  if((showPub || showRe || showEn) && loaded) {
      if(scrollBottom(activeChat) !== 0 || activeChat.scrollTop() !== 0) return;
      console.log('no scrollbar in active chat, requesting more msgs');
      if($('#chat').length)
        loadJSON(true);
  }
}

// IDLE HANDLING /////////////////////////////////////////////////////

window.idleTime = 0; // in minutes
window._onResumeFunctions = [];

setInterval('window.idleTime += 1', 60*1000);
var idleReset = function () {
  // update immediately when the user comes back
  if(isIdle()) {
    window.idleTime = 0;
    $.each(window._onResumeFunctions, function(ind, f) {
        console.log(f);
      f();
    });
  }
  window.idleTime = 0;
};

window.isIdle = function() {
  return window.idleTime >= maxIdleTime;
}

// add your function here if you want to be notified when the user
// resumes from being idle
window.addResumeFunction = function(f) {
  window._onResumeFunctions.push(f);
}