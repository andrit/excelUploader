<?php

namespace App\Core;

// Global Helper Functions

class Helpers {

    private $config;

    public function __construct()
    {

        $this->config = require(dirname(dirname(dirname(__FILE__))) . "/config.php");

    }

    public function view($path, $data = array(), $extract = true, $data2 = array(), $extract2 = true)
    {
        
        if( $extract ){
            extract($data);
        }
        
        if( $extract2 ){
            extract($data2);
        }
        
        require $this->config["appdir"] . "/views/{$path}.view.php";
        
    }

    public function redirect($path)
    {   
        $path = $this->config["rootdir"] . "/" . $path;
        header("Location: /{$path}");
        exit();
        
    }

   /* public function redirectToHome()
    {
        if(
            (!isset($_SESSION['getbystrinv']) && $_SESSION['getbystrinv'] == '') ||
            (!isset($_SESSION['getbypersonalinfo']) && $_SESSION['getbypersonalinfo'] == '')
            ){
                header("Location: /" . $this->config["rootdir"] . "/");
                exit();
        }
    }*/

    public function loadPartial($name)
    {
        require $this->config["appdir"] . "/views/partials/" . $name . ".php";
    }

    /**
     * Generate a random string of length specified
     * @param integer $length 
     * @return string returns a random 16 char string concatenated with a time + date stamp, demarcated respectively with a T or a D
     */
    public function randomString($length) {
        $time = time();
        $date = date('Ymd');
        $key = '';
        $keys = array_merge(range(0, 9), range('a', 'z'));
    
        for ($i = 0; $i < $length; $i++) {
            $key .= $keys[array_rand($keys)];
        }
        $key .= 'T' . $time;
        $key .= 'D' . $date;
    
        return $key;
    }

