
$(function(){

	$('.switch_background_image').change(function(){
		Change('?cmd=switch_to_new_background&image=' + encodeURI(this.value));
	});

	$('.switch_background_style').change(function(){
		Change('?cmd=switch_to_new_style&style=' + encodeURI(this.value));
	});

	function Change(query){
		var loc		= window.location.href;
		var pos		= loc.indexOf('?');
		if( pos>-1 ){
			loc = window.location.href.substr(0,pos);
		}
		loc			+= query;

		var a		= document.createElement('a');
		a.href		= loc;
		$gp.cGoTo(a,true);
	}
});
