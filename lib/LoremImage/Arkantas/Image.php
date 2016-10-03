<?php
namespace LoremImage\Arkantas;
/*
	Classe: Image
	www.coresphere.com.br
*/
class Image{

	/* Variáveis para armazenamento de arquivos/imgs */
	private $path;
	private $img;
	private $img_temp;
	
    /* Armazenam as dimensões da imagem atual e da nova imagem caso exista */
	private $width;
    private $height;
	private $newWidth;
	private $newHeight;
	private $sizeHtml;
	private $mime;
	
    /* Variáveis para o posicionamento do crop */
	private $pos_x, $pos_y;
    
    /* Informações sobre o arquivo enviado e diretório */
	private $format;
	private $extension;
	private $size;
	private $file;
	private $dir;

    /* Array RGB para resize com preenchimendo do fundo */
	private $rgb;
	
	/* Transparência na imagem habilitada */
	private $transparency = false; // obs: todos os artifícios que façam merge em duas imagens deixam de funcionar apropriadamente caso esteja habilitado

    /* Coordenadas para posicionamento (posCrop -> crop, coordinate -> cordenadas) */
	private $posCrop;
	private $coordinate;

    /*
	 * Construtor
	 * @param $string caminho da imagem a ser carregada [opcional]
	 * @return void
	 */
	public function __construct($path = '')
	{
		$this->path = $path;
		if($this->path)
			$this->info();
	
		// RGB padrão -> branco
		$this->rgb(255, 255, 255);
	}
	
	
    /*
	 * Carrega imagem
	 * @param String
	 * @return Object
	 */
	public function load($path = '')
	{
		$this->path = $path;
		$this->info();
		return $this;
	}
	
    /*
	 * Retorna dados da imagem
	 * @return void
	 */
	private function info()
	{
		// verifica se imagem existe
		if(is_file($this->path))
		{
			// dados do arquivo
			$this->imageInfo();

			// verifica se é imagem
			if(!$this->isImage()){
				throw new Exception('Erro: Arquivo '.$this->path.' não é uma imagem.');
			}else{
				// busca dimensões da imagem enviada
				$this->dimensions();

				// cria imagem para php
				$this->createImage();
			}
		}else{
			throw new Exception('Erro: Arquivo de imagem não encontrado.');
		}
	}

    /*
	 * method: isImage()
	 * Verifica se arquivo indicado é imagem
	 * return bool true/false
	 */
	private function isImage()
	{
		// filtra extensão
		$valida = getimagesize($this->path);
		if(!is_array( $valida ) || empty( $valida )){
			return false;
		}else{
			return true;
		}
	}
	

    /*
	 * method: imageInfo()
	 * Busca dados do arquivo
	 * return void
	 */
	private function imageInfo()
	{
		// imagem de origem
		$pathinfo = pathinfo($this->path);
		$this->extension = strtolower($pathinfo['extension']);
		$this->file 	 = $pathinfo['basename'];
		$this->dir 		 = $pathinfo['dirname'];
		$this->size 	 = filesize($this->path);
		
	}
	
    /*
	 * method: dimensions()
	 * Obtem dimensões da imagem indicada
	 * return void
	 */
	private function dimensions()
	{
		
		$properties = getimagesize($this->path);
		if(is_array($properties)){

			$this->width = $properties[0];
			$this->height  = $properties[1];
			
			/*
			 * formatos
			 * 1 = gif, 2 = jpeg, 3 = png, 6 = BMP
			 * http://br2.php.net/manual/en/function.exif-imagetype.php
			 */
			$this->format = $properties[2];
			$this->sizeHtml = $properties[3];
			$this->mime = $properties['mime'];
		}
		
	}
	
    /*
	 * method: isTransparency()
	 * Verifica se a imagem será manipulada com transparência
	 * @param: temp(0) e alpha(true)
	 * @return void
	 */
	private function isTransparency($temp = 0, $alpha = true)
	{
		if($this->transparency){
			$image = ($temp) ? $this->img_temp : $this->img ;

			imagealphablending($image, $alpha);
			imagesavealpha($image, true);
		}
	}
	
    /*
	 * method: criaImagem()
	 * Cria objeto de imagem para manipulação lib GD
	 * return void
	 */
	private function createImage()
	{
		switch ($this->format)
		{
			case 1:
				$this->img = imagecreatefromgif($this->path);
			break;
			case 2:
				$this->img = imagecreatefromjpeg($this->path);
			break;
			case 3:
				$this->img = imagecreatefrompng($this->path);
			break;
			case 6:
				$this->img = imagecreatefrombmp($this->path);
			break;
			default:
				throw new Exception('Extensao do arquivo inválida.');
			break;
		}
		
		// verifica se transparência está habilitada
		$this->isTransparency();
		
	}
	
	
	/* inicio :: métodos encadeados públicos */
	
	
    /*
	 * method: rgb($r, $g, $b)
	 * Obtem Armazena os valores RGB para redimensionamento com preenchimento
	 * @param: R, G e B
	 * @return Object instância atual do objeto, para métodos encadeados
	 */
	public function rgb($r, $g, $b)
	{
		$this->rgb = array($r, $g, $b);
		return $this;
	}

