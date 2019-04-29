<?php $this->load->view('header/header.php');?>



<?php $this->load->view('header/nav.php');?>



<div class="container" style="padding-top: 200px; padding-bottom: 100px;">
	
<form method="post" action="<?php echo base_url();?>home/signup">
 
   
      

      <div class="form-group">
      <label>FirstName</label>
      <input type="text" class="form-control" name="firstname" placeholder="Enter your FirstName">
      </div> 

      <div class="form-group">
      <label>LastName</label>
      <input type="text" class="form-control" name="lastname" placeholder="Enter your LastName">
      </div> 
      
      <div class="form-group">
      <label for="exampleInputEmail1">Email address</label>
      <input type="email" class="form-control" name="email" placeholder="Enter email">
      <small id="emailHelp" class="form-text text-muted">We'll never share your email with anyone else.</small>
      </div>
    
      <div class="form-group">
      <label for="exampleInputPassword1">Password</label>
      <input type="password" class="form-control" name="password" placeholder="Password">
      </div>
      
      <fieldset class="form-group">
      <legend>Gender</legend>
      <div class="form-check">
        <label class="form-check-label">
          <input type="radio" class="form-check-input" name="gender" value="male" checked="">
          Male
        </label>
      </div>
      <div class="form-check">
      <label class="form-check-label">
          <input type="radio" class="form-check-input" name="gender" value="female">
          Female
        </label>
      </div>
      </fieldset>
    
 
    <div class="form-group">
      <label for="exampleTextarea">About Me</label>
      <textarea class="form-control" name="aboutme" rows="3"></textarea>
    </div>
   
    <button type="submit" class="btn btn-primary">Submit</button>
    <button type="reset" class="btn btn-primary">Reset</button>

</form>

</div>





<?php $this->load->view('header/f.php');?>

<?php $this->load->view('header/footer.php');?>