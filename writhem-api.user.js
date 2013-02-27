// ==UserScript==
// @id             iitc-plugin-writhem-api@pironic
// @name           iitc: writhem-api hooks
// @version        0.0.2
// @namespace      https://github.com/breunigs/ingress-intel-total-conversion
// @updateURL      ingress.writhem.com/writhem-api.user.js
// @downloadURL    ingress.writhem.com/writhem-api.user.js
// @description    Will send all the logs received from google's rpc to writhem's server for further processing
// @include        https://www.ingress.com/intel*
// @match          https://www.ingress.com/intel*
// ==/UserScript==

function wrapper() {
    // ensure plugin framework is there, even if iitc is not yet loaded
    if(typeof window.plugin !== 'function') window.plugin = function() {};
    
    
    // PLUGIN START ////////////////////////////////////////////////////////
    
    // use own namespace for plugin
    window.plugin.writhemAPI = function() {};
    window.plugin.writhemAPI.url = "http://ingress.writhem.com/api/";
    window.plugin.writhemAPI.apikey = getURLParam('key');
    if (window.plugin.writhemAPI.apikey.length > 1) {
        window.plugin.writhemAPI.enabled = true;
    } else {
        window.plugin.writhemAPI.enabled = false;
    }
    
    var setup =  function() {
        window.plugin.writhemAPI.setupCallback();
        window.plugin.writhemAPI.setupOverloads();
    }
    
    window.plugin.writhemAPI.setupCallback = function() {
        if (window.plugin.writhemAPI.enabled) {
            $('#sidebar > #gamestat').after('<div id="writhemStatus"><a onclick="window.plugin.writhemAPI.enableToggle()">Disable WritheM API</a></div> ');
        } else {
            var msg = '<form name="form1" method="get">';
            msg += '<input default="key" name="key" type="text" size=32>';
            msg += '<button text="go" type="submit">login</button> ';
            msg += '</form>';
            $('#sidebar > #gamestat').after('<div id="writhemStatus">'+msg+'</div> ');
        }
    }
    
    window.plugin.writhemAPI.setupOverloads = function() {
        window.MAX_IDLE_TIME = 0; // never top updating the map.
        window.isIdle = function() {
            if (MAX_IDLE_TIME == 0) return false;
            return window.idleTime >= MAX_IDLE_TIME;
        }
        
        // intercept and inject my own functionality into the writheDataToHash method.
        // this will allow me to hijack the data for my own evil intent.
        window.plugin.writhemAPI.originalWriteDataToHash = window.chat.writeDataToHash;
        window.chat.writeDataToHash = function(newData, storageHash, skipSecureMsgs) {
            //console.log(newData);
            if (window.plugin.writhemAPI.enabled) {
                var data = {
                    "key":window.plugin.writhemAPI.apikey,
                    "method":"save",
                    "package":newData
                };
                // $('#writhemStatus').load(WRITHEMAPIURL,writhem_temp);
                console.log($.ajax(window.plugin.writhemAPI.url, {
                    data : JSON.stringify(data),
                    dataType : 'json',
                    type : 'POST',
                }));
                console.log(data);
            }

            window.plugin.writhemAPI.originalWriteDataToHash (newData,storageHash,skipSecureMsgs);
        }
    }
    
    window.plugin.writhemAPI.enableToggle = function() {
        if (window.plugin.writhemAPI.enabled) {
            window.plugin.writhemAPI.enabled = false;
            $('#writhemStatus').html('<a onclick="window.plugin.writhemAPI.enableToggle()">Enable WritheM API</a>');
        } else {
            window.plugin.writhemAPI.enabled = true;
            $('#writhemStatus').html('<a onclick="window.plugin.writhemAPI.enableToggle()">Disable WritheM API</a>');
        }
        console.log("WritheM API is now: " + window.plugin.writhemAPI.enabled);
    }
    
    
    // PLUGIN END //////////////////////////////////////////////////////////
    
    if(window.iitcLoaded && typeof setup === 'function') {
        setup();
    } else {
        if(window.bootPlugins)
            window.bootPlugins.push(setup);
        else
            window.bootPlugins = [setup];
    }
} // wrapper end
// inject code into site context
var script = document.createElement('script');
script.appendChild(document.createTextNode('('+ wrapper +')();'));
(document.body || document.head || document.documentElement).appendChild(script);
