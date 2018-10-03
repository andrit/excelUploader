<?php
//namespace Base;
date_default_timezone_set('America/New_York');
/**
* create the new base class.
* this will hold our contructor function and utility functions
*
* @since 1.0.0
*
* @return object A single instance of this class.
*/
//die('base hi');
class BaseClass {


    private static $env;
    private $TEST_DATA;
    private $TEST_LIBRARY;
    private $PROJECT_LOG_FILE;
    private $PROJECT_LOG_FLAG;
    private $page_auth_root;
    
    public static function getEnv(){
        return (getenv("TestServer") == "YES" ? 'dev' : 'prod');
    }
    
	public function __construct($projectLogFlag, $projectLogFile) {
		//die('in bc');
	    $this->env = self::getEnv();
		//die($projectLogFlag);
	    //validate log flag
	    if ($projectLogFlag===true || $projectLogFlag===false){
	        $this->PROJECT_LOG_FLAG=$projectLogFlag;	        
	    }else{
	        die('Log flag must be true/false');
	    }
     
     //validate log file
	    if ($this->env == 'prod') {
       $useLogPath='/pcr/temporary/logs/prod/' . $projectLogFile . '/php_debug.txt';
     }else{
       $useLogPath='/pcr/temporary/logs/test/' . $projectLogFile . '/php_debug.txt';
     }

     if (strlen($projectLogFile)<=0){
       die('Log file is invalid');
     }else if (file_exists($useLogPath) && is_file($useLogPath)){
	      $this->PROJECT_LOG_FILE=$useLogPath;
	    }else if (is_dir(dirname($useLogPath))){
	      $this->PROJECT_LOG_FILE=$useLogPath;
	    }else{
	      die('Log file is invalid');
	    }

     if ($this->env == 'prod') {
		    error_reporting ( E_ERROR );
		    ini_set ( 'display_errors', 'Off' );
		    
      $this->TEST_DATA = false;
      $this->TEST_LIBRARY="";

      if(!defined('GLB_LOG_FLAG')) define( "GLB_LOG_FLAG", false );
      if(!defined('GLB_LOG_FILE')) define ( "GLB_LOG_FILE", "/pcr/temporary/logs/prod/globalLog/php_debug.txt" );
			
      if(!defined('PCR_ROOT_REST_LIVE')) define( "PCR_ROOT_REST_LIVE", "https://10.2.1.3:2182/pcrcgi/intranet/" ); //pcrcgipbi
      if(!defined('PCR_ROOT_SOAP_LIVE')) define( "PCR_ROOT_SOAP_LIVE", "https://10.2.1.3:10021/web/services/" );
      
      $this->page_auth_root='https://apps.pcrichard.com/auth/';
     } else {
		    error_reporting ( E_ERROR );
		    ini_set ( 'display_errors', 'On' );
		    
      $this->TEST_DATA = true;
      $this->TEST_LIBRARY="pgmwebcgi";

      if(!defined('GLB_LOG_FLAG')) define( "GLB_LOG_FLAG", true );
      if(!defined('GLB_LOG_FILE')) define ( "GLB_LOG_FILE", "/pcr/temporary/logs/test/globalLog/php_debug.txt" );
			
      if(!defined('PCR_ROOT_REST_TEST')) define( "PCR_ROOT_REST_TEST", "https://10.2.1.3:1082/pcrcgi/intranet/" ); //pcrcgidev
      if(!defined('PCR_ROOT_SOAP_TEST')) define( "PCR_ROOT_SOAP_TEST", "https://10.2.1.3:10010/web/services/" );
      $this->page_auth_root='https://apps.pcrichard.com:8082/auth/';
     }

     if(!defined('REQUIRE_NEW_PASSWORD_MODE')) define("REQUIRE_NEW_PASSWORD_MODE", "1");
     if(!defined('CHANGE_PASSWORD_MODE')) define("CHANGE_PASSWORD_MODE", "2");
	}

