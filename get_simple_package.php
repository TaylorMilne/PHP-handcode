<?php
session_start();
include "connection.php";
include 'custom_function.php';

$package_name = strval($_GET['package_name']);
$_SESSION['package_name']=$package_name;
$org_name=test_input($_SESSION['select_org']);
$org_name=mysqli_real_escape_string($conn, $org_name);

$query="SELECT item_count, item_desc, item_price, item_total FROM eli_org_package WHERE org_name = '$org_name' and package_name='$package_name'";

$result = mysqli_query($conn,$query);

$subtotal=$total=0;

$update_php=PHP_PATH."update_package.php";

if($result)
{
echo "
<table id='item_table_in_main'>
<thead>
<td>QTY</td>
<td>DESCRIPTION</td>
<td>PRICE($)</td>";
//<td>TOTAL($)</td>
echo "
</thead>
<tbody>";
while($row = mysqli_fetch_array($result)) {
  echo "<tr>";
	echo "<td style='width:30px;'>".$row['item_count']."</td>";
	echo "<td style='width:100px;'>".$row['item_desc']."</td>";
	echo "<td style='width:100px;'>".$row['item_price']."</td>";
  echo "</tr>";
	$subtotal+=intval($row['item_total']);
}
echo "</tbody>
</table>";
$total=$subtotal;
echo "<input id='total_price' type='text' value='$total' class='no_display'>";
mysqli_free_result($result);
}
//mysqli_close($con);
die();
?>