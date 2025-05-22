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
require 'aws/aws-autoloader.php';  // AWS S3 handler
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
//********************************************************************
$router->addGet("/expressions/{language}", function($language) use($handlerExpressions) {  
  $handlerExpressions->getAll($language);
});

//********************************************************************
// cars
//********************************************************************
$router->addGet("/cars/active", function() use($handlerCars)  {  
  $handlerCars->getOnlyActive();
});
$router->addGet("/cars/all", function() use($handlerCars)  {  
  $handlerCars->getAll();
});

// same route, different method
if ( $_SERVER['REQUEST_METHOD'] === 'GET'  ) {
  $router->addGet("/cars/{id}", function($id) use($handlerCars)  {  
    $handlerCars->getCarById($id);
  });
}
if ( $_SERVER['REQUEST_METHOD'] === 'POST'  ) {
  $router->addPost("/cars/{id}", function($id) use($handlerCars)  {  
    $handlerCars->postCar($id);
  });
}

$router->addPost("/cars/status/{id}", function($id) use($handlerCars)  {  
  $handlerCars->ChangeStatus($id);
});








//********************************************************************
// bookings 
// the country is needed to inform the format of the date
//********************************************************************
$router->addGet("/bookings/{country}/{car_id}/{firstday}/{lastday}", 
      function($country, $car_id, $firstday, $lastday) use($handlerBookings)  {  

  $handlerBookings->getByCarIdAndPeriod( $country, $car_id, $firstday, $lastday );
});

$router->addGet("/bookings/{country}/{booking_id}", function($country, $booking_id) use($handlerBookings)  {  
  $handlerBookings->getBookingById( $country, $booking_id );
});

$router->addPost("/booking/{booking_id}", function($booking_id) use($handlerBookings)  {  
  $handlerBookings->postBooking($booking_id);
});

$router->addPost("/booking", function() use($handlerBookings)  {  
  $handlerBookings->postBooking();
});






$router->addGet("/products/{id}", function($id) {
    echo "This is the page for product $id";
});
$router->addGet("/products/{id}/orders/{order_id}", function($id, $order_id) {
    echo "This is the page for product $id, and order $order_id";
  die();
});
$router->dispatch($path);

//********************************************************************
//********************************************************************


?>