     /**
     * Get image mime type
     *
     * @param $file
     * @return mixed
     */
    public function getMimeType($file)
    {

        return (finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file));

    }

    /***************************************************/
    // This function is used to create auto versioning
    // for js and css files that will be changed often
    public function auto_versioning($file)
    {
        if( file_exists($file) ){
            return $file . "?v=" . filemtime($file);
        } else {
            return $file;
        }
    }

    // Restrict access of website by the allowed list of IPs on the config array
    public function restrict_by_ip()
    {

        if($this->config['restrictbyip'] == 'On'){

            $allowedip = false;

            foreach($this->config['allowediplist'] as $ip){

                /*echo 'REMOTE_ADDR: ' . $_SERVER['REMOTE_ADDR'] . ' - ALLOWED IP: ' . $ip . '<br/>';

                echo 'STRPOS RESULT: ' . strpos($_SERVER['REMOTE_ADDR'], $ip) . '<br/>';*/

                if(strpos($_SERVER['REMOTE_ADDR'], $ip) === 0){

                    $allowedip = true;

                    break;

                }

            }

            if( $allowedip === false ){

                header("HTTP/1.0 404 Not Found");
                die();

            }

        }

    }

    public function refinestreetname($street)
    {
        $street = trim(strtolower($street));

        // handle if $street is empty
        if($street == "" || empty($street)){
            return;
        }

        $number = trim(preg_replace('/\D/', '', $street));
        $text = strtolower(trim(preg_replace('/[0-9]+/', '', $street)));

        $name = "";

        if( $number != "" ){
            $numposstart = strpos($street, $number);
            $numposend = strpos($street, $number) + strlen($number);

            // Get direction if $numposstart > 0
            $direction = "";
            if( $numposstart > 0 ){
                $direction = trim(substr($street, 0, $numposstart));
            }
            $matchingdir = array();
            if( $direction != "" ){
                foreach( $this->config["streetdirection"] as $key => $value ){
                    foreach( $value as $val ){
                        $simdir = similar_text($direction, $val, $perc);
                        if($perc == 100){
                            $matchingdir[$simdir] = array(
                                "key"       => $key,
                                "val"       => $val
                            );
                        }
                    }
                }
            }
            $dir = (count($matchingdir) > 0 ? $matchingdir[max(array_keys($matchingdir))]["key"] : "");

            // Get the characters after the number
            $charafternumber = substr($street, $numposend);

            // Check if there is a number supplement like st, rd, th on character after number
            $suparray = array("th", "st", "rd");
            $supplement = "";
            $charafternumbernosupplement = "";
            foreach($suparray as $sp){
                if(strstr($charafternumber, $sp) !== false){
                    $spposstart = strpos($charafternumber, $sp);
                    $spposend = strpos($charafternumber, $sp) + strlen($sp);
                    $spacebefore = false;
                    $spaceafter = false;
                    $charbefore = "";
                    $charafter = "";
                    if($spposstart > 0){
                        $charbefore = substr($charafternumber, 0, $spposstart);
                    }
                    if($charbefore == " "){
                        $spacebefore = true;
                    }
                    $charafter = substr($charafternumber, $spposend, 1);
                    if($charafter == " "){
                        $spaceafter = true;
                    }
                    if( ($spposstart == 0 && $spaceafter) || ($spacebefore && $spaceafter) ){
                        $supplement = substr($charafternumber, 0, $spposend + 1);
                        $charafternumbernosupplement = substr($charafternumber, $spposend + 1);
                    }
                }
            }

            $haystack = ($charafternumbernosupplement != "" ? $charafternumbernosupplement : $charafternumber);
            $haystackarr = explode(" ", $haystack);
            $matchingname = array();
            if( count($haystackarr) > 0 ){
                foreach($haystackarr as $hs){
                    foreach( $this->config["streetname"] as $key => $value ){
                        foreach($value as $val){
                            $sim = similar_text($hs, $val, $perc);
                            if( $perc == 100 ){
                                $matchingname[$sim] = array(
                                    "key"   => $key,
                                    "val"   => $val
                                );
                            } 
                        }
                    }
                }
            }

            $converted = (count($matchingname) > 0 ? $matchingname[max(array_keys($matchingname))]["key"] : "");

            if( $converted != "" ){
                $namecnv = str_replace($matchingname[max(array_keys($matchingname))]["val"], $converted, $haystack);
            }

            $name = $dir . " " . $number . trim($supplement) . " " . $namecnv;

        } else {
            
            $streetarr = explode(" ", $street);

            // Check if the first word is street direction
            $matchingdir = array();
            if( $streetarr[0] !== "" ){
                foreach( $this->config["streetdirection"] as $key => $value ){
                    foreach( $value as $val ){
                        $simdir = similar_text($streetarr[0], $val, $perc);
                        if($perc == 100){
                            $matchingdir[$simdir] = array(
                                "key"       => $key,
                                "val"       => $val
                            );
                        }
                    }
                }
            }
            $dir = (count($matchingdir) > 0 ? $matchingdir[max(array_keys($matchingdir))]["key"] : "");

            // Get name if $streetarr count > 0
            $matchingname = array();
            if(count($streetarr) > 0){
                foreach($streetarr as $st){
                    foreach( $this->config["streetname"] as $key => $value ){
                        foreach($value as $val){
                            $sim = similar_text($st, $val, $perc);
                            if( $perc == 100 ){
                                $matchingname[$sim] = array(
                                    "key"   => $key,
                                    "val"   => $val
                                );
                            } 
                        }
                    }
                }
            }

            $converted = (count($matchingname) > 0 ? $matchingname[max(array_keys($matchingname))]["key"] : "");

            if( $dir != "" ){
                $exp = '/'.preg_quote($streetarr[0], '/').'/';
                $name = preg_replace($exp, $matchingdir[max(array_keys($matchingdir))]["key"], $street, 1);
            } else {
                $name = $street;
            }

            if( $converted != "" ){
                $name = str_replace($matchingname[max(array_keys($matchingname))]["val"], $converted, $name);
            }

        }

        return ucwords($name);

    }

}


?>