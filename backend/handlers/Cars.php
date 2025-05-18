<?php

class Cars
{

  public function getAll(): void   {

    $sql =  "select description, concat('car_', id, '.png') as car_image, id, plate ".
            "from cars  ".
            "where ifnull(active, false)= true and deleted_at is null ";

    executeFetchQueryAndReturnJsonResult( $sql, false);
  }


}