    /*
	 * method: hexa($color)
	 * Obtem Armazena os valores RGB para redimensionamento com preenchimento
	 * @param: R, G e B
	 * @return Object instância atual do objeto, para métodos encadeados
	 */
     public function hexa($color)
     {
          $color = str_replace('#', '', $color);

          if(strlen($color) == 3) $color .= $color; // #fff, #000 etc.

          $this->rgb = array(
            hexdec(substr( $color, 0, 2 )),
            hexdec(substr( $color, 2, 2 )),
            hexdec(substr( $color, 4, 2 )),
          );
          return $this;
     }
	
    /*
	 * method: coordinates()
	 * guarda valores das coordenadas para aplicar em resize do tipo 'coordinate'
	 * @param Integer $dst_x : x-coordinate of destination point. 
	 * @param Integer $dst_y : y-coordinate of destination point. 
	 * @param Integer $src_x : x-coordinate of source point. 
	 * @param Integer $src_y : y-coordinate of source point. 
	 * @param Integer $dst_w : Destination width. 
	 * @param Integer $dst_h : Destination height. 
	 * @return Object instância atual do objeto, para métodos encadeados
	 */
	public function coordinates($dst_x = 0, $dst_y = 0, $src_x = 0, $src_y = 0, $dst_w = 0, $dst_h = 0)
	{
		$this->coordinate['dst_x'] = $dst_x;
		$this->coordinate['dst_y'] = $dst_y;
		
		$this->coordinate['src_x'] = $src_x;
		$this->coordinate['src_y'] = $src_y;
		
		$this->coordinate['dst_w'] = $dst_w;
		$this->coordinate['dst_h'] = $dst_h;
		
		return $this;
	}
	
    /*
	 * method: transparency($bool)
	 * Obtem Armazena os valores RGB para redimensionamento com preenchimento
	 * @param: bool(true)
	 * @return Object instância atual do objeto, para métodos encadeados
	 */
	public function transparency($bool = true)
	{
		if(is_bool($bool))
			$this->transparency = $bool;
		
		return $this;
	}
	
	/*
	 * method: resize()
	 * Redimensiona imagem
	 * @param Int $newWidth valor em pixels da nova largura da imagem
	 * @param Int $newHeight valor em pixels da nova altura da imagem
	 * @param String $typeresize método para redimensionamento (padrão [vazio], preenchimento ou crop)
 	 * @return Object instância atual do objeto, para métodos encadeados
	 */
	public function resize($newWidth = 0, $newHeight = 0, $typeresize = '')
	{
		// seta variáveis passadas via parâmetro
		$this->newWidth  = $newWidth;
		$this->newHeight = $newHeight;

		// verifica se passou altura ou largura como porcentagem
		// largura %
		$pos = strpos($this->newWidth, '%');
		if($pos !== false && $pos > 0){
			$percent = ((int) str_replace('%', '', $this->newWidth)) / 100;
			$this->newWidth = round($this->width * $percent);
		}
		
		// altura %
		$pos = strpos($this->newHeight, '%');
		if($pos !== false && $pos > 0){
			$percent = ((int) str_replace('%', '', $this->newHeight)) / 100;
			$this->newHeight = $this->height * $percent;
		}

		// define se só passou nova largura ou altura
		if(!$this->newWidth && !$this->newHeight){
			return false;
		}
		// só passou altura
		elseif(!$this->newWidth){
			$this->newWidth = $this->width / ( $this->height/$this->newHeight);
		}
		// só passou largura
		elseif(!$this->newHeight){
			$this->newHeight = $this->height / ( $this->width/$this->newWidth);
		}

		// redimensiona de acordo com tipo
		switch($typeresize)
		{
			case 'crop':
				$this->cropResize();
			break;
			case 'fill':
				$this->fillResize();
			break;
			case 'proportional':
				$this->proportionalResize();
			break;
			case 'coordinate':
				$this->coordinateResize();
			break;
			default:
				$this->simpleResize();
			break;
		}

		// atualiza dimensões da imagem
		$this->width = $this->newWidth;
		$this->height = $this->newHeight;

		return $this;
	}
	
