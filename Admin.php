<?php

defined('is_running') or die('Not an entry point...');

gpPlugin_incl('Gadget.php');

class WallpaperChangerAdmin extends WallpaperChanger{


	function __construct(){

		parent::__construct();

		if (isset($_POST['save'])) {
			$this->Save_Config();

		}elseif (isset($_POST['savec'])) {
			$this->Save_Custom_Css();

		}elseif (isset($_GET['wpcedit'])) {
			$this->wpcedit($_GET['wpcedit']);
		}

		$this->Show_Config();

		//echo pre($this->config);
	}


	/**
	 *
	 */
	function Show_Config(){
		global $langmessage, $gp_titles;

		echo '<h2>Wallpaper Changer</h2>';
		echo '<form name="wpconfig" action="'.common::GetUrl('Admin_Wallpaper_Changer').'" method="post">';

		//style
		echo '<label>Image directory<br/><input type="text" name="path" value="'.$this->config['path'].'" /> &#187; ';
		echo common::Link('Admin_Uploaded',$langmessage['uploaded_files'],'','onclick="var pos=this.href.indexOf(\'?\'); if (pos>-1) this.href = this.href.substr(0,pos); this.href += \'?dir=\'+encodeURIComponent(document.forms.wpconfig.path.value);" target="_blank"').'</label><br/><br/>';
		for ($i=1; $i<=4; $i++) {
			echo '<div style="font-size:large; margin:0.5em 0; cursor:pointer" onclick="$(this).next(\'textarea\').toggle()">Common Style '.$i.'</div>';
			echo '<textarea name="style'.$i.'" cols="50" rows="6" style="width:100%;display:none;">'.htmlspecialchars($this->config['style'.$i]).'</textarea><br/>';
		}
		echo '<input type="submit" name="save" value="'.$langmessage['save'].'" />';
		echo '<span style="float:right">See also: <a href="http://www.w3schools.com/css/css_background.asp" target="_blank">w3 schools</a>, <a href="http://stackoverflow.com/questions/1150163/stretch-and-scale-a-css-image-in-the-background-with-css-only/9845744#9845744" target="_blank">stretched img</a> ...</span>';
		echo '</form>';

		//pages
		echo '<h3>Pages</h3>';
		echo '<table class="bordered striped full_width">';
		echo '<thead><tr style="text-align:left"><th>Page</th><th>IMG</th><th>CSS</th></tr></thead>';
		echo '<tbody>';
		foreach( $this->config['pages'] as $page => $info ){
			$path = $info['img'];
			echo '<tr><td>'.common::Link_Page($page).'</td>';
			echo '<td><a href="'.$path.'" name="gallery" rel="gallery_uploaded">'.basename($info['img']).'</a></td>';
			echo '<td>'.common::Link('Admin_Wallpaper_Changer',$langmessage['edit'],'wpcedit='.$page).'</td>';
			echo '</tr>';
		}
		echo '</tbody>';
		echo '</table>';
	}

	function wpcedit($t) { // edit custom css for page with title $t
		global $langmessage;
		if (!isset($this->config['pages'][$t])) {
			message('Invalid title '.$t);
			return;
		}
		if (isset($this->config['pages'][$t]['custom'])) {
			$css = $this->config['pages'][$t]['custom'];
		} else {
			$style = $this->config['pages'][$t]['style'];
			if (isset($this->config['style'.$style])) {
				$css = $this->config['style'.$style];
			} else {
				$css = $this->config['style1'];
			}
		}
		echo '<div style="font-size:large; margin:0 0 1em 0">Edit '.$t.'</div>';
		echo '<form name="wpcedit" action="'.common::GetUrl('Admin_Wallpaper_Changer').'" method="post">';
		echo '<textarea name="stylec" cols="50" rows="6" style="width:100%">'.htmlspecialchars($css).'</textarea><br/>';
		echo '<input type="hidden" name="title" value="'.$t.'" />';
		echo '<input type="submit" name="savec" value="'.$langmessage['save'].'" />';
		echo '</form><br/>';
	}

	function Save_Custom_Css() {
		global $langmessage;
		$t = trim($_POST['title']);
		if (!isset($this->config['pages'][$t])) {
			message('Invalid title '.$t);
			return;
		}
		$css = trim($_POST['stylec']);
		if ($css=='') {
			unset($this->config['pages'][$t]['custom']);
		} else {
			$this->config['pages'][$t]['custom'] = $css;
		}
		if (gpFiles::SaveArray($this->config_file,'config',$this->config)) {
			message($langmessage['SAVED'].' ('.$t.' , CSS)');
		}
	}

	function Save_Config() {
		global $langmessage;
		$s = trim($_POST['path']);
		if ($s=='') {
			$s='/';
		}
		if ($s[0]!='/') {
			$s='/'.$s;
		}
		if ($s[strlen($s)-1]=='/') {
			$s=substr($s,0,-1);
		}
		$this->config['path'] = $s;
		$this->config['style1'] = trim($_POST['style1']);
		$this->config['style2'] = trim($_POST['style2']);
		$this->config['style3'] = trim($_POST['style3']);
		$this->config['style4'] = trim($_POST['style4']);
		if ($this->config['style1']=='') {
			$this->config['style1'] = $this->default_style1;
		}
		if ($this->config['style2']=='') {
			$this->config['style2'] = $this->default_style2;
		}
		if ($this->config['style3']=='') {
			$this->config['style3'] = $this->default_style3;
		}
		if ($this->config['style4']=='') {
			$this->config['style4'] = $this->default_style4;
		}
		if (gpFiles::SaveArray($this->config_file,'config',$this->config)) {
			message($langmessage['SAVED']);
		}
	}

}
