<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Обновление цен в базе</title>
</head>
<body>
  <?php

  require_once('exceptions.php');

  /** Имя файла для импорта */
  define('IMPORT_FILE_NAME', "west.xml");

  /** Имя базы данных для WordPress */
  define('DB_NAME', 'zilmet_wp');

  /** Имя пользователя MySQL */
  define('DB_USER', 'zilmet_ru');

  /** Пароль к базе данных MySQL */
  //define('DB_PASSWORD', 'eick39d');
  define('DB_PASSWORD', 'E3c9X8p8');

  /** Имя сервера MySQL */
  define('DB_HOST', 'localhost');

  function var_dump_pre($val){
    echo '<pre>';
    var_dump($val);
    echo  '</pre>';
  }

  function print_pre($val){
    echo '<pre>';
    print_r($val);
    echo  '</pre>';
  }

  function echo_br($val){
    echo $val . "<br>";
  }

  function echo_br_foreach($array){
    foreach ( $array as $key ) {
      echo_br( $key );
    }
  }

  $noFound = array();
  $emptyItemsSku = array();
  $emptyItemsPrice = array();
  $exceptionLog = array();

  function import_get_post_id_by_sku($hDB, $sku, $title){
    
    $query = $hDB -> query("SELECT `post_id` FROM `wp_postmeta` WHERE `meta_value` = '" . $sku . "'");
    
    $row = $query -> fetch_array(MYSQLI_ASSOC);

    return $row;
  }

  function import_update_price_by_post_id($hDB, $post_id, $price){
    $query = $hDB -> query("UPDATE `wp_postmeta` SET `meta_value` = '" . $price . "' WHERE `post_id` = " . $post_id . " AND `meta_key` = '_regular_price'");
    $query = $hDB -> query("UPDATE `wp_postmeta` SET `meta_value` = '" . $price . "' WHERE `post_id` = " . $post_id . " AND `meta_key` = '_price'");
  }

  if (file_exists( IMPORT_FILE_NAME )) 
  {
      $hDB = new mysqli( DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );

      $smpl_xml = simplexml_load_file( IMPORT_FILE_NAME );

      if (false === $smpl_xml) {
          echo "Failed loading XML\n";
          foreach(libxml_get_errors() as $error) {
              echo "\t", $error->message;
          }
      }

      foreach($smpl_xml->Item as $product){
        $sku = $product->Artikul;
        $price = $product->Price;
        $title = $product->attributes();

        if( preg_match( "/\n/", $sku[0] ) || $sku[0] == "" ) 
        {
          $emptyItemsSku[] = $title;
        }
        elseif ( $price[0] == "" || $price[0] == 0 || preg_match( "/\n/", $price[0] ) )
        {
          $emptyItemsPrice[] = $sku[0];
        }
        elseif ( !in_array( $sku[0], $exceptionItems ) )
        {
          $row = import_get_post_id_by_sku( $hDB, $sku, $title );

          if($row == "") {
            $noFound[] = $sku[0];
            // $noFound[] = $title;
          }

          import_update_price_by_post_id($hDB, $row['post_id'], $price);
        }
        else
        {
          $exceptionLog[] = $sku[0];
        }
      }
      echo "Обновление прошло успешно!";
  } 
  else
  {
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