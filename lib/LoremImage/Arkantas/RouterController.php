<?php
namespace LoremImage\Arkantas;
/*
	ARKANTAS-MVC
	###########################################################
	Class: RouterController
	
	Releases:
		- 01-04-2011         : UPDATE ENGINE
		- 28-04-2011         : UPDATE ENGINE
		- 20-08-2011		 : UPDATE ENGINE
		- 21-08-2011		 : UPDATE ENGINE
		- 30-06-2012		 : UPDATE ENGINE
		- 16-07-2012		 : load(); ~~ error404();
		- 10-08-2012		 : load(); rt index;
		- 11-08-2012		 : match();
		- 12-08-2012		 : load(), error404();
		- 03-05-2013		 : load() - _get;
		- 14-04-2014		 : load() - _get parameters in all routes (rt) and end of typing route;
	
	Authors: Felipe Gallo <desenvolvimento@coresphere.com.br>
*/
class RouterController{
	
	private $path;
	private $uri;
	private $allowGet   = true;
	private $controller = array();
	private $error404   = true;
	private $url; //after load();
	
	public function __construct(){
		$this->setPath();
		$this->addDefault();
	}
	
	public function getUrl(){
		return $this->url;
	}

	public function setUrl($url = ""){
		if($url != ""){
			if(!empty($_GET['rt::value'])){
				if($_GET['rt::value'] != $url)
					$_GET['rt::value'] = $url;
			}else{
				$_GET['rt::value'] = $url;
			}
		}
		$this->url = $url;
	}
	
	public function getPath(){
		return $this->path;
	}
	
	public function setPath($path = "/"){
		if(substr($path, 0, 1) != '/'){ $path = '/' . $path; }
		if(substr($path, -1) != '/'){ $path .= '/'; }
		$this->path = $path;
	}
	
	public function getAllowGet(){
		return $this->allowGet;
	}
	
	public function setAllowGet($allowGet){
		if(is_bool($allowGet) === true)
			$this->allowGet = $allowGet;
	}
	
	public function getError404(){
		return $this->error404;
	}
	
	public function setError404($error404){
		$this->error404 = $error404;
	}
	
	public function getUri(){
		return $this->uri;
	}
	
	public function setUri($uri = ""){
		if($_SERVER['REQUEST_URI'] == $this->getPath()){
			$controller = $this->getController();
			foreach($controller as $key => $value){
				if($value["url"] == ""){
					$this->uri = "/";
					break;
				}
			}
		}else{
			if(substr($_SERVER['REQUEST_URI'], -1) != "/"){
				$_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'] . "/";
			}
			
			$this->uri = "/".substr($_SERVER['REQUEST_URI'], strlen($this->getPath()));
		}
	}
	
	public function getController(){
		return $this->controller;
	}
	
	
	#
	public function addDefault($controller = "index", $gets = array(array(0 => array()))){
	
		foreach($this->controller as $key => $value){
			if($value['url'] == ""){
				unset($this->controller[$key]);
			}
		}
		
		array_push($this->controller, array(
			"controller" => $controller,
			"url" => "",
			"get" => $gets
		));
	}

	public function add(array $controller){
		array_push($this->controller, array(
			"controller" => $controller["controller"],
			"url" => $controller["url"],
			"get" => $controller["get"]
		));
	}
	
	public function getDefault(){
		$default = "";
		foreach($this->controller as $key => $value){
			if($value['url'] == ""){
				$default = $value;
				break;
			}
		}
		return $default;
	}
	
	public function load(){
		return $this->loader();
	}
	
