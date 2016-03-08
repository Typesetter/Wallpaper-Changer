<?php

defined('is_running') or die('Not an entry point...');

class WallpaperChanger{

	var $files; //files in selected images directory
	var $config; //image directory, background style, list of page backgrounds for each page
	var $config_file;

	private $default_style1 = "#gpx_content {\n  background-image:url('%IMG%');\n  background-repeat:no-repeat;\n  background-position: left top;\n  background-attachment:scroll;\n  background-size:100% auto;\n}";
	private $default_style2 = "#gpx_content {\n  background-image:url('%IMG%');\n  background-repeat:no-repeat;\n  background-position: center center;\n  background-attachment:fixed;\n  background-size:cover;\n}";
	private $default_style3 = "#gpx_content {\n  background-image:url('%IMG%');\n  background-repeat:repeat;\n  background-position: left top;\n  background-attachment:scroll;\n  background-size:auto;\n}";
	private $default_style4 = "body {\n  background-image:url('%IMG%');\n  background-repeat:no-repeat;\n  background-position: center center;\n  background-attachment:fixed;\n  background-size:cover;\n}";


	function __construct(){
		global $addonPathData;

		$this->config_file = $addonPathData.'/config.php';
		$this->Load_Config();

		if( common::LoggedIn() ){
			gpPlugin::js('switchbg.js');
			gpPlugin::css('switchbg.css');
		}
	}


	/**
	 * PageRunScript Hook
	 *
	 */
	public function PageRunScript($cmd){
		global $page;

		if( common::LoggedIn() ){
			$page->admin_links[]		= array($page->title,'Select Wallpaper','cmd=SelectWallpaperDialog',' data-cmd="gpabox"');

			switch($cmd){

				// on current page
				case 'SelectWallpaperDialog':
				ob_start();
				$this->SelectWallpaperDialog();
				$page->contentBuffer = ob_get_clean();
				return 'return';

				case 'SelectWallpaper':
				$this->SelectWallpaper();
				$this->SetWallpaper();
				return '';
			}
		}

		$this->SetWallpaper();

		return $cmd;
	}


	function RunScript(){}




	/**
	 * Add the selected css (with the selected image) to the page
	 *
	 */
	function SetWallpaper() {
		global $title, $page, $dataDir, $dirPrefix;

		if( !isset($this->config['pages']) || !isset($this->config['pages'][$title]) ){
			return;
		}

		$img	= $this->config['pages'][$title]['img'];
		$css	= $this->PageCSS($title);

		if( empty($css) ){
			return;
		}


		$imgDir = $dataDir;
		if( $dirPrefix!='' ){
			$imgDir = substr($dataDir, 0, strlen($dataDir) - strlen($dirPrefix));
		}

		$full = str_replace('%20',' ',$imgDir.$img);
		if( !file_exists($full) ){
			return;
		}

		$css = str_replace('%IMG%', $img, $css);
		$page->head .= '<style type="text/css">'.$css.'</style>';
	}


	/**
	 * Return the CSS for the page
	 *
	 */
	function PageCSS($title){

		if( !isset($this->config['pages'][$title]) ){
			return '';
		}

		if( isset($this->config['pages'][$title]['custom'])) {
			return $this->config['pages'][$title]['custom'];
		}

		$style = $this->config['pages'][$title]['style'];

		if( isset($this->config['style'.$style]) ){
			return $this->config['style'.$style];
		}

		return '';
	}

	function Load_Config() {
		if (file_exists($this->config_file)) {
			include($this->config_file);
			$this->config = $config;
		} else {
			//default config
			$this->config = array();
			$this->config['path'] = '/image/';
			$this->config['style1'] = $this->default_style1;
			$this->config['style2'] = $this->default_style2;
			$this->config['style3'] = $this->default_style3;
			$this->config['style4'] = $this->default_style4;
			$this->config['pages'] = array();
		}
	}


	/**
	 * Display popup for selecting dialog
	 *
	 */
	function SelectWallpaperDialog(){
		global $langmessage, $title;

		echo '<div class="inline_box">';
		echo '<form method="post" action="?">';
		echo '<input type="hidden" name="title" value="'.htmlspecialchars($title).'" />';
		echo '<h3>Select Wallpaper</h3>';
		$this->StyleSelect();
		echo '<hr/>';
		$this->ImageSelect();
		echo '<hr/>';
		echo '<button type="submit" name="cmd" value="SelectWallpaper" class="gpsubmit">'.$langmessage['save'].'</button> ';
		echo '<button class="gpcancel" data-cmd="admin_box_close">'.$langmessage['cancel'].'</button> ';
		echo ' '.common::Link('Admin_Wallpaper_Changer','Admin');
		echo '</form>';
		echo '</div>';
	}


