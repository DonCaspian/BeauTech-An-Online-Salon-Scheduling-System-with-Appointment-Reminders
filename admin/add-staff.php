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
} else {
    
if(isset($_POST['submit']))
{
    $staffname = mysqli_real_escape_string($con, $_POST['staffname']);
    $role = mysqli_real_escape_string($con, $_POST['role']);
    $status = mysqli_real_escape_string($con, $_POST['status']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $mobilenumber = mysqli_real_escape_string($con, $_POST['mobilenumber']);
    $password = mysqli_real_escape_string($con, $_POST['password']);
    $create_admin_account = isset($_POST['create_admin_account']) ? 1 : 0;
    
    if ($create_admin_account) {
        $check_email = mysqli_query($con, "SELECT ID FROM tblAdStaff WHERE Email='$email'");
        if (mysqli_num_rows($check_email) > 0) {
            $msg = "Email already exists in admin system. Please use a different email.";
            echo "<script>alert('$msg');</script>";
        } else {
            
            $query_staff = mysqli_query($con, "
                INSERT INTO tblstaff (StaffName, Role, Status)
                VALUES ('$staffname', '$role', '$status')
            ");
            
            if ($query_staff) {
                $staff_id = mysqli_insert_id($con);
                
                $hashed_password = md5($password);
                
                $query_admin = mysqli_query($con, "
                    INSERT INTO tblAdStaff (StaffName, Email, Password, Status, CreatedAt)
                    VALUES ('$staffname', '$email', '$hashed_password', '1', NOW())
                ");
                
                if ($query_admin) {
                    echo "<script>alert('Service staff and admin account created successfully.');</script>";
                    echo "<script>window.location.href = 'edit-service-staff.php'</script>";
                } else {
                    
                    mysqli_query($con, "DELETE FROM tblstaff WHERE ID='$staff_id'");
                    echo "<script>alert('Failed to create admin account. Please try again.');</script>";
                }
            } else {
                echo "<script>alert('Failed to add service staff. Please try again.');</script>";
            }
        }
    } else {
        
        $query = mysqli_query($con, "
            INSERT INTO tblstaff (StaffName, Role, Status)
            VALUES ('$staffname', '$role', '$status')
        ");
        
        if ($query) {
            echo "<script>alert('Service staff has been added successfully.');</script>";
            echo "<script>window.location.href = 'edit-service-staff.php'</script>";
        } else {
            echo "<script>alert('Something Went Wrong. Please try again.');</script>";
        }
    }
}
?>
<!DOCTYPE HTML>
<html>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<head>
<title>BPMS | Add Service Staff</title>
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

<style>
    .admin-account-section {
        background-color: #f9f9f9;
        padding: 20px;
        border-radius: 5px;
        margin-top: 20px;
        border-left: 4px solid #5cb85c;
    }
    
    .admin-account-section h5 {
        color: #5cb85c;
        font-weight: 600;
        margin-bottom: 15px;
    }
    
    .checkbox-label {
        font-size: 16px;
        font-weight: 600;
    }
    
    .password-field-wrapper {
        position: relative;
    }
    
    .toggle-password {
        position: absolute;
        right: 10px;
        top: 38px;
        cursor: pointer;
        color: #666;
    }
    
    .info-box {
        background-color: #d9edf7;
        border: 1px solid #bce8f1;
        color: #31708f;
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 20px;
    }
    
    .info-box i {
        margin-right: 10px;
    }
</style>
</head> 
<body class="cbp-spmenu-push">
	<div class="main-content">
		
		 <?php include_once('includes/sidebar.php');?>
		
	 <?php include_once('includes/header.php');?>
		
		<div id="page-wrapper">
			<div class="main-page">
				<div class="forms">
					<h3 class="title1">Add Service Staff</h3>
					<div class="form-grids row widget-shadow" data-example-id="basic-forms"> 
						<div class="form-title">
							<h4>Add Hairstylist or Nail Technician:</h4>
						</div>
						<div class="form-body">
							<form method="post" id="staffForm">
								<p style="font-size:16px; color:red" align="center"> <?php if(isset($msg)){
    echo $msg;
  }  ?> </p>
  
                                <div class="info-box">
                                    <i class="fa fa-info-circle"></i>
                                    <strong>Note:</strong> Fill in the basic information below. Check the box to create an admin account for this staff member to access the system.
                                </div>
  
							    <h5 style="border-bottom: 2px solid #ddd; padding-bottom: 10px; margin-bottom: 20px;">
							        <i class="fa fa-user"></i> Basic Information
							    </h5>
							    
							    <div class="form-group"> 
							 	    <label for="staffname">Staff Name *</label> 
							 	    <input type="text" class="form-control" id="staffname" name="staffname" placeholder="Full Name" required="true"> 
							    </div>
							 
							    <div class="form-group"> 
							 	    <label for="role">Role / Staff Type *</label> 
							 	    <select class="form-control" id="role" name="role" required="true">
							 		    <option value="">Select Role</option>
							 		    <option value="Hair">Hairstylist</option>
							 		    <option value="Nails">Nail Technician</option>
							 	    </select>
							    </div>
							 
							    <div class="form-group"> 
							 	    <label for="status">Status *</label> 
							 	    <select class="form-control" id="status" name="status" required="true">
							 		    <option value="">Select Status</option>
							 		    <option value="Active" selected>Active</option>
							 		    <option value="Inactive">Inactive</option>
							 	    </select>
							    </div>
							    
							    <div class="admin-account-section">
							        <div class="form-group">
							            <label class="checkbox-label">
							                <input type="checkbox" id="create_admin_account" name="create_admin_account" value="1">
							                <strong> Create Admin Account for this Staff Member</strong>
							            </label>
							            <p style="margin-top: 10px; color: #666; font-size: 14px;">
							                <i class="fa fa-info-circle"></i> 
							                Enable this to allow the staff member to login and access the staff dashboard.
							            </p>
							        </div>
							        
							        <div id="admin-fields" style="display: none;">
							            <h5><i class="fa fa-lock"></i> Admin Account Details</h5>
							            
							            <div class="form-group"> 
							 	            <label for="email">Email Address *</label> 
							 	            <input type="email" class="form-control" id="email" name="email" placeholder="email@example.com"> 
							 	            <small class="help-block">This will be used as the username for login.</small>
							            </div>
							            
							            <div class="form-group"> 
							 	            <label for="mobilenumber">Mobile Number *</label> 
							 	            <input type="text" class="form-control" id="mobilenumber" name="mobilenumber" placeholder="09XXXXXXXXX" pattern="[0-9]{11}" maxlength="11"> 
							 	            <small class="help-block">11-digit mobile number (e.g., 09171234567)</small>
							            </div>
							            
							            <div class="form-group password-field-wrapper"> 
							 	            <label for="password">Password *</label> 
							 	            <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" minlength="6"> 
							 	            <i class="fa fa-eye toggle-password" id="togglePassword"></i>
							 	            <small class="help-block">Minimum 6 characters</small>
							            </div>
							            
							            <div class="form-group password-field-wrapper"> 
							 	            <label for="confirm_password">Confirm Password *</label> 
							 	            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Re-enter password" minlength="6"> 
							 	            <i class="fa fa-eye toggle-password" id="toggleConfirmPassword"></i>
							            </div>
							            
							            <div class="alert alert-warning" style="margin-top: 15px;">
							                <i class="fa fa-exclamation-triangle"></i>
							                <strong>Important:</strong> Make sure to save these credentials and provide them to the staff member securely.
							            </div>
							        </div>
							    </div>
							
							    <button type="submit" name="submit" class="btn btn-success btn-lg" style="margin-top: 20px;">
							        <i class="fa fa-save"></i> Add Service Staff
							    </button> 
							</form> 
						</div>
						
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
   
   <script>
   // Toggle admin account fields
   $('#create_admin_account').change(function() {
       if($(this).is(':checked')) {
           $('#admin-fields').slideDown();
           // Make fields required
           $('#email, #mobilenumber, #password, #confirm_password').prop('required', true);
       } else {
           $('#admin-fields').slideUp();
           // Remove required attribute
           $('#email, #mobilenumber, #password, #confirm_password').prop('required', false);
       }
   });
   
   // Toggle password visibility
   $('#togglePassword').click(function() {
       const passwordField = $('#password');
       const type = passwordField.attr('type') === 'password' ? 'text' : 'password';
       passwordField.attr('type', type);
       $(this).toggleClass('fa-eye fa-eye-slash');
   });
   
   $('#toggleConfirmPassword').click(function() {
       const passwordField = $('#confirm_password');
       const type = passwordField.attr('type') === 'password' ? 'text' : 'password';
       passwordField.attr('type', type);
       $(this).toggleClass('fa-eye fa-eye-slash');
   });
   
   // Form validation
   $('#staffForm').submit(function(e) {
       if($('#create_admin_account').is(':checked')) {
           const password = $('#password').val();
           const confirmPassword = $('#confirm_password').val();
           
           if(password !== confirmPassword) {
               e.preventDefault();
               alert('Passwords do not match!');
               return false;
           }
           
           if(password.length < 6) {
               e.preventDefault();
               alert('Password must be at least 6 characters long!');
               return false;
           }
           
           const mobile = $('#mobilenumber').val();
           if(mobile.length !== 11 || !mobile.match(/^[0-9]+$/)) {
               e.preventDefault();
               alert('Mobile number must be exactly 11 digits!');
               return false;
           }
       }
   });
   </script>
</body>
</html>
<?php } ?>