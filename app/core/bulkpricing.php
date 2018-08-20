<?php 
namespace App\Core;
//use Base;
require (dirname(dirname(dirname(__FILE__)))).'\BaseClass.php';
// Base Class
/* $root_path = str_replace('/bulkpricing/core', '', dirname(__FILE__));
require_once $root_path .'/Baseclass.php'; */

class BulkPricing extends \BaseClass {
    
    private $config;
    
    public function __construct() {
        //parent::__construct("env","PROJECT_LOG_FLAG", "PROJECT_LOG_FILE", "TEST_DATA", "TEST_LIBRARY", "page_auth_root");
        $env = parent::getEnv();
        //this class specific
        $this->config = require dirname(dirname(dirname(__FILE__))) . "/config.php";
        $this->application = $this->config["rootdir"];
        $this->maxlifetime = 1800;

        
        if ($env==="dev"){
            define("LOG_FLAG", true);
            $this->session_path = '/pcr/php/appst/'. $this->application .'/session/';
        }else{
            define("LOG_FLAG", false);
            $this->session_path = '/pcr/php/apps/'. $this->application .'/session/';
        }
        
        parent::__construct(LOG_FLAG, "bulkPricing");


        
    }   
    
    /***************************************************/
    public function callSessionStart()
    {
        // # start the session! // # session save path, maxlifetime, cookie path valid
        self::pcr_session_start($this->session_path, $this->maxlifetime, '/'. $this->application .'/');
    }
    

    

    /**
    * checking authentication 
    *
    */
    public function checkLoggedIn($appname, $path ){
        
            if(isset($_REQUEST['id'])){
                $result=$this->PCRTokenExchange($_REQUEST['id']);
                if(isset($result->salesman_number) && $result->salesman_number > 0) {
                    $_SESSION["user_info"]=$result;
                    $_SESSION["authenticated"] = 'true';
                    $_SESSION["REMOTE_ADDR"]=$_SERVER["REMOTE_ADDR"];
                }
            }

            if ($_SESSION["authenticated"]!='true'){    
                $PAGE_AUTH_ROOT=$this->get_page_auth_root();
                header("Location: " . $PAGE_AUTH_ROOT . "?app=" . $appname . "&return_url=" . urlencode("http://" . $_SERVER["HTTP_HOST"] . "/" . $path . "/"));
                exit;
            }
    }
    
   
    
    // # PHP's default session maxlifetime is 1440 seconds (24 mins).
    // # default session maxlifetime to 12 hours.
    public function pcr_session_start($session_path='', $maxlifetime=43200, $cookie_path='/') {
        
        if(empty($session_path)) {
            $this->pcrLclLogger(" BaseClass::pcr_session_start() ", 'The session save path in the first parm is missing');
            return 'The session save path in the first parm is missing';
        }
        
        // # The garbage collector is only started with a probability of session.gc_probability divided by session.gc_divisor.
        // # Using the default values for those options (1 and 100 respectively), the chance is only at 1%.
        // # session.gc_probability(=1) / session.gc_divisor(=1) = 100% of the time;
        ini_set('session.gc_probability', 1);
        ini_set('session.gc_divisor', 1);
        
        // # maxlifetime defaults to 12 hours
        ini_set('session.gc_maxlifetime', $maxlifetime);
        
        // # if session_path is used make sure dir exists
        if(!empty($session_path) && is_dir($session_path)) {
            
            // # set the session save path
            ini_set('session.save_path', $session_path); // # probably redundant
            session_save_path($session_path);
        }
        
        if(!empty($cookie_path)) {
            // # set the cookie
            session_set_cookie_params(0, $cookie_path);
            //session_set_cookie_params($maxlifetime, $cookie_path); // usage reference: http://php.net/manual/en/function.session-set-cookie-params.php
            //setcookie(session_name(), session_id(), time()+$maxlifetime);
        }
        
        // # check for existing session before starting new
        // # if phpversion() is >= 5.4
        if(phpversion() >= 5.4) {
            
            // # check session_status() function for value 'PHP_SESSION_NONE'
            if(session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
        } else { // # maintain backwards compat. with PHP versions older than 5.4
            
            // # if $_SESSION object if NOT set, start session
            if(empty($_SESSION)) {
                session_start();
            }
        }
        
        // # run existing session through pcr_session_check
        //$this->pcr_session_check();
        
        return;
    }
    
    public function pcr_session_check() {
        
        // # update the session_last_activity parm on every request that uses pcr_session_start()
        // # destroy session based on maxlifetime and last known activity
        if(!empty($_SESSION)) {
            $last_activity = (isset($_SESSION['session_last_activity']) ? $_SESSION['session_last_activity'] : time());
            $now = time();
            $maxlifetime = ini_get('session.gc_maxlifetime');
            
            if($now - $last_activity > $maxlifetime) {
                $this->pcr_session_end();
            } else {
                $_SESSION['session_last_activity'] = time();
            }
        }
    }
    
    public function pcr_session_end($redirect_url='') {
        
        // # handle logout
        if(!empty($_SESSION)) {
            $_SESSION = array();
            session_destroy();
        }
        
        if(!empty($redirect_url)) {
            
            // # sanitize the redirect URL
            $redirect_url = filter_var($redirect_url, FILTER_SANITIZE_URL);
            // # redirect after logout.
            header('Location: '. $redirect_url);
            
        } else {
            
            // # redirect after logout.
            header('Location: /'.$this->application);
        }
        
        exit();
    }
    
}