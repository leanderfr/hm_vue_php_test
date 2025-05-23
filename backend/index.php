<?php
declare(strict_types=1);

header("Content-Type: text/html; charset=utf-8"); 

// headers that allows another url frontend making api calls to this backend
//  CORS = Cross-Origin Resource Sharing
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization");
header('Access-Control-Allow-Methods: POST, GET, DELETE, PUT, PATCH, OPTIONS');       

$method = $_SERVER['REQUEST_METHOD'];

// OPTIONS= browser sent just a signal to check out
if ($method == "OPTIONS") {
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization");
    header("HTTP/1.1 200 OK");
    die();
} 
header("HTTP/1.0 200 OK"); 


require 'setup.php';
require 'functions.php';
require __DIR__.'/vendor/autoload.php';
require "Router.php";
require "handlers/Expressions.php";  
require "handlers/Cars.php";  
require "handlers/Bookings.php";  


//********************************************************************
// analyse the received route and points what to do
//********************************************************************
$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

$params = explode("/", $path);

// prepare handlers
$handlerExpressions = new Expressions;
$handlerCars = new Cars;
$handlerBookings = new Bookings;

$router = new Router;

//********************************************************************
// expressions (english/portuguese)
// resultformat =>  json , returns as an array of json,     reference, returns as a simple keyed array,  expressions.tablename, expresssions.title, etc
//********************************************************************
$router->addGet("/expressions/{resultformat}/{country}/{status}", function($resultformat, $country, $status) use($handlerExpressions) {  
  $handlerExpressions->getExpressions($resultformat, $country, $status);
});

//********************************************************************
// cars
//********************************************************************
if ( $_SERVER['REQUEST_METHOD'] === 'GET'  )  {
    $router->addGet("/cars/{status}", function($status) use($handlerCars)  {  
      $handlerCars->getCars($status);
    });

    // same route, different method
    $router->addGet("/cars/{id}", function($id) use($handlerCars)  {  
      $handlerCars->getCarById($id);
    });
}

if ( $_SERVER['REQUEST_METHOD'] === 'POST'  ) {
    // update
    $router->addPost("/cars/{id}", function($id) use($handlerCars)  {  
      $handlerCars->postCar($id);
    });

    // insert
    $router->addPost("/cars", function() use($handlerCars)  {  
      $handlerCars->postCar();
    });

    // change status
    $router->addPost("/cars/status/{id}", function($id) use($handlerCars)  {  
      $handlerCars->ChangeStatus($id);
    });
}






//********************************************************************
// bookings 
// the country is needed to inform the format of the date to be 
// retrieved in the database
//********************************************************************
if ( $_SERVER['REQUEST_METHOD'] === 'GET'  )  {

    $router->addGet("/products/{id}", function($id) {
        echo "This is the page for product $id";
    });
    $router->addGet("/products/{id}/orders/{order_id}", function($id, $order_id) {
        echo "This is the page for product $id, and order $order_id";
      die();
    });

    $router->addGet("/bookings/{country}/{car_id}/{firstday}/{lastday}", 
          function($country, $car_id, $firstday, $lastday) use($handlerBookings)  {  

      $handlerBookings->getByCarIdAndPeriod( $country, $car_id, $firstday, $lastday );
    });

    $router->addGet("/bookings/{country}/{id}", function($country, $id) use($handlerBookings)  {  
      $handlerBookings->getBookingById( $country, $id );
    });
}

// same route, different methods
if ( $_SERVER['REQUEST_METHOD'] === 'POST'  )  {
  // patch (update) record
  $router->addPost("/booking/{booking_id}", function($id) use($handlerBookings)  {     
    $handlerBookings->postBooking($id);
  });
  // new record
  $router->addPost("/booking", function($id) use($handlerBookings)  {  
    $handlerBookings->postBooking($id);
  });
}

if ( $_SERVER['REQUEST_METHOD'] === 'DELETE'  ) {
  $router->addDelete("/booking/{id}", function($id) use($handlerBookings)  {  
    $handlerBookings->deleteBooking($id);
  });
}


$router->dispatch($path);

//********************************************************************
//********************************************************************


?>