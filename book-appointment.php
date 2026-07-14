<?php 
date_default_timezone_set('Asia/Manila');
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('includes/dbconnection.php');
include('includes/paymongo.php');
include('includes/sendAdminAppointmentEmail.php');

if (strlen($_SESSION['bpmsuid'])==0) {
  header('location:logout.php');
  exit();
} else {

if (isset($_POST['submit'])) {
    $uid = $_SESSION['bpmsuid'];
    $adate = mysqli_real_escape_string($con, $_POST['adate']);
    $slotid = mysqli_real_escape_string($con, $_POST['slotid']);
    $service = mysqli_real_escape_string($con, $_POST['service']);
    $msg = mysqli_real_escape_string($con, $_POST['message']);
    $staffID = mysqli_real_escape_string($con, $_POST['staff']);
    $aptnumber = mt_rand(100000000, 999999999);

    if (empty($staffID)) {
        echo "<script>alert('Please select a staff member.');</script>";
    } else {
        
        $slotquery = mysqli_query($con, "SELECT SlotTime FROM tbltimeslots WHERE ID='$slotid'");
        
        if (!$slotquery || mysqli_num_rows($slotquery) == 0) {
            echo "<script>alert('Invalid time slot selected.');</script>";
        } else {
            $slotrow = mysqli_fetch_array($slotquery);
            $atime = $slotrow['SlotTime'];
            
            $slotCheck = mysqli_query($con, "SELECT ID FROM tbltimeslots WHERE ID='$slotid' AND IsAvailable=1");
                
            if (mysqli_num_rows($slotCheck) == 0) {
                echo "<script>alert('Selected time slot is no longer available.');</script>";
            } else {
                date_default_timezone_set('Asia/Manila');
                $reminderDateTime = date('Y-m-d H:i:s', strtotime("$adate $atime -1 day"));
                
                $check = mysqli_query($con, "
                    SELECT ID FROM tblbook 
                    WHERE AptDate='$adate' 
                    AND AptTime='$atime' 
                    AND StaffID='$staffID'
                ");
                
                if (mysqli_num_rows($check) > 0) {
                    echo "<script>alert('Sorry, this staff member is already booked at this time. Please select another staff or time slot.');</script>";
                } else {
                    
                    $serviceQuery = mysqli_query($con, "SELECT Cost FROM tblservices WHERE ServiceName='$service'");
                    $serviceData = mysqli_fetch_array($serviceQuery);
                    $servicePrice = $serviceData['Cost'];
                    
                    $query = mysqli_query($con, "
                        INSERT INTO tblbook 
                        (UserID, AptNumber, AptDate, AptTime, Service, Message, StaffID, ReminderDateTime, payment_status, Status) 
                        VALUES 
                        ('$uid', '$aptnumber', '$adate', '$atime', '$service', '$msg', '$staffID', '$reminderDateTime', 'PENDING', 0)
                    ");

                    if ($query) {
                        $appointment_id = mysqli_insert_id($con);
                        $amount = (int)($servicePrice * 100); 
                        
                        $staffQuery = mysqli_query($con, "SELECT * FROM tblstaff WHERE ID='$staffID'");
                        if ($staffQuery && mysqli_num_rows($staffQuery) > 0) {
                            $staffData = mysqli_fetch_array($staffQuery);
                            if (isset($staffData['Name'])) {
                                $staffName = $staffData['Name'];
                            } elseif (isset($staffData['StaffName'])) {
                                $staffName = $staffData['StaffName'];
                            } elseif (isset($staffData['FullName'])) {
                                $staffName = $staffData['FullName'];
                            } elseif (isset($staffData['FirstName'])) {
                                $staffName = $staffData['FirstName'];
                            } else {
                                $staffName = 'Staff ID: ' . $staffID;
                            }
                        } else {
                            $staffName = 'Staff ID: ' . $staffID;
                        }
                        
                        $payload = [
                            "data" => [
                                "attributes" => [
                                    "line_items" => [[
                                        "name" => "Salon Appointment - " . $service,
                                        "amount" => $amount,
                                        "currency" => "PHP",
                                        "quantity" => 1,
                                        "description" => "Appointment #$aptnumber with $staffName on $adate at $atime"
                                    ]],
                                    "payment_method_types" => ["gcash", "card", "paymaya"],
                                    "success_url" => SITE_URL . "/payment-success.php?appointment_id=$appointment_id",
                                    "cancel_url" => SITE_URL . "/payment-cancel.php?appointment_id=$appointment_id",
                                    "description" => "Salon Appointment Booking #$aptnumber",
                                    "reference_number" => "$aptnumber",
                                    "metadata" => [
                                        "appointment_id" => "$appointment_id",
                                        "appointment_number" => "$aptnumber",
                                        "user_id" => "$uid",
                                        "service" => "$service",
                                        "staff_id" => "$staffID"
                                    ]
                                ]
                            ]
                        ];
                        
                        $ch = curl_init(PAYMONGO_API_URL . "/checkout_sessions");
                        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                            "Authorization: Basic " . base64_encode(PAYMONGO_SECRET_KEY . ":"),
                            "Content-Type: application/json"
                        ]);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                        
                        $response = curl_exec($ch);
                        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        $curl_error = curl_error($ch);
                        curl_close($ch);
                        
                        if($http_code == 200 || $http_code == 201) {
                            $responseData = json_decode($response, true);
                            
                            if(isset($responseData['data'])) {
                                $checkout_id = $responseData['data']['id'];
                                $checkout_url = $responseData['data']['attributes']['checkout_url'];
                                
                                mysqli_query($con, "
                                    UPDATE tblbook 
                                    SET paymongo_checkout_id='$checkout_id'
                                    WHERE ID='$appointment_id'
                                ");
                                
                                header("Location: $checkout_url");
                                exit;
                            } else {
                                
                                error_log("PayMongo Response Error: " . print_r($responseData, true));
                                echo "<script>alert('Payment setup failed. Please try again.');</script>";
                            }
                        } else {
                            
                            $error = json_decode($response, true);
                            error_log("PayMongo API Error (HTTP $http_code): " . print_r($error, true));
                            if ($curl_error) {
                                error_log("cURL Error: $curl_error");
                            }
                            echo "<script>alert('Payment system error. Please contact support. Error code: $http_code');</script>";
                        }
                    } else {
                        echo '<script>alert("Database error: ' . mysqli_error($con) . '")</script>';
                    }
                }
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=0.70">
    <title>Beauty Parlour Management System | Book Appointment</title>
    <link rel="stylesheet" href="assets/css/style-starter.css">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/main.min.css" rel="stylesheet">
    <script src="assets/js/jquery-3.3.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/main.min.js"></script>
    <style>
      #calendar {
        background: #fff;
        border-radius: 10px;
        padding: 10px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      }
      .card {
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      }
      .payment-notice {
        background: #fff3cd;
        border: 1px solid #ffc107;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
      }
      .payment-notice .fa {
        color: #856404;
        margin-right: 10px;
      }
    </style>
</head>
<body id="home">
<?php include_once('includes/header.php'); ?>

<section class="w3l-inner-banner-main">
    <div class="about-inner contact">
        <div class="container text-center">
            <h3 class="header-name">Book Appointment</h3>
            <p class="tiltle-para">Book an appointment with us and experience the difference</p>
        </div>
    </div>
    <div class="breadcrumbs-sub">
        <div class="container">
            <ul class="breadcrumbs-custom-path">
                <li><a href="index.php">Home <span class="fa fa-angle-right"></span></a></li>
                <li class="active">Book Appointment</li>
            </ul>
        </div>
    </div>
</section>

<section class="w3l-contact-info-main" id="contact">
  <div class="contact-sec">
    <div class="container">
      
      <div class="payment-notice">
        <span class="fa fa-info-circle"></span>
        <strong>Payment Required:</strong> You will be redirected to our secure payment gateway to complete your booking. Accepted payment methods: GCash, Credit/Debit Card, PayMaya.
      </div>
      
      <div class="row">
        
        <div class="col-md-5">
          <div class="cont-details">
            <?php
            $ret = mysqli_query($con, "SELECT * FROM tblpage WHERE PageType='contactus'");
            while ($row = mysqli_fetch_array($ret)) {
            ?>
              <div class="cont-top">
                <div class="cont-left text-center"><span class="fa fa-phone text-primary"></span></div>
                <div class="cont-right">
                  <h6>Call Us</h6>
                  <p class="para"><a href="tel:+<?php echo $row['MobileNumber'];?>">+<?php echo $row['MobileNumber'];?></a></p>
                </div>
              </div>
              <div class="cont-top margin-up">
                <div class="cont-left text-center"><span class="fa fa-envelope-o text-primary"></span></div>
                <div class="cont-right">
                  <h6>Email Us</h6>
                  <p class="para"><a href="mailto:<?php echo $row['Email'];?>"><?php echo $row['Email'];?></a></p>
                </div>
              </div>
              <div class="cont-top margin-up">
                <div class="cont-left text-center"><span class="fa fa-map-marker text-primary"></span></div>
                <div class="cont-right">
                  <h6>Address</h6>
                  <p class="para"><?php echo $row['PageDescription'];?></p>
                </div>
              </div>
              <div class="cont-top margin-up">
                <div class="cont-left text-center"><span class="fa fa-clock text-primary"></span></div>
                <div class="cont-right">
                  <h6>Time</h6>
                  <p class="para"><?php echo $row['Timing'];?></p>
                </div>
              </div>
            <?php } ?>
          </div>
        </div>

        <div class="col-md-7">
          <form method="post" action="">
            
            <div class="card mb-4">
              <div class="card-body">
                <h4 class="card-title mb-3">Select Your Appointment Date</h4>
                <div id="calendar"></div>
              </div>
            </div>

            <div class="card mb-4">
              <div class="card-body">
                <label><b>Select Service</b></label>
                <select class="form-control" name="service" id="serviceSelect" required>
                  <option value="">-- Choose a Service --</option>
                  <?php
                  $query = mysqli_query($con, "SELECT * FROM tblservices ORDER BY ServiceName ASC");
                  while ($row = mysqli_fetch_array($query)) {
                      $serviceName = strtolower($row['ServiceName']);
                      $category = '';
            
                      if (strpos($serviceName, 'hair') !== false) {
                          $category = 'Hair';
                      } elseif (strpos($serviceName, 'nail') !== false || strpos($serviceName, 'mani') !== false || strpos($serviceName, 'pedi') !== false) {
                          $category = 'Nails';
                      }
            
                      echo '<option value="'.htmlspecialchars($row['ServiceName']).'"
                            data-category="'.$category.'"
                            data-cost="'.$row['Cost'].'">'
                            .htmlspecialchars($row['ServiceName']).' - ₱'.number_format($row['Cost'], 2).
                           '</option>';
                  }
                  ?>
                </select>
                <div id="servicePriceDisplay" class="mt-2" style="display:none;">
                  <strong>Price: ₱<span id="priceAmount">0.00</span></strong>
                </div>
              </div>
            </div>
            
            <div class="card mb-4" id="staffCard" style="display:none;">
              <div class="card-body">
                <label><b>Select Staff Member</b></label>
                <select class="form-control" name="staff" id="staffSelect" required>
                  <option value="">-- Select Staff --</option>
                </select>
                <small class="text-muted">Please select a staff member before choosing a time slot</small>
              </div>
            </div>

            <div id="timeSlotContainer" class="card mb-4" style="display:none;">
              <div class="card-body">
                <h5>Available Time Slots for <span id="selectedDate"></span></h5>
                <select id="timeSlotsDropdown" name="slotid" class="form-select mt-2" required>
                  <option value="">-- Select Time Slot --</option>
                </select>
                <small class="text-muted">Time slots shown are available for your selected staff member</small>
              </div>
            </div>

            <input type="hidden" id="adate" name="adate">

            <div class="card mb-4">
              <div class="card-body">
                <label><b>Additional Message</b></label>
                <textarea class="form-control" name="message" placeholder="Optional: Add notes or special requests"></textarea>
              </div>
            </div>

            <button type="submit" class="btn btn-primary w-100" name="submit">
              Proceed to Payment
            </button>
            <small class="text-muted d-block text-center mt-2">
              You will be redirected to secure payment gateway
            </small>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include_once('includes/footer.php');?>

<div id="timeslotTooltip" style="
  display:none;
  position:absolute;
  background:#ffffff;
  border:1px solid #ddd;
  padding:10px;
  border-radius:6px;
  font-size:13px;
  box-shadow:0 4px 12px rgba(0,0,0,0.15);
  z-index:9999;
  max-width:220px;
"></div>

<script>
document.addEventListener('DOMContentLoaded', function () {

  const calendarEl = document.getElementById('calendar');
  const timeSlotContainer = document.getElementById('timeSlotContainer');
  const selectedDateLabel = document.getElementById('selectedDate');
  const dropdown = document.getElementById('timeSlotsDropdown');
  const adateField = document.getElementById('adate');
  const tooltip = document.getElementById('timeslotTooltip');
  const staffSelect = document.getElementById('staffSelect');

  let selectedDate = null;

  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    height: 550,
    events: 'get-available-dates.php',

    eventMouseEnter: function (info) {
      const dateStr = info.event.startStr;

      fetch('get-remaining-timeslots.php?date=' + dateStr)
        .then(res => res.json())
        .then(slots => {
          if (!Array.isArray(slots) || slots.length === 0) {
            tooltip.innerHTML = '<strong>No available time slots</strong>';
          } else {
            tooltip.innerHTML =
              '<strong>Available Slots:</strong><br>' +
              slots.map(t => '• ' + t).join('<br>');
          }
          tooltip.style.display = 'block';
        })
        .catch(() => {
          tooltip.innerHTML = '<strong>Error loading slots</strong>';
          tooltip.style.display = 'block';
        });

      info.el.addEventListener('mousemove', function (e) {
        tooltip.style.left = (e.pageX + 15) + 'px';
        tooltip.style.top = (e.pageY + 15) + 'px';
      });
    },

    eventMouseLeave: function () {
      tooltip.style.display = 'none';
    },

    eventClick: function (info) {
      const dateStr = info.event.startStr;
      selectedDate = dateStr;

      selectedDateLabel.textContent = new Date(dateStr).toDateString();
      adateField.value = dateStr;

      if (staffSelect.value) {
        loadTimeSlots(dateStr, staffSelect.value);
      } else {
        timeSlotContainer.style.display = 'none';
        alert('Please select a staff member first before choosing a time slot.');
      }
    }
  });

  calendar.render();

  function loadTimeSlots(dateStr, staffID) {
    timeSlotContainer.style.display = 'block';
    dropdown.innerHTML = '<option value="">Loading slots...</option>';

    fetch('get-available-times.php?date=' + dateStr + '&staffID=' + staffID)
      .then(res => {
        if (!res.ok) throw new Error('Server error');
        return res.json();
      })
      .then(data => {
        dropdown.innerHTML = '';

        if (!Array.isArray(data) || data.length === 0) {
          dropdown.innerHTML =
            '<option value="">No available time slots for this staff</option>';
          return;
        }

        dropdown.innerHTML =
          '<option value="">-- Select Time Slot --</option>';

        data.forEach(slot => {
          const opt = document.createElement('option');
          opt.value = slot.id;
          opt.textContent = slot.time;
          dropdown.appendChild(opt);
        });
      })
      .catch(err => {
        console.error(err);
        dropdown.innerHTML =
          '<option value="">Error loading slots</option>';
      });
  }

  staffSelect.addEventListener('change', function () {
    if (selectedDate && this.value) {
      loadTimeSlots(selectedDate, this.value);
    } else if (!this.value) {
      timeSlotContainer.style.display = 'none';
    }
  });
});
</script>

<script>
document.getElementById('serviceSelect').addEventListener('change', function () {
    const category = this.options[this.selectedIndex].dataset.category;
    const cost = this.options[this.selectedIndex].dataset.cost;
    const staffCard = document.getElementById('staffCard');
    const staffSelect = document.getElementById('staffSelect');
    const timeSlotContainer = document.getElementById('timeSlotContainer');
    const priceDisplay = document.getElementById('servicePriceDisplay');
    const priceAmount = document.getElementById('priceAmount');

    staffSelect.innerHTML = '<option value="">-- Select Staff --</option>';
    timeSlotContainer.style.display = 'none';

    if (!category) {
        staffCard.style.display = 'none';
        priceDisplay.style.display = 'none';
        return;
    }

    // Show price
    if (cost) {
        priceAmount.textContent = parseFloat(cost).toFixed(2);
        priceDisplay.style.display = 'block';
    }

    fetch('get-staff-by-category.php?category=' + category)
        .then(response => response.json())
        .then(data => {
            if (data.length === 0) {
                staffSelect.innerHTML =
                    '<option value="">No available staff</option>';
            } else {
                data.forEach(staff => {
                    const option = document.createElement('option');
                    option.value = staff.id;
                    option.textContent = staff.name;
                    staffSelect.appendChild(option);
                });
            }
            staffCard.style.display = 'block';
        })
        .catch(err => {
            console.error(err);
            staffCard.style.display = 'none';
        });
});
</script>

<script src="assets/js/bootstrap.min.js"></script>
</body>
</html>
<?php } ?>