	/*
	* method: #simpleResize()
	* Redimensiona imagem, modo padrão, sem crop ou preenchimento
	* (distorcendo caso tenha passado ambos altura e largura)
	* @return void
	*/
	private function simpleResize()
	{
		// cria imagem de destino temporária
		$this->img_temp = imagecreatetruecolor($this->newWidth, $this->newHeight);

		// verifica se transparência está habilitada
		$this->isTransparency(1, false);
		
		imagecopyresampled($this->img_temp, $this->img, 0, 0, 0, 0, $this->newWidth, $this->newHeight, $this->width, $this->height);
		$this->img = $this->img_temp;
	}
	
	/*
	* method: #fillImage()
	* adiciona cor de fundo sobre a imagem
	* @return void
	*/
	private function fillImage()
	{
		$backgroundcolor = imagecolorallocate($this->img_temp, $this->rgb[0], $this->rgb[1], $this->rgb[2]);
		imagefill($this->img_temp, 0, 0, $backgroundcolor);
	}
	
	
	/*
	* method: #fillResize()
	* Redimensiona imagem sem cropar, proporcionalmente,
	* preenchendo espaço vazio com cor rgb especificada
	* @return void
	*/
	private function fillResize()
	{
		// cria imagem de destino temporária
		$this->img_temp = imagecreatetruecolor($this->newWidth, $this->newHeight);

		// verifica se transparência está habilitada
		$this->isTransparency(1, false);
		
		// adiciona cor de fundo à nova imagem
		$this->fillImage();

		// salva variáveis para centralização
		$dif_x = $dif_w = $this->newWidth;
		$dif_y = $dif_h = $this->newHeight;

		/*
		 * Verifica altura e largura
		 */
		if(($this->width / $this->newWidth) > ($this->height / $this->newHeight)){
			$fator = $this->width / $this->newWidth;
		}else{
			$fator = $this->height / $this->newHeight;
		}
		
		$dif_w = $this->width / $fator;
		$dif_h = $this->height / $fator;

		// copia com o novo tamanho, centralizando
		$dif_x = ( $dif_x - $dif_w ) / 2;
		$dif_y = ( $dif_y - $dif_h ) / 2;
		imagecopyresampled($this->img_temp, $this->img, $dif_x, $dif_y, 0, 0, $dif_w, $dif_h, $this->width, $this->height);
		$this->img = $this->img_temp;
	}
	
	/*
	* method: #proportionalResize()
	* Redimensiona imagem sem cropar, proporcionalmente e sem preenchimento.
	* @return void
	*/
	private function proportionalResize()
	{
		/*
		 * Verifica altura e largura proporcional.
		 */
		$ratio_orig = $this->width/$this->height;

		if($this->newWidth/$this->newHeight > $ratio_orig){
			$dif_w = $this->newHeight*$ratio_orig;
			$dif_h = $this->newHeight;
		}else{
			$dif_w = $this->newWidth;
			$dif_h = $this->newWidth/$ratio_orig;
		}

		// cria imagem de destino temporária
		$this->img_temp = imagecreatetruecolor($dif_w, $dif_h);

		// verifica se transparência está habilitada
		$this->isTransparency(1, false);
		
		// Resample
		imagecopyresampled($this->img_temp, $this->img, 0, 0, 0, 0, $dif_w, $dif_h, $this->width, $this->height);
		$this->img = $this->img_temp;
	}
	
	/*
	 * method: #calculatePosCrop()
	 * Calcula a posição do crop
	 * Os índices 0 e 1 correspondem à posição x e y do crop na imagem
	 * Os índices 2 e 3 correspondem ao tamanho do crop
	 * @return void
	 */
	private function calculatePosCrop()
	{
		// média altura/largura
		$hm = $this->height / $this->newHeight;
		$wm = $this->width / $this->newWidth;

		// 50% para cálculo do crop
		$h_height = $this->newHeight / 2;
		$h_width = $this->newWidth / 2;

		// calcula novas largura e altura
		if(!is_array($this->posCrop)){
			if($wm > $hm){
				$this->posCrop[2] = $this->width / $hm;
				$this->posCrop[3] = $this->newHeight;
				$this->posCrop[0] = ( $this->posCrop[2] / 2 ) - $h_width;
				$this->posCrop[1] = 0;
			}
			// largura <= altura
			elseif(($wm <= $hm)){
				$this->posCrop[2] = $this->newWidth;
				$this->posCrop[3] = $this->height / $wm;
				$this->posCrop[0] = 0;
				$this->posCrop[1] = ( $this->posCrop[3] / 2 ) - $h_height;
			}
		}
	}
	
