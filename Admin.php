<?php

defined('is_running') or die('Not an entry point...');

gpPlugin_incl('Gadget.php');

class WallpaperChangerAdmin extends WallpaperChanger{


	function __construct(){

		parent::__construct();

		$cmd = common::GetCommand();
		switch($cmd){
			case 'SaveWallpaperConfig';
				$this->SaveWallpaperConfig();
			break;
			case 'SelectWallpaper':
				$this->SelectWallpaper();
			break;
		}

		$this->Show_Config();

		echo pre($this->config);
	}


	/**
	 *
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
		echo '<button type="submit" name="cmd" value="SaveWallpaperConfig" class="gpsubmit">'.$langmessage['save'].'</button>';
		echo '<span style="float:right">See also: <a href="http://www.w3schools.com/css/css_background.asp" target="_blank">w3 schools</a>, <a href="http://stackoverflow.com/questions/1150163/stretch-and-scale-a-css-image-in-the-background-with-css-only/9845744#9845744" target="_blank">stretched img</a> ...</span>';
		echo '</form>';

		//pages
		echo '<br/>';
		echo '<h3>Pages</h3>';
		echo '<table class="bordered striped full_width">';
		echo '<thead><tr style="text-align:left"><th>Page</th><th>IMG</th><th>CSS</th></tr></thead>';
		echo '<tbody>';
		foreach( $this->config['pages'] as $page => $info ){
			echo '<tr><td>';
			echo common::Link_Page($page);
			echo '</td><td>';
			echo '<a href="'.$info['img'].'" name="gallery" rel="gallery_uploaded">'.basename($info['img']).'</a>';
			echo '</td><td>';

			if( !empty($info['custom']) ){
				echo 'Custom';
			}else{
				echo '#'.$info['style'];
			}
			echo ' &nbsp; ';
			echo common::Link($page,$langmessage['edit'],'cmd=SelectWallpaperDialog','data-cmd="gpabox"');
			echo '</td></tr>';
		}
		echo '</tbody>';
		echo '</table>';
	}


	/**
	 * Save the addon configuration
	 *
	 */
	function SaveWallpaperConfig(){
		global $langmessage, $dataDir;

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

		//check the directory
		$data_real	= realpath($dataDir);
		$dir		= realpath($dataDir.'/data/_uploaded/'.trim($s,'/\\'));

		if( !is_dir($dir) ){
			msg('Image directory is not a folder');
			return false;
		}

		if( strpos($dir,$data_real) !== 0 ){
			msg('Invalid image directory');
			return false;
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
			msg($langmessage['SAVED']);
		}
	}

}
