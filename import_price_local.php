<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Обновление цен в базе</title>
</head>
<body>
  <?php
  require_once('exceptions.php');
  require_once('db_connect.php');
  require_once('print_finctions.php');
  require_once('db_functions.php');

  $noFound = array();
  $emptyItemsSku = array();
  $emptyItemsPrice = array();
  $exceptionLog = array();



  if (file_exists( IMPORT_FILE_NAME )) 
  {
      $smpl_xml = simplexml_load_file( IMPORT_FILE_NAME );

      if (false === $smpl_xml) {
          echo "Failed loading XML\n";
          foreach(libxml_get_errors() as $error) {
              echo "\t", $error->message;
          }
      }

      foreach($smpl_xml->Item as $product){
        $title = null;
        $sku = null;
        $price = null;

        $title = $product->attributes();

        if( preg_match( "/\n/", $product->Artikul ) || $product->Artikul == "" ) {
          $emptyItemsSku[] = $title;
          $sku = null;
        } else {
          $sku = $product->Artikul;
        }

        if ( in_array( $sku[0], $exceptionParts ) ){
          $price = $product->Price2;
        } else if ( in_array( $sku[0], $exceptionOem ) ) {
          $price = $product->Price3;
        } else {
          $price = $product->Price1;
        }

        if ( $price[0] == "" || $price[0] == 0 || preg_match( "/\n/", $price[0] ) ) {
          $emptyItemsPrice[] = $sku[0];
        }
        elseif ( !in_array( $sku[0], $exceptionItems ) ) {

          $row = import_get_post_id_by_sku( $hDB, $sku, $title );

          if($row == "") {
            $noFound[] = $sku[0];
          }else{
            import_update_price_by_post_id($hDB, $row['post_id'], $price);
            import_delete_all_sale_by_post_id($hDB, $row['post_id']);
            echo_br( $sku[0] );
          }
        }
        else {
          $exceptionLog[] = $sku[0];
        }
      }
      echo "Обновление прошло успешно!";
  } else {
    exit('Failed to open ' . IMPORT_FILE_NAME);
  }
?>

<h3>Не найдены артикулы: <?=count($noFound)?> элементов</h3>
<?php echo_br_foreach($noFound); ?>

<h3>Пустые артикулы: <?=count($emptyItemsSku)?> элементов</h3>
<?php echo_br_foreach($emptyItemsSku); ?>

<h3>Артикулы без цены: <?=count($emptyItemsPrice)?> элементов</h3>
<?php echo_br_foreach($emptyItemsPrice); ?>

<h3>Артикулы, попавшие под исключения: <?=count($exceptionLog)?> элементов</h3>
<?php echo_br_foreach($exceptionLog); ?>

</body>
</html>