	/*
	 * #method: cropResize()
	 * Redimensiona imagem, cropando para encaixar no novo tamanho, sem sobras
	 * baseado no script original de Noah Winecoff
	 * http://www.findmotive.com/2006/12/13/php-crop-image/
	 * atualizado para receber o posicionamento X e Y e/ou Coordenadas Inteligentes
	 * do crop na imagem.
	 * @return void
	 */
	private function cropResize()
	{
		
		// calcula posicionamento do crop
		$this->calculatePosCrop();

		// cria imagem de destino temporária
		$this->img_temp = imagecreatetruecolor($this->newWidth, $this->newHeight);

		// verifica se transparência está habilitada
		$this->isTransparency(1, false);
		
		//adiciona cor de fundo à nova imagem
		$this->fillImage();
          
		//coordenadas inteligentes
		switch($this->posCrop[0]){
			
			case 'esquerdo':
				$this->pos_x = 0;
			break;
			
			case 'direito':
				$this->pos_x = $this->width - $this->newWidth;
			break;

			case 'meio':
				$this->pos_x = ( $this->width - $this->newWidth ) / 2;
			break;

			default:
				$this->pos_x = $this->posCrop[0];
			break;

		}
		
		switch($this->posCrop[1]){
			case 'topo':
				$this->pos_y = 0;
			break;
			
			case 'inferior':
				$this->pos_y = $this->height - $this->newHeight;
			break;
			
			case 'meio':
				$this->pos_y = ( $this->height - $this->newHeight ) / 2;
			break;
			
			default:
				$this->pos_y = $this->posCrop[1];
			break;
		}

		$this->posCrop[0] = $this->pos_x;
		$this->posCrop[1] = $this->pos_y;

		imagecopyresampled($this->img_temp, $this->img, -$this->posCrop[0], -$this->posCrop[1], 0, 0, $this->posCrop[2], $this->posCrop[3], $this->width, $this->height);
		$this->img = $this->img_temp;
	}
	
	/*
	* method: #coordinateResize()
	* Redimensiona imagem de acordo com as cordenadas setadas através
	* da váriavel array $coordinate;
	* @return void
	*/
	private function coordinateResize()
	{
		// cria imagem de destino temporária
		$this->img_temp = imagecreatetruecolor($this->newWidth, $this->newHeight);

		// verifica se transparência está habilitada
		$this->isTransparency(1, false);
		
		//adiciona cor de fundo à nova imagem
		$this->fillImage();
		
		imagecopyresampled($this->img_temp, $this->img, $this->coordinate['dst_x'], $this->coordinate['dst_y'], $this->coordinate['src_x'], $this->coordinate['src_y'],$this->coordinate['dst_w'], $this->coordinate['dst_h'], $this->width, $this->height);
		
		$this->img = $this->img_temp;
	}

