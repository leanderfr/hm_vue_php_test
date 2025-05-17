<?php

declare(strict_types=1);

$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

require "Router.php";
$router = new Router;
$router->add("/", function() {
    echo "This is the homepage";
});
$router->add("/about", function() {
    echo "This is the about page";
});
$router->add("/products/{id}", function($id) {
    echo "This is the page for product $id";
});
$router->add("/products/{id}/orders/{order_id}", function($id, $order_id) {
    echo "This is the page for product $id, and order $order_id";
  die();
});
$router->dispatch($path);


?>