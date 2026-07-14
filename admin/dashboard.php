<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
include('includes/auth.php');
if (strlen($_SESSION['bpmsaid']==0)) {
  header('location:logout.php');
  } 
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'day';

if ($filter == 'week') {
    $dateCondition = "BookingDate >= DATE(NOW()) - INTERVAL 7 DAY";
} elseif ($filter == 'month') {
    $dateCondition = "MONTH(BookingDate) = MONTH(CURDATE()) 
                      AND YEAR(BookingDate) = YEAR(CURDATE())";
} else {
    $dateCondition = "DATE(BookingDate) = CURDATE()";
}
$transactionQuery = mysqli_query($con, "
    SELECT * FROM tblbook
    WHERE Status='Selected' AND $dateCondition
    ORDER BY BookingDate DESC
");
$calendarData = [];
$calendarQuery = mysqli_query($con, "
    SELECT DATE(AptDate) as date_only, COUNT(*) as total 
    FROM tblbook 
    WHERE AptDate IS NOT NULL 
      AND AptDate != '0000-00-00'
      AND Status='Selected'
    GROUP BY DATE(AptDate)
");
if (!$calendarQuery) {
    echo "<!-- Calendar Query Error: " . mysqli_error($con) . " -->";
} else {
    while ($row = mysqli_fetch_assoc($calendarQuery)) {
        $calendarData[$row['date_only']] = $row['total'];
    }
}

$inventory_stats_query = "SELECT 
    COUNT(*) as total_products,
    SUM(Quantity) as total_stock,
    SUM(CASE WHEN Quantity <= ReorderLevel THEN 1 ELSE 0 END) as low_stock,
    SUM(CASE WHEN Quantity = 0 THEN 1 ELSE 0 END) as out_of_stock
    FROM tblinventory";

$inventory_result = mysqli_query($con, $inventory_stats_query);

if (!$inventory_result) {
    
    $totalproducts = 0;
    $totalstock = 0;
    $lowstock = 0;
    $outofstock = 0;
} else {
    $inventory_stats = mysqli_fetch_assoc($inventory_result);
    $totalproducts = $inventory_stats['total_products'] ?? 0;
    $totalstock = $inventory_stats['total_stock'] ?? 0;
    $lowstock = $inventory_stats['low_stock'] ?? 0;
    $outofstock = $inventory_stats['out_of_stock'] ?? 0;
}
?>

<!DOCTYPE HTML>
<html>
    <meta name="viewport" content="width=device-width, initial-scale=0.40">
<head>
<title>BPMS | Admin Dashboard</title>
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
<script src="js/Chart.js"></script>
<link rel="stylesheet" href="css/clndr.css" type="text/css" />
<script src="js/underscore-min.js" type="text/javascript"></script>
<script src= "js/moment-2.2.1.js" type="text/javascript"></script>
<script src="js/clndr.js" type="text/javascript"></script>
<script src="js/metisMenu.min.js"></script>
<script src="js/custom.js"></script>
<link href="css/custom.css" rel="stylesheet">
<style>
    .section-header {
        margin: 0 0 15px 0;
        padding: 15px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 8px 8px 0 0;
    }
    
    .section-header h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: #fff;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .table-container {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        overflow: hidden;
        margin-bottom: 20px;
    }
    
    .table-responsive {
        overflow-x: auto;
    }
    
    .table {
        margin-bottom: 0;
    }
    
    .table > thead > tr > th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
        padding: 12px 8px;
        vertical-align: middle;
    }
    
    .table > tbody > tr > td {
        padding: 10px 8px;
        vertical-align: middle;
        font-size: 13px;
    }
    
    .table > tbody > tr:hover {
        background-color: #f8f9fa;
    }
    
    .no-appointments, .no-inventory {
        text-align: center;
        padding: 40px;
        color: #999;
    }
    
    .filter-buttons {
        padding: 10px 15px;
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
    
    .filter-buttons .btn {
        margin-right: 5px;
    }

    /* Inventory KPI Cards */
    .inventory-kpi-row {
        margin: 20px 0;
    }

    .inventory-kpi-card {
        background: #fff;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 15px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: transform 0.2s, box-shadow 0.2s;
        min-height: 120px;
    }
    
    .inventory-kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .inventory-kpi-card .kpi-label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
        opacity: 0.8;
    }
    
    .inventory-kpi-card .kpi-title {
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 10px;
    }
    
    .inventory-kpi-card .kpi-value {
        font-size: 32px;
        font-weight: 700;
        line-height: 1;
    }

    /* Inventory color variants */
    .kpi-products {
        border-left: 4px solid #9b59b6;
    }
    
    .kpi-products .kpi-label,
    .kpi-products .kpi-title {
        color: #9b59b6;
    }

    .kpi-stock {
        border-left: 4px solid #1abc9c;
    }
    
    .kpi-stock .kpi-label,
    .kpi-stock .kpi-title {
        color: #1abc9c;
    }

    .kpi-lowstock {
        border-left: 4px solid #e67e22;
    }
    
    .kpi-lowstock .kpi-label,
    .kpi-lowstock .kpi-title {
        color: #e67e22;
    }

    .kpi-outofstock {
        border-left: 4px solid #e74c3c;
    }
    
    .kpi-outofstock .kpi-label,
    .kpi-outofstock .kpi-title {
        color: #e74c3c;
    }

    /* Stock level indicators */
    .stock-indicator {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-right: 5px;
    }

    .stock-ok {
        background-color: #5cb85c;
    }

    .stock-low {
        background-color: #f0ad4e;
    }

    .stock-out {
        background-color: #d9534f;
    }

    .action-buttons .btn {
        margin-right: 5px;
        margin-bottom: 3px;
    }

    .inventory-action-buttons {
        margin: 15px 0;
        padding: 15px;
        background-color: #f8f9fa;
        border-radius: 8px;
    }

    .inventory-action-buttons .btn {
        margin-right: 10px;
        margin-bottom: 5px;
    }
    
    /* Calendar styling */
    .clndr .clndr-table {
        width: 100%;
        table-layout: fixed;
        border-collapse: collapse;
    }
    
    .clndr .clndr-table th,
    .clndr .clndr-table td {
        text-align: center;
        padding: 10px;
        border: 1px solid #e0e0e0;
    }
    
    .clndr .clndr-table th {
        background-color: #f5f5f5;
        font-weight: bold;
        color: #333;
    }
    
    .clndr .day {
        cursor: pointer;
        position: relative;
        min-height: 60px;
        vertical-align: top;
    }
    
    .clndr .day:hover {
        background-color: #f0f0f0;
    }
    
    .clndr .day.today {
        background-color: #e3f2fd;
        font-weight: bold;
    }
    
    .clndr .day.event {
        background-color: #fff3cd;
        font-weight: bold;
    }
    
    .clndr .day .day-number {
        display: block;
        font-size: 14px;
        margin-bottom: 5px;
    }
    
    .clndr .day .event-indicator {
        display: block;
        background-color: #ff5722;
        color: white;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        line-height: 24px;
        margin: 0 auto;
        font-size: 12px;
        font-weight: bold;
    }
    
    .clndr .day.adjacent-month {
        color: #ccc;
    }
    
    .clndr .clndr-controls {
        text-align: center;
        margin-bottom: 20px;
        padding: 15px;
        background-color: #f5f5f5;
        border-radius: 5px;
    }
    
    .clndr .clndr-controls .month {
        display: inline-block;
        font-size: 20px;
        font-weight: bold;
        margin: 0 20px;
        min-width: 200px;
    }
    
    .clndr .clndr-previous-button,
    .clndr .clndr-next-button {
        display: inline-block;
        padding: 8px 16px;
        background-color: #007bff;
        color: white;
        text-decoration: none;
        border-radius: 4px;
        cursor: pointer;
        border: none;
        font-size: 14px;
    }
    
    .clndr .clndr-previous-button:hover,
    .clndr .clndr-next-button:hover {
        background-color: #0056b3;
    }