    /*
	 * method: filter()
	 * aplica filtros avançados
	 *  - requer o GD compilado com a função imagefilter() e outros, como o sharpen()
	 *    http://br.php.net/imagefilter
	 * @param String $filter constante/nome do filtro
	 * @param Integer $qtd número de vezes que o filtro deve ser aplicado
	 * utilizado em blur, edge, emboss, pixel e rascunho
	 * @param $arg1, $arg2 e $arg3 - ver manual da função imagefilter
	 * @return Object instância atual do objeto, para métodos encadeados
	 *
	 * filters: 
	 *  - pixelate (php 5.3+)
	 *  - smooth
	 *  - noise
	 *  - negative
	 *  - emboss
	 *  - edge
	 *  - contrast
	 *  - colorize
	 *  - grayscale
	 *  - brightness
	 *  - blur2
	 *  - blur
	 *  - sharpen
	 */
	public function filter($filter, $qtd = 1, $arg1 = NULL, $arg2 = NULL, $arg3 = NULL, $arg4 = NULL)
	{
		switch($filter)
		{
			//blur
			case 'blur':
				if(is_numeric($qtd) && $qtd > 1){
					for($i = 1 ; $i <= $qtd ; $i++){
						imagefilter($this->img, IMG_FILTER_GAUSSIAN_BLUR);
					}
				}else{
					imagefilter($this->img, IMG_FILTER_GAUSSIAN_BLUR);
				}
			break;
			
			//blur2
			case 'blur2':
				if(is_numeric($qtd) && $qtd > 1){
					for($i = 1; $i <= $qtd ; $i++){
						imagefilter($this->img, IMG_FILTER_SELECTIVE_BLUR);
                    }
                }else{
					imagefilter($this->img, IMG_FILTER_SELECTIVE_BLUR);
                }
			break;
			
			//brilho
            case 'brightness':
				imagefilter($this->img, IMG_FILTER_BRIGHTNESS, $arg1);
			break;
			
            case 'grayscale':
				imagefilter($this->img, IMG_FILTER_GRAYSCALE);
			break;
			
			case 'colorize':
				imagefilter($this->img, IMG_FILTER_COLORIZE, $arg1, $arg2, $arg3, $arg4);
			break;
			
            case 'contrast':
				imagefilter($this->img, IMG_FILTER_CONTRAST, $arg1);
			break;
			
			case 'edge':
                if(is_numeric( $qtd ) && $qtd > 1){
					for($i = 1; $i <= $qtd ; $i++){
						imagefilter($this->img, IMG_FILTER_EDGEDETECT);
					}
				}else{
					imagefilter($this->img, IMG_FILTER_EDGEDETECT);
				}
			break;
			
			case 'emboss':
				if(is_numeric($qtd) && $qtd > 1){
					for($i = 1 ; $i <= $qtd ; $i++){
						imagefilter($this->img, IMG_FILTER_EMBOSS);
					}
                }else{
					imagefilter($this->img, IMG_FILTER_EMBOSS);
				}
			break;
			
			case 'negative':
				imagefilter($this->img, IMG_FILTER_NEGATE);
			break;
			
            case 'noise':
				if(is_numeric($qtd) && $qtd > 1){
					for( $i = 1; $i <= $qtd; $i++ ){
						imagefilter($this->img, IMG_FILTER_MEAN_REMOVAL);
					}
				}else{
					imagefilter($this->img, IMG_FILTER_MEAN_REMOVAL);
				}
			break;
			
			case 'smooth':
				if(is_numeric($qtd) && $qtd > 1){
					for($i = 1; $i <= $qtd; $i++){
                        imagefilter($this->img, IMG_FILTER_SMOOTH, $arg1);
                    }
                }else{
					imagefilter($this->img, IMG_FILTER_SMOOTH, $arg1);
				}
			break;
			
			case "sharpen":
				$this->sharpen();
			break;
			
			// SOMENTE 5.3 ou superior
			case 'pixelate':
				if(is_numeric($qtd) && $qtd > 1){
					for($i = 1; $i <= $qtd; $i++){
						imagefilter($this->img, IMG_FILTER_PIXELATE, $arg1, $arg2);
					}
				}else{
					imagefilter($this->img, IMG_FILTER_PIXELATE, $arg1, $arg2);
				}
			break;
		
			default:
			break;
		}
		return $this;
	}
	
    /*
	 * method: #sharpen()
	 * melhor filtro sharpen GD
	 * @return Object instância atual do objeto, para métodos encadeados
	 */
	private function sharpen()
	{
		$qualidade = array(
			array(-1, -1, -1),
			array(-1, 16, -1),
			array(-1, -1, -1),
		);
    
		$divisao = array_sum(array_map('array_sum', $qualidade));
		imageconvolution($this->img, $qualidade, $divisao, 0); //0 = offset
        
        return $this;
    }
	
    /*
	 * #method: mark()
	 * @param String $image caminho da imagem de marca d'água
	 * @param Int/String $x posição x da marca na imagem ou constante para marcaFixa()
	 * @param Int/Sring $y posição y da marca na imagem ou constante para marcaFixa()
	 * @return Boolean true/false dependendo do resultado da operação
	 * @param Int $alfa valor para transparência (0-100)
	 * -> se utilizar alfa, a função imagecopymerge não preserva
	 * -> o alfa nativo do PNG
	 * @return Object instância atual do objeto, para métodos encadeados
	 */
	public function mark($image, $x = 0, $y = 0, $alfa = 100)
	{
		// cria imagem temporária para merge
		if($image){

			if(is_string($x) && is_string($y)){
				//define x/y e chama método mark novamente
				return $this->fixedMark($image, $x . '-' . $y, $alfa);
			}

			$pathinfo = pathinfo($image);
			switch(strtolower($pathinfo['extension']))
			{
				case 'jpg':
				case 'jpeg':
					$markwater = imagecreatefromjpeg($image);
				break;
				
				case 'png':
					$markwater = imagecreatefrompng($image);
				break;
				
				case 'gif':
					$markwater = imagecreatefromgif($image);
				break;
				
				case 'bmp':
					$markwater = imagecreatefrombmp($image);
				break;
				
				default:
					throw new Exception('Arquivo de marca d\'água inválido.');
					return false;
				break;
			}
		}else{
			return false;
		}
		
		// dimensões
		$mark_w = imagesx($markwater);
		$mark_h = imagesy($markwater);
		
		// retorna imagens com marca d'água
		if(is_numeric($alfa) && (($alfa > 0) && ($alfa < 100))){
			imagecopymerge($this->img, $markwater, $x, $y, 0, 0, $mark_w, $mark_h, $alfa);
		}else{
			imagecopy($this->img, $markwater, $x, $y, 0, 0, $mark_w, $mark_h);
		}
		return $this;
	}
	 
