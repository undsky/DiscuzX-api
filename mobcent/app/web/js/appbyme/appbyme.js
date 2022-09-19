
//2.0定义
function connectAppbymeJavascriptBridge(callback) {
    if (window.AppbymeJavascriptBridge) {
        callback(AppbymeJavascriptBridge)
    } else {
        document.addEventListener('connectAppbymeJavascriptBridge', function() {
            callback(AppbymeJavascriptBridge)
        }, false)
    }
}


//1.x
var browser = {
	versions: function() {
		var a = navigator.userAgent,
			b = navigator.appVersion;
		return {
			trident: a.indexOf("Trident") > -1,
			presto: a.indexOf("Presto") > -1,
			webKit: a.indexOf("AppleWebKit") > -1,
			gecko: a.indexOf("Gecko") > -1 && a.indexOf("KHTML") == -1,
			mobile: !! a.match(/AppleWebKit.*Mobile.*/),
			ios: !! a.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/),
			android: a.indexOf("Android") > -1,
			iPhone: a.indexOf("iPhone") > -1,
			iPad: a.indexOf("iPad") > -1,
			webApp: a.indexOf("Safari") == -1,
			appbyme: a.indexOf("Appbyme") > -1
		}
	}(),
	language: (navigator.browserLanguage || navigator.language).toLowerCase()
};

function onLogout() {
	if (browser.versions.android) {
		appbyme.onLogout()
	} else {
		return document.location = "appbyme://onLogout"
	}
}
function onLogin() {
	if (browser.versions.android) {
		appbyme.onLogin()
	} else {
		return document.location = "appbyme://onLogin"
	}
}
function onShare(title, content, url) {
	if (browser.versions.android) {
		appbyme.onShare(title, content, url)
	} else {
		return document.location = "appbyme://onShare?"+encodeURIComponent(title)+"&"+encodeURIComponent(content)+"&"+encodeURIComponent(url);
	}
}
function getUserInfo() {
	if (browser.versions.android) {
		appbyme.getUserInfo()
	} else {
		return document.location = "appbyme://getUserInfo"
	}
}
function isAppbymeWeb() {
	if (browser.versions.appbyme) {
		return true
	}
};


var SHAKE_THRESHOLD = 3000;//默认阀值  
var last_update = 0;  
var x = y = z = last_x = last_y = last_z = 0;

function initShake(threshold){
	if(threshold != null && threshold != ""){
		SHAKE_THRESHOLD = threshold;
	}
	
	if (browser.versions.android) {
		SHAKE_THRESHOLD = SHAKE_THRESHOLD + 1000;
	}
	
	if (window.DeviceMotionEvent) {  
    	window.addEventListener('devicemotion', deviceMotionHandler, false);  
    } else {  
        alert('您的设备不支持摇一摇功能');  
    }
}

function deviceMotionHandler(eventData) {  
	var acceleration = eventData.accelerationIncludingGravity;  
    var curTime = new Date().getTime();  
    if ((curTime - last_update) > 100) {  
    	var diffTime = curTime - last_update;  
        last_update = curTime;  
        x = acceleration.x;  
        y = acceleration.y;  
        z = acceleration.z;  
        var speed = Math.abs(x + y + z - last_x - last_y - last_z) / diffTime * 10000;  
        if (speed > SHAKE_THRESHOLD) {
        	shakeCallBack();
        }  
        last_x = x;  
        last_y = y;  
        last_z = z;  
     }  
}
