<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('includes/dbconnection.php');

if (!isset($_SESSION['staff_id']) || $_SESSION['staff_id'] == '') {
    header('location:logout.php');
    exit();
}

if (!isset($_SESSION['last_regeneration'])) {
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 300) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

$stats_query = "SELECT 
    COUNT(*) as total_appointments,
    SUM(CASE WHEN Status='' OR Status IS NULL THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN Status='1' THEN 1 ELSE 0 END) as accepted,
    SUM(CASE WHEN Status='2' THEN 1 ELSE 0 END) as rejected
    FROM tblbook";

$stats_result = mysqli_query($con, $stats_query);

if (!$stats_result) {
    die("Database Error: " . mysqli_error($con));
}

$stats = mysqli_fetch_assoc($stats_result);
$totalappointment = $stats['total_appointments'];
$totalpending = $stats['pending'];
$totalaccepted = $stats['accepted'];
$totalrejected = $stats['rejected'];

$customers_query = "SELECT COUNT(*) as total FROM tbluser";
$customers_result = mysqli_query($con, $customers_query);
if (!$customers_result) {
    die("Database Error: " . mysqli_error($con));
}
$totalcustomers = mysqli_fetch_assoc($customers_result)['total'];

$enquiries_query = "SELECT COUNT(*) as total FROM tblcontact";
$enquiries_result = mysqli_query($con, $enquiries_query);
if (!$enquiries_result) {
    die("Database Error: " . mysqli_error($con));
}
$totalenquiries = mysqli_fetch_assoc($enquiries_result)['total'];

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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BPMS | Staff Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="css/bootstrap.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/font-awesome.css" rel="stylesheet">
    <link href="css/dashboard.css" rel="stylesheet">

    <script src="js/jquery-1.11.1.min.js"></script>
    <script src="js/modernizr.custom.js"></script>
    
    <style>
        /* Dashboard KPI Cards - Uniform Styling */
        .kpi-card {
            background: #fff;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            height: 140px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .kpi-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .kpi-card .kpi-label {
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
            opacity: 0.8;
        }
        
        .kpi-card .kpi-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 15px;
        }
        
        .kpi-card .kpi-value {
            font-size: 36px;
            font-weight: 700;
            line-height: 1;
        }
        
        /* Color variants */
        .kpi-pending {
            border-left: 4px solid #f0ad4e;
        }
        
        .kpi-pending .kpi-label,
        .kpi-pending .kpi-title {
            color: #f0ad4e;
        }
        
        .kpi-accepted {
            border-left: 4px solid #5cb85c;
        }
        
        .kpi-accepted .kpi-label,
        .kpi-accepted .kpi-title {
            color: #5cb85c;
        }
        
        .kpi-rejected {
            border-left: 4px solid #d9534f;
        }
        
        .kpi-rejected .kpi-label,
        .kpi-rejected .kpi-title {
            color: #d9534f;
        }
        
        .kpi-customers {
            border-left: 4px solid #5bc0de;
        }
        
        .kpi-customers .kpi-label,
        .kpi-customers .kpi-title {
            color: #5bc0de;
        }
        
        .kpi-enquiries {
            border-left: 4px solid #5e5ce6;
        }
        
        .kpi-enquiries .kpi-label,
        .kpi-enquiries .kpi-title {
            color: #5e5ce6;
        }
        
        .kpi-total {
            border-left: 4px solid #337ab7;
        }
        
        .kpi-total .kpi-label,
        .kpi-total .kpi-title {
            color: #337ab7;
        }

        /* Inventory specific colors */
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
        
        /* Section headers */
        .section-header {
            margin: 30px 0 20px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #e7e7e7;
        }
        
        .section-header h3 {
            margin: 0;
            font-size: 22px;
            font-weight: 600;
            color: #333;
        }
        
        /* Table styling improvements */
        .table-responsive {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .table > thead > tr > th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }
        
        .table > tbody > tr:hover {
            background-color: #f8f9fa;
        }
        
        /* Ensure equal column widths */
        .equal-height-row {
            display: flex;
            flex-wrap: wrap;
            margin-left: -15px;
            margin-right: -15px;
        }
        
        .equal-height-row > [class*='col-'] {
            display: flex;
            flex-direction: column;
        }
        
        .no-appointments, .no-inventory {
            text-align: center;
            padding: 40px;
            color: #999;
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
        }
    </style>
</head>