    /*
	 * #method: fixedMark()
	 * adiciona imagem de marca d'água, com valores fixos
	 * @param String $imagem caminho da imagem de marca d'água
	 * @param String $posicao posição/orientação fixa da marca d'água
	 * [topo, meio, baixo] + [esquerda, centro, direita]
	 * @param Int $alfa valor para transparência (0-100)
	 * @return void
	 */
	private function fixedMark($image, $pos, $alfa = 100)
	{
		// dimensões da marca d'água
		list($mark_w, $mark_h) = getimagesize($image);

		// define X e Y para posicionamento
		switch($pos)
		{
			case 'top-left':
				$x = 0;
				$y = 0;
			break;
			
			case 'top-center':
				$x = ($this->width - $mark_w) / 2;
				$y = 0;
			break;
			
			case 'top-right':
				$x = $this->width - $mark_w;
				$y = 0;
			break;
			
			case 'middle-left':
				$x = 0;
				$y = ($this->height / 2) - ($mark_h / 2);
			break;
			
			case 'middle-center':
				$x = ($this->width - $mark_w) / 2;
				$y = ($this->height / 2) - ($mark_h / 2);
			break;
			
			case 'middle-right':
				$x = $this->width - $mark_w;
				$y = ($this->height / 2) - ($mark_h / 2);
			break;
			
			case 'bottom-left':
				$x = 0;
				$y = $this->height - $mark_h;
			break;
			
			case 'bottom-center':
				$x = ($this->width - $mark_w) / 2;
				$y = $this->height - $mark_h;
			break;
			
			case 'bottom-right':
				$x = $this->width - $mark_w;
				$y = $this->height - $mark_h;
			break;
			
			default:
				return false;
			break;
			
		}

		//cria marca
		$this->mark($image, $x, $y, $alfa);
		
		return $this;
	}

	/*
	 * method: rotate();
	 * rotaciona imagem em graus
	 * @param Int $graus grau para giro
	 * @return Object instância atual do objeto, para métodos encadeados
	 */
	public function rotate($graus)
	{
		$backgroundcolor = imagecolorallocate($this->img, $this->rgb[0], $this->rgb[1], $this->rgb[2]);
		$this->img = imagerotate($this->img, $graus, $backgroundcolor);
		imagealphablending($this->img, true);
		imagesavealpha($this->img, true);
		
		$this->width  = imagesx($this->img);
		$this->height = imagesx($this->img);
		  
		return $this;
	}
	
	/*
	 * method: flip();
	 * flip/inverte imagem
	 * script original de Noah Winecoff
	 * http://www.php.net/manual/en/ref.image.php#62029
	 * @param String $tipo tipo de espelhamento: h - horizontal, v - vertical
	 * @return Object instância atual do objeto, para métodos encadeados
	 */
	public function flip($type = 'h')
	{
		$w = imagesx($this->img);
		$h = imagesy($this->img);

		$this->img_temp = imagecreatetruecolor($w, $h);

		// vertical
		if('v' == $type){
			for($y = 0 ; $y < $h ; $y++){
				imagecopy($this->img_temp, $this->img, 0, $y, 0, $h - $y - 1, $w, 1);
			}
		}
		// horizontal
		elseif('h' == $type)
		{
			for($x = 0 ; $x < $w ; $x++){
				imagecopy($this->img_temp, $this->img, $x, 0, $w - $x - 1, 0, 1, $h);
			}
		}

		$this->img = $this->img_temp;

		return $this;
	}
	
