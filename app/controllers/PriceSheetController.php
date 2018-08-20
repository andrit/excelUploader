<?php

namespace App\Controllers;

use App\Core\BulkPricing;
use App\Core\Helpers;

class PriceSheetController
{
    public function __construct()
    {
        $this->BulkPricing = new BulkPricing;
        $this->Helpers = new Helpers;
        // If the variables required for the page do not exist
        // redirect to the home page
       // $this->Helpers->redirectToHome();
    }

    public function initiate() {
        return $this->Helpers->view('main');
    }

    public function pricesheet()
    {
        //$data = $this->getPriceSheetData()
    }

    public function getSheetInfo()
    {
        
    }

    private function cgiUploadSheet() {
        $this->BulkPricing->loadPCRRESTDATA('POST', 
                                            'CGPURQ', 
                                            array('salesman'=>90595, 
                                                'file'=> 'y4s7hswcq48qkehtT1533238809D20180802.xls',
                                                'ignoreFOQ'=> '0',
                                                 'ignoreInvalid'=>'0') )
    }
    //cant find the uploads to place file on server
    private function saveFileToDir($fileToSave, $uploaddir = './uploads/', $fileext = '.xlsx'){
        $filename= $this->Helpers->randomString(16);
        $filemimetype = $this->Helpers->getMimeType($fileToSave);

        while(file_exists($fileToSave)){
            $filename= $this->Helpers->randomString(16);    
        } 

        //if($filemimetype === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'){
            file_put_contents($uploaddir . $filename . $fileext, $fileToSave);
       // } else{
            //throw new RuntimeException('Invalid file format.');
        //    return;
        //}
        
        
    }
    //for POST /uploadpricesheet
    public function uploadSheet()
    {
        $postdata = json_decode(file_get_contents('php://input'), true);

        $ignoreinvalid = $postdata['ignoreinvalid'];
        $skipoqf = $postdata['skipoqf'];

        $boom = explode(",", $postdata['file']);
        $mimetype = $boom[0];
        $xlsxfile = base64_decode($boom[1], true);

        if(!empty($xlsxfile)){
            $this->saveFileToDir($xlsxfile); 
            $this->cgiUploadSheet();
        } else {
           // echo 'Please Upload a File.';
            exit;
        }
        
    }
}