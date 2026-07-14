<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('includes/dbconnection.php');

if (!isset($_SESSION['staff_id']) || $_SESSION['staff_id'] == '') {
    header('location:logout.php');
    exit();
}

$stats_query = "SELECT 
    COUNT(*) as total_products,
    SUM(Quantity) as total_stock,
    SUM(CASE WHEN Quantity <= ReorderLevel THEN 1 ELSE 0 END) as low_stock,
    SUM(CASE WHEN Quantity = 0 THEN 1 ELSE 0 END) as out_of_stock,
    SUM(CASE WHEN Price IS NOT NULL AND Price > 0 THEN Quantity * Price ELSE 0 END) as total_value
    FROM tblinventory";

$stats_result = mysqli_query($con, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

$category_query = "SELECT 
    Category,
    COUNT(*) as product_count,
    SUM(Quantity) as total_quantity,
    SUM(CASE WHEN Price IS NOT NULL THEN Quantity * Price ELSE 0 END) as category_value
    FROM tblinventory
    GROUP BY Category
    ORDER BY product_count DESC";

$category_result = mysqli_query($con, $category_query);

$products_query = "SELECT * FROM tblinventory ORDER BY Category, ProductName";
$products_result = mysqli_query($con, $products_query);
?>

<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BPMS | Stock Report</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="css/bootstrap.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/font-awesome.css" rel="stylesheet">

    <script src="js/jquery-1.11.1.min.js"></script>
    
    <style>
        .page-header {
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e7e7e7;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-header h2 {
            margin: 0;
            color: #333;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-card .stat-label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            color: #777;
            margin-bottom: 10px;
        }

        .stat-card .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #333;
        }

        .stat-card.primary {
            border-top: 4px solid #337ab7;
        }

        .stat-card.success {
            border-top: 4px solid #5cb85c;
        }

        .stat-card.warning {
            border-top: 4px solid #f0ad4e;
        }

        .stat-card.danger {
            border-top: 4px solid #d9534f;
        }

        .stat-card.info {
            border-top: 4px solid #5bc0de;
        }

        .report-section {
            background: #fff;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .report-section h4 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #333;
            font-weight: 600;
            border-bottom: 2px solid #e7e7e7;
            padding-bottom: 10px;
        }

        .table > thead > tr > th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
        }

        .table > tbody > tr:hover {
            background-color: #f8f9fa;
        }

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

        .print-hide {
            display: block;
        }

        @media print {
            .print-hide {
                display: none;
            }

            .page-header {
                border-bottom: 2px solid #000;
            }

            .stat-card,
            .report-section {
                box-shadow: none;
                border: 1px solid #ddd;
            }

            body {
                background: white;
            }

            .main-content {
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>

<body class="cbp-spmenu-push">
<div class="main-content">

    <div class="print-hide">
        <?php include_once('includes/sidebar.php'); ?>
        <?php include_once('includes/header.php'); ?>
    </div>

    <div id="page-wrapper">
        <div class="main-page">
            <div class="page-header">
                <h2><i class="fa fa-file-text"></i> Stock Report</h2>
                <div class="print-hide">
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="fa fa-print"></i> Print Report
                    </button>
                    <a href="manage-inventory.php" class="btn btn-default">
                        <i class="fa fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>

            <div style="text-align: center; margin-bottom: 20px;">
                <p><strong>Generated:</strong> <?php echo date('F d, Y h:i A'); ?></p>
            </div>

            <div class="stats-grid">
                <div class="stat-card primary">
                    <div class="stat-label">Total Products</div>
                    <div class="stat-value"><?php echo $stats['total_products']; ?></div>
                </div>

                <div class="stat-card success">
                    <div class="stat-label">Total Stock Units</div>
                    <div class="stat-value"><?php echo number_format($stats['total_stock']); ?></div>
                </div>

                <div class="stat-card warning">
                    <div class="stat-label">Low Stock Items</div>
                    <div class="stat-value"><?php echo $stats['low_stock']; ?></div>
                </div>

                <div class="stat-card danger">
                    <div class="stat-label">Out of Stock</div>
                    <div class="stat-value"><?php echo $stats['out_of_stock']; ?></div>
                </div>

                <div class="stat-card info">
                    <div class="stat-label">Total Inventory Value</div>
                    <div class="stat-value" style="font-size: 24px;">
                        ₱<?php echo number_format($stats['total_value'], 2); ?>
                    </div>
                </div>
            </div>

            <div class="report-section">
                <h4><i class="fa fa-pie-chart"></i> Inventory by Category</h4>
                
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>Category</th>
                            <th style="text-align: center;">Products</th>
                            <th style="text-align: center;">Total Units</th>
                            <th style="text-align: right;">Total Value</th>
                        </tr>
                        </thead>
                        <tbody>

                        <?php
                        if ($category_result && mysqli_num_rows($category_result) > 0) {
                            while ($cat = mysqli_fetch_assoc($category_result)) {
                        ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($cat['Category']); ?></strong></td>
                                <td style="text-align: center;"><?php echo $cat['product_count']; ?></td>
                                <td style="text-align: center;"><?php echo number_format($cat['total_quantity']); ?></td>
                                <td style="text-align: right;">₱<?php echo number_format($cat['category_value'], 2); ?></td>
                            </tr>
                        <?php
                            }
                        }
                        ?>

                        </tbody>
                    </table>
                </div>
            </div>

            <div class="report-section">
                <h4><i class="fa fa-list"></i> Complete Inventory List</h4>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th style="text-align: center;">Stock</th>
                            <th style="text-align: center;">Reorder</th>
                            <th style="text-align: right;">Unit Price</th>
                            <th style="text-align: right;">Total Value</th>
                            <th style="text-align: center;">Status</th>
                        </tr>
                        </thead>
                        <tbody>

                        <?php
                        if ($products_result && mysqli_num_rows($products_result) > 0) {
                            $cnt = 1;
                            $current_category = '';
                            
                            while ($product = mysqli_fetch_assoc($products_result)) {
                                $quantity = (int)$product['Quantity'];
                                $reorderLevel = (int)$product['ReorderLevel'];
                                $price = (float)$product['Price'];
                                $total_value = $quantity * $price;

                                if ($current_category != $product['Category']) {
                                    $current_category = $product['Category'];
                                    echo '<tr style="background: #e3f2fd;">';
                                    echo '<td colspan="8" style="font-weight: 700; padding: 12px;">';
                                    echo '<i class="fa fa-folder-open"></i> ' . htmlspecialchars($current_category);
                                    echo '</td></tr>';
                                }
                        ?>
                            <tr>
                                <td><?php echo $cnt; ?></td>
                                <td><?php echo htmlspecialchars($product['ProductName']); ?></td>
                                <td><?php echo htmlspecialchars($product['Category']); ?></td>
                                <td style="text-align: center;">
                                    <strong><?php echo $quantity; ?></strong> <?php echo htmlspecialchars($product['Unit']); ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php echo $reorderLevel; ?> <?php echo htmlspecialchars($product['Unit']); ?>
                                </td>
                                <td style="text-align: right;">
                                    <?php echo $price > 0 ? '₱' . number_format($price, 2) : '-'; ?>
                                </td>
                                <td style="text-align: right;">
                                    <?php echo $price > 0 ? '₱' . number_format($total_value, 2) : '-'; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php
                                    if ($quantity == 0) {
                                        echo '<span class="stock-indicator stock-out"></span>';
                                        echo '<span class="label label-danger">Out</span>';
                                    } elseif ($quantity <= $reorderLevel) {
                                        echo '<span class="stock-indicator stock-low"></span>';
                                        echo '<span class="label label-warning">Low</span>';
                                    } else {
                                        echo '<span class="stock-indicator stock-ok"></span>';
                                        echo '<span class="label label-success">OK</span>';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php
                                $cnt++;
                            }
                        } else {
                            echo '<tr><td colspan="8" style="text-align: center; padding: 40px;">No products in inventory</td></tr>';
                        }
                        ?>

                        </tbody>
                    </table>
                </div>
            </div>

            <div style="text-align: center; margin-top: 40px; padding-top: 20px; border-top: 2px solid #e7e7e7;">
                <p style="color: #999;">
                    <small>This report was generated by the Beauty Parlour Management System</small>
                </p>
            </div>

        </div>
    </div>

    <div class="print-hide">
        <?php include_once('includes/footer.php'); ?>
    </div>
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

<script src="js/bootstrap.js"></script>

</body>
</html>