	/***************************************************/
	public function doPlainHeader($title, $needjQuery="true") {
		header ( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
		header ( "Cache-Control: no-cache" );
		header ( "Pragma: no-cache" );
		header ( "Content-Type: text/html" );

		$output = '<html><head><title>'.$title.'</title>';
		if($needjQuery === true){
			$output .= '<script type="text/javascript" src="/js/jquery-1.5.1.min.js"></script>';
		}
		$output .= '</head><body>';

		echo $output;
	}

	/***************************************************/
	public function doPlainFooter() {
		echo '</body></html>';
	}

	/***************************************************/
	public function doXMLHeader() {
		header ( "Content-Type: text/xml" );
		echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
	}

	/***************************************************/
    public function loadPCRRESTData($method = "POST", $url, $parms, $timeout=30, $cookie="") {
    	$root = PCR_ROOT_REST_LIVE;

    	$postingXML=false;
    	if ($this->TEST_DATA === true) {
    		$root = PCR_ROOT_REST_TEST . $this->TEST_LIBRARY . '/';
    	}
    
    	$contentData = "";
    	foreach ( $parms as $name => $value ) {
    		if (gettype ( $value ) == "array") {
    		    if ($this->endsWith($name, "[]")){
    		        $usename=substr($name, 0, strlen($name)-2);
    		    }
    			foreach ( $value as $name2 => $value2 ) {
    				if ($contentData != "") {
    					$contentData .= '&';
    				}
    
    				$contentData .= urlencode ((isset($usename))?$usename:$name2 ) . '=' . urlencode ( $value2 );
    			}
    		} else if ($name=='*XML') {
    		    if ($contentData != "") {
    		        $contentData .= '&';
    		    }
    		    
    			/*
    			 * similar to a SOAP web service where XML data is posted to the server and XML data is returned, but no envelope is required
    			 * i.e. /serviceplan/classes/Solicitations.class.php where the offer code is looked up <Code>123-456</Code> instead of 
    			 * traditional & separated parms.
    			 */
    		    $postingXML=true;
    			$contentData  = $value;
    		} else {
    		    if ($contentData != "") {
    		        $contentData .= '&';
    		    }
    		    
    			$contentData .= urlencode ( $name ) . '=' . urlencode ( $value );
    		}
    	}
    
    	if ($postingXML) {
    		$opts = array (
    				'http' => array (
    						'method' => $method,
    						'header' => "Accept-language: en\r\n" . "Content-Type: text/xml\r\n" . "Content-Length: " . strlen ( $contentData ) . "\r\n",
    						'content' => $contentData
    				)
    		);
    	}else if (strtoupper($method)=='POST'){
    		$opts = array (
    				'http' => array (
    						'method' => $method,
    						'header' => "Accept-language: en\r\n" . "Content-Type: application/x-www-form-urlencoded\r\n" . "Content-Length: " . strlen ( $contentData ) . "\r\n",
    						'content' => $contentData
    				)
    		);
    	}else{
    		$opts = array (
    				'http' => array (
    						'method' => $method
    				)
    		);
    		$url=$url.'?'.$contentData;
    		$contentData='';  //remove data from contentData because it's been prepended to the url variable
    	}
    
    	//cookie logic
    	if ($cookie!=""){ 
    	    $opts["http"]["header"].="Cookie: $cookie" . "\r\n";
    	}
    
    	//only needed on local machine for testing
    	if ($GLOBALS ["SSL_IGNORE_PEER"] === true) {
        	$opts["ssl"]=
    	       array(
    	           "verify_peer"=>false,
    	           "verify_peer_name"=>false
    	       );
    	}
    	
    	$context = stream_context_create ( $opts );
    	$get_path='';
    	if (stripos($url,'http')===false){
    	 //only preprend the root if http (or https) wasn't provided in the URL
    	 $get_path=$root . $url;
     	}else{
     	 $get_path=$url;
     	}

     	if (isset($contentData) && $contentData!=""){
     	    $get_path_spacer='?';
     	}else{
     	    $get_path_spacer='';
     	}
     	
    	try {
    	 if ($timeout<=0){
    	     $timeout=30;
    	 }
    	 ini_set('default_socket_timeout', $timeout);
     	 $file = file_get_contents ( $get_path, false, $context );
    	} catch ( Exception $e ) {	
    	    $this->pcrLclLogger("loadPCRRESTData METHOD / URL / Result", $method . "/" . $get_path . $get_path_spacer . $contentData . "\n" . $e->getMessage());    	    
    	    return;
    	}
    
    	$this->pcrLclLogger("loadPCRRESTData METHOD / URL / Result", $method . "/" . $get_path . $get_path_spacer . $contentData . "\n" . $file);
    
    	return $file;
    }

	/***************************************************/
	public function loadPCRSOAPData($method = "POST", $url, $SOAPData, $SOAPAction, $timeout=10) {
		$root = PCR_ROOT_SOAP_LIVE;
		if ($this->TEST_DATA === true) {
			$root = PCR_ROOT_SOAP_TEST;
		}

		$opts = array (
				'http' => array (
						'method' => $method,
						'header' => "Accept-language: en\r\n" . "Content-Type: text/xml\r\n" . "Content-Length: " . strlen ( $SOAPData ) . "\r\n" . "SOAPAction: \"" . $SOAPAction . "\"",
						'content' => $SOAPData
				)
		);

		$context = stream_context_create ( $opts );

		try {
		 ini_set('default_socket_timeout', $timeout);
	 	 $file = file_get_contents ( $root . $url, false, $context );
		} catch ( Exception $e ) {}

		return $file;
	}

	 /***************************************************/
	 function loadPCRRESTDataJSON($method = "POST", $url, $parms, $timeout=30, $cookie="", $returnArray=false) {
		$jsonstring=$this->loadPCRRESTData($method, $url, $parms, $timeout, $cookie);
		$jsonstring=utf8_encode($jsonstring);
		$jsonObj=json_decode($jsonstring, $returnArray);
		$lastJSONError=json_last_error();

		if ($lastJSONError==JSON_ERROR_NONE){
			return $jsonObj;
		}else{
			return $lastJSONError;
		}
	 }
 
	/***************************************************/
	public function xml2array($contents = "", $get_attributes = 1, $priority = 'tag') {
		if (! function_exists ( 'xml_parser_create' )) {
			return array ();
		}
		$parser = xml_parser_create ( '' );

		xml_parser_set_option ( $parser, XML_OPTION_TARGET_ENCODING, "UTF-8" );
		xml_parser_set_option ( $parser, XML_OPTION_CASE_FOLDING, 0 );
		xml_parser_set_option ( $parser, XML_OPTION_SKIP_WHITE, 1 );
		xml_parse_into_struct ( $parser, trim ( $contents ), $xml_values );
		xml_parser_free ( $parser );
		if (! $xml_values)
			return;
		$xml_array = array ();
		$parents = array ();
		$opened_tags = array ();
		$arr = array ();
		$current = & $xml_array;
		$repeated_tag_index = array ();
		foreach ( $xml_values as $data ) {
			unset ( $attributes, $value );
			extract ( $data );
			$result = array ();
			$attributes_data = array ();
			if (isset ( $value )) {
				if ($priority == 'tag')
					$result = $value;
				else
					$result ['value'] = $value;
			}
			if (isset ( $attributes ) and $get_attributes) {
				foreach ( $attributes as $attr => $val ) {
					if ($priority == 'tag')
						$attributes_data [$attr] = $val;
					else
						$result ['attr'] [$attr] = $val; // Set all the attributes in a
						                               // array called 'attr'
				}
			}
			if ($type == "open") {
				$parent [$level - 1] = & $current;
				if (! is_array ( $current ) or (! in_array ( $tag, array_keys ( $current ) ))) {
					$current [$tag] = $result;
					if ($attributes_data)
						$current [$tag . '_attr'] = $attributes_data;
					$repeated_tag_index [$tag . '_' . $level] = 1;
					$current = & $current [$tag];
				} else {
					if (isset ( $current [$tag] [0] )) {
						$current [$tag] [$repeated_tag_index [$tag . '_' . $level]] = $result;
						$repeated_tag_index [$tag . '_' . $level] ++;
					} else {
						$current [$tag] = array (
								$current [$tag],
								$result
						);
						$repeated_tag_index [$tag . '_' . $level] = 2;
						if (isset ( $current [$tag . '_attr'] )) {
							$current [$tag] ['0_attr'] = $current [$tag . '_attr'];
							unset ( $current [$tag . '_attr'] );
						}
					}
					$last_item_index = $repeated_tag_index [$tag . '_' . $level] - 1;
					$current = & $current [$tag] [$last_item_index];
				}
			} elseif ($type == "complete") {
				if (! isset ( $current [$tag] )) {
					$current [$tag] = $result;
					$repeated_tag_index [$tag . '_' . $level] = 1;
					if ($priority == 'tag' and $attributes_data)
						$current [$tag . '_attr'] = $attributes_data;
				} else {
					if (isset ( $current [$tag] [0] ) and is_array ( $current [$tag] )) {
						$current [$tag] [$repeated_tag_index [$tag . '_' . $level]] = $result;
						if ($priority == 'tag' and $get_attributes and $attributes_data) {
							$current [$tag] [$repeated_tag_index [$tag . '_' . $level] . '_attr'] = $attributes_data;
						}
						$repeated_tag_index [$tag . '_' . $level] ++;
					} else {
						$current [$tag] = array (
								$current [$tag],
								$result
						);
						$repeated_tag_index [$tag . '_' . $level] = 1;
						if ($priority == 'tag' and $get_attributes) {
							if (isset ( $current [$tag . '_attr'] )) {
								$current [$tag] ['0_attr'] = $current [$tag . '_attr'];
								unset ( $current [$tag . '_attr'] );
							}
							if ($attributes_data) {
								$current [$tag] [$repeated_tag_index [$tag . '_' . $level] . '_attr'] = $attributes_data;
							}
						}
						$repeated_tag_index [$tag . '_' . $level] ++; // 0 and 1 index is
						                                            // already taken
					}
				}
			} elseif ($type == 'close') {
				$current = & $parent [$level - 1];
			}
		}
		return ($xml_array);
	}

	/***************************************************/
	public function xmlElement($element) {
		// xml2array will treat an xml tag with no value as a
		// zero length array, so this translates it to an
		// empty string
		if (gettype ( $element ) == "array" && count ( $element ) == 0) {
			return "";
		}
		return $element;
	}

	/***************************************************/
	private function pcrLclLogger($lbl, $val) {
		$this->pcrGlbLogger ( GLB_LOG_FLAG, GLB_LOG_FILE, $lbl, $val );
	}

	/***************************************************/
	private function pcrGlbLogger($log_flag, $log_file, $lbl, $val) {
		if (! $log_flag) {
			return;
		}

		if (trim ( $log_file ) == "") {
			return;
		}

		$fh = fopen ( $log_file, 'a' );
		if ($fh === false) {
			$fh = fopen ( $log_file, 'w' );
		}

		if ($fh === false) {
			return;
		}

		if (gettype ( $val ) == "object") {
			$json  = json_encode($val);
			$val = json_decode($json, true);
		}

		// create log string
		if (gettype ( $val ) == "array") {
			$outputData = print_r($val, true);
		} else {
			$outputData = $val;
		}

		if (strlen ( $outputData ) > 0) {
			$outputData .= "\r\n";
		}

		// mask the credit card with asterisks
		$pattern = '/(Card_Number=)([0-9A-Za-z]+)/i';
		$replacement = '$1****************';
		$outputData = preg_replace ( $pattern, $replacement, $outputData );

		// mask the credit card with asterisks
		$pattern = '/(\[Card_Number\])([0-9A-Za-z]+)/i';
		$replacement = '$1****************';
		$outputData = preg_replace ( $pattern, $replacement, $outputData );

		// mask access number
		$pattern = '/(accessNumber=)([\~\!\@\#\$\%\^\*\(\)\_\+\-\=\[\]\{\}\;\:\<\>\?\,\.\\/0-9A-Za-z ]+)/i';
		$replacement = '$1**********';
		$outputData = preg_replace ( $pattern, $replacement, $outputData );

		// mask password (middle)
		$pattern = '/(password=)([\~\!\@\#\$\%\^\*\(\)\_\+\-\=\[\]\{\}\;\:\<\>\?\,\.\\/0-9A-Za-z ]+)/i';
		$replacement = '$1**********';
		$outputData = preg_replace ( $pattern, $replacement, $outputData );

		// mask token
		$pattern = '/(token=)([\~\!\@\#\$\%\^\*\(\)\_\+\-\=\[\]\{\}\;\:\<\>\?\,\.\\/0-9A-Za-z ]+)/i';
		$replacement = '$1**********';
		$outputData = preg_replace ( $pattern, $replacement, $outputData );
		
		// mask userLoginKey
		$pattern = '/(userLoginKey=)([\~\!\@\#\$\%\^\*\(\)\_\+\-\=\[\]\{\}\;\:\<\>\?\,\.\\/0-9A-Za-z ]+)/i';
		$replacement = '$1**********';
		$outputData = preg_replace ( $pattern, $replacement, $outputData );
		
		$logData = "Date: " . date ( "m/d/Y h:i a") . "\r\n" . "Script: " . $_SERVER ["REQUEST_URI"] . "\r\n" . "SessionId: " . session_id () . "\r\n" . "Log Desc: " . $lbl . "\r\n\r\n" . $outputData . "===========================================================================================\r\n";

		fwrite ( $fh, $logData );
		fclose ( $fh );
	}

	/***************************************************/
	public function pcrLogger($lbl, $val){
	    $this->pcrGlbLogger($this->PROJECT_LOG_FLAG, $this->PROJECT_LOG_FILE, $lbl, $val);
	}
	
	/***************************************************/
	public function grab_dump($var) {
		ob_start ();
		var_dump ( $var );
		return ob_get_clean ();
	}

	/***************************************************/
	public function formatErrorMessage($message) {
		global $errorMessageOnly;

		if (isset ( $errorMessageOnly ) && $errorMessageOnly === true) {
			return $message;
		} else {
			return '<div class="fieldErrorMessage">' . $message . '</div>';
		}
	}

	/***************************************************/
	public function validateRequiredField($label, $value) {
		if ($value == "") {
			return $this->formatErrorMessage ( $label . ' is required.' );
		} else {
			return "";
		}
	}

	/***************************************************/
	public function validateNumeric($label, $value, $minValue, $maxValue) {
		if (! is_numeric ( $value )) {
			return $this->formatErrorMessage ( $label . ' is invalid.' );
		}

		return "";
	}

	/***************************************************/
	public function validateLength($label, $value, $minLength) {
		$valueLen = strlen ( trim ( $value ) );
		if ($valueLen < $minLength) {
			return $this->formatErrorMessage ( $label . ' is invalid.' );
		}

		return "";
	}

	/***************************************************/
	public function lastXChars($chkString, $xChars) {
		$lastX = "";

		try {
			$length = strlen ( $chkString );
			$characters = $xChars;
			$start = $length - $characters;
			$lastX = substr ( $chkString, $start, $characters );
		} catch ( Exception $e ) {
		}

		return $lastX;
	}

	/***************************************************/
	public function addressBlock($addressNumber, $address1, $address2, $city, $state, $zip, $zip2) {
		$return = "";

		// address
		if ($addressNumber != "") {
			$return = $return . trim ( $addressNumber ) . " ";
		}

		if ($address1 != "") {
			$return = $return . trim ( $address1 ) . " ";
		}

		if ($address2 != "") {
			$return = $return . trim ( $address2 ) . " ";
		}

		$return = $return . "<br>";

		// city/state/zip
		if ($city != "") {
			$return = $return . trim( $city );
			if ($state == "") {
				$return = $return . ' ';
			} else {
				$return = $return . ', ';
			}
		}

		if ($state != "") {
			$return = $return . trim ( $state ) . ' ';
		}

		if ($zip > 0) {
			$return = $return . str_pad($zip, 5, "0", STR_PAD_LEFT);
			if ($zip2 > 0) {
				$return = $return . '-' . str_pad($zip2, 4, "0", STR_PAD_LEFT);
			}
		}
		return $return;
	}

	/***************************************************/
	public function formatAddressAsString($addressNbr, $addressLine1, $addressLine2, $city, $state, $zipCode, $zipCodeExt){
		$address=$addressNbr . " " . htmlspecialchars($addressLine1) . " ";
		if ($addressLine2!=""){
			$address.=htmlspecialchars($addressLine2) . " ";
		}

		$address.=htmlspecialchars($city) . ", " . $state . " " . str_pad($zipCode, 5, "0", STR_PAD_LEFT);

		if ($zipCodeExt!=""){
			$address.="-" . str_pad($zipCodeExt, 4, "0", STR_PAD_LEFT);
		}

		return $address;
	}

	/***************************************************/
	public function formatPhone($phone, $basic=false){
		//basic=true tells this function to return a 10-digit phone as xxx-xxx-xxxx
		//basic=false tells this function to return a 10-digit phone as (xxx) xxx-xxxx
		$phone = preg_replace("/[^0-9]/", "", $phone);

		if(strlen($phone) == 7){
			return preg_replace("/([0-9]{3})([0-9]{4})/", "$1-$2", $phone);
		}elseif(strlen($phone) == 10){
			if ($basic){
				return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "$1-$2-$3", $phone);
			}else{
				return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "($1) $2-$3", $phone);
			}
		}else{
			return $phone;
		}
	}

	/***************************************************/
	public function formatDate($date){
		$formattedDate=substr($date, 0, 2) . '/' .
				       substr($date, 2, 2) . '/' .
					   substr($date, 4, 4);
		return $formattedDate;
	}

	/***************************************************/
	public function validateLogin($system, $user, $password, $newPasswordMode="0", $newPassword="", $confirmNewPassword=""){
		$result = $this->loadPCRRESTData('POST', 'CGGNLG', array(
															'application'=>$system,
															'username'=>$user,
															'password'=>$password,
															'newPasswordMode'=>$newPasswordMode,
															'newPassword'=>$newPassword,
															'confirmNewPassword'=>$confirmNewPassword
															)
		);

		$xml_as_array = $this->xml2array($result);
		$returnedObject = $this->__xmlAsArrayToObject($this->xmlelement($xml_as_array["Root"]));

		return $returnedObject;
	}

	/*
	 * Takes the xml2array object and makes it a more usable object
	 * using rules that the CGGNLG program use to create the xml
	 * structure.  Example is that any section ending with _BLOCK
	 * means that there may be repeated data inside and that should
	 * be treated like an array always.
	 */
	public function __xmlAsArrayToObject($inObject){
		$returnObject=null;

		foreach ($inObject as $key => $value) {
			if ($this->endsWith($key, "_BLOCK")){
				$this->__putXMLToArray($returnObject, $key, $value, true); //pass true for repeating data
			}else{
				$this->__putXMLToArray($returnObject, $key, $value);
			}
		}

		return $returnObject;
	}

	/***************************************************/
	/*
	 * Use in conjunction with __xmlAsArrayToObject
	 */
	public function __putXMLToArray(&$userObject, $name, $element, $forceArray=false){
		if (gettype($element)=="array"){
			foreach ($element as $key => $value) {
				if (gettype($value)=="array"){
					foreach ($value as $key2 => $value2) {
						$userObject[$key][$value2]=$value2;
					}
				}else{
					if ($forceArray){
						$userObject[$key][$value]=$value;
					}else{
						$userObject[$name][$value]=$value;
					}
				}
			}
		}else{
			if ($forceArray){
				$userObject[$name][$element]=$element;
			}else{
				$userObject[$name]=$element;
			}
		}
	}

	/***************************************************/
	/*
	 * Example: service call problem/repair description
	 *  - service call returns comment with crlf and this will translate to html line break
	 *    with proper html encoding
	 */
	public function formatHTMLComment($comment){
		return str_replace(array("\r\n", "\n", "\r"), "<br>", htmlentities($comment));
	}

	/***************************************************/
	public function endsWith($haystack, $needle){
		$length = strlen($needle);
		if ($length == 0) {
			return true;
		}

		return (substr($haystack, -$length) === $needle);
	}

	/***************************************************/
	public function getServerNameWithPort($type='HTTP'){
		if ($type=="HTTPS"){
			$serverPort=$_SERVER["HTTPS_PORT"];
			if ($serverPort=="443"){
				$serverPort="";
			}
		}else{
			$serverPort=$_SERVER["HTTP_PORT"];
			if ($serverPort=="80"){
				$serverPort="";
			}
		}

		$serverName=$_SERVER["PCR_SERVER_NAME"];
		if ($serverPort==""){
			$serverNameWithPort=$serverName;
		}else{
			$serverNameWithPort=$serverName . ':' . $serverPort;
		}

		return $serverNameWithPort;
	}

	/***************************************************/
	public function getWebServiceUsername(){
	 if ($this->TEST_DATA===true){
	  return "DaveIsTesting";
	 }else{
	 	 return "pcrService";
	 }
	}

	/***************************************************/
	public function getWebServicePassword(){
	 if ($this->TEST_DATA===true){
		return "0urPassw0rd1sTh1s";
	 }else{
		return "W3bS3rv1c3sRul3";
	 }
	}

	/***************************************************/
	public function formatDollars($value){
		return "$ " . number_format($value, 2);
	}

	/***************************************************
	 * http://en.wikipedia.org/wiki/Private_network
	 * RFC1918	name	IP address range
	   24-bit	block	10.0.0.0    - 10.255.255.255
	   20-bit	block	172.16.0.0  - 172.31.255.255
	   16-bit	block	192.168.0.0 - 192.168.255.255
	 */
	public function isInsidePCRNetwork($testAddress = false) {
		if ($testAddress === false) {
			$remoteAddress = $_SERVER ["REMOTE_ADDR"];
		} else {
			$remoteAddress = $testAddress;
		}

		if ($remoteAddress != null) {
			if ($this->startsWith($remoteAddress, "10." )) {
				// 24-bit
				return true;
			} else {
				$ipelem = preg_split ( '/\./', $remoteAddress );
				$ct_ipelem = count ( $ipelem );
				if ($ct_ipelem != 4) {
					return false;
				}

				//ensure all numeric, conver to number
				for ($i = 0; $i < $ct_ipelem; $i++) {
					if (!is_numeric($ipelem[$i])){
						return false;
					}
					$ipelem [$i]=intval($ipelem [$i]);
				}

				// 20 bit
				if ($ipelem [0] == 172) {
					if ($ipelem [1] >= 16 && $ipelem [1] <= 31) {
						return true;
					}
					return false;
				}

				// 16 bit
				if ($ipelem [0] == 192 && $ipelem [1] == 168) {
					return true;
				}

				return false;
			}
		}

		return false;
	}

	/***************************************************/
	public function startsWith($haystack, $needle) {
		return strncmp($haystack, $needle, strlen($needle))==0 ? true :false;
	}

	/***************************************************/
	function isEven($number) {
		if ($number % 2 == 0) {
			return true;
		}else{
			return false;
		}
	}

	/***************************************************/
	public function isOdd($number) {
		if (isEven($number)){
			return false;
		}else{
			return true;
		}
	}

	/******************************************************/
	public function actual_time($timestamp, $format='Y-m-d h:i:s', $timezone='America/New_York'){
		$theTime = time();
		$tz = new DateTimeZone($timezone);
		$transition = $tz->getTransitions($theTime, $theTime);
		$offset = $transition[0]['offset'];
		//$offset=$offset/60/60; //this will give hours

		$timestamp = $timestamp + $offset;
		return gmdate($format,$timestamp);
	}

	/******************************************************/
	public function array_to_xml($student_info, &$xml_element_info) {
		foreach($student_info as $key => $value) {
			if(is_array($value)) {
				if(!is_numeric($key)){
					$subnode = $xml_element_info->addChild("$key");
					$this->array_to_xml($value, $subnode);
				} else{
					$this->array_to_xml($value, $xml_element_info);
				}
			} else {
				$key = $this->xml_entities($key);
				$value = $this->xml_entities($value);

				$xml_element_info->addChild("$key","$value");
			}
		}
	}

	/******************************************************/
	public function xml_entities($string) {
		return strtr(
				$string,
				array(
						"<" => "&lt;",
						">" => "&gt;",
						'"' => "&quot;",
						"'" => "&apos;",
						"&" => "&amp;",
				)
		);
	}

 /******************************************************/
	public function PCRTokenExchange($id){
     $this->pcrLclLogger("BaseClass->PCRTokenExchange() ", 'id is: ' . $id);
	    $authTokenExchangeURL=$this->get_page_auth_root() . "get.php";
     
	    $response=$this->loadPCRRESTData("POST", $authTokenExchangeURL, array("id"=>$id));
	    if ($response!=""){
	        $jsonstring=utf8_decode($response);
	        $data = json_decode($jsonstring);
	        return $data;
	    }
	
	    return false;
	}
 
 /******************************************************/
 public function get_page_auth_root() {
	   return $this->page_auth_root;
	}
	
 /******************************************************/
	public function pcr_session_start($session_path='', $maxlifetime=43200, $cookie_path='/') {
		if(empty($session_path)) {
			 $this->pcrLclLogger("BaseClass->pcr_session_start() ", 'The session save path in the first parm is missing');
			 return 'The session save path in the first parm is missing';
		}

		ini_set('session.gc_probability', 1);
		ini_set('session.gc_divisor', 1);
		ini_set('session.gc_maxlifetime', $maxlifetime);
		if(!empty($session_path) && is_dir($session_path)){
  		  ini_set('session.save_path', $session_path);
		  session_save_path($session_path);
        }

	 	if(!empty($cookie_path)) {
		 	session_set_cookie_params(0, $cookie_path);
		}
  
	 	session_start();
		
		$this->pcr_session_check();

		return;
	}

 /******************************************************/
	public function pcr_session_check() {
		$this->pcrLclLogger("pcr_session_check", "");
	   if(!empty($_SESSION)) {
	   	 $this->pcrLclLogger("pcr_session_check", "not empty");
	     $last_activity = (isset($_SESSION['session_last_activity']) ? $_SESSION['session_last_activity'] : time());
	     $now = time();
	     $maxlifetime = ini_get('session.gc_maxlifetime');

	     if(($now - $last_activity) > $maxlifetime) {
	       $this->pcr_session_end();
	     }else{
	       $this->pcrLclLogger("pcr_session_check", "updating last activity");
	       $_SESSION['session_last_activity'] = time();
	       session_write_close();
	       session_start();
	     }
	   }
	}

 /******************************************************/
 public function pcr_session_end($redirect_url='') {
   $this->pcrLclLogger("pcr_session_end", redirect_url);
   foreach ($_SESSION as $key=>$value){
     unset($_SESSION[$key]);
   }
   
   session_regenerate_id();

   if(!empty($redirect_url)) {
     $redirect_url = filter_var($redirect_url, FILTER_SANITIZE_URL);
     header('Location: '. $redirect_url);
   }else{
     header('Location: /php/'.$this->application);
   }
    
   exit;
 }

 /***************************************************/
