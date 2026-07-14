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

if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    $delete_query = "DELETE FROM tblinventory WHERE ID = $delete_id";
    
    if (mysqli_query($con, $delete_query)) {
        $msg = "Product deleted successfully!";
    } else {
        $error = "Error deleting product: " . mysqli_error($con);
    }
}

$search = isset($_GET['search']) ? trim(mysqli_real_escape_string($con, $_GET['search'])) : '';
$category_filter = isset($_GET['category']) ? trim(mysqli_real_escape_string($con, $_GET['category'])) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';

$where_conditions = array();

if (!empty($search) && $search !== '') {
    $where_conditions[] = "(ProductName LIKE '%$search%' OR Supplier LIKE '%$search%')";
}

if (!empty($category_filter) && $category_filter !== '') {
    $where_conditions[] = "Category = '$category_filter'";
}

if (!empty($status_filter) && $status_filter !== '') {
    if ($status_filter == 'out_of_stock') {
        $where_conditions[] = "Quantity = 0";
    } elseif ($status_filter == 'low_stock') {
        $where_conditions[] = "Quantity > 0 AND Quantity <= ReorderLevel";
    } elseif ($status_filter == 'in_stock') {
        $where_conditions[] = "Quantity > ReorderLevel";
    }
}

$where_clause = '';
if (count($where_conditions) > 0) {
    $where_clause = "WHERE " . implode(' AND ', $where_conditions);
}

$query = "SELECT * FROM tblinventory $where_clause ORDER BY ProductName ASC";
$inventory_result = mysqli_query($con, $query);

if (!$inventory_result) {
    $error = "Error loading inventory: " . mysqli_error($con);
}
?>

<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BPMS | Manage Inventory</title>
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
        
        .filter-section {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .table-container {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
        
        .action-btn {
            margin-right: 5px;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
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
                <h2><i class="fa fa-list"></i> Manage Inventory</h2>
                <a href="add-product.php" class="btn btn-primary">
                    <i class="fa fa-plus-circle"></i> Add New Product
                </a>
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

            <div class="filter-section">
                <form method="get" action="">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="search">Search</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       placeholder="Product name or supplier"
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="category">Category</label>
                                <select class="form-control" id="category" name="category">
                                    <option value="">All Categories</option>
                                    <option value="Hair Products" <?php echo $category_filter == 'Hair Products' ? 'selected' : ''; ?>>Hair Products</option>
                                    <option value="Skin Care" <?php echo $category_filter == 'Skin Care' ? 'selected' : ''; ?>>Skin Care</option>
                                    <option value="Nail Care" <?php echo $category_filter == 'Nail Care' ? 'selected' : ''; ?>>Nail Care</option>
                                    <option value="Massage Oils" <?php echo $category_filter == 'Massage Oils' ? 'selected' : ''; ?>>Massage Oils</option>
                                    <option value="Tools & Equipment" <?php echo $category_filter == 'Tools & Equipment' ? 'selected' : ''; ?>>Tools & Equipment</option>
                                    <option value="Sanitization" <?php echo $category_filter == 'Sanitization' ? 'selected' : ''; ?>>Sanitization</option>
                                    <option value="Other" <?php echo $category_filter == 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="status">Stock Status</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="">All Status</option>
                                    <option value="in_stock" <?php echo $status_filter == 'in_stock' ? 'selected' : ''; ?>>In Stock</option>
                                    <option value="low_stock" <?php echo $status_filter == 'low_stock' ? 'selected' : ''; ?>>Low Stock</option>
                                    <option value="out_of_stock" <?php echo $status_filter == 'out_of_stock' ? 'selected' : ''; ?>>Out of Stock</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fa fa-filter"></i> Filter
                                </button>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($search) || !empty($category_filter) || !empty($status_filter)): ?>
                        <div class="row">
                            <div class="col-md-12">
                                <a href="manage-inventory.php" class="btn btn-default btn-sm">
                                    <i class="fa fa-times"></i> Clear Filters
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </form>
            </div>

            <div class="table-container">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th style="width: 100px; text-align: center;">Stock</th>
                            <th style="width: 100px; text-align: center;">Reorder</th>
                            <th style="width: 100px; text-align: center;">Price</th>
                            <th>Supplier</th>
                            <th style="width: 120px; text-align: center;">Status</th>
                            <th style="width: 200px; text-align: center;">Actions</th>
                        </tr>
                        </thead>
                        <tbody>

                        <?php
                        if ($inventory_result && mysqli_num_rows($inventory_result) > 0) {
                            $cnt = 1;
                            while ($row = mysqli_fetch_assoc($inventory_result)) {
                                $quantity = (int)$row['Quantity'];
                                $reorderLevel = (int)$row['ReorderLevel'];
                        ?>
                            <tr>
                                <td><?php echo $cnt; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['ProductName']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['Category']); ?></td>
                                <td style="text-align: center;">
                                    <strong><?php echo $quantity; ?></strong> 
                                    <?php echo htmlspecialchars($row['Unit']); ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php echo $reorderLevel; ?> 
                                    <?php echo htmlspecialchars($row['Unit']); ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php echo $row['Price'] ? '₱' . number_format($row['Price'], 2) : '-'; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['Supplier'] ?? '-'); ?></td>
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
                                <td style="text-align: center;">
                                    <a href="view-product.php?id=<?php echo $row['ID']; ?>" 
                                       class="btn btn-info btn-xs action-btn" title="View">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <a href="edit-product.php?id=<?php echo $row['ID']; ?>" 
                                       class="btn btn-primary btn-xs action-btn" title="Edit">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <a href="restock.php?id=<?php echo $row['ID']; ?>" 
                                       class="btn btn-success btn-xs action-btn" title="Restock">
                                        <i class="fa fa-plus"></i>
                                    </a>
                                    <a href="?delete=<?php echo $row['ID']; ?>" 
                                       class="btn btn-danger btn-xs action-btn" 
                                       onclick="return confirm('Are you sure you want to delete this product?');" 
                                       title="Delete">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php
                                $cnt++;
                            }
                        } else {
                            echo '<tr><td colspan="9" class="no-data">No products found</td></tr>';
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