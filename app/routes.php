<?php 

use App\Core\Request;

if( Request::method() == 'GET' ){
   // $req = "/?id=" . $_REQUEST['id'];
    //var_dump($req);
   // var_dump(Request::uri());
   // if(isset($_REQUEST['id'])){
    //    echo "before urlcheck";
       if( Request::uri() == '/' ){

            $router->get('/', 'PriceSheetController@initiate');
        }
       
}

// Posts
if( Request::method() == 'POST' ){
    if( Request::uri() == '/uploadpricesheet' ){
        $router->post('/uploadpricesheet', 'PriceSheetController@uploadSheet');
    }
    if( Request::uri() == '/pollpricesheetstatus' ){
        $router->post('/pollpricesheetstatus', 'PriceSheetController@pollSheetInfo');
    }
    
}




