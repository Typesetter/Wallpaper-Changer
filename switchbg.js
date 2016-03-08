
$(function(){

	$(document).on('change','.switch_background_style',function(){
		if( this.value == 0 ){
			$('#wallpaper_changer_custom').slideDown();
		}else{
			$('#wallpaper_changer_custom').slideUp();
		}
	});

});
