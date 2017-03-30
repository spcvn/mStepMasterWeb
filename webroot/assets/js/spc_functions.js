var isNetworkError=function(){

		var network_unknown="NETWORK_UNKNOWN";
		var network_disconnected="NETWORK_DISCONNECTED";
		var network_connected="NETWORK_CONNECTED";
		window[network_unknown]=-1;
		window[network_disconnected]=0;
		window[network_connected]=1;

		var res={};
		switch(true){

				case(navigator["onLine"]==undefined):
				res["status"]=window[network_unknown];
		break;
				case(navigator["onLine"]==false):
				res["status"]=window[network_disconnected];
		break;
				default:
				res["status"]=window[network_connected];
		break;
		}

		return res;
};

var loadScript=function(path,callback){

		var self=this;
		var src_split=path.split(".");
		var ext     =src_split[src_split.length-1];
		var elements={"js":"script","css":"link"};
		var types   ={"js":"text/javascript","css":"text/css"};
		var src     ={"js":"src","css":"href"};
		var other   ={"js":[],"css":[{"property":"rel","value":"stylesheet"}]};

		//if(loadScript["fn"]==undefined) loadScript["fn"]={};
		//loadScript["fn"][path]=true;

		var done=false;
		var head=document.getElementsByTagName('head')[0];
		var script=document.createElement(elements[ext]);
		if(!!src[ext])   script[src[ext]]=path;
		if(!!types[ext]) script.type=types[ext];

		if(other[ext].length>0){
		
				for(var i in other[ext]){
				
						if(!other[ext].hasOwnProperty(i)) continue;
						script[other[ext][i]["property"]]=other[ext][i]["value"];
				}
		}

		head.appendChild(script);
		script.onload=script.onreadystatechange=function(){

				if(!done && (!this.readyState || this.readyState==="loaded" || this.readyState==="complete")){

						done=true;
						if(callback!=undefined) callback();
						script.onload=script.onreadystatechange=null;
						if(head && script.parentNode) head.removeChild(script);
				}
		};
};

var getWeekDayFromNow=function(ms,num){

		var date=new Date(parseInt(ms));
		var ymd=[];
		var res=[];
		ymd.push(date.getFullYear());
		ymd.push(Number(date.getMonth()+1).addZero());
		ymd.push(Number(date.getDate()).addZero());
		var origin=Number(ymd.join(""));
		res.push(origin+"");
		for(var i=1;i<=(num-1);i++) res.push(origin.addDate(i));
		return res;
};

var isSmart=function(){

		var ua=navigator.userAgent.toLowerCase();
		if(ua.indexOf('iphone')>0 || ua.indexOf('ipod')>0 || ua.indexOf('android')>0 && ua.indexOf('mobile')>0) return true;
		return false;
};

var isTablet=function(){

		var ua=navigator.userAgent.toLowerCase();
		if(isSmart()) return false;
		if(ua.indexOf('ipad')>0 || ua.indexOf('android')>0) return true;
		return false;
};

var isSp=function(){

		if(isSmart() || isTablet()) return true;
		return false;
};

var uiAlert=function(params){

    	var settings={"buttons":{}};
    	settings["title"]=(!!params["title"]?params["title"]:"&nbsp;");
		settings["main_title"]=(!!params["main_title"]?params["main_title"]:settings["title"]);
    	settings["name"]=(!!params["name"]?params["name"]:"&nbsp;");
    	settings["message"]=(!!params["message"]?params["message"]:"&nbsp;");
		settings["main_title"]=(!!params["main_title"]?params["main_title"]:params["title"]);
		settings["close"]=($.isFunction(params["close"])?params["close"]:false);

    	if(!!params["yes"]){

    	    settings["buttons"]["YES"]={};
    	    settings["buttons"]["YES"]["class"]="blue";
    	    settings["buttons"]["YES"]["action"]=function(){

    	        	params["yes"]();
    	    };
    	}

    	if(!!params["no"]){

    	    settings["buttons"]["NO"]={};
    	    settings["buttons"]["NO"]["class"]="gray";
    	    settings["buttons"]["NO"]["action"]=function(){

    	        	params["no"]();
    	    };
    	}

		var js=document.location.protocol+BASE_URL+"assets/js/jquery.confirm.js";
		loadScript(js,function(){

				jQuery.confirm(settings);
		});
};

var nl2br=function(str, is_xhtml) {   

    	var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';    
    	return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1'+ breakTag +'$2');
};

var modalWindow=function(callback){

		var elem=this;
		var instance=TPL.getInstance("schedule_site_modal_window");
		var tpl =instance.get_schedule_site_modal_window;
		var html=$(tpl);
		elem.append(html);
		html.ready(function(){

				html.show();
				callback.call(html,{

						"cw":html.outerWidth(),
						"ch":html.outerHeight()
				});
		});
};

function floatFormat(number,n){

		var _pow=Math.pow(10,n);
		return Math.round(number*_pow )/_pow;
}


