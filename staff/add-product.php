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
        $query = "INSERT INTO tblinventory (ProductName, Category, Quantity, ReorderLevel, Unit, Price, Supplier) 
                  VALUES ('$productname', '$category', $quantity, $reorderlevel, '$unit', $price, '$supplier')";
        
        $result = mysqli_query($con, $query);
        
        if ($result) {
            $msg = "Product added successfully!";
            $_POST = array();
        } else {
            $error = "Error adding product: " . mysqli_error($con);
        }
    }
}
?>

<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BPMS | Add Product</title>
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
    </style>
</head>

<body class="cbp-spmenu-push">
<div class="main-content">

    <?php include_once('includes/sidebar.php'); ?>
    <?php include_once('includes/header.php'); ?>

    <div id="page-wrapper">
        <div class="main-page">
            <div class="page-header">
                <h2><i class="fa fa-plus-circle"></i> Add New Product</h2>
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

            <div class="form-container">
                <form method="post" action="">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="productname" class="required">Product Name</label>
                                <input type="text" class="form-control" id="productname" name="productname" 
                                       placeholder="Enter product name" required
                                       value="<?php echo isset($_POST['productname']) ? htmlspecialchars($_POST['productname']) : ''; ?>">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category" class="required">Category</label>
                                <select class="form-control" id="category" name="category" required>
                                    <option value="">Select Category</option>
                                    <option value="Hair Products">Hair Products</option>
                                    <option value="Skin Care">Skin Care</option>
                                    <option value="Nail Care">Nail Care</option>
                                    <option value="Massage Oils">Massage Oils</option>
                                    <option value="Tools & Equipment">Tools & Equipment</option>
                                    <option value="Sanitization">Sanitization</option>
                                    <option value="Other">Other</option>
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
                                       value="<?php echo isset($_POST['quantity']) ? htmlspecialchars($_POST['quantity']) : ''; ?>">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="unit" class="required">Unit</label>
                                <select class="form-control" id="unit" name="unit" required>
                                    <option value="units">Units</option>
                                    <option value="bottles">Bottles</option>
                                    <option value="boxes">Boxes</option>
                                    <option value="liters">Liters</option>
                                    <option value="kg">Kilograms</option>
                                    <option value="pieces">Pieces</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="reorderlevel" class="required">Reorder Level</label>
                                <input type="number" class="form-control" id="reorderlevel" name="reorderlevel" 
                                       placeholder="Minimum stock level" min="0" required
                                       value="<?php echo isset($_POST['reorderlevel']) ? htmlspecialchars($_POST['reorderlevel']) : '10'; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="price">Price per Unit</label>
                                <input type="number" class="form-control" id="price" name="price" 
                                       placeholder="0.00" step="0.01" min="0"
                                       value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="supplier">Supplier</label>
                                <input type="text" class="form-control" id="supplier" name="supplier" 
                                       placeholder="Supplier name"
                                       value="<?php echo isset($_POST['supplier']) ? htmlspecialchars($_POST['supplier']) : ''; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" name="submit" class="btn btn-primary btn-lg btn-action">
                                <i class="fa fa-save"></i> Add Product
                            </button>
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