	private function loader(){

		$this->setUri();
		$controller = $this->getController();
		$arrCan     = array();
		$url		= $this->getDefault();
		$this->setUrl($url['url']);
		
		# filtra canditados por campos na url
		foreach($controller as $key => $value){
			$getController = $value["get"];
			foreach($getController as $keyGet => $valueGet){
				$uri = explode("/", $this->getUri());
				//echo $this->getUri() . " - " . print_r($valueGet) . "<br />";
				if($this->getUri() != "/"){
					if((count($uri) - 2) == count($valueGet)){
						array_push($arrCan, array("controller" => $value["controller"], "url" => $value["url"], "get" => $valueGet));
					}
				}else{
					if(count($valueGet[0]) == 0){
						array_push($arrCan, array("controller" => $value["controller"], "url" => $value["url"], "get" => $valueGet));
					}
				}
			}
			unset($uri, $match);
		}
		
		# filtra canditados com campos na url por tipo de campo
		$arrayBool = array();
 		foreach($arrCan as $key => $value){
		
			$boolCan = array("controller" => $value["controller"], "url" => $value["url"], "get" => $value["get"]);
			$match   = $value["get"];
			$matches = $this->match($value["get"], $this->getUri());
			
			$foundrouter = false;
			for($i = 0 ; $i < count($match) ; $i++){
				
				if(substr(@$match[$i]['name'], 0, 6) == "static"){
				
					$valuematch = @$matches[$match[$i]['name']];
					
					# caso o get padrão esteja habilitado, o valor é omitido para encontrar a rota e recuperado após a completa identificação
					if($this->getAllowGet() == true){
						$valuematch = explode("?", $valuematch, 2);
						$valuematch = $valuematch[0];
					}
				
					if(substr($match[$i]['name'], 6) == $valuematch){
						array_push($boolCan, true);
						//echo "RT: " . $value['controller'] . " - " . $value['url'] . " e verdadeiro _static<br/>";
					}else{
						array_push($boolCan, false);
						//echo "RT: " . $value['controller'] . " - " . $value['url'] . " e falso _static<br/>";
					}
				}
				
				# verifica a var 'rt' (rota) -- check route url
				if(@$match[$i]['name'] == "rt"){
				
					$valuematch = @$matches[$match[$i]['name']];
					
					# caso o get padrão esteja habilitado, o valor é omitido para encontrar a rota e recuperado após a completa identificação
					if($this->getAllowGet() == true){
						$valuematch = explode("?", $valuematch, 2);
						$valuematch = $valuematch[0];
					}
					
				
					if($valuematch == $value['url']){
						//echo print_r($match) . " - " . $this->getUri();
						if($this->validateTypes($match, $this->getUri())){
							$foundrouter = true;
							array_push($boolCan, true);
							//echo "RT: " . $value['controller'] . " - " . $value['url'] . " e verdadeiro<br/>";
						}else{
							//echo "RT1: " . $value['controller'] . " - " . $value['url'] . " e falso<br/>	";
							array_push($boolCan, false);
						}
					}else{
						//echo "RT: " . $value['controller'] . " - " . $value['url'] . " e falso<br/>	";
						array_push($boolCan, false);
					}
					
					unset($valuematch);
				}
				
			}

		
			# busca em default caso não tenha encontrado um candidato
			if(!$foundrouter){
				$controller = $this->getDefault();
				if($controller["controller"] == $value["controller"]){

					//echo print_r($match) . " - " . $this->getUri() . "<br />";
					//echo count($match) . ' + ' . count($matches) . " <br />";
					if(count($match) == (count($matches) / 3)){
						if($this->getUri() == "/"){
							array_push($boolCan, true);
							//echo "RT: " . $value['controller'] . " - " . $value['url'] . " e verdadeiro _default<br/>";
						}else{
							//echo print_r($match) . " - " . $this->getUri() . "<br />";	
							if($this->validateTypes($match, $this->getUri())){
								array_push($boolCan, true);
								//echo "RT: " . $value['controller'] . " - " . $value['url'] . " e verdadeiro _default<br/>";
							}else{
								array_push($boolCan, false);
								//echo "RT: " . $value['controller'] . " - " . $value['url'] . " e falso _default<br/>";
							}
						}
					}else{
						array_push($boolCan, false);
						//echo "RT: " . $value['controller'] . " - " . $value['url'] . " e falso _default<br/>";
					}
				}
			}
			
			array_push($arrayBool, $boolCan);
			unset($matches, $boolCan, $match);
		}


		# verifica os gets candidatos em suas 'notas' e atribui ao único vencedor
		foreach($arrayBool as $key => $value){
			$numericIndice = false;
			foreach($value as $indice => $valor){
				if(is_numeric($indice)){
					$numericIndice = true;
					if($valor == false)
						unset($arrayBool[$key]);
				}
			}
			
			
			if(!$numericIndice && $value['url'] != "") # part : '&& $value['url'] != ""' - is add after test in diferent default routes with null url in (weeby) 30/03/2014 22h35
				unset($arrayBool[$key]);

		}
	
	
		# verifica qtd de candidatos aprovados
		if(count($arrayBool) > 1){

			# verificar quem tem mais bool (true) e chamar quem tiver mais
			$arrayOrder = array();
			foreach($arrayBool as $key => $value){
				$arrayOrder[count($value)] = $value;
			}
			arsort($arrayOrder);

			foreach($arrayOrder as $key => $value){
				if($value["controller"] != $url['controller'] && $value["url"] != ""){
					unset($_GET);
					
					$_GET = $this->match($value["get"], $this->getUri(), $this->getAllowGet());

					$url = $value;
					$this->setUrl($value['url']);
					break;
				}
			}
		}elseif(count($arrayBool) == 1){
			foreach($arrayBool as $key => $value){
				unset($_GET);
				$_GET = $this->match($value["get"], $this->getUri(), $this->getAllowGet());
				
				if($value["controller"] != $url['controller'])
					$url = $value;
				
				if($value['url'] != $this->getUrl())
					$this->setUrl($value['url']);
				
				$url['url'] = $value['url'];
				break;
			}
		}else{
			$url = $this->error404($url);
		}


		$rt = $url;
		$_GET['rt'] = $rt['controller'];
 		/*echo "<pre>";
		echo print_r($_GET);
		echo "</pre>";*/
		return null;
	}
	
