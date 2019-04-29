<?php $this->load->view('header/header.php');?>



<?php $this->load->view('header/nav.php');?>


<div class="container" style="padding-top: 200px;">

  <div class="row">

<div class="col">



<div class="card text-white bg-success mb-3" style="max-width: 20rem;">

  <div class="card-header">New Properties</div>

<div class="card-body">

       <div class="row">

          <div class="col">

             <a href="<?php echo base_url();?>Home/addprop">Add new</a>

          </div>

          <div class="col">

             <a href="<?php echo base_url();?>Home/getdata"><h4 class="card-title" align="right"><?php echo $count;?></h4></a>

          </div>

      </div>

  </div>

</div>

</div>

<div class="col">

<div class="card text-white bg-default mb-3" style="max-width: 20rem;">

  <div class="card-header"> <font color="black">Shop's</font></div>



<div class="card-body">

       <div class="row">

          <div class="col">

             <a href="#">Add new</a>

          </div>

          <div class="col">

             <h4 class="card-title" align="right">13</h4>

          </div>

      </div>

  </div>

</div>

</div>



<div class="col">

<div class="card text-white bg-success mb-3" style="max-width: 20rem;">

  <div class="card-header">Rent Properties</div>



  <div class="card-body">

       <div class="row">

          <div class="col">

             <a href="#">Add new</a>

          </div>

          <div class="col">

             <h4 class="card-title" align="right">11</h4>

          </div>

      </div>

  </div>

</div>

</div>



<div class="col">

<div class="card text-white bg-default mb-3" style="max-width: 20rem;">

  <div class="card-header"> <font color="black">Other's</font></div>



  <div class="card-body">

       <div class="row">

          <div class="col">

             <a href="#">Add new</a>

          </div>

          <div class="col">

             <h4 class="card-title" align="right">3</h4>

          </div>

      </div>

  </div>

  

</div>

</div>



</div>

<div class="row">

  <div class="col">

<div class="jumbotron">

  <h1 class="display-5"><?php print_r($_SESSION['ad_name']);?></h1>
  <br>
  <p><?php 
  $currentDate = date('Y-m-d h:i:s');
  echo $currentDate;
  ?></p>

 

      
    <a class="btn btn-success btn-lg" href="<?php echo base_url();?>Home/logout" role="button">Log Out</a>

  

</div>

</div>

<div class="col">



<div class="card mb-3">

  <h3 class="card-header">Welcome</h3>

  

  <img style="height: 200px; width: 100%; display: block;" src="https://pngimage.net/wp-content/uploads/2018/06/property-png-9.png" alt="Card image">

</div>

</div>

<a href="<?php echo base_url();?>Home/getdata" class="btn btn-success btn-lg btn-block">Data</a>

</div>



<nav class="navbar navbar-expand-lg navbar-light bg-light">

  <marquee><a class="navbar-brand" href="#">PropertyPoints.co.in</a></marquee>

  



</nav>



</div>













<?php $this->load->view('header/f.php');?>

<?php $this->load->view('header/footer.php');?>