<div class="sticky-header header-section ">
  <div class="header-left">
    <button id="showLeftPush"><i class="fa fa-bars"></i></button>
    <div class="logo">
      <a href="dashboard.php">
        <h1>BPMS</h1>
        <span>Staff Panel</span>
      </a>
    </div>
    <div class="clearfix"> </div>
  </div>
  <div class="header-right">
    <div class="profile_details_left">
      <ul class="nofitications-dropdown">
        <?php
        $ret1=mysqli_query($con,"select tbluser.FirstName,tbluser.LastName,tblbook.ID as bid,tblbook.AptNumber from tblbook join tbluser on tbluser.ID=tblbook.UserID where tblbook.Status is null");
        $num=mysqli_num_rows($ret1);
        ?>  
        <li class="dropdown head-dpdn">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-bell"></i><span class="badge blue"><?php echo $num;?></span></a>
          
          <ul class="dropdown-menu">
            <li>
              <div class="notification_header">
                <h3>You have <?php echo $num;?> new notification</h3>
              </div>
            </li>
            <li>
        
               <div class="notification_desc">
                 <?php if($num>0){
                while($result=mysqli_fetch_array($ret1))
                {
                ?>
             <a class="dropdown-item" href="view-appointment.php?viewid=<?php echo $result['bid'];?>">New appointment received from <?php echo $result['FirstName'];?> <?php echo $result['LastName'];?> (<?php echo $result['AptNumber'];?>)</a>
             <hr />
                <?php }} else {?>
    <a class="dropdown-item" href="all-appointment.php">No New Appointment Received</a>
        <?php } ?>
                       
              </div>
              <div class="clearfix"></div>  
             </a></li>
             
            
             <li>
              <div class="notification_bottom">
                <a href="new-appointment.php">See all notifications</a>
              </div> 
            </li>
          </ul>
        </li> 
      
      </ul>
      <div class="clearfix"> </div>
    </div>
    <div class="profile_details">  
    <?php
    $staffid=$_SESSION['staff_id'];
    $ret=mysqli_query($con,"select StaffName from tblAdStaff where ID='$staffid'");
    $row=mysqli_fetch_array($ret);
    $name=$row['StaffName'];
    ?> 
      <ul>
        <li class="dropdown profile_details_drop">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
            <div class="profile_img"> 
              <span class="prfil-img"><img src="images/staff.png" alt="" width="50" height="50"> </span> 
              <div class="user-name">
                <p><?php echo $name; ?></p>
                <span>Staff</span>
              </div>
              <i class="fa fa-angle-down lnr"></i>
              <i class="fa fa-angle-up lnr"></i>
              <div class="clearfix"></div>  
            </div>  
          </a>
          <ul class="dropdown-menu drp-mnu">
            <li> <a href="change-password.php"><i class="fa fa-cog"></i> Change Password</a> </li> 
            <li> <a href="staff-profile.php"><i class="fa fa-user"></i> My Profile</a> </li> 
            <li> <a href="logout.php"><i class="fa fa-sign-out"></i> Logout</a> </li>
          </ul>
        </li>
      </ul>
    </div>  
    <div class="clearfix"> </div> 
  </div>
  <div class="clearfix"> </div> 
</div>