</style>
</head> 
<body class="cbp-spmenu-push">
<div class="main-content">
	<?php include_once('includes/sidebar.php');?>
	<?php include_once('includes/header.php');?>
		<div id="page-wrapper" class="row calender widget-shadow">
			<div class="main-page">
			    <div class="row">
			        <div class="col-md-6">
			            <div class="table-container">
			                <div class="section-header">
                                <h3>Recent Appointments</h3>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                    <tr>
                                        <th style="width: 35px; text-align: center;">#</th>
                                        <th style="width: 90px;">APT NO.</th>
                                        <th style="width: 95px;">DATE</th>
                                        <th style="width: 75px;">TIME</th>
                                        <th style="width: 75px; text-align: center;">STATUS</th>
                                        <th style="width: 60px; text-align: center;">ACTION</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                
                                    <?php
                                    $query = "SELECT ID, AptNumber, AptDate, AptTime, Status, Remark 
                                             FROM tblbook 
                                             WHERE AptDate != '0000-00-00' 
                                             AND AptTime != '00:00:00'
                                             ORDER BY ID DESC 
                                             LIMIT 5";
                                    
                                    $appointments_result = mysqli_query($con, $query);
                                    
                                    if (!$appointments_result) {
                                        echo '<tr><td colspan="6" class="text-center text-danger">Error loading appointments: ' . mysqli_error($con) . '</td></tr>';
                                    } else {
                                        if (mysqli_num_rows($appointments_result) == 0) {
                                            echo '<tr><td colspan="6" class="no-appointments">No recent appointments found</td></tr>';
                                        } else {
                                            $cnt = 1;
                                            while ($row = mysqli_fetch_assoc($appointments_result)) {
                                                $statusValue = $row['Status'];
                                    ?>
                                        <tr>
                                            <td style="text-align: center;"><?php echo $cnt; ?></td>
                
                                            <td>
                                                <?php
                                                if ($row['AptNumber'] == 0) {
                                                    echo '<span class="label label-default">Walk-in</span>';
                                                } else {
                                                    echo htmlspecialchars($row['AptNumber']);
                                                }
                                                ?>
                                            </td>
                
                                            <td><?php echo htmlspecialchars(date("M d, Y", strtotime($row['AptDate']))); ?></td>
                                            <td><?php echo htmlspecialchars(date("h:i A", strtotime($row['AptTime']))); ?></td>
                
                                            <td style="text-align: center;">
                                                <?php
                                                $statusLower = strtolower(trim($statusValue ?? ''));
                                                
                                                if ($statusValue === '1' || $statusValue === 1 || $statusLower === 'accepted' || $statusLower === 'selected') {
                                                    echo '<span class="label label-success">Accepted</span>';
                                                } elseif ($statusValue === '2' || $statusValue === 2 || $statusLower === 'rejected' || $statusLower === 'cancelled') {
                                                    echo '<span class="label label-danger">Rejected</span>';
                                                } elseif (empty($statusValue) || $statusLower === 'pending' || $statusLower === '') {
                                                    echo '<span class="label label-warning">Pending</span>';
                                                } else {
                                                    echo '<span class="label label-info">' . htmlspecialchars(ucfirst($statusValue)) . '</span>';
                                                }
                                                ?>
                                            </td>
                                            <td style="text-align: center;">
                                                <a href="view-appointment.php?viewid=<?php echo urlencode($row['ID']); ?>"
                                                   class="btn btn-primary btn-xs" title="View Details">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php
                                                $cnt++;
                                            }
                                        }
                                    }
                                    ?>
                
                                    </tbody>
                                </table>
                            </div>
                        </div>
			        </div>
			        <div class="col-md-6">
			            <div class="table-container">
			                <div class="section-header">
                                <h3>Transactions</h3>
                            </div>
                            <div class="filter-buttons">
                                <a href="dashboard.php?filter=day" class="btn btn-default btn-xs <?php echo ($filter == 'day' ? 'active' : ''); ?>">Today</a>
                                <a href="dashboard.php?filter=week" class="btn btn-default btn-xs <?php echo ($filter == 'week' ? 'active' : ''); ?>">This Week</a>
                                <a href="dashboard.php?filter=month" class="btn btn-default btn-xs <?php echo ($filter == 'month' ? 'active' : ''); ?>">This Month</a>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th style="width: 35px; text-align: center;">#</th>
                                            <th style="width: 90px;">APT NO.</th>
                                            <th>SERVICE</th>
                                            <th style="width: 95px;">DATE</th>
                                            <th style="width: 75px;">TIME</th>
                                            <th style="width: 60px; text-align: center;">TYPE</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $cnt = 1;
                                        if (mysqli_num_rows($transactionQuery) > 0) {
                                            while ($row = mysqli_fetch_array($transactionQuery)) {
                                            $type = ($row['UserID'] == 0) ? 'Walk-In' : 'Online';
                                        ?>
                                        <tr>
                                            <td style="text-align: center;"><?php echo $cnt++; ?></td>
                                            <td><?php echo htmlspecialchars($row['AptNumber']); ?></td>
                                            <td><?php echo htmlspecialchars($row['Service']); ?></td>
                                            <td><?php echo htmlspecialchars(date("M d, Y", strtotime($row['AptDate']))); ?></td>
                                            <td><?php echo htmlspecialchars(date("h:i A", strtotime($row['AptTime']))); ?></td>
                                            <td style="text-align: center;">
                                                <?php if ($type == 'Online'): ?>
                                                    <span class="label label-info">Online</span>
                                                <?php else: ?>
                                                    <span class="label label-default">Walk-In</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php
                                            }
                                        } else {
                                        ?>
                                        <tr>
                                            <td colspan="6" style="text-align:center; padding: 30px; color: #999;">No transactions found for selected period</td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
			        </div>
			    </div>
                <div class="row" style="margin-top: 30px;">
                    <div class="col-md-12">
                        <a href="add-walkin.php" class="btn btn-success" style="margin-bottom:15px;">
                            + Add Walk-In Appointment
                        </a>
                    </div>
                </div>
                
                <div class="row" style="margin-top: 30px;">
                    <div class="col-md-12">
                        <div class="section-header">
                            <h3>Inventory Management</h3>
                        </div>
                    </div>
                </div>

                <div class="row inventory-kpi-row">
                    
                    <div class="col-md-3 col-sm-6">
                        <div class="inventory-kpi-card kpi-products">
                            <div>
                                <div class="kpi-label">Total</div>
                                <div class="kpi-title">Products</div>
                            </div>
                            <div class="kpi-value"><?php echo htmlspecialchars($totalproducts); ?></div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <div class="inventory-kpi-card kpi-stock">
                            <div>
                                <div class="kpi-label">Total</div>
                                <div class="kpi-title">Stock Units</div>
                            </div>
                            <div class="kpi-value"><?php echo htmlspecialchars($totalstock); ?></div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <div class="inventory-kpi-card kpi-lowstock">
                            <div>
                                <div class="kpi-label">Total</div>
                                <div class="kpi-title">Low Stock</div>
                            </div>
                            <div class="kpi-value"><?php echo htmlspecialchars($lowstock); ?></div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <div class="inventory-kpi-card kpi-outofstock">
                            <div>
                                <div class="kpi-label">Total</div>
                                <div class="kpi-title">Out of Stock</div>
                            </div>
                            <div class="kpi-value"><?php echo htmlspecialchars($outofstock); ?></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="inventory-action-buttons">
                            <a href="add-product.php" class="btn btn-primary btn-lg">
                                <i class="fa fa-plus-circle"></i> Add New Product
                            </a>
                            <a href="manage-inventory.php" class="btn btn-info btn-lg">
                                <i class="fa fa-list"></i> Manage Inventory
                            </a>
                            <a href="stock-report.php" class="btn btn-warning btn-lg">
                                <i class="fa fa-file-text"></i> Stock Report
                            </a>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="table-container">
                            <div class="section-header">
                                <h3>Inventory Alerts</h3>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                    <tr>
                                        <th style="width: 40px; text-align: center;">#</th>
                                        <th>Product Name</th>
                                        <th style="width: 150px;">Category</th>
                                        <th style="width: 120px; text-align: center;">Current Stock</th>
                                        <th style="width: 120px; text-align: center;">Reorder Level</th>
                                        <th style="width: 100px; text-align: center;">Status</th>
                                        <th style="width: 150px; text-align: center;">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    <?php
                                    
                                    $inventory_query = "SELECT ID, ProductName, Category, Quantity, ReorderLevel, Unit 
                                                       FROM tblinventory 
                                                       WHERE Quantity <= ReorderLevel
                                                       ORDER BY Quantity ASC
                                                       LIMIT 10";
                                    
                                    $inv_result = mysqli_query($con, $inventory_query);
                                    
                                    if (!$inv_result) {
                                        echo '<tr><td colspan="7" class="text-center text-info">Inventory table not set up yet. <a href="setup-inventory.php">Click here to set up</a></td></tr>';
                                    } else {
                                        if (mysqli_num_rows($inv_result) == 0) {
                                            echo '<tr><td colspan="7" class="no-inventory">All inventory levels are healthy! No alerts at this time.</td></tr>';
                                        } else {
                                            $cnt = 1;
                                            while ($inv_row = mysqli_fetch_assoc($inv_result)) {
                                                $quantity = (int)$inv_row['Quantity'];
                                                $reorderLevel = (int)$inv_row['ReorderLevel'];
                                    ?>
                                        <tr>
                                            <td style="text-align: center;"><?php echo $cnt; ?></td>
                                            <td><?php echo htmlspecialchars($inv_row['ProductName']); ?></td>
                                            <td><?php echo htmlspecialchars($inv_row['Category']); ?></td>
                                            <td style="text-align: center;">
                                                <strong><?php echo $quantity; ?></strong> 
                                                <?php echo htmlspecialchars($inv_row['Unit']); ?>
                                            </td>
                                            <td style="text-align: center;">
                                                <?php echo $reorderLevel; ?> 
                                                <?php echo htmlspecialchars($inv_row['Unit']); ?>
                                            </td>
                                            <td style="text-align: center;">
                                                <?php
                                                if ($quantity == 0) {
                                                    echo '<span class="stock-indicator stock-out"></span>';
                                                    echo '<span class="label label-danger">Out of Stock</span>';
                                                } elseif ($quantity <= $reorderLevel) {
                                                    echo '<span class="stock-indicator stock-low"></span>';
                                                    echo '<span class="label label-warning">Low Stock</span>';
                                                } else {
                                                    echo '<span class="stock-indicator stock-ok"></span>';
                                                    echo '<span class="label label-success">In Stock</span>';
                                                }
                                                ?>
                                            </td>
                                            <td style="text-align: center;" class="action-buttons">
                                                <a href="edit-product.php?id=<?php echo urlencode($inv_row['ID']); ?>" 
                                                   class="btn btn-primary btn-xs">
                                                    <i class="fa fa-edit"></i> Edit
                                                </a>
                                                <a href="restock.php?id=<?php echo urlencode($inv_row['ID']); ?>" 
                                                   class="btn btn-success btn-xs">
                                                    <i class="fa fa-plus"></i> Restock
                                                </a>
                                            </td>
                                        </tr>
                                    <?php
                                                $cnt++;
                                            }
                                        }
                                    }
                                    ?>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row calender widget-shadow">
                    <div class="row-one">
                        <div class="col-md-12">
                            <h3 class="title1">Online Reservations Calendar</h3>
                            <div id="online-calendar"></div>
                        </div>
                    </div>
                </div>
				<div class="row calender widget-shadow">
					<div class="row-one">
					<div class="col-md-4 widget">
						<?php $query1=mysqli_query($con,"Select * from tbluser");
                        $totalcust=mysqli_num_rows($query1);
                        ?>
						<div class="stats-left ">
							<h5>Total</h5>
							<h4>Customer</h4>
						</div>
						<div class="stats-right">
							<label> <?php echo htmlspecialchars($totalcust);?></label>
						</div>
						<div class="clearfix"> </div>	
					</div>
					<div class="col-md-4 widget states-mdl">
						<?php $query2=mysqli_query($con,"Select * from tblbook");
$totalappointment=mysqli_num_rows($query2);
?>
						<div class="stats-left">
							<h5>Total</h5>
							<h4>Appointment</h4>
						</div>
						<div class="stats-right">
							<label> <?php echo htmlspecialchars($totalappointment);?></label>
						</div>
						<div class="clearfix"> </div>	
					</div>
					<div class="col-md-4 widget states-last">
						<?php $query3=mysqli_query($con,"Select * from tblbook where Status='Selected'");
$totalaccapt=mysqli_num_rows($query3);
?>
						<div class="stats-left">
							<h5>Total</h5>
							<h4>Accepted Apt</h4>
						</div>
						<div class="stats-right">
							<label><?php echo htmlspecialchars($totalaccapt);?></label>
						</div>
						<div class="clearfix"> </div>	
					</div>
					<div class="clearfix"> </div>	
				</div>
					</div>
				<div class="row calender widget-shadow">
					<div class="row-one">
					<div class="col-md-4 widget">
						<?php $query4 = mysqli_query($con,"
                            SELECT * FROM tblbook 
                            WHERE Status='' OR Status IS NULL
                        ");
                        $totalpending = mysqli_num_rows($query4);
                        ?>
						<div class="stats-left ">
							<h5>Total</h5>
							<h4>Pending Apt</h4>
						</div>
						<div class="stats-right">
                            <label><?php echo htmlspecialchars($totalpending); ?></label>
						</div>
						<div class="clearfix"> </div>	
					</div>
					<div class="col-md-4 widget states-mdl">
						<?php $query5=mysqli_query($con,"Select * from  tblservices");
$totalser=mysqli_num_rows($query5);
?>
						<div class="stats-left">
							<h5>Total</h5>
							<h4>Services</h4>
						</div>
						<div class="stats-right">
							<label> <?php echo htmlspecialchars($totalser);?></label>
						</div>
						<div class="clearfix"> </div>	
					</div>
					<div class="col-md-4 widget states-last">
						<?php
 $query6=mysqli_query($con,"select tblinvoice.ServiceId as ServiceId, tblservices.Cost
 from tblinvoice 
  join tblservices  on tblservices.ID=tblinvoice.ServiceId where date(PostingDate)=CURDATE();");
$todysale = 0;
while($row=mysqli_fetch_array($query6))
{
$todays_sale=$row['Cost'];
$todysale+=$todays_sale;
}
 ?>
						<div class="stats-left">
							<h5>Today</h5>
							<h4>Sales</h4>
						</div>
						<div class="stats-right">
							<label> <?php 
if($todysale==""):
							echo "0";
else:
	echo htmlspecialchars($todysale);
endif;
						?></label>
						</div>
						<div class="clearfix"> </div>	
					</div>
					<div class="clearfix"> </div>	
				</div>
					</div>
				<div class="row calender widget-shadow">
					<div class="row-one">
					<div class="col-md-4 widget">
						<?php
 $query7=mysqli_query($con,"select tblinvoice.ServiceId as ServiceId, tblservices.Cost
 from tblinvoice 
  join tblservices  on tblservices.ID=tblinvoice.ServiceId where date(PostingDate)=CURDATE()-1;");
$yesterdaysale = 0;
while($row7=mysqli_fetch_array($query7))
{
$yesterdays_sale=$row7['Cost'];
$yesterdaysale+=$yesterdays_sale;
}
 ?>
						<div class="stats-left ">
							<h5>Yesterday</h5>
							<h4>Sales</h4>
						</div>
						<div class="stats-right">
							<label> <?php 
                            if($yesterdaysale==""):
                            							echo "0";
                            else:
                            	echo htmlspecialchars($yesterdaysale);
                            endif;
						?></label>
						</div>
						<div class="clearfix"> </div>	
					</div>
					<div class="col-md-4 widget states-mdl">
						<?php
 $query8=mysqli_query($con,"select tblinvoice.ServiceId as ServiceId, tblservices.Cost
 from tblinvoice 
  join tblservices  on tblservices.ID=tblinvoice.ServiceId where date(PostingDate)>=(DATE(NOW()) - INTERVAL 7 DAY);");
$tseven = 0;
while($row8=mysqli_fetch_array($query8))
{
$sevendays_sale=$row8['Cost'];
$tseven+=$sevendays_sale;
}
 ?>
						<div class="stats-left">
							<h5>Last Sevendays</h5>
							<h4>Sale</h4>
						</div>
						<div class="stats-right">
							<label> <?php 
						if($tseven==""):
							echo "0";
                        else:
                        	echo htmlspecialchars($tseven);
                        endif;?></label>
						</div>
						<div class="clearfix"> </div>	
					</div>
					<div class="col-md-4 widget states-last">
						<?php
                         $query9=mysqli_query($con,"select tblinvoice.ServiceId as ServiceId, tblservices.Cost
                         from tblinvoice 
                          join tblservices  on tblservices.ID=tblinvoice.ServiceId");
                        $totalsale = 0;
                        while($row9=mysqli_fetch_array($query9))
                        {
                        $total_sale=$row9['Cost'];
                        $totalsale+=$total_sale;
                        }
                         ?>
						<div class="stats-left">
							<h5>Total</h5>
							<h4>Sales</h4>
						</div>
						<div class="stats-right">
							<label><?php
		if($totalsale==""):
							echo "0";
else:
	echo htmlspecialchars($totalsale);
endif;
						?></label>
						</div>
						<div class="clearfix"> </div>	
					</div>
					<div class="clearfix"> </div>	
				</div>
						
					</div>
				</div>
				<div class="clearfix"> </div>
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
   <script type="text/template" id="clndr-template">
       <div class="clndr-controls">
           <button class="clndr-previous-button">Previous</button>
           <span class="month"><%= month %> <%= year %></span>
           <button class="clndr-next-button">Next</button>
       </div>
       <table class="clndr-table">
           <thead>
               <tr class="header-days">
                   <% for(var i = 0; i < daysOfTheWeek.length; i++) { %>
                       <th class="header-day"><%= daysOfTheWeek[i] %></th>
                   <% } %>
               </tr>
           </thead>
           <tbody>
               <% for(var i = 0; i < numberOfRows; i++){ %>
                   <tr>
                       <% for(var j = 0; j < 7; j++){ %>
                           <% var d = j + i * 7; %>
                           <td class="<%= days[d].classes %>">
                               <span class="day-number"><%= days[d].day %></span>
                               <% if(days[d].events.length > 0) { %>
                                   <span class="event-indicator"><%= days[d].events[0].count %></span>
                               <% } %>
                           </td>
                       <% } %>
                   </tr>
               <% } %>
           </tbody>
       </table>
   </script>
   
   <script>
    $(document).ready(function() {
        if ($('#online-calendar').length === 0) {
            console.error("Calendar element #online-calendar not found!");
            return;
        }
        var onlineReservations = <?php echo json_encode($calendarData); ?>;
        console.log("Calendar data loaded:", onlineReservations);
        console.log("Number of dates with appointments:", Object.keys(onlineReservations).length);
        
        var events = [];
        
        for (var date in onlineReservations) {
            if (onlineReservations.hasOwnProperty(date)) {
                var count = onlineReservations[date];
                events.push({
                    date: date,
                    title: count + " Appointment" + (count > 1 ? "s" : ""),
                    count: count
                });
                console.log("Added event for date:", date, "with", count, "appointments");
            }
        }
        console.log("Total calendar events created:", events.length);
        try {
            var startDate = moment();
            if (events.length > 0) {
                var latestMoment = moment(events[0].date);
                for (var i = 1; i < events.length; i++) {
                    var currentMoment = moment(events[i].date);
                    if (currentMoment.isAfter(latestMoment)) {
                        latestMoment = currentMoment;
                    }
                }
                
                if (latestMoment.isAfter(moment().subtract(2, 'months'))) {
                    startDate = latestMoment;
                }
            }
            var calendar = $('#online-calendar').clndr({
                template: $('#clndr-template').html(),
                events: events,
                startWithMonth: startDate,
                clickEvents: {
                    click: function(target) {
                        console.log('Date clicked:', target);
                        if (target.events.length > 0) {
                            var count = target.events.length;
                            var dateStr = target.date.format('MMMM D, YYYY');
                            alert(count + ' appointment' + (count > 1 ? 's' : '') + ' on ' + dateStr);
                        }
                    }
                },
                showAdjacentMonths: true,
                adjacentDaysChangeMonth: false,
                daysOfTheWeek: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
            });
            console.log("Calendar initialized successfully! Starting at:", startDate.format('MMMM YYYY'));
        } catch(e) {
            console.error("Error initializing calendar:", e);
        }
    });
    </script>
</body>
</html>