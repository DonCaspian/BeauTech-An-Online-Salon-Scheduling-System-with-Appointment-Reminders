<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('includes/dbconnection.php');

if (!isset($_SESSION['bpmsaid']) || $_SESSION['bpmsaid'] == '') {
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
    $productname = mysqli_real_escape_string($con, $_POST['productname']);
    $category = mysqli_real_escape_string($con, $_POST['category']);
    $quantity = (int)$_POST['quantity'];
    $reorderlevel = (int)$_POST['reorderlevel'];
    $unit = mysqli_real_escape_string($con, $_POST['unit']);
    $price = (float)$_POST['price'];
    $supplier = mysqli_real_escape_string($con, $_POST['supplier']);

    if (empty($productname) || empty($category) || $quantity < 0) {
        $error = "Please fill all required fields with valid data.";
    } else {
        $update_query = "UPDATE tblinventory SET 
                        ProductName = '$productname',
                        Category = '$category',
                        Quantity = $quantity,
                        ReorderLevel = $reorderlevel,
                        Unit = '$unit',
                        Price = $price,
                        Supplier = '$supplier'
                        WHERE ID = $product_id";
        
        $update_result = mysqli_query($con, $update_query);
        
        if ($update_result) {
            $msg = "Product updated successfully!";
            
            $result = mysqli_query($con, "SELECT * FROM tblinventory WHERE ID = $product_id");
            $product = mysqli_fetch_assoc($result);
        } else {
            $error = "Error updating product: " . mysqli_error($con);
        }
    }
}
?>

<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BPMS | Edit Product</title>
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

        .info-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .info-box .info-label {
            font-weight: 600;
            color: #555;
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
                <h2><i class="fa fa-edit"></i> Edit Product</h2>
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

            <div class="info-box">
                <span class="info-label">Product ID:</span> <?php echo $product['ID']; ?> | 
                <span class="info-label">Created:</span> <?php echo date('M d, Y', strtotime($product['CreatedDate'])); ?> | 
                <span class="info-label">Last Updated:</span> <?php echo date('M d, Y h:i A', strtotime($product['UpdatedDate'])); ?>
            </div>

            <div class="form-container">
                <form method="post" action="">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="productname" class="required">Product Name</label>
                                <input type="text" class="form-control" id="productname" name="productname" 
                                       placeholder="Enter product name" required
                                       value="<?php echo htmlspecialchars($product['ProductName']); ?>">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category" class="required">Category</label>
                                <select class="form-control" id="category" name="category" required>
                                    <option value="">Select Category</option>
                                    <option value="Hair Products" <?php echo $product['Category'] == 'Hair Products' ? 'selected' : ''; ?>>Hair Products</option>
                                    <option value="Skin Care" <?php echo $product['Category'] == 'Skin Care' ? 'selected' : ''; ?>>Skin Care</option>
                                    <option value="Nail Care" <?php echo $product['Category'] == 'Nail Care' ? 'selected' : ''; ?>>Nail Care</option>
                                    <option value="Massage Oils" <?php echo $product['Category'] == 'Massage Oils' ? 'selected' : ''; ?>>Massage Oils</option>
                                    <option value="Tools & Equipment" <?php echo $product['Category'] == 'Tools & Equipment' ? 'selected' : ''; ?>>Tools & Equipment</option>
                                    <option value="Sanitization" <?php echo $product['Category'] == 'Sanitization' ? 'selected' : ''; ?>>Sanitization</option>
                                    <option value="Other" <?php echo $product['Category'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="quantity" class="required">Quantity</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" 
                                       placeholder="0" min="0" required
                                       value="<?php echo $product['Quantity']; ?>">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="unit" class="required">Unit</label>
                                <select class="form-control" id="unit" name="unit" required>
                                    <option value="units" <?php echo $product['Unit'] == 'units' ? 'selected' : ''; ?>>Units</option>
                                    <option value="bottles" <?php echo $product['Unit'] == 'bottles' ? 'selected' : ''; ?>>Bottles</option>
                                    <option value="boxes" <?php echo $product['Unit'] == 'boxes' ? 'selected' : ''; ?>>Boxes</option>
                                    <option value="liters" <?php echo $product['Unit'] == 'liters' ? 'selected' : ''; ?>>Liters</option>
                                    <option value="kg" <?php echo $product['Unit'] == 'kg' ? 'selected' : ''; ?>>Kilograms</option>
                                    <option value="pieces" <?php echo $product['Unit'] == 'pieces' ? 'selected' : ''; ?>>Pieces</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="reorderlevel" class="required">Reorder Level</label>
                                <input type="number" class="form-control" id="reorderlevel" name="reorderlevel" 
                                       placeholder="Minimum stock level" min="0" required
                                       value="<?php echo $product['ReorderLevel']; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="price">Price per Unit</label>
                                <input type="number" class="form-control" id="price" name="price" 
                                       placeholder="0.00" step="0.01" min="0"
                                       value="<?php echo $product['Price']; ?>">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="supplier">Supplier</label>
                                <input type="text" class="form-control" id="supplier" name="supplier" 
                                       placeholder="Supplier name"
                                       value="<?php echo htmlspecialchars($product['Supplier']); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" name="submit" class="btn btn-primary btn-lg btn-action">
                                <i class="fa fa-save"></i> Update Product
                            </button>
                            <a href="view-product.php?id=<?php echo $product_id; ?>" class="btn btn-info btn-lg btn-action">
                                <i class="fa fa-eye"></i> View Details
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
</script>

<script src="js/bootstrap.js"></script>

</body>
</html>