	/*
	 * method: legend()
	 * adiciona texto à imagem
	 * @param String $text texto a ser inserido
	 * @param Int $size tamanho da fonte
	 * Ver: http://br2.php.net/imagestring
	 * @param Int $x posição x do texto na imagem
	 * @param Int $y posição y do texto na imagem
	 * @param Array/String $backgroundcolor array com cores RGB ou string com cor hexadecimal
	 * @param Boolean $truetype true para utilizar fonte truetype, false para fonte do sistema
	 * @param String $font nome da fonte truetype a ser utilizada
	 * @return void
	 */
	public function legend($text, $size = 5, $x = 0, $y = 0, $backgroundcolor = '', $truetype = false, $font = '')
	{
	
		$textcolor = imagecolorallocate($this->img, $this->rgb[0], $this->rgb[1], $this->rgb[2]);

		/*
		 * Define tamanho da legenda para posições fixas e fundo da legenda
		 */
		if($truetype === true){
			$textDimension = imagettfbbox($size, 0, $font, $text);
			$widthText = $textDimension[4];
			$heightText = $size;
		}else{
			if($size > 5) $size = 5;
			$widthText = imagefontwidth($size) * strlen($text);
			$heightText = imagefontheight($size);
		}

		if(is_string($x) && is_string($y))
			list($x, $y) = $this->positionLegend($x . '-' . $y, $widthText, $heightText);

		/*
		 * Cria uma nova imagem para usar de fundo da legenda
		 */
		if($backgroundcolor)
		{
			if(is_array($backgroundcolor)){
				$this->rgb = $backgroundcolor;
			}elseif(strlen($backgroundcolor) > 3){
				$this->hexa($backgroundcolor);
			}

			$this->img_temp = imagecreatetruecolor($widthText, $heightText);
			$backgroundcolor = imagecolorallocate($this->img_temp, $this->rgb[0], $this->rgb[1], $this->rgb[2]);
			imagefill($this->img_temp, 0, 0, $backgroundcolor);

			imagecopy($this->img, $this->img_temp, $x, $y, 0, 0, $widthText, $heightText);
		}

		// truetype ou fonte do sistema?
		if($truetype === true){
			$y = $y + $size;
			imagettftext($this->img, $size, 0, $x, $y, $textcolor, $font, $text);
		}else{
			imagestring($this->img, $size, $x, $y, $text, $textcolor);
		}
	
		return $this;
	}

    /*
	 * method: positionLegend()
	 * Calcula a posição da legenda de acordo com string passada via parâmetro
	 * @param String $pos valores pré-definidos (topo_esquerda, meio_centro etc.)
	 * @param Integer $width largura da imagem
	 * @param Integer $height altura da imagem
	 * @return void
	 */
	private function positionLegend($pos, $width, $height)
	{
 
		switch($pos)
		{
			case 'top-left':
				$x = 0;
				$y = 0;
			break;
			
			case 'top-center':
				$x = ($this->width - $width) / 2;
				$y = 0;
			break;
			
			case 'top-right':
				$x = $this->width - $width;
				$y = 0;
			break;
			
			case 'middle-left':
				$x = 0;
				$y = ($this->height / 2) - ($height / 2);
			break;
			
			case 'middle-center':
				$x = ($this->width - $width) / 2;
				$y = ($this->height - $height) / 2;
			break;
			
			case 'middle-right':
				$x = $this->width - $width;
				$y = ($this->height / 2) - ($height / 2);
			break;
			
			case 'bottom-left':
				$x = 0;
				$y = $this->height - $height;
			break;
			
			case 'bottom-center':
				$x = ($this->width - $width) / 2;
				$y = $this->height - $height;
			break;
			
			case 'bottom-right':
				$x = $this->width - $width;
				$y = $this->height - $height;
			break;
			
			default:
				return false;
			break;
			
		}
		
		return array($x, $y);
	}	
	
	
	/* fim :: métodos encadeados públicos */
	
	
    /*
	 * method: save()
	 * melhor filtro sharpen GD
	 * @param String $path caminho e nome do arquivo a serem criados
	 * @param Int $quality qualidade da imagem no caso de JPEG (0-100)
	 * @return void
	 */
	public function save($path='', $quality = 100)
	{
		//dados do arquivo de destino
		if($path)
		{
			$pathinfo = pathinfo($path);
			$dir_path = $pathinfo['dirname'];
			$ext_path = strtolower($pathinfo['extension']);

			// valida diretório
			if(!is_dir($dir_path))
				throw new Exception( 'Diretório de destino inválido ou inexistente');
		}
		
		if(!isset($ext_path))
			$ext_path = $this->extension;
	
		switch($ext_path)
		{
			case 'jpg':
			case 'jpeg':
			case 'bmp':
			
				if($path){
					imagejpeg($this->img, $path, $quality);
				}else{
					header("Content-type: image/jpeg; charset=utf-8");
					imagejpeg($this->img, NULL, $quality);
					imagedestroy($this->img);
					exit;
				}
			break;
			case 'png':
				if($path){
					imagepng($this->img, $path);
				}else{
					header("Content-type: image/png; charset=utf-8");
					imagepng($this->img);
					imagedestroy($this->img);
					exit;
				}
			break;
			case 'gif':
				if($path){
					imagegif($this->img, $path);
				}else{
					header("Content-type: image/gif; charset=utf-8");
					imagegif($this->img);
					imagedestroy($this->img);
					exit;
				}
			break;
			case 'ico':
				if($path){
					imagejpeg($this->img, $path, $quality);
				}else{
					header("Content-type: image/x-icon");
					imagejpeg($this->img, $path, $quality);
					imagedestroy($img);
					exit;
				}
			break;
			default:
				return false;
			break;
		}
	}
	
}

//------------------------------------------------------------------------------
// suporte para a manipulação de arquivos BMP

