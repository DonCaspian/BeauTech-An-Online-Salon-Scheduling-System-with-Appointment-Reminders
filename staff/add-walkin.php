<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('includes/dbconnection.php');
if (!isset($_SESSION['staff_id']) || $_SESSION['staff_id'] == '') {
    header('location:logout.php');
    exit();
}

if (isset($_POST['submit'])) {
    $appointmentnumber = mt_rand(100000000, 999999999);
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $phone = mysqli_real_escape_string($con, $_POST['phone']);
    $adate = mysqli_real_escape_string($con, $_POST['adate']);
    $atime = mysqli_real_escape_string($con, $_POST['atime']);
    $service = mysqli_real_escape_string($con, $_POST['service']);
    $message = mysqli_real_escape_string($con, $_POST['message']);

    $checkQuery = mysqli_query($con, "
        SELECT ID FROM tblbook 
        WHERE AptDate='$adate' 
        AND AptTime='$atime'
        AND Status IN ('', '1', 'Selected')
    ");
    
    if (mysqli_num_rows($checkQuery) > 0) {
        echo "<script>alert('This time slot is already booked. Please choose another time.');</script>";
    } else {
        $query = mysqli_query($con, "
            INSERT INTO tblbook(
                UserID,
                AptNumber, 
                Name,
                Email,
                PhoneNumber,
                AptDate,
                AptTime,
                Service,
                Message,
                Status,
                BookingDate
            ) VALUES(
                0,
                '$appointmentnumber',
                '$name',
                '$email',
                '$phone',
                '$adate',
                '$atime',
                '$service',
                '$message',
                'Selected',
                NOW()
            )
        ");
        if ($query) {
            echo "<script>alert('Walk-in appointment added successfully. Appointment Number: $appointmentnumber');</script>";
            echo "<script>window.location.href='dashboard.php'</script>";
        } else {
            echo "<script>alert('Something went wrong. Please try again.');</script>";
        }
    }
}
?>

<!DOCTYPE HTML>
<html>
<head>
    <title>BPMS | Add Walk-In Appointment</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="css/bootstrap.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/font-awesome.css" rel="stylesheet">

    <script src="js/jquery-1.11.1.min.js"></script>
    <script src="js/modernizr.custom.js"></script>
    
    <style>
        .form-container {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        
        .form-group label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        
        .form-control {
            border-radius: 4px;
            border: 1px solid #ddd;
            padding: 10px;
        }
        
        .form-control:focus {
            border-color: #5e5ce6;
            box-shadow: 0 0 0 0.2rem rgba(94, 92, 230, 0.25);
        }
        
        .btn-submit {
            background-color: #5cb85c;
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 4px;
            margin-top: 20px;
        }
        
        .btn-submit:hover {
            background-color: #4cae4c;
        }
        
        .btn-cancel {
            background-color: #6c757d;
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 4px;
            margin-top: 20px;
            margin-left: 10px;
        }
        
        .btn-cancel:hover {
            background-color: #5a6268;
        }
        
        .page-title {
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e7e7e7;
        }
        
        .page-title h3 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
            color: #333;
        }
        
        .required {
            color: #d9534f;
        }
        
        .info-box {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #2196F3;
            margin-bottom: 20px;
        }
        
        .info-box i {
            color: #2196F3;
            margin-right: 10px;
        }
    </style>
</head>

<body class="cbp-spmenu-push">
<div class="main-content">
    <?php include_once('includes/sidebar.php'); ?>
    <?php include_once('includes/header.php'); ?>

    <div id="page-wrapper">
        <div class="main-page">
            
            <div class="page-title">
                <h3><i class="fa fa-user-plus"></i> Add Walk-In Appointment</h3>
            </div>
            
            <div class="info-box">
                <i class="fa fa-info-circle"></i>
                <strong>Note:</strong> This form is for walk-in customers who visit without prior online booking. 
                The appointment will be automatically marked as "Selected" (Accepted).
            </div>

            <div class="form-container">
                <form method="post" action="">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Customer Name <span class="required">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       name="name" 
                                       placeholder="Enter customer name"
                                       required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email Address <span class="required">*</span></label>
                                <input type="email" 
                                       class="form-control" 
                                       name="email" 
                                       placeholder="Enter email address"
                                       required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Phone Number <span class="required">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       name="phone" 
                                       placeholder="Enter phone number"
                                       pattern="[0-9]{10,11}"
                                       maxlength="11"
                                       required>
                                <small class="text-muted">Enter 10-11 digit phone number</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Service <span class="required">*</span></label>
                                <select class="form-control" name="service" required>
                                    <option value="">Select Service</option>
                                    <?php
                                    $servicesQuery = mysqli_query($con, "SELECT * FROM tblservices");
                                    while ($service = mysqli_fetch_array($servicesQuery)) {
                                    ?>
                                        <option value="<?php echo $service['ServiceName']; ?>">
                                            <?php echo $service['ServiceName']; ?> - 
                                            PHP <?php echo $service['Cost']; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Appointment Date <span class="required">*</span></label>
                                <input type="date" 
                                       class="form-control" 
                                       name="adate" 
                                       min="<?php echo date('Y-m-d'); ?>"
                                       required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Appointment Time <span class="required">*</span></label>
                                <input type="time" 
                                       class="form-control" 
                                       name="atime" 
                                       required>
                                <small class="text-muted">Choose available time slot</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Additional Message (Optional)</label>
                                <textarea class="form-control" 
                                          name="message" 
                                          rows="4" 
                                          placeholder="Enter any additional notes or requirements"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" name="submit" class="btn btn-success btn-submit">
                                <i class="fa fa-check"></i> Add Walk-In Appointment
                            </button>
                            <a href="dashboard.php" class="btn btn-default btn-cancel">
                                <i class="fa fa-times"></i> Cancel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="form-container" style="margin-top: 20px;">
                <h4 style="margin-bottom: 20px;">
                    <i class="fa fa-calendar-check-o"></i> Check Available Time Slots
                </h4>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Select Date</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="checkDate" 
                                   min="<?php echo date('Y-m-d'); ?>"
                                   value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>&nbsp;</label><br>
                            <button type="button" 
                                    class="btn btn-primary" 
                                    onclick="checkAvailability()">
                                <i class="fa fa-search"></i> Check Availability
                            </button>
                        </div>
                    </div>
                </div>
                <div id="availabilityResult"></div>
            </div>
        </div>
    </div>
    <?php include_once('includes/footer.php'); ?>
</div>

<script src="js/classie.js"></script>
<script>
    var menuLeft = document.getElementById('cbp-spmenu-s1'),
        showLeftPush = document.getElementById('showLeftPush'),
        body = document.body;

    showLeftPush.onclick = function () {
        classie.toggle(this, 'active');
        classie.toggle(body, 'cbp-spmenu-push-toright');
        classie.toggle(menuLeft, 'cbp-spmenu-open');
    };
    
    function checkAvailability() {
        var date = document.getElementById('checkDate').value;
        if (!date) {
            alert('Please select a date');
            return;}
        
        $.ajax({
            url: 'check-availability.php',
            type: 'POST',
            data: { date: date },
            success: function(response) {
                document.getElementById('availabilityResult').innerHTML = response;},
            error: function() {
                alert('Error checking availability');
            }
        });
    }
</script>

<script src="js/jquery.nicescroll.js"></script>
<script src="js/scripts.js"></script>
<script src="js/bootstrap.js"></script>

</body>
</html>