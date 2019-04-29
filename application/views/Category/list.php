<?php defined('BASEPATH') OR exit('No direct script access allowed');?>


<?php $this->load->view('header/header.php');?>



<?php $this->load->view('header/nav.php');?>

<ol class="breadcrumb">
  <li class="breadcrumb-item"><a href="<?php echo base_url();?>welcome/home">Home</a></li>
  <li class="breadcrumb-item active">All Records</li>
</ol>

<div class="container"  style="padding-top: 50px;">

    <div class="col-md-12">
        <div class="row" style="padding-top: 50px;">
            <a href="<?php echo base_url();?>home/add" class = "btn btn-success" >Add New</a>
        </div>
    </div>
	  <br/>
    <table class="table table-stripd table-hover">
		      <tr class="table-success">
			       <th>S.no</th>
			       <th>Location</th>
             <th>Property</th>
             <th>Registerd Date</th>
             <th>Action</th>
          </tr>
	            <?php foreach($posts as $post){?>
     <tr>
         <td><?php echo $post->id;?></td>
         <td><?php echo $post->location_name;?></td>
         <td><?php echo $post->property_name;?></td>
         <td><?php echo $post->reg_date;?></td>
         <td>
          <a href="<?php echo site_url('Home/view/'.$post->id); ?>">View</a> 
           <a href="<?php echo site_url('Home/edit/'.$post->id); ?>">Edit</a> 
            
           <a href="<?php echo site_url('Home/delete/'.$post->id); ?>" 
              onClick="return confirm('Are you sure you want to delete?')">Delete</a>
         </td>

      

      </tr>     
     <?php }?>  

      
	     </table>
    
         <div>
            <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
               <a class="navbar-brand" href="#">Total Records : <?php echo $count;?></a>
                 <p> 
                    <span style="padding-left: 750px;"> 
                         <center>
                             Copyright &copy PropertyPoint
                         </center>
                    </span>
                 </p>
            </nav>
       </div>


</div>

<?php $this->load->view('header/f.php');?>

<?php $this->load->view('header/footer.php');?>
