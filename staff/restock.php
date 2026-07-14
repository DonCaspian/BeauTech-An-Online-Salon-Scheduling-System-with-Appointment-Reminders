<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('includes/dbconnection.php');

if (!isset($_SESSION['staff_id']) || $_SESSION['staff_id'] == '') {
    header('location:logout.php');
    exit();
}

$msg = '';
$error = '';
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

if (isset($_POST['submit'])) {
    $add_quantity = (int)$_POST['add_quantity'];
    $notes = mysqli_real_escape_string($con, $_POST['notes']);

    if ($add_quantity <= 0) {
        $error = "Please enter a valid quantity to add.";
    } else {
        $new_quantity = $product['Quantity'] + $add_quantity;
        
        $update_query = "UPDATE tblinventory SET Quantity = $new_quantity WHERE ID = $product_id";
        $update_result = mysqli_query($con, $update_query);
        
        if ($update_result) {
            
            $staff_id = $_SESSION['staff_id'];
            $log_query = "INSERT INTO tblrestock_log (ProductID, StaffID, QuantityAdded, NewQuantity, Notes, RestockDate) 
                         VALUES ($product_id, '$staff_id', $add_quantity, $new_quantity, '$notes', NOW())";
            mysqli_query($con, $log_query);
            
            $msg = "Product restocked successfully! Added $add_quantity " . htmlspecialchars($product['Unit']) . ". New quantity: $new_quantity";
            
            $result = mysqli_query($con, "SELECT * FROM tblinventory WHERE ID = $product_id");
            $product = mysqli_fetch_assoc($result);
        } else {
            $error = "Error restocking product: " . mysqli_error($con);
        }
    }
}
?>

<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BPMS | Restock Product</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="css/bootstrap.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/font-awesome.css" rel="stylesheet">

    <script src="js/jquery-1.11.1.min.js"></script>
    
    <style>
        .form-container {
            background: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        
        .page-header {
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e7e7e7;
        }
        
        .page-header h2 {
            margin: 0;
            color: #333;
        }
        
        .product-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .product-info h4 {
            margin-top: 0;
            color: #333;
            font-weight: 600;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #555;
        }

        .info-value {
            color: #333;
        }

        .stock-status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
        }

        .status-low {
            background: #f0ad4e;
            color: white;
        }

        .status-out {
            background: #d9534f;
            color: white;
        }

        .status-ok {
            background: #5cb85c;
            color: white;
        }
        
        .form-group label {
            font-weight: 600;
            color: #555;
        }
        
        .required:after {
            content: " *";
            color: red;
        }
        
        .alert {
            border-radius: 4px;
        }
        
        .btn-action {
            margin-right: 10px;
        }

        .calculation-box {
            background: #e8f5e9;
            padding: 15px;
            border-radius: 4px;
            margin-top: 15px;
        }

        .calculation-box .calc-label {
            font-weight: 600;
            color: #2e7d32;
        }

        #new_quantity_display {
            font-size: 24px;
            font-weight: 700;
            color: #2e7d32;
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
                <h2><i class="fa fa-plus-circle"></i> Restock Product</h2>
            </div>

            <?php if ($msg): ?>
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <strong>Success!</strong> <?php echo $msg; ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <strong>Error!</strong> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="product-info">
                <h4><i class="fa fa-cube"></i> <?php echo htmlspecialchars($product['ProductName']); ?></h4>
                
                <div class="info-row">
                    <span class="info-label">Category:</span>
                    <span class="info-value"><?php echo htmlspecialchars($product['Category']); ?></span>
                </div>

                <div class="info-row">
                    <span class="info-label">Current Stock:</span>
                    <span class="info-value">
                        <strong style="font-size: 18px;"><?php echo $product['Quantity']; ?></strong> 
                        <?php echo htmlspecialchars($product['Unit']); ?>
                    </span>
                </div>

                <div class="info-row">
                    <span class="info-label">Reorder Level:</span>
                    <span class="info-value"><?php echo $product['ReorderLevel']; ?> <?php echo htmlspecialchars($product['Unit']); ?></span>
                </div>

                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value">
                        <?php
                        $quantity = (int)$product['Quantity'];
                        $reorderLevel = (int)$product['ReorderLevel'];
                        
                        if ($quantity == 0) {
                            echo '<span class="stock-status status-out">OUT OF STOCK</span>';
                        } elseif ($quantity <= $reorderLevel) {
                            echo '<span class="stock-status status-low">LOW STOCK</span>';
                        } else {
                            echo '<span class="stock-status status-ok">IN STOCK</span>';
                        }
                        ?>
                    </span>
                </div>

                <?php if ($product['Supplier']): ?>
                <div class="info-row">
                    <span class="info-label">Supplier:</span>
                    <span class="info-value"><?php echo htmlspecialchars($product['Supplier']); ?></span>
                </div>
                <?php endif; ?>
            </div>

            <div class="form-container">
                <form method="post" action="" id="restockForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="add_quantity" class="required">Quantity to Add</label>
                                <input type="number" class="form-control" id="add_quantity" name="add_quantity" 
                                       placeholder="Enter quantity" min="1" required
                                       onkeyup="calculateNewQuantity()">
                                <small class="help-block">Enter the number of <?php echo htmlspecialchars($product['Unit']); ?> to add to current stock</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Unit</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($product['Unit']); ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="calculation-box" id="calculationBox" style="display: none;">
                        <span class="calc-label">New Stock Level:</span>
                        <div id="new_quantity_display">0</div>
                        <?php echo htmlspecialchars($product['Unit']); ?>
                    </div>

                    <div class="row" style="margin-top: 20px;">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="notes">Notes (Optional)</label>
                                <textarea class="form-control" id="notes" name="notes" 
                                          rows="3" placeholder="Enter any notes about this restock (supplier, invoice number, etc.)"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" name="submit" class="btn btn-success btn-lg btn-action">
                                <i class="fa fa-check"></i> Confirm Restock
                            </button>
                            <a href="edit-product.php?id=<?php echo $product_id; ?>" class="btn btn-primary btn-lg btn-action">
                                <i class="fa fa-edit"></i> Edit Product
                            </a>
                            <a href="manage-inventory.php" class="btn btn-default btn-lg">
                                <i class="fa fa-arrow-left"></i> Back to Inventory
                            </a>
                        </div>
                    </div>
                </form>
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

    function calculateNewQuantity() {
        var currentStock = <?php echo $product['Quantity']; ?>;
        var addQuantity = parseInt(document.getElementById('add_quantity').value) || 0;
        var newQuantity = currentStock + addQuantity;
        
        if (addQuantity > 0) {
            document.getElementById('calculationBox').style.display = 'block';
            document.getElementById('new_quantity_display').textContent = newQuantity;
        } else {
            document.getElementById('calculationBox').style.display = 'none';
        }
    }
</script>

<script src="js/bootstrap.js"></script>

</body>
</html>