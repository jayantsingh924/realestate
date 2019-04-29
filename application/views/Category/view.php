<?php defined('BASEPATH') OR exit('No direct script access allowed');?>


<?php $this->load->view('header/header.php');?>
<?php $this->load->view('header/nav.php');?>
<ol class="breadcrumb">
  <li class="breadcrumb-item"><a href="<?php echo base_url();?>Home">Home</a></li>
  <li class="breadcrumb-item active">All Records</li>
</ol>

<div class="container">
    
<section class="intro-single">
    <div class="container">
      <div class="row">
        <div class="col-md-12 col-lg-8">
          <div class="title-single-box">
            <h1 class="title-single">
               <?php echo $property['property_name']; ?>
            </h1>
            <span class="color-text-a"> 
            	<?php echo $property['location_name']; ?>
            </span>
          </div>
        </div>
        <div class="col-md-12 col-lg-4">
          <nav aria-label="breadcrumb" class="breadcrumb-box d-flex justify-content-lg-end">
            <ol class="breadcrumb">
              <li class="breadcrumb-item">
                <a href="<?php echo base_url();?>Home">Home</a>
              </li>
              <li class="breadcrumb-item">
                <a href="#">Properties</a>
              </li>
              <li class="breadcrumb-item active" aria-current="page">
                <?php echo $property['property_name']; ?>
              </li>
            </ol>
          </nav>
        </div>
      </div>
    </div>
  </section>
  <!--/ Intro Single End / -->
  <!--/ Property Single Star /-->
  <section class="property-single nav-arrow-b">
    <div class="container">
      <div class="row">
        <div class="col-sm-12">
          <div id="property-single-carousel"  class="owl-carousel owl-arrow gallery-property">
            <div class="carousel-item-b">
                  <img style="height: 500px; width: 100%;" src="<?php echo base_url(); ?>uploads/<?php echo $property['image_1']; ?>" alt="Front View">
            </div>
            <div class="carousel-item-b">
             <img style="height: 500px; width: 100%;" src="<?php echo base_url(); ?>uploads/<?php echo $property['image_2']; ?>" alt="Back View">
            </div>
            <div class="carousel-item-b">
              <img style="height: 500px; width: 100%;"  src="<?php echo base_url(); ?>uploads/<?php echo $property['image_3']; ?>" alt="Left View">
            </div>
            <div class="carousel-item-b">
              <img style="height: 500px; width: 100%;" src="<?php echo base_url(); ?>uploads/<?php echo $property['image_4']; ?>" alt="Right View">
            </div>
            </div>
          
          <div class="row justify-content-between">
            <div class="col-md-5 col-lg-4">
              <div class="property-price d-flex justify-content-center foo">
                <div class="card-header-c d-flex">
                  <div class="card-box-ico">
                    <span class="ion-money">Rs</span>
                  </div>
                  <div class="card-title-c align-self-center">
                    <h5 class="title-c"><?php echo $property['rent']; ?>/m</h5>

                  </div>
                </div>
              </div>
              <div class="property-summary">
                <div class="row">
                  <div class="col-sm-12">
                    <div class="title-box-d section-t4">
                      <h3 class="title-d">Quick Summary</h3>
                    </div>
                  </div>
                </div>
                <div class="summary-list">
                  <ul class="list">
                    <li class="d-flex justify-content-between">
                      <strong>Property ID:</strong>
                      <span><?php echo $property['id']; ?></span>
                    </li>
                    <li class="d-flex justify-content-between">
                      <strong>Location:</strong>
                      <span><?php echo $property['location_name']; ?></span>
                    </li>
                    <li class="d-flex justify-content-between">
                      <strong>Property Type:</strong>
                      <span><?php echo $property['property_name']; ?></span>
                    </li>
                    <li class="d-flex justify-content-between">
                      <strong>Area:</strong>
                      <span><?php echo $property['area']; ?>m
                        <sup>2</sup>
                      </span>
                    </li>
                    <li class="d-flex justify-content-between">
                      <strong>Beds:</strong>
                      <span><?php echo $property['beds']; ?></span>
                    </li>
                    <li class="d-flex justify-content-between">
                      <strong>Baths:</strong>
                      <span><?php echo $property['bathroom']; ?></span>
                    </li>
                    <li class="d-flex justify-content-between">
                      <strong>Garage:</strong>
                      <span><?php echo $property['garage']; ?></span>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
            <div class="col-md-7 col-lg-7 section-md-t3">
              <div class="row">
                <div class="col-sm-12">
                  <div class="title-box-d">
                    <h3 class="title-d">Property Description</h3>
                  </div>
                </div>
              </div>
              <div class="property-description">
                <p class="description color-text-a">	<?php echo $property['description']; ?>
               </p>
             
              </div>
              <div class="row section-t3">
                <div class="col-sm-12">
                  <div class="title-box-d">
                    <h3 class="title-d">Amenities</h3>
                  </div>
                </div>
              </div>
              <div class="amenities-list color-text-a">
                <ul class="list-a no-margin">
                  <li>Balcony</li>
                  <li>Outdoor Kitchen</li>
                  <li>Cable Tv</li>
                  <li>Deck</li>
                  <li>Tennis Courts</li>
                  <li>Internet</li>
                  <li>Parking</li>
                  <li>Sun Room</li>
                  <li>Concrete Flooring</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
   
 
      </div>
    </div>
  </section>
  

    
 </div>

<?php $this->load->view('header/f.php');?>

<?php $this->load->view('header/footer.php');?>


            