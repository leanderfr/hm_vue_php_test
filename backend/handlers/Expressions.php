<?php

class Expressions
{

  public function getExpressions(string $resultformat, string $country, string $status): void   {
    if ( $_SERVER['REQUEST_METHOD'] !== 'GET' ) {
      http_response_code(500);   
      die( 'Method not allowed1' );
    }

    if ($country!='usa' && $country!='country' )   routeError();

    $language = $country=='usa' ? 'english' : 'portuguese';

    if ( $resultformat=='reference')
      $sql =  "select $language as expression, item ".
              "from expressions  ".
              "where ifnull(active, false)= true and deleted_at is null ";

    if ( $resultformat=='json')    
      $sql =  "select id, english, portuguese, item, ifnull(active, false) as active ".
              "from expressions  ".
              "where deleted_at is null ";

    if ($status=='active') $sql .= 'and ifnull(active, false)=true';
    if ($status=='inactive') $sql .= 'and ifnull(active, false)=false';


    // 3th parameter, true= specific to 'expressions', it prepares the result specific way to ease frontend's life
    if ( $resultformat=='reference')
      executeFetchQueryAndReturnJsonResult( $sql, false, true );

    if ( $resultformat=='json')
      executeFetchQueryAndReturnJsonResult( $sql, false, false );

  }


}