	private function error404(array $url){
		if($this->error404 == true){
			return array(
						"controller" => "error404",
						"url" => "",
						"get" => array()
					);
		}
		return $url;
	}
	
	
	private function validateTypes($get, $uri){

		$uri = explode("/", substr(substr($uri, 0, -1), 1));
		if(sizeof($get) != sizeof($uri))
			throw new Exception('error on validate types on router controller.');
		
		
		$validate = true;
		for($i = 0 ; $i < sizeof($get) ; $i++){
			
			# caso o get padrão esteja habilitado, o valor é omitido para encontrar a rota e recuperado após a completa identificação
			if(($i+1) == sizeof($get) && $this->getAllowGet() == true && (@$get[$i]['type'] == "int" || @$get[$i]['type'] == "text")){
				$valuematch = explode("?", $uri[$i], 2);
				$uri[$i] = $valuematch[0];
			}
			
			# validation type: int/text or array(_get)
			switch(@$get[$i]['type']){
				case "int":
					if(!is_numeric($uri[$i]))
						$validate = false;
				break;
				
				case "text":
					if(!is_string($uri[$i]))
						$validate = false;
				break;
				
				case "array":
					if(substr($uri[$i], 0, 1) != "?")
						$validate = false;
				break;
				
				default:
					$validate = false;
				break;
			}
			
		}
		

		return $validate;
	}
	
	private function match($key, $value, $allowGet = false){
	
		$value = explode("/", substr(substr($value, 0, -1), 1));
		$match = array();
		
		if(sizeof($key) != sizeof($value))
			throw new Exception('error on match variables on router controller.');


		for($i = 0, $getcount = 0 ; $i < sizeof($key) ; $i++){
		
			if(@$key[$i]['type'] == "array"){
				if($allowGet === true && substr($value[$i], 0, 1) == "?"){
					$match[$getcount] = $value[$i];
					$match = array_merge($match, $this->getValuesOfArrayGet($value[$i]));
					$getcount++;
				}else{
					$match[$getcount] = $value[$i];
					$match[@$key[$i]['name']] = $value[$i];
					$match[@$key[$i]['name'].'::value'] = $value[$i];
					$getcount++;
				}
			}else{
				if($allowGet === true && ($i+1) === sizeof($key)){
					$valuematch = explode("?", $value[$i], 2);

					#
					$match[$getcount] = $valuematch[0];
					$match[@$key[$i]['name']] = $valuematch[0];
					$match[@$key[$i]['name'].'::value'] = $valuematch[0];
					$getcount++;
					
					if(sizeof($valuematch) > 1){
					#
					//$match = array_merge($match, $this->getValuesOfArrayGet($valuematch[1]));
					$varraymatch = $this->getValuesOfArrayGet($valuematch[1]);
					foreach ($varraymatch as $kmatch => $vmatch){
						if(@substr($kmatch, -7) != '::value'){
							$match[$getcount] = $vmatch;
							$match[$kmatch] = $vmatch;
							$match[$kmatch.'::value'] = $vmatch;
							$getcount++;
						}
					}
					}
					
				}else{
					$match[$getcount] = $value[$i];
					$match[@$key[$i]['name']] = $value[$i];
					$match[@$key[$i]['name'].'::value'] = $value[$i];
					$getcount++;
				}
			}
			
		}

		/*
		echo "<pre>";
		echo print_r($match);
		echo "</pre>";
		exit;
		*/
		return $match;
	}
	
	private function getValuesOfArrayGet($value){
	
		$match = array();
		$value = (substr($value, 0, 1) == "?") ? substr($value, 1) : $value;

		$getmatch = $value;
		$getmatch = explode("&", $getmatch);
		
		foreach($getmatch as $key => $value){
			$value = explode("=", $value);

			if(!empty($value[0])){
				if(sizeof($value) >= 2){
					# concatena os values escedentes
					if(sizeof($value) > 2){
						for($i = 1 ; $i < sizeof($value) ; $i++)
							$value[1] .= $value[$i];
					}
				}
				
				# define get
				$match[$value[0]] = @$value[1];
				$match[$value[0].'::value'] = @$value[1];
				
			}
			unset($value);
		}
	
		return $match;
		
	}
	
}
?>