/*
 * Range should look like this (for 1 IP Address range - additional can be added but must include min/max):
 *    $ipRange ["198.241.168"] ["min"] = 1;
 *    $ipRange ["198.241.168"] ["max"] = 254;
 */
 function checkIPAgainstRange($range, $ipToCheck) {
  $arCheckParts = preg_split ( "/\./", $ipToCheck );
  if (count ( $arCheckParts ) != 4) {
   return false;
  }

  $first3Parts=implode(".",array_slice($arCheckParts,0,3));
  if (!array_key_exists($first3Parts, $range)){
   return false;
  }

  $lastOctet=$arCheckParts[3];
  if (!ctype_digit($lastOctet)){
   return false;
  }

  $lastOctet=intval($lastOctet);

  foreach ($range as $key => $value) {
   if (isset($value["min"]) && $lastOctet>=$value["min"] && isset($value["max"]) && $lastOctet<=$value["max"]){
    return true;
   }else{
    //return false;
   }
  }

  return false;
 }
 
 /***************************************************/
/*
 * Output an array as XML (full head/body)
 */
 function outputArrayAsXML($array, $wrapper="<Response></Response>"){
   $fileArrayInfo=new SimpleXMLElement($wrapper);
   $this->array_to_xml($array,$fileArrayInfo);
   echo $fileArrayInfo->asXML();
 }
}