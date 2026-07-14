<?php
session_start();
if (!isset($_SESSION['bpmsaid']) || $_SESSION['bpmsaid'] == '') {
    header('Location: index.php');
    exit();
}
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['bpmsaid']==0)) {
  header('location:logout.php');
  } else{

$admin_staff = mysqli_query($con, "SELECT ID, AdminName, Email FROM tbladmin WHERE role='staff'");

$service_staff = mysqli_query($con, "SELECT ID, StaffName, Role, Status FROM tblstaff ORDER BY Role, StaffName");
?>
<!DOCTYPE HTML>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>BPMS | Manage Staff</title>
<script type="application/x-javascript"> addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false); function hideURLbar(){ window.scrollTo(0,1); } </script>

<link href="css/bootstrap.css" rel='stylesheet' type='text/css' />

<link href="css/style.css" rel='stylesheet' type='text/css' />

<link href="css/font-awesome.css" rel="stylesheet"> 

<script src="js/jquery-1.11.1.min.js"></script>
<script src="js/modernizr.custom.js"></script>

<link href='//fonts.googleapis.com/css?family=Roboto+Condensed:400,300,300italic,400italic,700,700italic' rel='stylesheet' type='text/css'>
 
<link href="css/animate.css" rel="stylesheet" type="text/css" media="all">
<script src="js/wow.min.js"></script>
	<script>
		 new WOW().init();
	</script>

<script src="js/metisMenu.min.js"></script>
<script src="js/custom.js"></script>
<link href="css/custom.css" rel="stylesheet">

</head> 
<body class="cbp-spmenu-push">
	<div class="main-content">
		
		 <?php include_once('includes/sidebar.php');?>
		
	 <?php include_once('includes/header.php');?>
		
		<div id="page-wrapper">
			<div class="main-page">
				<div class="tables">
					<h3 class="title1">Manage Staff</h3>
					
					<div class="table-responsive bs-example widget-shadow">
						<h4>Admin Staff Accounts:</h4>
						<table class="table table-bordered"> 
							<thead> 
								<tr> 
									<th>#</th> 
									<th>Name</th> 
									<th>Email</th> 
									<th>Action</th>
								</tr> 
							</thead> 
							<tbody>
<?php 
$cnt=1;
if(mysqli_num_rows($admin_staff) > 0) {
while($row=mysqli_fetch_array($admin_staff)) { 
?>
								<tr> 
									<th scope="row"><?php echo $cnt;?></th> 
									<td><?php echo $row['AdminName'];?></td> 
									<td><?php echo $row['Email'];?></td> 
									<td>
										<a href="edit-admin-staff.php?editid=<?php echo $row['ID'];?>" class="btn btn-primary btn-sm">Edit</a>
										<a href="manage-staff.php?delid=<?php echo $row['ID'];?>&type=admin" class="btn btn-danger btn-sm" onclick="return confirm('Do you really want to delete this staff account?');">Delete</a>
									</td> 
								</tr>   
<?php 
$cnt=$cnt+1;
}
} else { ?>
								<tr>
									<td colspan="4" style="text-align:center; color:red;">No admin staff found</td>
								</tr>
<?php } ?>
							</tbody> 
						</table> 
					</div>
					
					<div class="table-responsive bs-example widget-shadow" style="margin-top: 30px;">
						<h4>Service Staff (Hairstylists & Nail Technicians):</h4>
						<table class="table table-bordered"> 
							<thead> 
								<tr> 
									<th>#</th> 
									<th>Staff Name</th> 
									<th>Role</th>
									<th>Status</th>
									<th>Action</th>
								</tr> 
							</thead> 
							<tbody>
<?php 
$cnt=1;
if(mysqli_num_rows($service_staff) > 0) {
while($row=mysqli_fetch_array($service_staff)) { 
?>
								<tr> 
									<th scope="row"><?php echo $cnt;?></th> 
									<td><?php echo $row['StaffName'];?></td> 
									<td>
										<?php 
										if($row['Role'] == 'Hair') {
											echo '<span class="badge badge-info">Hairstylist</span>';
										} else if($row['Role'] == 'Nails') {
											echo '<span class="badge badge-success">Nail Technician</span>';
										} else {
											echo $row['Role'];
										}
										?>
									</td>
									<td>
										<?php 
										if($row['Status'] == 'Active') {
											echo '<span class="label label-success">Active</span>';
										} else {
											echo '<span class="label label-default">Inactive</span>';
										}
										?>
									</td>
									<td>
										<a href="edit-service-staff.php?editid=<?php echo $row['ID'];?>" class="btn btn-primary btn-sm">Edit</a>
										<a href="manage-staff.php?delid=<?php echo $row['ID'];?>&type=service" class="btn btn-danger btn-sm" onclick="return confirm('Do you really want to delete this staff member?');">Delete</a>
									</td> 
								</tr>   
<?php 
$cnt=$cnt+1;
}
} else { ?>
								<tr>
									<td colspan="5" style="text-align:center; color:red;">No service staff found</td>
								</tr>
<?php } ?>
							</tbody> 
						</table> 
					</div>
					
				</div>
			</div>
		</div>
		 <?php include_once('includes/footer.php');?>
	</div>
	
		<script src="js/classie.js"></script>
		<script>
			var menuLeft = document.getElementById( 'cbp-spmenu-s1' ),
				showLeftPush = document.getElementById( 'showLeftPush' ),
				body = document.body;
				
			showLeftPush.onclick = function() {
				classie.toggle( this, 'active' );
				classie.toggle( body, 'cbp-spmenu-push-toright' );
				classie.toggle( menuLeft, 'cbp-spmenu-open' );
				disableOther( 'showLeftPush' );
			};
			
			function disableOther( button ) {
				if( button !== 'showLeftPush' ) {
					classie.toggle( showLeftPush, 'disabled' );
				}
			}
		</script>
	
	<script src="js/jquery.nicescroll.js"></script>
	<script src="js/scripts.js"></script>
	
   <script src="js/bootstrap.js"> </script>
</body>
</html>
<?php } ?>