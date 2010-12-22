/*
Plugin Name: Tynt.com For WordPress
Plugin URI: http://mikhailkozlov.com/tynt-insight-for-wordpress/
Description: Make link-backs to your content effortless for readers and gain new insight into user engagement with Tynt Insight.
Author: Mikhail Kozlov
Version: 1.0.0
License: MIT
Author Url: http://mikhailkozlov.com/
*/

var $j =jQuery.noConflict();
$j(document).ready(function(){
	$j("#param-b").change(function(){
		var h = $j(this);
		if(h.is(":checked")){
			h.nextAll("div").find("input:checkbox").removeAttr("disabled");
		}else{
			h.nextAll("div").find("input:checkbox").attr("disabled","disabled").removeAttr("checked");
		}
	});
	$j("#tynt-sponsor-text, #tynt-sponsor-name, #tynt-sponsor-link").bind("keyup",function(){
		//var h = $j(this);
		var t= $j("#tynt-sponsor-preview span");
		if($j("#tynt-sponsor-name").val().length < 2){
			$j("#param-el").val('');
			t.css({"color":"red"}).text("Please provide Sponsor Name");
		}else if($j("#tynt-sponsor-link").val().length < 2){
			$j("#param-el").val('');
			t.css({"color":"red"}).text("Please provide Destination Link");
		}else{
			tifwpBuildLink();
		}
		
	});
	
});

function tifwpBuildLink(){
	var l = $j("#tynt-sponsor-text").val()+ ' <a href="'+$j("#tynt-sponsor-link").val()+'" target="_blank" color="#003399">'+$j("#tynt-sponsor-name").val()+'</a>';
	$j("#param-el").val(l);
	$j("#tynt-sponsor-preview span").css({"color":"#000"}).html(l);
	
	
}