<?php $this->load->view('header/header.php'); ?>

<?php $this->load->view('header/nav.php'); ?>


<div class="container" style="padding-top: 200px; padding-bottom: 100px;">
<form action="<?php echo base_url();?>Login/client_login" method="post">
  <fieldset>
    <legend>Log In</legend>
    <div class="form-group row">
      <label for="staticEmail" class="col-sm-2 col-form-label">Email</label>
      <div class="col-sm-10">
        <input type="text" readonly="" class="form-control-plaintext" id="staticEmail" value="email@example.com">
      </div>
    </div>
    <div class="form-group">
      <label for="exampleInputEmail1">Email address</label>
      <input type="email" class="form-control"  name="email" placeholder="Enter your email">
      <small id="emailHelp" class="form-text text-muted">We'll never share your email with anyone else.</small>
        <div class="col text-danger"> <?php echo form_error('email'); ?> 
    </div>
    <div class="form-group">
      <label for="exampleInputPassword1">Password</label>
      <input type="password" class="form-control"  name="password" placeholder="Password">
       <div class="col text-danger"> <?php echo form_error('password'); ?> 
    </div>
   
    
  <div style="padding-top: 10px;">
   
    <button type="submit" class="btn btn-primary">Submit</button>
     <button type="reset" class="btn btn-success">Reset</button>
     </div>

  </fieldset>
</form>

</div>

<?php $this->load->view('header/f.php');?>
<?php $this->load->view('header/footer.php');?>