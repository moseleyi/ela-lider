/* http://keith-wood.name/countdown.html
   Countdown for jQuery v1.6.1.
   Written by Keith Wood (kbwood{at}iinet.com.au) January 2008.
   Available under the MIT (https://github.com/jquery/jquery/blob/master/MIT-LICENSE.txt) license. 
   Please attribute the author if you use it. */

/* Display a countdown timer.
   Attach it with options like:
   $('div selector').countdown(
       {until: new Date(2009, 1 - 1, 1, 0, 0, 0), onExpiry: happyNewYear}); */
(function(l){function p(){function a(c){var e=1E12>c?e=performance.now?performance.now()+performance.timing.navigationStart:Date.now():c||(new Date).getTime();1E3<=e-d&&(n._updateTargets(),d=e);b(a)}this.regional=[];this.regional[""]={labels:"Years Months Weeks Days Hours Minutes Seconds".split(" "),labels1:"Year Month Week Day Hour Minute Second".split(" "),compactLabels:["y","m","w","d"],whichLabels:null,digits:"0123456789".split(""),timeSeparator:":",isRTL:!1};this._defaults={until:null,since:null,timezone:null,serverSync:null,format:"dHMS",layout:"",compact:!1,significant:0,description:"",expiryUrl:"",expiryText:"",alwaysExpire:!1,onExpiry:null,onTick:null,tickInterval:1};l.extend(this._defaults,this.regional[""]);this._serverSyncs=[];var b=window.requestAnimationFrame||window.webkitRequestAnimationFrame||window.mozRequestAnimationFrame||window.oRequestAnimationFrame||window.msRequestAnimationFrame||null,d=0;!b||l.noRequestAnimationFrame?(l.noRequestAnimationFrame=null,setInterval(function(){n._updateTargets()},980)):(d=window.animationStartTime||window.webkitAnimationStartTime||window.mozAnimationStartTime||window.oAnimationStartTime||window.msAnimationStartTime||(new Date).getTime(),b(a))}l.extend(p.prototype,{markerClassName:"hasCountdown",propertyName:"countdown",_rtlClass:"countdown_rtl",_sectionClass:"countdown_section",_amountClass:"countdown_amount",_rowClass:"countdown_row",_holdingClass:"countdown_holding",_showClass:"countdown_show",_descrClass:"countdown_descr",_timerTargets:[],setDefaults:function(a){this._resetExtraLabels(this._defaults,a);l.extend(this._defaults,a||{})},UTCDate:function(a,b,d,c,e,g,f,k){"object"==typeof b&&b.constructor==Date&&(k=b.getMilliseconds(),f=b.getSeconds(),g=b.getMinutes(),e=b.getHours(),c=b.getDate(),d=b.getMonth(),b=b.getFullYear());var j=new Date;j.setUTCFullYear(b);j.setUTCDate(1);j.setUTCMonth(d||0);j.setUTCDate(c||1);j.setUTCHours(e||0);j.setUTCMinutes((g||0)-(30>Math.abs(a)?60*a:a));j.setUTCSeconds(f||0);j.setUTCMilliseconds(k||0);return j},periodsToSeconds:function(a){return 31557600*a[0]+2629800*a[1]+604800*a[2]+86400*a[3]+3600*a[4]+60*a[5]+a[6]},_attachPlugin:function(a,b){a=l(a);if(!a.hasClass(this.markerClassName)){var d={options:l.extend({},this._defaults),_periods:[0,0,0,0,0,0,0]};a.addClass(this.markerClassName).data(this.propertyName,d);this._optionPlugin(a,b)}},_addTarget:function(a){this._hasTarget(a)||this._timerTargets.push(a)},_hasTarget:function(a){return-1<l.inArray(a,this._timerTargets)},_removeTarget:function(a){this._timerTargets=l.map(this._timerTargets,function(b){return b==a?null:b})},_updateTargets:function(){for(var a=this._timerTargets.length-1;0<=a;a--)this._updateCountdown(this._timerTargets[a])},_optionPlugin:function(a,b,d){a=l(a);var c=a.data(this.propertyName);if(!b||"string"==typeof b&&null==d){var e=b;return(b=(c||{}).options)&&e?b[e]:b}a.hasClass(this.markerClassName)&&(b=b||{},"string"==typeof b&&(e=b,b={},b[e]=d),this._resetExtraLabels(c.options,b),l.extend(c.options,b),this._adjustSettings(a,c),b=new Date,(c._since&&c._since<b||c._until&&c._until>b)&&this._addTarget(a[0]),this._updateCountdown(a,c))},_updateCountdown:function(a,b){var d=l(a);if(b=b||d.data(this.propertyName)){d.html(this._generateHTML(b)).toggleClass(this._rtlClass,b.options.isRTL);if(l.isFunction(b.options.onTick)){var c="lap"!=b._hold?b._periods:this._calculatePeriods(b,b._show,b.options.significant,new Date);(1==b.options.tickInterval||0==this.periodsToSeconds(c)%b.options.tickInterval)&&b.options.onTick.apply(a,[c])}if("pause"!=b._hold&&(b._since?b._now.getTime()<b._since.getTime():b._now.getTime()>=b._until.getTime())&&!b._expiring){b._expiring=!0;if(this._hasTarget(a)||b.options.alwaysExpire)this._removeTarget(a),l.isFunction(b.options.onExpiry)&&b.options.onExpiry.apply(a,[]),b.options.expiryText&&(c=b.options.layout,b.options.layout=b.options.expiryText,this._updateCountdown(a,b),b.options.layout=c),b.options.expiryUrl&&(window.location=b.options.expiryUrl);b._expiring=!1}else"pause"==b._hold&&this._removeTarget(a);d.data(this.propertyName,b)}},_resetExtraLabels:function(a,b){var d=!1,c;for(c in b)if("whichLabels"!=c&&c.match(/[Ll]abels/)){d=!0;break}if(d)for(c in a)c.match(/[Ll]abels[02-9]/)&&(a[c]=null)},_adjustSettings:function(a,b){var d,c=0;d=null;for(c=0;c<this._serverSyncs.length;c++)if(this._serverSyncs[c][0]==b.options.serverSync){d=this._serverSyncs[c][1];break}null!=d?(c=b.options.serverSync?d:0,d=new Date):(c=l.isFunction(b.options.serverSync)?b.options.serverSync.apply(a,[]):null,d=new Date,c=c?d.getTime()-c.getTime():0,this._serverSyncs.push([b.options.serverSync,c]));var e=b.options.timezone,e=null==e?-d.getTimezoneOffset():e;b._since=b.options.since;null!=b._since&&(b._since=this.UTCDate(e,this._determineTime(b._since,null)),b._since&&c&&b._since.setMilliseconds(b._since.getMilliseconds()+c));b._until=this.UTCDate(e,this._determineTime(b.options.until,d));c&&b._until.setMilliseconds(b._until.getMilliseconds()+c);b._show=this._determineShow(b)},_destroyPlugin:function(a){a=l(a);a.hasClass(this.markerClassName)&&(this._removeTarget(a[0]),a.removeClass(this.markerClassName).empty().removeData(this.propertyName))},_pausePlugin:function(a){this._hold(a,"pause")},_lapPlugin:function(a){this._hold(a,"lap")},_resumePlugin:function(a){this._hold(a,null)},_hold:function(a,b){var d=l.data(a,this.propertyName);if(d){if("pause"==d._hold&&!b){d._periods=d._savePeriods;var c=d._since?"-":"+";d[d._since?"_since":"_until"]=this._determineTime(c+d._periods[0]+"y"+c+d._periods[1]+"o"+c+d._periods[2]+"w"+c+d._periods[3]+"d"+c+d._periods[4]+"h"+c+d._periods[5]+"m"+c+d._periods[6]+"s");this._addTarget(a)}d._hold=b;d._savePeriods="pause"==b?d._periods:null;l.data(a,this.propertyName,d);this._updateCountdown(a,d)}},_getTimesPlugin:function(a){a=l.data(a,this.propertyName);return!a?null:!a._hold?a._periods:this._calculatePeriods(a,a._show,a.options.significant,new Date)},_determineTime:function(a,b){var d;if(null==a)d=b;else if("string"==typeof a){d=a.toLowerCase();for(var c=new Date,e=c.getFullYear(),g=c.getMonth(),f=c.getDate(),k=c.getHours(),j=c.getMinutes(),c=c.getSeconds(),h=/([+-]?[0-9]+)\s*(s|m|h|d|w|o|y)?/g,m=h.exec(d);m;){switch(m[2]||"s"){case "s":c+=parseInt(m[1],10);break;case "m":j+=parseInt(m[1],10);break;case "h":k+=parseInt(m[1],10);break;case "d":f+=parseInt(m[1],10);break;case "w":f+=7*parseInt(m[1],10);break;case "o":g+=parseInt(m[1],10);f=Math.min(f,n._getDaysInMonth(e,g));break;case "y":e+=parseInt(m[1],10),f=Math.min(f,n._getDaysInMonth(e,g))}m=h.exec(d)}d=new Date(e,g,f,k,j,c,0)}else"number"==typeof a?(d=new Date,d.setTime(d.getTime()+1E3*a)):d=a;d&&d.setMilliseconds(0);return d},_getDaysInMonth:function(a,b){return 32-(new Date(a,b,32)).getDate()},_normalLabels:function(a){return a},_generateHTML:function(a){var b=this;a._periods=a._hold?a._periods:this._calculatePeriods(a,a._show,a.options.significant,new Date);for(var d=!1,c=0,e=a.options.significant,g=l.extend({},a._show),f=0;6>=f;f++)d|="?"==a._show[f]&&0<a._periods[f],g[f]="?"==a._show[f]&&!d?null:a._show[f],c+=g[f]?1:0,e-=0<a._periods[f]?1:0;for(var k=[!1,!1,!1,!1,!1,!1,!1],f=6;0<=f;f--)a._show[f]&&(a._periods[f]?k[f]=!0:(k[f]=0<e,e--));var j=a.options.compact?a.options.compactLabels:a.options.labels,h=a.options.whichLabels||this._normalLabels,d=function(c){var d=a.options["compactLabels"+h(a._periods[c])];return g[c]?b._translateDigits(a,a._periods[c])+(d?d[c]:j[c])+" ":""},e=function(c){var d=a.options["labels"+h(a._periods[c])];return!a.options.significant&&g[c]||a.options.significant&&k[c]?'<span class="'+n._sectionClass+'"><span class="'+n._amountClass+'">'+b._translateDigits(a,a._periods[c])+"</span><br/>"+(d?d[c]:j[c])+"</span>":""};return a.options.layout?this._buildLayout(a,g,a.options.layout,a.options.compact,a.options.significant,k):(a.options.compact?'<span class="'+this._rowClass+" "+this._amountClass+(a._hold?" "+this._holdingClass:"")+'">'+d(0)+d(1)+d(2)+d(3)+(g[4]?this._minDigits(a,a._periods[4],2):"")+(g[5]?(g[4]?a.options.timeSeparator:"")+this._minDigits(a,a._periods[5],2):"")+(g[6]?(g[4]||g[5]?a.options.timeSeparator:"")+this._minDigits(a,a._periods[6],2):""):'<span class="'+this._rowClass+" "+this._showClass+(a.options.significant||c)+(a._hold?" "+this._holdingClass:"")+'">'+e(0)+e(1)+e(2)+e(3)+e(4)+e(5)+e(6))+"</span>"+(a.options.description?'<span class="'+this._rowClass+" "+this._descrClass+'">'+a.options.description+"</span>":"")},_buildLayout:function(a,b,d,c,e,g){var f=a.options[c?"compactLabels":"labels"],k=a.options.whichLabels||this._normalLabels,j=function(b){return(a.options[(c?"compactLabels":"labels")+k(a._periods[b])]||f)[b]},h=function(b,c){return a.options.digits[Math.floor(b/c)%10]},j={desc:a.options.description,sep:a.options.timeSeparator,yl:j(0),yn:this._minDigits(a,a._periods[0],1),ynn:this._minDigits(a,a._periods[0],2),ynnn:this._minDigits(a,a._periods[0],3),y1:h(a._periods[0],1),y10:h(a._periods[0],10),y100:h(a._periods[0],100),y1000:h(a._periods[0],1E3),ol:j(1),on:this._minDigits(a,a._periods[1],1),onn:this._minDigits(a,a._periods[1],2),onnn:this._minDigits(a,a._periods[1],3),o1:h(a._periods[1],1),o10:h(a._periods[1],10),o100:h(a._periods[1],100),o1000:h(a._periods[1],1E3),wl:j(2),wn:this._minDigits(a,a._periods[2],1),wnn:this._minDigits(a,a._periods[2],2),wnnn:this._minDigits(a,a._periods[2],3),w1:h(a._periods[2],1),w10:h(a._periods[2],10),w100:h(a._periods[2],100),w1000:h(a._periods[2],1E3),dl:j(3),dn:this._minDigits(a,a._periods[3],1),dnn:this._minDigits(a,a._periods[3],2),dnnn:this._minDigits(a,a._periods[3],3),d1:h(a._periods[3],1),d10:h(a._periods[3],10),d100:h(a._periods[3],100),d1000:h(a._periods[3],1E3),hl:j(4),hn:this._minDigits(a,a._periods[4],1),hnn:this._minDigits(a,a._periods[4],2),hnnn:this._minDigits(a,a._periods[4],3),h1:h(a._periods[4],1),h10:h(a._periods[4],10),h100:h(a._periods[4],100),h1000:h(a._periods[4],1E3),ml:j(5),mn:this._minDigits(a,a._periods[5],1),mnn:this._minDigits(a,a._periods[5],2),mnnn:this._minDigits(a,a._periods[5],3),m1:h(a._periods[5],1),m10:h(a._periods[5],10),m100:h(a._periods[5],100),m1000:h(a._periods[5],1E3),sl:j(6),sn:this._minDigits(a,a._periods[6],1),snn:this._minDigits(a,a._periods[6],2),snnn:this._minDigits(a,a._periods[6],3),s1:h(a._periods[6],1),s10:h(a._periods[6],10),s100:h(a._periods[6],100),s1000:h(a._periods[6],1E3)},m=d;for(d=0;6>=d;d++)h="yowdhms".charAt(d),m=m.replace(RegExp("\\{"+h+"<\\}(.*)\\{"+h+">\\}","g"),!e&&b[d]||e&&g[d]?"$1":"");l.each(j,function(a,b){m=m.replace(RegExp("\\{"+a+"\\}","g"),b)});return m},_minDigits:function(a,b,d){b=""+b;if(b.length>=d)return this._translateDigits(a,b);b="0000000000"+b;return this._translateDigits(a,b.substr(b.length-d))},_translateDigits:function(a,b){return(""+b).replace(/[0-9]/g,function(b){return a.options.digits[b]})},_determineShow:function(a){a=a.options.format;var b=[];b[0]=a.match("y")?"?":a.match("Y")?"!":null;b[1]=a.match("o")?"?":a.match("O")?"!":null;b[2]=a.match("w")?"?":a.match("W")?"!":null;b[3]=a.match("d")?"?":a.match("D")?"!":null;b[4]=a.match("h")?"?":a.match("H")?"!":null;b[5]=a.match("m")?"?":a.match("M")?"!":null;b[6]=a.match("s")?"?":a.match("S")?"!":null;return b},_calculatePeriods:function(a,b,d,c){a._now=c;a._now.setMilliseconds(0);var e=new Date(a._now.getTime());a._since?c.getTime()<a._since.getTime()?a._now=c=e:c=a._since:(e.setTime(a._until.getTime()),c.getTime()>a._until.getTime()&&(a._now=c=e));var g=[0,0,0,0,0,0,0];if(b[0]||b[1]){var f=n._getDaysInMonth(c.getFullYear(),c.getMonth()),k=n._getDaysInMonth(e.getFullYear(),e.getMonth()),k=e.getDate()==c.getDate()||e.getDate()>=Math.min(f,k)&&c.getDate()>=Math.min(f,k),k=Math.max(0,12*(e.getFullYear()-c.getFullYear())+e.getMonth()-c.getMonth()+(e.getDate()<c.getDate()&&!k||k&&60*(60*e.getHours()+e.getMinutes())+e.getSeconds()<60*(60*c.getHours()+c.getMinutes())+c.getSeconds()?-1:0));g[0]=b[0]?Math.floor(k/12):0;g[1]=b[1]?k-12*g[0]:0;c=new Date(c.getTime());f=c.getDate()==f;k=n._getDaysInMonth(c.getFullYear()+g[0],c.getMonth()+g[1]);c.getDate()>k&&c.setDate(k);c.setFullYear(c.getFullYear()+g[0]);c.setMonth(c.getMonth()+g[1]);f&&c.setDate(k)}var j=Math.floor((e.getTime()-c.getTime())/1E3);c=function(a,c){g[a]=b[a]?Math.floor(j/c):0;j-=g[a]*c};c(2,604800);c(3,86400);c(4,3600);c(5,60);c(6,1);if(0<j&&!a._since){a=[1,12,4.3482,7,24,60,60];c=6;e=1;for(f=6;0<=f;f--)b[f]&&(g[c]>=e&&(g[c]=0,j=1),0<j&&(g[f]++,j=0,c=f,e=1)),e*=a[f]}if(d)for(f=0;6>=f;f++)d&&g[f]?d--:d||(g[f]=0);return g}});var q=["getTimes"];l.fn.countdown=function(a){var b=Array.prototype.slice.call(arguments,1),d;d="option"==a&&(0==b.length||1==b.length&&"string"==typeof b[0])?!0:-1<l.inArray(a,q);return d?n["_"+a+"Plugin"].apply(n,[this[0]].concat(b)):this.each(function(){if("string"==typeof a){if(!n["_"+a+"Plugin"])throw"Unknown command: "+a;n["_"+a+"Plugin"].apply(n,[this].concat(b))}else n._attachPlugin(this,a||{})})};var n=l.countdown=new p})(jQuery);