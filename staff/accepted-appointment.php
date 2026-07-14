<?php session_start();
if (!isset($_SESSION['staff_id']) || $_SESSION['staff_id'] == '') {
    header('Location: index.php');
    exit();
}
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['staff_id'] == 0)) {
    header('location:logout.php');
} else {
    if ($_GET['delid']) {
        $sid = $_GET['delid'];
        mysqli_query($con, "delete from tblbook where ID ='$sid'");
        echo "<script>alert('Data Deleted');</script>";
        echo "<script>window.location.href='accepted-appointment.php'</script>";
    } ?>
    <!DOCTYPE HTML>
    <html>
    <meta name="viewport" content="width=device-width, initial-scale=0.45">

    <head>
        <title>BPMS || Accepted Appointment</title>
        <script type="application/x-javascript">
            addEventListener("load", function() {
                setTimeout(hideURLbar, 0);
            }, false);

            function hideURLbar() {
                window.scrollTo(0, 1);
            }
        </script> 
        
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
        <?php include_once('includes/sidebar.php'); ?> 
        <div class="main-page"> 
        <?php include_once('includes/header.php'); ?>
                <div id="page-wrapper">
                    <div class="main-page">
                        <div class="tables">
                            <h3 class="title1">Accepted Appointment</h3>
                            <div class="table-responsive bs-example widget-shadow">
                                <h4>New Appointment:</h4>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th> Appointment Number</th>
                                            <th>Name</th>
                                            <th>Mobile Number</th>
                                            <th>Appointment Date</th>
                                            <th>Appointment Time</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody> <?php $ret = mysqli_query($con, "select tbluser.FirstName,tbluser.LastName,tbluser.Email,tbluser.MobileNumber,tblbook.ID as bid,tblbook.AptNumber,tblbook.AptDate,tblbook.AptTime,tblbook.Message,tblbook.BookingDate,tblbook.Status from tblbook join tbluser on tbluser.ID=tblbook.UserID where tblbook.Status='Selected'");
                                            $cnt = 1;
                                            while ($row = mysqli_fetch_array($ret)) { ?> <tr>
                                                <th scope="row"><?php echo $cnt; ?></th>
                                                <td><?php echo $row['AptNumber']; ?></td>
                                                <td><?php echo $row['FirstName']; ?> <?php echo $row['LastName']; ?></td>
                                                <td><?php echo $row['MobileNumber']; ?></td>
                                                <td><?php echo $row['AptDate']; ?></td>
                                                <td><?php echo $row['AptTime']; ?></td><?php if ($row['Status'] == "") { ?> <td class="font-w600"><?php echo "Not Updated Yet"; ?></td> <?php } else { ?> <td><?php echo $row['Status']; ?></td><?php } ?> <td width="150"><a href="view-appointment.php?viewid=<?php echo $row['bid']; ?>" class="btn btn-primary btn-sm">View</a> <a href="all-appointment.php?delid=<?php echo $row['bid']; ?>" class="btn btn-danger btn-sm" onClick="return confirm('Are you sure you want to delete?')">Delete</a> </td>
                                            </tr> <?php $cnt = $cnt + 1;
                                                } ?></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div> <?php include_once('includes/footer.php'); ?>
            </div>
            <script src="js/classie.js"></script>
            <script>
                var menuLeft = document.getElementById('cbp-spmenu-s1'),
                    showLeftPush = document.getElementById('showLeftPush'),
                    body = document.body;
                showLeftPush.onclick = function() {
                    classie.toggle(this, 'active');
                    classie.toggle(body, 'cbp-spmenu-push-toright');
                    classie.toggle(menuLeft, 'cbp-spmenu-open');
                    disableOther('showLeftPush');
                };

                function disableOther(button) {
                    if (button !== 'showLeftPush') {
                        classie.toggle(showLeftPush, 'disabled');
                    }
                }
            </script>
            <script src="js/jquery.nicescroll.js"></script>
            <script src="js/scripts.js"></script>
            <script src="js/bootstrap.js"> </script>
    </body>
    </html> 
<?php } ?>