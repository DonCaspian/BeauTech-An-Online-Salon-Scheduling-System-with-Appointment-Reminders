<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('includes/dbconnection.php');

if (!isset($_SESSION['bpmsaid']) || $_SESSION['bpmsaid'] == '') {
    header('location:logout.php');
    exit();
}

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id == 0) {
    header('location:manage-inventory.php');
    exit();
}

$query = "SELECT * FROM tblinventory WHERE ID = $product_id";
$result = mysqli_query($con, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    header('location:manage-inventory.php');
    exit();
}

$product = mysqli_fetch_assoc($result);

$history_query = "SELECT * FROM tblrestock_log WHERE ProductID = $product_id ORDER BY RestockDate DESC LIMIT 10";
$history_result = mysqli_query($con, $history_query);
?>

<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BPMS | Product Details</title>
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
        }
        
        .page-header h2 {
            margin: 0;
            color: #333;
        }
        
        .product-card {
            background: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .product-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e7e7e7;
        }

        .product-title {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            margin: 0;
        }

        .stock-badge {
            font-size: 16px;
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: 600;
        }

        .badge-ok {
            background: #5cb85c;
            color: white;
        }

        .badge-low {
            background: #f0ad4e;
            color: white;
        }

        .badge-out {
            background: #d9534f;
            color: white;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        .detail-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 6px;
        }

        .detail-label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            color: #777;
            margin-bottom: 5px;
        }

        .detail-value {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }

        .description-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            margin-top: 20px;
        }

        .action-buttons {
            margin-top: 30px;
        }

        .action-buttons .btn {
            margin-right: 10px;
        }

        .history-table {
            margin-top: 30px;
        }

        .table > thead > tr > th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
        }

        .no-history {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .stock-indicator-large {
            display: inline-block;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            margin-right: 8px;
        }
    </style>
</head>

<body class="cbp-spmenu-push">
<div class="main-content">

    <?php include_once('includes/sidebar.php'); ?>
    <?php include_once('includes/header.php'); ?>

    <div id="page-wrapper">
        <div class="main-page">
            <div class="page-header">
                <h2><i class="fa fa-cube"></i> Product Details</h2>
            </div>

            <div class="product-card">
                <div class="product-header">
                    <h3 class="product-title"><?php echo htmlspecialchars($product['ProductName']); ?></h3>
                    <?php
                    $quantity = (int)$product['Quantity'];
                    $reorderLevel = (int)$product['ReorderLevel'];
                    
                    if ($quantity == 0) {
                        echo '<span class="stock-badge badge-out">';
                        echo '<span class="stock-indicator-large" style="background:#fff;"></span>';
                        echo 'OUT OF STOCK</span>';
                    } elseif ($quantity <= $reorderLevel) {
                        echo '<span class="stock-badge badge-low">';
                        echo '<span class="stock-indicator-large" style="background:#fff;"></span>';
                        echo 'LOW STOCK</span>';
                    } else {
                        echo '<span class="stock-badge badge-ok">';
                        echo '<span class="stock-indicator-large" style="background:#fff;"></span>';
                        echo 'IN STOCK</span>';
                    }
                    ?>
                </div>

                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Category</div>
                        <div class="detail-value"><?php echo htmlspecialchars($product['Category']); ?></div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">Product ID</div>
                        <div class="detail-value">#<?php echo $product['ID']; ?></div>
                    </div>

                    <div class="detail-item" style="background: <?php echo $quantity <= $reorderLevel ? '#fff3cd' : '#d4edda'; ?>">
                        <div class="detail-label">Current Stock</div>
                        <div class="detail-value" style="color: <?php echo $quantity <= $reorderLevel ? '#856404' : '#155724'; ?>">
                            <?php echo $quantity; ?> <?php echo htmlspecialchars($product['Unit']); ?>
                        </div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">Reorder Level</div>
                        <div class="detail-value"><?php echo $reorderLevel; ?> <?php echo htmlspecialchars($product['Unit']); ?></div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">Unit Type</div>
                        <div class="detail-value"><?php echo htmlspecialchars(ucfirst($product['Unit'])); ?></div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">Price per Unit</div>
                        <div class="detail-value">
                            <?php echo $product['Price'] ? '₱' . number_format($product['Price'], 2) : 'Not set'; ?>
                        </div>
                    </div>

                    <?php if ($product['Supplier']): ?>
                    <div class="detail-item">
                        <div class="detail-label">Supplier</div>
                        <div class="detail-value"><?php echo htmlspecialchars($product['Supplier']); ?></div>
                    </div>
                    <?php endif; ?>

                    <div class="detail-item">
                        <div class="detail-label">Total Value</div>
                        <div class="detail-value">
                            <?php 
                            if ($product['Price']) {
                                $total_value = $quantity * $product['Price'];
                                echo '₱' . number_format($total_value, 2);
                            } else {
                                echo 'N/A';
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <div class="description-box" style="background: #e3f2fd;">
                    <div style="display: flex; justify-content: space-between;">
                        <div>
                            <div class="detail-label">Created</div>
                            <div><?php echo date('M d, Y h:i A', strtotime($product['CreatedDate'])); ?></div>
                        </div>
                        <div>
                            <div class="detail-label">Last Updated</div>
                            <div><?php echo date('M d, Y h:i A', strtotime($product['UpdatedDate'])); ?></div>
                        </div>
                    </div>
                </div>

                <div class="action-buttons">
                    <a href="restock.php?id=<?php echo $product_id; ?>" class="btn btn-success btn-lg">
                        <i class="fa fa-plus-circle"></i> Restock Product
                    </a>
                    <a href="edit-product.php?id=<?php echo $product_id; ?>" class="btn btn-primary btn-lg">
                        <i class="fa fa-edit"></i> Edit Product
                    </a>
                    <a href="manage-inventory.php" class="btn btn-default btn-lg">
                        <i class="fa fa-arrow-left"></i> Back to Inventory
                    </a>
                </div>
            </div>

            <div class="product-card history-table">
                <h4><i class="fa fa-history"></i> Restock History</h4>
                
                <div class="table-responsive" style="margin-top: 20px;">
                    <table class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Date & Time</th>
                            <th>Quantity Added</th>
                            <th>New Stock Level</th>
                            <th>Notes</th>
                        </tr>
                        </thead>
                        <tbody>

                        <?php
                        if ($history_result && mysqli_num_rows($history_result) > 0) {
                            $cnt = 1;
                            while ($history = mysqli_fetch_assoc($history_result)) {
                        ?>
                            <tr>
                                <td><?php echo $cnt; ?></td>
                                <td><?php echo date('M d, Y h:i A', strtotime($history['RestockDate'])); ?></td>
                                <td>
                                    <span style="color: #5cb85c; font-weight: 600;">
                                        +<?php echo $history['QuantityAdded']; ?> <?php echo htmlspecialchars($product['Unit']); ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?php echo $history['NewQuantity']; ?></strong> <?php echo htmlspecialchars($product['Unit']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($history['Notes'] ?? '-'); ?></td>
                            </tr>
                        <?php
                                $cnt++;
                            }
                        } else {
                            echo '<tr><td colspan="5" class="no-history">No restock history available</td></tr>';
                        }
                        ?>

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