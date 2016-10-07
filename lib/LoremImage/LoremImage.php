<?php
namespace LoremImage;
use \LoremImage\Arkantas\Image as Image;
use \LoremImage\Arkantas\RouterController as RouterController;

class LoremImage{

	private $path_url;
	private $path_images;

	public function __construct($path_url = '/loremimage/', $path_images = ''){
		$this->setPathUrl($path_url);
		$this->setPathImages($path_images);
	}

	public function getPathUrl(){
		return $this->path_url;
	}

	public function getPathImages(){
		return $this->path_images;
	}

	public function setPathUrl($path){
		$this->path_url = $path;

		$rc = new RouterController;
		$rc->setPath($path);
		$rc->setError404(true);
		
		$rc->addDefault('api', array(
				array(
					0 => array("name" => "width", "type" => "int"),
					1 => array("name" => "height", "type" => "int")
				),
				array(
					0 => array("name" => "width", "type" => "int"),
					1 => array("name" => "height", "type" => "int"),
					2 => array("name" => "category", "type" => "text")
				),
				array(
					0 => array("name" => "width", "type" => "int"),
					1 => array("name" => "height", "type" => "int"),
					2 => array("name" => "category", "type" => "text"),
					3 => array("name" => "picture", "type" => "int")
				),
				#
				array(
					0 => array("name" => "picture", "type" => "int")
				),
				array(
					0 => array("name" => "category", "type" => "text")
				),
				array(
					0 => array("name" => "category", "type" => "text"),
					1 => array("name" => "picture", "type" => "int")
				)
		));
		$rc->load();
	}

	public function setPathImages($path){
		$this->path_images = $path;
	}

	private function getImage(){

		$category = '';
		if(@$_GET['category'] != '')
			$category = (is_dir($this->getPathImages() . $_GET['category'])) ? $_GET['category'] . '/' : '' ; 

		$category_search = ($category == '') ? 1 : 0 ;
		$files 	  = $this->listFiles($this->getPathImages() . $category, $category_search);

		//echo "<pre>"; echo print_r($_GET); exit;

		# select image
		if(is_numeric(@$_GET['picture'])){
			if(($_GET['picture']-1) < sizeof($files) && ($_GET['picture']-1) >= 0)
				return $files[($_GET['picture']-1)];
		}

		if(count($files) == 0)
			return null;

		# rand
		return $files[rand(0, (sizeof($files) -1))];
	}


	public function render(){

		$path_image = $this->getImage();

		if(empty($path_image)){
			echo 'Images not found.';
			exit;
		}

		$img = new Image($path_image);
		$img->transparency();

		# width, height
		if(is_numeric(@$_GET['width']) && is_numeric(@$_GET['height']))
			$img->resize($_GET['width'], $_GET['height'], 'crop');

		# effects
		$effects = array(
			'pixelate',
	 		'smooth',
	 		'noise',
	 		'negative',
	 		'emboss',
	 		'edge',
	 		'contrast',
	 		'colorize',
	 		'grayscale',
	 		'brightness',
	 		'blur2',
	 		'blur',
	 		'sharpen'
		);
		if(in_array(@$_GET['effect'], $effects)){
			$qtd = (is_numeric(@$_GET['qtd']) && $_GET['qtd'] > 0) ? @$_GET['qtd'] : 1 ;
			$img->filter($_GET['effect'], $qtd, @$_GET['arg1'], @$_GET['arg2'], @$_GET['arg3'], @$_GET['arg4']);
		}

		$img->save();
	}

	public function listFiles( $from = '.', $category_search = 1)
	{
	    if(! is_dir($from))
	        return false;
	    
	    $files = array();
	    $dirs = array($from);
	    while( NULL !== ($dir = array_pop( $dirs)))
	    {
	        if( $dh = opendir($dir))
	        {
	            while( false !== ($file = readdir($dh)))
	            {
	                if( $file == '.' || $file == '..')
	                    continue;
	                $path = $dir . '/' . $file;
	                if( is_dir($path)){
	                	if($category_search)
	                    	$dirs[] = $path;
	                }else{
	                	$imgsize_validator = getimagesize($path);
	                	if(is_array( $imgsize_validator ))
	                    	$files[] = $path;
	                }
	            }
	            closedir($dh);
	        }
	    }


	    return $files;
	}

}