<body class="cbp-spmenu-push">
<div class="main-content">

    <?php include_once('includes/sidebar.php'); ?>
    <?php include_once('includes/header.php'); ?>

    <div id="page-wrapper">
        <div class="main-page">
            
            <div class="section-header">
                <h3>Appointments Overview</h3>
            </div>
            
            <div class="row equal-height-row">
                
                <div class="col-md-3 col-sm-6">
                    <div class="kpi-card kpi-total">
                        <div>
                            <div class="kpi-label">Total</div>
                            <div class="kpi-title">Appointments</div>
                        </div>
                        <div class="kpi-value"><?php echo htmlspecialchars($totalappointment); ?></div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="kpi-card kpi-pending">
                        <div>
                            <div class="kpi-label">Total</div>
                            <div class="kpi-title">Pending</div>
                        </div>
                        <div class="kpi-value"><?php echo htmlspecialchars($totalpending); ?></div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="kpi-card kpi-accepted">
                        <div>
                            <div class="kpi-label">Total</div>
                            <div class="kpi-title">Accepted</div>
                        </div>
                        <div class="kpi-value"><?php echo htmlspecialchars($totalaccepted); ?></div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="kpi-card kpi-rejected">
                        <div>
                            <div class="kpi-label">Total</div>
                            <div class="kpi-title">Rejected</div>
                        </div>
                        <div class="kpi-value"><?php echo htmlspecialchars($totalrejected); ?></div>
                    </div>
                </div>
            </div>

            <div class="section-header">
                <h3>Business Metrics</h3>
            </div>
            
            <div class="row equal-height-row">
                
                <div class="col-md-6 col-sm-6">
                    <div class="kpi-card kpi-customers">
                        <div>
                            <div class="kpi-label">Total</div>
                            <div class="kpi-title">Customers</div>
                        </div>
                        <div class="kpi-value"><?php echo htmlspecialchars($totalcustomers); ?></div>
                    </div>
                </div>

                <div class="col-md-6 col-sm-6">
                    <div class="kpi-card kpi-enquiries">
                        <div>
                            <div class="kpi-label">Total</div>
                            <div class="kpi-title">Enquiries</div>
                        </div>
                        <div class="kpi-value"><?php echo htmlspecialchars($totalenquiries); ?></div>
                    </div>
                </div>
            </div>

            <div class="section-header">
                <h3>Inventory Management</h3>
            </div>
            
            <div class="row equal-height-row">
                
                <div class="col-md-3 col-sm-6">
                    <div class="kpi-card kpi-products">
                        <div>
                            <div class="kpi-label">Total</div>
                            <div class="kpi-title">Products</div>
                        </div>
                        <div class="kpi-value"><?php echo htmlspecialchars($totalproducts); ?></div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="kpi-card kpi-stock">
                        <div>
                            <div class="kpi-label">Total</div>
                            <div class="kpi-title">Stock Units</div>
                        </div>
                        <div class="kpi-value"><?php echo htmlspecialchars($totalstock); ?></div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="kpi-card kpi-lowstock">
                        <div>
                            <div class="kpi-label">Total</div>
                            <div class="kpi-title">Low Stock</div>
                        </div>
                        <div class="kpi-value"><?php echo htmlspecialchars($lowstock); ?></div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="kpi-card kpi-outofstock">
                        <div>
                            <div class="kpi-label">Total</div>
                            <div class="kpi-title">Out of Stock</div>
                        </div>
                        <div class="kpi-value"><?php echo htmlspecialchars($outofstock); ?></div>
                    </div>
                </div>
            </div>

            <div class="row" style="margin-bottom: 20px;">
                <div class="col-md-12">
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

            <div class="section-header">
                <h3>Inventory Alerts</h3>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>Product Name</th>
                        <th>Category</th>
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
                            <td><?php echo $cnt; ?></td>
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

            <div class="row" style="margin: 30px 0 20px 0;">
                <div class="col-md-12">
                    <a href="add-walkin.php" class="btn btn-success btn-lg">
                        <i class="fa fa-plus-circle"></i> Add Walk-In Appointment
                    </a>
                </div>
            </div>
            
            <div class="section-header">
                <h3>Recent Appointments</h3>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>Appointment No.</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th style="width: 120px; text-align: center;">Status</th>
                        <th style="width: 100px; text-align: center;">Action</th>
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
                    
                    $result = mysqli_query($con, $query);
                    
                    if (!$result) {
                        echo '<tr><td colspan="6" class="text-center text-danger">Error loading appointments: ' . mysqli_error($con) . '</td></tr>';
                    } else {
                        if (mysqli_num_rows($result) == 0) {
                            echo '<tr><td colspan="6" class="no-appointments">No recent appointments found</td></tr>';
                        } else {
                            $cnt = 1;
                            while ($row = mysqli_fetch_assoc($result)) {
                                $statusValue = $row['Status'];
                    ?>
                        <tr>
                            <td><?php echo $cnt; ?></td>

                            <td>
                                <?php
                                echo ($row['AptNumber'] == 0)
                                    ? '<span class="label label-default">Walk-in</span>'
                                    : htmlspecialchars($row['AptNumber']);
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
                                   class="btn btn-primary btn-sm">
                                    <i class="fa fa-eye"></i> View
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

    <?php include_once('includes/footer.php'); ?>
</div>

<script src="js/classie.js"></script>
<script>
    var menuLeft = document.getElementById('cbp-spmenu-s1'),
        showLeftPush = document.getElementById('showLeftPush'),
        body = document.body;

    if (showLeftPush) {
        showLeftPush.onclick = function () {
            classie.toggle(this, 'active');
            classie.toggle(body, 'cbp-spmenu-push-toright');
            classie.toggle(menuLeft, 'cbp-spmenu-open');
        };
    }
</script>

<script src="js/jquery.nicescroll.js"></script>
<script src="js/scripts.js"></script>
<script src="js/bootstrap.js"></script>

</body>
</html>