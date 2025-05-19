<?php

class Cars
{

  public function getAll(): void   {
    if ( $_SERVER['REQUEST_METHOD'] !== 'GET' ) {
      http_response_code(500);   
      die( 'Method not allowed1' );
    }


    $sql =  "select description, concat('car_', id, '.png') as car_image, id, plate ".
            "from cars  ".
            "where ifnull(active, false)= true and deleted_at is null ";

    executeFetchQueryAndReturnJsonResult( $sql, false);
  }


}