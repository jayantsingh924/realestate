

<?php 
$this->load->view('header/header.php');?>

<?php $this->load->view('header/nav.php');?>


<div class="container" style="padding-top: 200px; padding-bottom: 100px;">
      <legend>UPDATE DETAILS</legend>

            <div class="row">
                <div class="col">

                    <form action="<?php echo base_url();?>home/edit" method="post" enctype="multipart/form-data">
                     <div class="form-group">
                        <label for="exampleInputEmail1">Location</label>
                        <input type="text" class="form-control" value = "<?php echo $post['location_name']?>" name="location" placeholder="Location Name">

                          <div class="col text-danger" <?php echo form_error('location_name'); ?> >
                          </div>
                     </div>
                </div>
                <div class="col">
                     <div class="form-group">
                        <label for="exampleInputPassword1">Property Name</label>
                        <input type="text" class="form-control"  value = "<?php echo $post['property_name']?>" name="property" placeholder="Property Name">
                         <div class="col text-danger" <?php echo form_error('property_name'); ?> >
                         </div>
                     </div>
                </div>
            </div>  

            <div class="row">
                 <div class="col">
   
   
                        <div class="form-group">
                        <label for="exampleInputEmail1">Area</label>
                        <input type="text" class="form-control"  value = "<?php echo $post['area']?>"  name="area" placeholder="Enter Property Area">
                        
                          <div class="col text-danger" <?php echo form_error('area'); ?> >
                      </div></div>
                  </div>
                  <div class="col">

                        <div class="form-group">
                        <label for="exampleInputEmail1">Beds</label>
                        <input type="number" class="form-control"   value = "<?php echo $post['beds']?>" name="beds" placeholder="Enter Beds Quantity">
                       
                          <div class="col text-danger" <?php echo form_error('beds'); ?> >
                      </div></div>

                  </div>
            </div>
  


            <div class="row">
                  <div class="col">
                        <div class="form-group">
                                <label>Garage</label>
                                <input type="number" class="form-control"  value = "<?php echo $post['garage']?>"   name="garage" placeholder="Garage">

                              <div class="col text-danger" <?php echo form_error('garage'); ?> >
                              </div>
                        </div>
                  </div>
                  <div class="col">
                              <div class="form-group">
                                <label>Rent</label>
                                <input type="number" class="form-control"   value = "<?php echo $post['rent']?>"  name="rent" placeholder="Enter the price">

                              <div class="col text-danger" <?php echo form_error('rent'); ?> >
                              </div>
                        </div>
                  </div>
          </div>
                  <div class="form-group">
                      <input type="hidden" name="flag" value="edit">
                  </div>
                    <div class="form-group">
                      <input type="hidden" name="id" value="<?php echo $post['id']?>">
                  </div>
          
<!-- 
          <div class="row">
               <div class="col">
                   <div class="form-group">
                       <label>Front View</label>
                       <input type="file" class="form-control-file" name="image_1" aria-describedby="fileHelp">
                       <small id="fileHelp" class="form-text text-muted">image 1</small>
                   </div>
               </div>
                <div class="col">
                   <div class="form-group">
                       <label>Back View</label>
                       <input type="file" class="form-control-file"  name="image_2" aria-describedby="fileHelp">
                       <small id="fileHelp" class="form-text text-muted">image 2</small>
                   </div>
               </div>
          </div>

          <div class="row">
               <div class="col">
                   <div class="form-group">
                       <label>Left View</label>
                       <input type="file" class="form-control-file" name="image_3" aria-describedby="fileHelp">
                       <small id="fileHelp" class="form-text text-muted">image 3</small>
                   </div>
               </div>
                <div class="col">
                   <div class="form-group">
                       <label>Right View</label>
                       <input type="file" class="form-control-file" name="image_4"  aria-describedby="fileHelp">
                       <small id="fileHelp" class="form-text text-muted">image 4</small>
                   </div>
               </div>
          </div>  -->
          <div class="row">
            <div class="col">
                 <div class="form-group">
                     <label for="exampleTextarea">Property Description</label>
                     <textarea class="form-control"  name="description" rows="3"></textarea>
                 </div>
            </div>
          </div>         


             <div style="padding-top: 10px;">
                               <button type="submit" class="btn btn-primary">Submit</button>
                               <button type="reset" class="btn btn-success">Reset</button>
             </div>
 
</form>

</div>

<?php $this->load->view('header/f.php');?>
<?php $this->load->view('header/footer.php');?>