// gTweetFeed
(function($){var els=['div:wrap','div:header','div:controls','div:loader','a:more','a:less','ul:list'],cmd={},e={},o={};cmd=$.fn.gtweetsfeed=function(options){o=$.extend({},$.fn.gtweetsfeed.defaults,options);if(!o.username)return $(this);e.root=$(this);cmd.init();return e.root;};$.extend(cmd,{init:function(){cmd.build();cmd.markup();cmd.load();},less:function(){if(o.key>=o.minimum){if('fade'==o.effect)e.items.eq(o.key).fadeTo(o.duration,0,function(){e.items.eq(o.key+1).removeClass(o.prefix+'last');e.items.eq(o.key).addClass(o.prefix+'last');$(this).hide();});else if('slide'==o.effect)e.items.eq(o.key).slideUp(o.duration,function(){e.items.eq(o.key+1).removeClass(o.prefix+'last');e.items.eq(o.key).addClass(o.prefix+'last');$(this).hide();});else e.items.eq(o.key).removeClass(o.prefix+'last').show();o.key--;};},more:function(){if(o.key<o.maximum){e.items.eq(o.key).removeClass(o.prefix+'last');o.key++;if('fade'==o.effect)e.items.eq(o.key).show().addClass(o.prefix+'last').fadeTo(o.duration,1);else if('slide'==o.effect)e.items.eq(o.key).addClass(o.prefix+'last').slideDown(o.duration);else e.items.eq(o.key).addClass(o.prefix+'last').show();};},intro:function(){if('fade'==o.effect){e.items.each(function(i){if(i<o.number){var $li=$(this);setTimeout(function(){$li.show().fadeTo(o.duration,1);},i*o.duration);}else $(this).hide();});}else if('slide'==o.effect){e.items.each(function(i){if(i<o.number){var $li=$(this);setTimeout(function(){$li.slideDown(o.duration);},i*o.duration);}else $(this).hide();});}else{e.items.each(function(i){if(i<o.number)$(this).show();else $(this).hide();});};},load:function(){$.getJSON(o.src_tpl.replace('{username}',o.username)+'?callback=?',function(data){o.total=data.length;o.key=o.number-1;cmd.render(data);cmd.intro();});},build:function(){$.each(els,function(i,v){var bits=v.split(':'),tag=bits[0],id=bits[1],$e=$('<'+tag+'>').attr('id',o.prefix+id);if(tag=='a')$e.attr('href','#'+id).attr('title',o[id+'_title']).click(cmd[id]);e[id]=$e;});},markup:function(){e.root.append(e.wrap.append(e.header.html(o.header_tpl.replace('{username}',o.username)).css('display',o.header?'block':'none'),e.controls.append(e.more,e.less).css('display',o.controls?'block':'none'),e.loader,e.list.hide()));},render:function(tweets){var arr=[];$.each(tweets,function(i,v){var msg=v.text.replace(/((https?|s?ftp|ssh)\:\/\/[^"\s\<\>]*[^.,;'">\:\s\<\>\)\]\!])/g,function(url){return'<a href="'+url+'">'+url+'</a>';}).replace(/\B@([_a-z0-9]+)/ig,function(reply){return reply.charAt(0)+'<a href="http://twitter.com/'+reply.substring(1)+'">'+reply.substring(1)+'</a>';});arr.push('<li>'+o.li_tpl.replace('{msg}',msg).replace('{username}',o.username).replace('{id}',v.id).replace('{time}',cmd.time(v.created_at))+'</li>');});e.items=e.list.html(arr.join('')).children().css({display:'none',opacity:('fade'==o.effect)?0:1});e.items.eq(0).addClass(o.prefix+'first');e.items.eq(o.key).addClass(o.prefix+'last');e.loader.remove();e.list.show();},time:function(time_value){var values=time_value.split(" "),time_value=values[1]+" "+values[2]+", "+values[5]+" "+values[3],parsed_date=Date.parse(time_value),relative_to=(arguments.length>1)?arguments[1]:new Date(),delta=parseInt((relative_to.getTime()-parsed_date)/1000),delta=delta+(relative_to.getTimezoneOffset()*60);if(delta<60)return'less than a minute ago';else if(delta<120)return'about a minute ago';else if(delta<(60*60))return(parseInt(delta/60)).toString()+' minutes ago';else if(delta<(120*60))return'about an hour ago';else if(delta<(24*60*60))return'about '+(parseInt(delta/3600)).toString()+' hours ago';else if(delta<(48*60*60))return'1 day ago';else return(parseInt(delta/86400)).toString()+' days ago';}});$.fn.gtweetsfeed.defaults={prefix:'gtweetfeed-',controls:false,more_title:'Show more tweet',less_title:'Show less tweet',username:'',effect:'fade',duration:500,header:false,header_tpl:'{username}\'s Tweets',maximum:15,minimum:1,number:5,src_tpl:'http://twitter.com/statuses/user_timeline/{username}.json',li_tpl:'<span>{msg}</span><a href="http://twitter.com/{username}/statuses/{id}">{time}</a>'};})(jQuery);

// bof dom ready
$(function() {
	
	var url = window.location;
	
	// twitter feed setup
	$('#twitter-feed').gtweetsfeed({
		username: 'gcochez',
		controls: true
	});
	
	// menu binding
	$('#menu a').bind('click', function(ev) {
		var a = this;
		$('#main').fadeTo(200, 0, function() {
			$(this).load(a.href+' #main', function() {
				$(a).parent().addClass('active').siblings().removeClass('active').end().blur();
				$('#main').fadeTo(200, 1);
			});
		});
		
		// prevent default action
		return false;
	});

// eof dom ready
});