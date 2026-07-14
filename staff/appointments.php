<?php
include('includes/dbconnection.php');
include('includes/staff-auth.php');
$result = mysqli_query($con, "SELECT * FROM tblappointment ORDER BY AptDate DESC");
?>

<h2>Appointments</h2>
<table border="1" width="100%">
<tr>
    <th>#</th>
    <th>Customer</th>
    <th>Date</th>
    <th>Time</th>
    <th>Status</th>
</tr>

<?php $cnt=1; while($row=mysqli_fetch_assoc($result)) { ?>
<tr>
    <td><?php echo $cnt++; ?></td>
    <td><?php echo $row['Name']; ?></td>
    <td><?php echo $row['AptDate']; ?></td>
    <td><?php echo $row['AptTime']; ?></td>
    <td><?php echo $row['Status']; ?></td>
</tr>
<?php } ?>
</table>