/*********************************************/
/* Function: ImageCreateFromBMP */
/* Author: DHKold */
/* Contact: admin@dhkold.com */
/* Date: The 15th of June 2005 */
/* Version: 2.0B */
/*********************************************/

	function imagecreatefrombmp($filename)
	{
		//Ouverture du fichier en mode binaire
		if (! $f1 = fopen($filename,"rb")) return FALSE;

		//1 : Chargement des ent?tes FICHIER
		$FILE = unpack("vfile_type/Vfile_size/Vreserved/Vbitmap_offset", fread($f1,14));
		if ($FILE['file_type'] != 19778) return FALSE;

		//2 : Chargement des ent?tes BMP
		$BMP = unpack('Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel'.
			'/Vcompression/Vsize_bitmap/Vhoriz_resolution'.
			'/Vvert_resolution/Vcolors_used/Vcolors_important', fread($f1,40));
		$BMP['colors'] = pow(2,$BMP['bits_per_pixel']);
		if ($BMP['size_bitmap'] == 0) $BMP['size_bitmap'] = $FILE['file_size'] - $FILE['bitmap_offset'];
		$BMP['bytes_per_pixel'] = $BMP['bits_per_pixel']/8;
		$BMP['bytes_per_pixel2'] = ceil($BMP['bytes_per_pixel']);
		$BMP['decal'] = ($BMP['width']*$BMP['bytes_per_pixel']/4);
		$BMP['decal'] -= floor($BMP['width']*$BMP['bytes_per_pixel']/4);
		$BMP['decal'] = 4-(4*$BMP['decal']);
		if ($BMP['decal'] == 4) $BMP['decal'] = 0;

		//3 : Chargement des couleurs de la palette
		$PALETTE = array();
		if ($BMP['colors'] < 16777216)
			$PALETTE = unpack('V'.$BMP['colors'], fread($f1,$BMP['colors']*4));

		//4 : Creation de l'image
		$IMG = fread($f1,$BMP['size_bitmap']);
		$VIDE = chr(0);

		$res = imagecreatetruecolor($BMP['width'],$BMP['height']);
		$P = 0;
		$Y = $BMP['height']-1;
		while ($Y >= 0)
		{
			$X=0;
			while ($X < $BMP['width'])
			{
				if ($BMP['bits_per_pixel'] == 24)
					$COLOR = @unpack("V",substr($IMG,$P,3).$VIDE);
				elseif ($BMP['bits_per_pixel'] == 16)
				{
					$COLOR = @unpack("n",substr($IMG,$P,2));
					$COLOR[1] = $PALETTE[$COLOR[1]+1];
				}
				elseif ($BMP['bits_per_pixel'] == 8)
				{
					$COLOR = @unpack("n",$VIDE.substr($IMG,$P,1));
					$COLOR[1] = $PALETTE[$COLOR[1]+1];
				}
				elseif ($BMP['bits_per_pixel'] == 4)
				{
					$COLOR = @unpack("n",$VIDE.substr($IMG,floor($P),1));
					if (($P*2)%2 == 0) $COLOR[1] = ($COLOR[1] >> 4) ; else $COLOR[1] = ($COLOR[1] & 0x0F);
						$COLOR[1] = $PALETTE[$COLOR[1]+1];
				}
				elseif ($BMP['bits_per_pixel'] == 1)
				{
					$COLOR = @unpack("n",$VIDE.substr($IMG,floor($P),1));
					if (($P*8)%8 == 0) $COLOR[1] = $COLOR[1] >>7;
					elseif (($P*8)%8 == 1) $COLOR[1] = ($COLOR[1] & 0x40)>>6;
					elseif (($P*8)%8 == 2) $COLOR[1] = ($COLOR[1] & 0x20)>>5;
					elseif (($P*8)%8 == 3) $COLOR[1] = ($COLOR[1] & 0x10)>>4;
					elseif (($P*8)%8 == 4) $COLOR[1] = ($COLOR[1] & 0x8)>>3;
					elseif (($P*8)%8 == 5) $COLOR[1] = ($COLOR[1] & 0x4)>>2;
					elseif (($P*8)%8 == 6) $COLOR[1] = ($COLOR[1] & 0x2)>>1;
					elseif (($P*8)%8 == 7) $COLOR[1] = ($COLOR[1] & 0x1);
					$COLOR[1] = $PALETTE[$COLOR[1]+1];
				}
				else
					return FALSE;
				
				imagesetpixel($res,$X,$Y,$COLOR[1]);
				$X++;
				$P += $BMP['bytes_per_pixel'];
			}
			$Y--;
			$P+=$BMP['decal'];
		}

		//Fermeture du fichier
		fclose($f1);

		return $res;
	}
?>