<?php
session_start();
include "connection.php";
include 'custom_function.php';


$package_name = strval($_GET['package_name']);
$_SESSION['package_name']=$package_name;

$email=test_input($_SESSION['email']);//org email
$email=mysqli_real_escape_string($conn, $email);

if(!isset($_GET['cater_email']))
{
    $query="SELECT item_count, item_desc, item_price, item_total, caterer_email FROM eli_org_package WHERE org_email = '$email' and package_name='$package_name'";

    $caterer_email="";

    $result = mysqli_query($conn,$query);

    $total=0;

    $update_php=PHP_PATH."update_package.php";

    if($result)
    {
    echo "<form method='POST' action='$update_php' id='package_item' autocomplete='off'>
    <table id='item_table'>
    <thead>
    <td>Edit</td>
    <td>QTY</td>
    <td>ITEMS</td>
    <td>PRICE($)</td>
    </thead>
    <tbody>";

    while($row = mysqli_fetch_array($result)) {
      echo "<tr>";
      echo "<td style='width:20px;'><input type='checkbox'/></td>";
      echo "<td style='width:30px;'><input type='number' class='price_table_input' value='" . $row['item_count'] . "' name='item_count[]' onchange='calc_table()'/></td>";
      echo "<td style='width:100px;'><input type='text' class='price_table_input' value='" . $row['item_desc'] . "' name='item_desc[]'/></td>";
      echo "<td style='width:50px;'><input type='number' step='0.1' class='price_table_input' value='" . $row['item_price'] . "' name='item_price[]' onchange='calc_table()'/></td>";
      echo "</tr>";
      $total+=intval($row['item_total']);

      if(!empty($row['caterer_email']))
        $caterer_email=$row['caterer_email'];

    }
    echo "</tbody>
    </table>
    <input type='hidden' name='action' value='update_package'>
    </form>";

    echo '
     <div class="plus_total" id="plus_total">
          <button class="btn" id="blue_plus_btn_1" onclick="addoneitem()"></button>
          <button class="btn" id="red_minus_btn_1" onclick="removeoneitem()"></button>
          <div class="sst_sum">
            <div class="hel_strong">Total:</div>
            <div id="total_value" class="hel_weak">$'.$total.'</div>
          </div>
        <div class="clearfix"></div>
        <input type="submit" form="package_item" class="org_set_green_bt" value="UPDATE PACKAGE" style="width: 45%; font-size: 15px;"/>
       </div><!--plus total-->
    ';
    echo "<input type='text' value='$caterer_email' class='no_display' id='caterer_email_input'>";

    mysqli_free_result($result);
    }
    else
      echo mysqli_error($conn);
}else{
   //update cater_email
  $cater_email=$_GET['cater_email'];

  $query="update eli_org_package set caterer_email='$cater_email' where org_email='$email' and package_name='$package_name'";
  if(mysqli_query($conn, $query))
    echo 1;
  else
    echo 0;
}


//mysqli_close($con);
die();
?>