	/**
	 * Show available images in the current directory
	 *
	 */
	function ImageSelect(){
		global $title, $dataDir;

		includeFile('admin/admin_uploaded.php');


		//get the current image
		$curr_name	= '';
		$curr		= '';
		if( isset($this->config['pages'][$title]) ){
			$curr_name	= basename($this->config['pages'][$title]['img']);
			$curr		= $this->config['pages'][$title]['img'];
		}


		//display available images
		$path		= trim($this->config['path'],'/\\');
		$dir		= $dataDir.'/data/_uploaded/'.$path;
		$files		= scandir($dir);
		$curr_shown = false;
		echo '<div class="wallpaper_images">';
		foreach($files as $file){

			if( $file == '.' || $file == '..' ){
				continue;
			}

			$full		= $dir.'/'.$file;
			$img		= '/data/_uploaded/'.$path.'/'.$file;
			$img		= common::GetDir($img);
			$thumb		= self::ThumbnailPath($img);
			$checked	= '';

			if( !admin_uploaded::isImg($full) ){
				continue;
			}

			if( $curr == $img ){
				$checked = ' checked';
				$curr_shown = true;
			}

			echo '<label>';
			echo '<input type="radio" name="image" value="'.htmlspecialchars($img).'" '.$checked.' />';
			echo '<img src="'.$thumb.'" alt="'.htmlspecialchars($file).'" title="'.htmlspecialchars($img).'" />';
			echo '</label>';
		}


		// if choosen image is located in some other directory
		if( !$curr_shown && !empty($curr) ){
			$thumb		= self::ThumbnailPath($curr);
			echo '<label>';
			echo '<input type="radio" name="image" value="'.htmlspecialchars($curr).'" checked />';
			echo '<img src="'.$thumb.'" alt="'.htmlspecialchars($curr_name).'" title="'.htmlspecialchars($curr).'" />';
			echo '</label>';
		}


		echo '</div>';
	}


	/**
	 * Return the thumbnail path of an image
	 *
	 */
	public static function ThumbnailPath($img){

		//already thumbnail path
		if( strpos($img,'/data/_uploaded/image/thumbnails') !== false ){
			return $img;
		}

		$dir_part = '/data/_uploaded/';
		$pos = strpos($img,$dir_part);
		if( $pos === false ){
			return $img;
		}

		return substr_replace($img,'/data/_uploaded/image/thumbnails/',$pos, strlen($dir_part) ).'.jpg';
	}


	/**
	 * Display <select> for style options
	 *
	 */
	function StyleSelect(){
		global $title;


		$curr_style			= -1;
		$textarea_style		= 'display:none';
		if( isset($this->config['pages'][$title]['custom']) ){
			$curr_style		= 0;
			$textarea_style = '';

		}elseif( isset($this->config['pages'][$title]['style']) ){
			$curr_style		= $this->config['pages'][$title]['style'];
		}

		echo '<b>CSS: </b>';
		echo '<select name="style" class="switch_background_style" title="Style">';
		echo '<option value="-1" '.($curr_style==-1 ? 'selected="selected"':'').'> None </option>';

		for($i = 1; $i <=4; $i++){
			echo '<option value="'.$i.'" '.($curr_style==$i ? 'selected="selected"':'').'>Style '.$i.'</option>';
		}

		echo '<option value="0" '.($curr_style==0 ? 'selected="selected"':'').'> Custom </option>'; //custom
		echo '</select>';


		$css = $this->PageCSS($title);
		echo '<textarea id="wallpaper_changer_custom" name="custom_css" cols="50" rows="6" class="gptextarea" style="width:100%;'.$textarea_style.'">';
		echo htmlspecialchars($css);
		echo '</textarea>';
	}


	/**
	 * Save the posted style and image
	 *
	 */
	function SelectWallpaper(){
		global $langmessage;

		$title = $_POST['title'];

		if( !$this->SwitchImage($title) ){
			return;
		}

		if( !$this->SwitchStyle($title) ){
			return;
		}

		if( !gpFiles::SaveArray($this->config_file,'config',$this->config) ){
			msg($langmessage['OOPS']);
		}
	}


	/**
	 * Change the background image used for the current page;
	 *
	 */
	function SwitchImage($title){
		global $langmessage, $dataDir;

		if( !isset($_POST['image']) ){
			return false;
		}


		$bg = $_POST['image'];

		// remove background image
		if( $bg=='' ){
			if (isset($this->config['pages'][$title])) {
				unset($this->config['pages'][$title]);
			}


		// set background image
		}else{
			$this->config['pages'][$title]['img'] = $bg;
		}

		return true;
	}



	/**
	 * Change the css used for the current page
	 *
	 */
	function SwitchStyle($title){
		global $langmessage;

		if( !isset($_POST['style']) || !isset($this->config['pages'][$title]) ){
			return false;
		}

		$i			= (int)$_POST['style'];
		$custom		= trim($_POST['custom_css']);

		if( $i === 0 ){

			if( empty($custom) ){
				msg('Custom CSS Required');
				return false;
			}

			$this->config['pages'][$title]['custom'] = $custom;

		}elseif( $i === -1 ){
			unset($this->config['pages'][$title]);

		}elseif( isset($this->config['style'.$i]) ){
			unset($this->config['pages'][$title]['custom']);
			$this->config['pages'][$title]['style'] = $i; // set background style

		}else{
			msg('Invalid style #'.$i);
			return false;
		}

		return true;
	}

}
