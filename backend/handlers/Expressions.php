<?php

class Expressions
{

  public function getAll(string $language): void   {
    if ($language!='portuguese' && $language!='english' )   routeError();

    $sql =  "select $language as expression, item ".
            "from expressions  ".
            "where ifnull(active, false)= true and deleted_at is null ";

    // 3th parameter, true= specific to 'expressions', it prepares the result specific way to ease frontend's life
    executeFetchQueryAndReturnJsonResult( $sql, false, true );
  }


}