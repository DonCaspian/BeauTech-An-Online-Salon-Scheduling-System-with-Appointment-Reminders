<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
include('includes/auth.php');

if (strlen($_SESSION['bpmsaid']) == 0) {
    header('location:logout.php');
    exit();
}

if (isset($_POST['action']) && $_POST['action'] == 'toggle_availability') {
    header('Content-Type: application/json');
    
    $staff_id = intval($_POST['staff_id']);
    $current_status = mysqli_real_escape_string($con, $_POST['current_status']);
    
    $new_status = ($current_status == 'Active') ? 'Inactive' : 'Active';
    
    $query = mysqli_query($con, "UPDATE tblstaff SET Status='$new_status' WHERE ID='$staff_id'");
    
    if ($query) {
        echo json_encode([
            'success' => true, 
            'new_status' => $new_status,
            'message' => 'Staff availability updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to update availability'
        ]);
    }
    exit();
}

if (isset($_GET['delid'])) {
    $sid = intval($_GET['delid']);
    $query = mysqli_query($con, "DELETE FROM tblstaff WHERE ID='$sid'");
    if ($query) {
        echo "<script>alert('Staff member deleted successfully');</script>";
        echo "<script>window.location.href='edit-service-staff.php'</script>";
    }
}

if (isset($_POST['submit'])) {
    $staffid = intval($_POST['staff_id']);
    $staffname = mysqli_real_escape_string($con, $_POST['staffname']);
    $role = mysqli_real_escape_string($con, $_POST['role']);
    $status = $_POST['status'];
    
    $query = mysqli_query($con, "UPDATE tblstaff SET 
        StaffName='$staffname',
        Role='$role',
        Status='$status'
        WHERE ID='$staffid'");
    
    if ($query) {
        echo "<script>alert('Staff member updated successfully');</script>";
        echo "<script>window.location.href='edit-service-staff.php'</script>";
    } else {
        echo "<script>alert('Something went wrong. Please try again');</script>";
    }
}
?>

<!DOCTYPE HTML>
<html>
<head>
<title>BPMS | Manage Service Staff</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="css/bootstrap.css" rel='stylesheet' type='text/css' />

<link href="css/style.css" rel='stylesheet' type='text/css' />

<link href="css/font-awesome.css" rel="stylesheet">

<script src="js/jquery-1.11.1.min.js"></script>
<script src="js/modernizr.custom.js"></script>

<link href='//fonts.googleapis.com/css?family=Roboto+Condensed:400,300,300italic,400italic,700,700italic' rel='stylesheet' type='text/css'>
 
<script src="js/metisMenu.min.js"></script>
<script src="js/custom.js"></script>
<link href="css/custom.css" rel="stylesheet">

<style>
    .staff-card {
        background: #fff;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: transform 0.2s;
    }
    
    .staff-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .staff-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f0f0f0;
    }
    
    .staff-name {
        font-size: 20px;
        font-weight: 600;
        color: #333;
        margin: 0;
    }
    
    .staff-info {
        margin-bottom: 10px;
    }
    
    .staff-info label {
        font-weight: 600;
        color: #666;
        margin-right: 10px;
        min-width: 120px;
        display: inline-block;
    }
    
    .staff-actions {
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #f0f0f0;
        display: flex;
        gap: 10px;
        align-items: center;
    }
    
    .availability-toggle {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 30px;
    }
    
    .availability-toggle input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    
    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 30px;
    }
    
    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 22px;
        width: 22px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }
    
    input:checked + .toggle-slider {
        background-color: #5cb85c;
    }
    
    input:checked + .toggle-slider:before {
        transform: translateX(30px);
    }
    
    .status-badge {
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .status-active {
        background-color: #d4edda;
        color: #155724;
    }
    
    .status-inactive {
        background-color: #f8d7da;
        color: #721c24;
    }
    
    .page-header {
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 3px solid #5cb85c;
    }
    
    .page-header h2 {
        margin: 0;
        color: #333;
    }
    
    .add-staff-btn {
        margin-bottom: 20px;
    }
    
    .modal-header {
        background-color: #5cb85c;
        color: white;
    }
    
    .modal-header .close {
        color: white;
        opacity: 0.8;
    }
    
    .modal-header .close:hover {
        opacity: 1;
    }
    
    .form-group label {
        font-weight: 600;
        color: #555;
    }
    
    .no-staff {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }
    
    .no-staff i {
        font-size: 64px;
        margin-bottom: 20px;
        opacity: 0.3;
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
                <h2><i class="fa fa-users"></i> Manage Service Staff</h2>
            </div>
            
            <div class="add-staff-btn">
                <button class="btn btn-success btn-lg" data-toggle="modal" data-target="#addStaffModal">
                    <i class="fa fa-plus-circle"></i> Add New Staff Member
                </button>
            </div>
            
            <div class="row">
                <?php
                $ret = mysqli_query($con, "SELECT * FROM tblstaff ORDER BY StaffName ASC");
                $staff_count = mysqli_num_rows($ret);
                
                if ($staff_count > 0) {
                    while ($row = mysqli_fetch_array($ret)) {
                ?>
                <div class="col-md-6">
                    <div class="staff-card">
                        <div class="staff-header">
                            <h3 class="staff-name"><?php echo htmlspecialchars($row['StaffName']); ?></h3>
                            <span class="status-badge <?php echo ($row['Status'] == 'Active') ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo htmlspecialchars($row['Status']); ?>
                            </span>
                        </div>
                        
                        <div class="staff-info">
                            <label><i class="fa fa-star"></i> Role:</label>
                            <span><?php echo htmlspecialchars($row['Role']); ?></span>
                        </div>
                        
                        <div class="staff-actions">
                            <label style="margin: 0; display: flex; align-items: center;">
                                <span style="margin-right: 10px; font-weight: 600;">Availability:</span>
                                <label class="availability-toggle">
                                    <input type="checkbox" 
                                           class="availability-checkbox" 
                                           data-staff-id="<?php echo $row['ID']; ?>"
                                           data-current-status="<?php echo $row['Status']; ?>"
                                           <?php echo ($row['Status'] == 'Active') ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </label>
                            
                            <div style="margin-left: auto;">
                                <button class="btn btn-primary btn-sm" 
                                        onclick="editStaff(<?php echo $row['ID']; ?>, '<?php echo htmlspecialchars($row['StaffName']); ?>', '<?php echo htmlspecialchars($row['Role']); ?>', '<?php echo $row['Status']; ?>')">
                                    <i class="fa fa-edit"></i> Edit
                                </button>
                                <a href="edit-service-staff.php?delid=<?php echo $row['ID']; ?>" 
                                   class="btn btn-danger btn-sm" 
                                   onclick="return confirm('Are you sure you want to delete this staff member?');">
                                    <i class="fa fa-trash"></i> Delete
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                    }
                } else {
                ?>
                <div class="col-md-12">
                    <div class="no-staff">
                        <i class="fa fa-users"></i>
                        <h3>No Staff Members Found</h3>
                        <p>Click the "Add New Staff Member" button to get started.</p>
                    </div>
                </div>
                <?php } ?>
            </div>
            
        </div>
    </div>
    
    <?php include_once('includes/footer.php'); ?>
</div>

<div class="modal fade" id="addStaffModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-user-plus"></i> Add New Staff Member</h4>
            </div>
            <form method="post" action="add-service-staff.php">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Staff Name *</label>
                        <input type="text" class="form-control" name="staffname" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Role *</label>
                        <select class="form-control" name="role" required>
                            <option value="">Select Role</option>
                            <option value="Hair">Hair Specialist</option>
                            <option value="Nails">Nail Technician</option>
                            <option value="Makeup">Makeup Artist</option>
                            <option value="Facial">Facial Specialist</option>
                            <option value="Massage">Massage Therapist</option>
                            <option value="Waxing">Waxing Specialist</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Status *</label>
                        <select class="form-control" name="status" required>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="submit" class="btn btn-success">
                        <i class="fa fa-save"></i> Add Staff Member
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editStaffModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-edit"></i> Edit Staff Member</h4>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" id="edit_staff_id" name="staff_id">
                    
                    <div class="form-group">
                        <label>Staff Name *</label>
                        <input type="text" class="form-control" id="edit_staffname" name="staffname" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Role *</label>
                        <select class="form-control" id="edit_role" name="role" required>
                            <option value="">Select Role</option>
                            <option value="Hair">Hair Specialist</option>
                            <option value="Nails">Nail Technician</option>
                            <option value="Makeup">Makeup Artist</option>
                            <option value="Facial">Facial Specialist</option>
                            <option value="Massage">Massage Therapist</option>
                            <option value="Waxing">Waxing Specialist</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Status *</label>
                        <select class="form-control" id="edit_status" name="status" required>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Update Staff Member
                    </button>
                </div>
            </form>
        </div>
    </div>
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

<script>
// Handle availability toggle
$(document).on('change', '.availability-checkbox', function() {
    var checkbox = $(this);
    var staffId = checkbox.data('staff-id');
    var currentStatus = checkbox.data('current-status');
    
    $.ajax({
        url: 'edit-service-staff.php',
        method: 'POST',
        data: {
            action: 'toggle_availability',
            staff_id: staffId,
            current_status: currentStatus
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Update the data attribute
                checkbox.data('current-status', response.new_status);
                
                // Update the status badge
                var statusBadge = checkbox.closest('.staff-card').find('.status-badge');
                if (response.new_status == 'Active') {
                    statusBadge.removeClass('status-inactive').addClass('status-active');
                    statusBadge.text('Active');
                } else {
                    statusBadge.removeClass('status-active').addClass('status-inactive');
                    statusBadge.text('Inactive');
                }
                
                // Show success message
                showNotification('Success', response.message, 'success');
            } else {
                // Revert checkbox
                checkbox.prop('checked', currentStatus == 'Active');
                showNotification('Error', response.message, 'error');
            }
        },
        error: function() {
            // Revert checkbox
            checkbox.prop('checked', currentStatus == 'Active');
            showNotification('Error', 'Failed to update availability', 'error');
        }
    });
});

// Edit staff function
function editStaff(staffId, staffName, role, status) {
    $('#edit_staff_id').val(staffId);
    $('#edit_staffname').val(staffName);
    $('#edit_role').val(role);
    $('#edit_status').val(status);
    $('#editStaffModal').modal('show');
}

// Notification function
function showNotification(title, message, type) {
    var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    var alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible" style="position:fixed;top:70px;right:20px;z-index:9999;min-width:300px;">' +
                    '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                    '<strong>' + title + ':</strong> ' + message +
                    '</div>';
    
    $('body').append(alertHtml);
    
    setTimeout(function() {
        $('.alert').fadeOut('slow', function() {
            $(this).remove();
        });
    }, 3000);
}
</script>

</body>
</html>