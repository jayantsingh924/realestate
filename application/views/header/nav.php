 <nav class="navbar navbar-default navbar-trans navbar-expand-lg fixed-top">
    <div class="container">
      <button class="navbar-toggler collapsed" type="button" data-toggle="collapse" data-target="#navbarDefault"
        aria-controls="navbarDefault" aria-expanded="false" aria-label="Toggle navigation">
        <span></span>
        <span></span>
        <span></span>
      </button>
      <a class="navbar-brand text-brand" href="<?php echo base_url();?>home">Property's<span class="color-b">Point</span></a>
      <button type="button" class="btn btn-link nav-search navbar-toggle-box-collapse d-md-none" data-toggle="collapse"
        data-target="#navbarTogglerDemo01" aria-expanded="false">
        <span class="fa fa-search" aria-hidden="true"></span>
      </button>
      <div class="navbar-collapse collapse justify-content-center" id="navbarDefault">
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link active" href="<?php echo base_url();?>home">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?php echo base_url();?>Home/about">About</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?php echo base_url();?>home/property">Property</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?php echo base_url();?>home/blog">Blog</a>
          </li>
          
          <li class="nav-item">
            
            <a class="nav-link" href="<?php echo base_url();?>home/contact">Contact</a>
        
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown"
              aria-haspopup="true" aria-expanded="false">
              Log In
            </a>
            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
              <a class="dropdown-item" href="<?php echo base_url();?>home/login">Admin Login</a>
              <a class="dropdown-item" href="<?php echo base_url();?>Login/client">Client Login</a>
               <a class="dropdown-item" href="<?php echo base_url();?>home/regist">Sign Up</a>
           
            </div>
          </li>
        </ul>
      </div>

   
      &nbsp;
      <button type="button" class="btn btn-b-n navbar-toggle-box-collapse d-none d-md-block" data-toggle="collapse"
        data-target="#navbarTogglerDemo01" aria-expanded="false">
        <span class="fa fa-search" aria-hidden="true"></span>
      </button>
    </div>
  </nav>
  <!--/ Nav End /-->
