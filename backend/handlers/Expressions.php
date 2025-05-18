<?php
declare(strict_types=1);

class Expressions
{

  public function getAll(string $language): void   {
    if ($language!='portuguese' && $language!='english' )   routeError();

    $sql =  "select $language as expression, item ".
            "from terms  ".
            "where ifnull(active, false)= true and deleted_at is null ";

    executeFetchQueryAndReturnJsonResult( $sql, false, true );
  }


}