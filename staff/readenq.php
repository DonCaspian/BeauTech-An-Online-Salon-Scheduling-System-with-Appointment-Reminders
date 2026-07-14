<?php
session_start();

if (!isset($_SESSION['staff_id']) || $_SESSION['staff_id'] == '') {
    header('Location: index.php');
    exit();
}

error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['staff_id']) == 0) {
    header('location:logout.php');
    exit();
} else {

    if (isset($_GET['delid'])) {
        $sid = $_GET['delid'];
        mysqli_query($con, "DELETE FROM tblcontact WHERE ID = '$sid'");
        echo "<script>alert('Data Deleted');</script>";
        echo "<script>window.location.href='readenq.php'</script>";
    }
?>
<!DOCTYPE HTML>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=0.95">
    <title>Staff Panel || Manage Read Enquiry</title>

    <script type="application/x-javascript"> 
        addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false); 
        function hideURLbar(){ window.scrollTo(0,1); } 
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
        
        <?php include_once('includes/header.php'); ?>
        
        <div id="page-wrapper">
            <div class="main-page">
                <div class="tables">
                    <h3 class="title1">Manage Read Enquiry</h3>
                    
                    <div class="table-responsive bs-example widget-shadow">
                        <h4>View Enquiry:</h4>
                        <table class="table table-bordered"> 
                            <thead> 
                                <tr>
                                    <th>S.No</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Enquiry Date</th>
                                    <th>Action</th>
                                </tr> 
                            </thead> 
                            <tbody>
                                <?php
                                $ret = mysqli_query($con, "SELECT * FROM tblcontact WHERE IsRead='1'");
                                $cnt = 1;
                                while ($row = mysqli_fetch_array($ret)) {
                                ?>
                                <tr class="gradeX">
                                    <td><?php echo $cnt; ?></td>
                                    <td><?php echo $row['FirstName']; ?> <?php echo $row['LastName']; ?></td>
                                    <td><?php echo $row['Email']; ?></td>
                                    <td>
                                        <span class="badge badge-primary"><?php echo $row['EnquiryDate']; ?></span>
                                    </td>
                                    <td>
                                        <a href="view-enquiry.php?viewid=<?php echo $row['ID']; ?>" class="btn btn-primary">View</a>
                                        <a href="readenq.php?delid=<?php echo $row['ID']; ?>" class="btn btn-danger" onClick="return confirm('Are you sure you want to delete?')">Delete</a>
                                    </td>
                                </tr>
                                <?php 
                                    $cnt++;
                                } ?>
                            </tbody> 
                        </table> 
                    </div>
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
            
        showLeftPush.onclick = function() {
            classie.toggle(this, 'active');
            classie.toggle(body, 'cbp-spmenu-push-toright');
            classie.toggle(menuLeft, 'cbp-spmenu-open');
            disableOther('showLeftPush');
        };
        
        function disableOther(button) {
            if(button !== 'showLeftPush') {
                classie.toggle(showLeftPush, 'disabled');
            }
        }
    </script>
    
    <script src="js/jquery.nicescroll.js"></script>
    <script src="js/scripts.js"></script>
    
    <script src="js/bootstrap.js"></script>
</body>
</html>
<?php } ?>