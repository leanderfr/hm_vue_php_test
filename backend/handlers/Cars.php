<?php

class Cars
{

  public function getOnlyActive(): void   {
    if ( $_SERVER['REQUEST_METHOD'] !== 'GET' ) {
      http_response_code(500);   
      die( 'Method not allowed' );
    }
    $sql =  "select description, concat('car_', id, '.png') as car_image, id, plate, ifnull(active, false) as active ".
            "from cars  ".
            "where ifnull(active, false)= true and deleted_at is null ";

    executeFetchQueryAndReturnJsonResult( $sql, false);
  }

  public function getAll(): void   {
    if ( $_SERVER['REQUEST_METHOD'] !== 'GET' ) {
      http_response_code(500);   
      die( 'Method not allowed' );
    }
    $sql =  "select description, concat('car_', id, '.png') as car_image, id, plate, ifnull(active, false) as active ".
            "from cars  ".
            "where deleted_at is null ";

    executeFetchQueryAndReturnJsonResult( $sql, false);
  }

  public function getCarById($id): void   {
    if ( $_SERVER['REQUEST_METHOD'] !== 'GET' ) {
      http_response_code(500);   
      die( 'Method not allowed' );
    }
    $sql =  "select description, concat('car_', id, '.png') as car_image, id, plate, ifnull(active, false) as active ".
            "from cars  ".
            "where id=$id ";

    executeFetchQueryAndReturnJsonResult( $sql, true);
  }




  //***************************************************************************************************************************************
  //***************************************************************************************************************************************
  public function changeStatus($id): void   {
    global $dbConnection;

    if ( $_SERVER['REQUEST_METHOD'] !== 'POST'  ) {
      http_response_code(500);   
      die( 'Method not allowed' );
    }


    if (! is_numeric($id)) {
      internalError( 'Not numeric' );
    }

    $crudSql = "update cars set active = if(active, false, true) where id = $id ";
    $dbConnection -> autocommit(true);    // record without need to transaction

    $result = executeCrudQueryAndReturnResult($crudSql, true);    

    http_response_code(200);   // 200= it was ok
    die( '__success__' );
  }



  //***************************************************************************************************************************************
  //***************************************************************************************************************************************
  public function postCar($car_id=''): void   {
    global $dbConnection;

    if ( $_SERVER['REQUEST_METHOD'] !== 'POST'  ) {
      http_response_code(500);   
      die( 'Method not allowed' );
    }
  	if ($car_id!='' && ! is_numeric($car_id))   routeError();

    // verify request
    $fields = [ ['description', 'description', 5, 100]  ,
                ['plate', 'plate', 5, 20] ];

    $dataError = '';
    for ($i=0; $i < count($fields); $i++)  {

      $fieldType = $fields[$i][0];
      $fieldName = $fields[$i][1];
      $minSize = $fields[$i][2];
      $maxSize = $fields[$i][3];

      $fieldValue = $_POST[$fieldName];

      // is numeric
      if ($fields[$i][0] == 'int') {
        if (! is_numeric($fieldValue)) {
          $dataError = 'Not numeric';
          break;
        }
      }

      // min / max sizes
      if ($fieldType=='string') {
          if ( strlen($fieldValue) < $minSize || strlen($fieldValue) > $maxSize )  {
            $dataError = $fieldName . ' - String size error';
            break;
          }
      }
    }



    if ($dataError!='') internalError( $dataError );

    $description =   $_POST['description'];
    $plate = $_POST['plate'];

    // is image ok  
    // the front end already checked if the user chose an image when adding record, what's impeditive to go on
    // if the users didnt choose an image when updating record, bypass the image recording
    $bypassImage = true;
    if ( isset($_FILES['image']['tmp_name']) )  {
      $imgInfo = getimagesize($_FILES['image']['tmp_name']);

  //    if ($imgInfo === FALSE) 
  //      internalError( 'File erro');

      if ($imgInfo[2] !== IMAGETYPE_PNG) 
        internalError( 'Image must be PNG');

      if ($_FILES['image']['size'] > 1500000) 
        internalError( 'Max size 1.5 MB');

      $bypassImage = false;
    }    

    // if no ID's been informed, its a POST, new record
    if ($car_id=='')    {
      $crudSql = "insert into cars(description, plate, created_at, updated_at) ". 
                "select '$description', '$plate', now(), now() "; 
      $dbOperation = 'insert';
    }

    // if ID's been informed, its a PATCH, update
    else {
      $crudSql = "update cars set description='$description', plate='$plate', updated_at=now() ". 
                "where id = $car_id ";
      $dbOperation = 'update';
    }
    $dbConnection -> autocommit(true);    // record without need to transaction

    // execute query and get the ID of the just handled record (third param)
    $result = executeCrudQueryAndReturnResult($crudSql, true);    

    // uploads the image to AWS S3
    if (! $bypassImage)      {
      uploadImageToAWS_S3('image', $car_id);
    }

    http_response_code(200);   // 200= it was ok
    if ($dbOperation == 'update')   die( '__success__' );
    else die( $result );    // __success__|id registro

  }
}
