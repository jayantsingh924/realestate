<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {
	
	public function __construct()
	{
		parent::__construct();
		session_start();
		$this->load->model('Master');
		$this->load->library('form_validation');
		//$this->lang->load('message', 'english');
	}
	
	public function index()
	   { 
      $data['posts'] = $this->Master->getlatestPosts();
		  $this->load->view('home', $data);
	    }
  public function client()
       { 
        $this->load->view('client');
       } 
    public function client_dash()
       { 
        $this->load->view('client_dash');
       }     



       
           
	public function login()
	    { 
	      if (!empty($_SESSION['ad_uid'])){
               	redirect('home/dashboard');
               }
          else
               {
		        $this->load->view('login');
                  
		       }
     	}
	public function dashboard()
	    { 
	    	 if (!empty($_SESSION['ad_uid']))
	            {  
               // print_r($_SESSION); die();
                $allcount = $this->Master->getrecordCounttest();
                $data['count'] = $allcount;
               	 $this->load->view('dashboard', $data);
               }
          else
               {
		        $this->load->view('login');
                  
		       }
     	}

      public function regist(){
           $this->load->view('register');
      }

	public function signup()
	     { 
       
             $post = $this->input->post();
             $this->load->library('form_validation');
             $this->form_validation->set_rules('firstname', 'First Name', 'required');
             $this->form_validation->set_rules('lastname', 'Last Name', 'required');
     
    
          if($this->form_validation->run() == FALSE)
            {  
               $this->load->view('register');
            }
        else
            { 
              $insert_data['firstname']=$post['firstname'];
              $insert_data['lastname']=$post['lastname'];
              $insert_data['email']=$post['email'];
              $insert_data['password']=$post['password'];
              $insert_data['gender']=$post['gender'];
              $insert_data['about_us']=$post['aboutme'];
              $insert_data['reg_date']=date('Y-m-d H:i:s');
             
              $result=$this->Master->addclient($insert_data);
            
           
              if($result)
                {
                    redirect('/home');
                }
              else
                {
                    redirect('/home/signup');
                }
            }
        
	     }
	public function about()
	     {
	     	$this->load->view('header/about_us');
	     } 
  public function contact()
       {
        $this->load->view('header/contact_us');
       } 
  public function blog()
       {
        $this->load->view('header/blog');
       }   
  public function property()
       {
        $this->load->view('property/property_grid');
       }    
	public function listdata()
	     {
	     	$this->load->view('category/list');
	     }   
	public function addprop()
	     {
	     	$this->load->view('Category/add');
	     } 
    public function add()
	     {  
	     	 $file = $this->upload_file();
	         $file_2 = $this->upload_file_2();
	     	 $file_3 = $this->upload_file_3();
	     	 $file_4 = $this->upload_file_4();
	     	 $post = $this->input->post();
             $this->load->library('form_validation');
	     	     $this->form_validation->set_rules('location', 'Location Name', 'required');
             $this->form_validation->set_rules('property', 'Property Name', 'required');
     
    
          if($this->form_validation->run() == FALSE)
            {
               $this->addprop();
            }
        else
            {  
        	    $insert_data['location_name']=$post['location'];
        	    $insert_data['property_name']=$post['property'];
        	    $insert_data['area']=$post['area'];
        	    $insert_data['beds']=$post['beds'];
        	    $insert_data['garage']=$post['garage'];
        	    $insert_data['rent']=$post['rent'];
        	    $insert_data['description']=$post['description'];
        	    $insert_data['reg_date']=date('Y-m-d H:i:s');
        	    $insert_data['image_1']=$file['file_name'];
        	    $insert_data['image_2']=$file_2['file_name'];
        	    $insert_data['image_3']=$file_3['file_name'];
        	    $insert_data['image_4']=$file_4['file_name'];
        	    

        	    


        	    $result=$this->Master->addtest($insert_data);
        	  //echo  $this->db->last_query(); die();
           
              if($result)
                {
                    redirect('/home');
                }
              else
                {
                    redirect('/home/addprop');
                }
            }
			
	     } 

    public function upload_file()
          {
            $config['upload_path']          = './uploads/';
            $config['allowed_types']        = 'jpg|jpeg|png';

            $this->load->library('upload', $config);
               if ( ! $this->upload->do_upload('image_1'))
                { 
                  $this->form_validation->set_error_delimiters('<p class="error">', '</p>');
                  $error = array('error' => $this->upload->display_errors());
         
                }
                else
                { 
                    return $this->upload->data();
                }
          }
    public function upload_file_2()
          {
            $config['upload_path']          = './uploads/';
            $config['allowed_types']        = 'jpg|jpeg|png';

            $this->load->library('upload', $config);
               if ( ! $this->upload->do_upload('image_2'))
                { 
                  $this->form_validation->set_error_delimiters('<p class="error">', '</p>');
                  $error = array('error' => $this->upload->display_errors());
                }
                else
                { 
                    return $this->upload->data();
                }
          }
    public function upload_file_3()
          {
            $config['upload_path']          = './uploads/';
            $config['allowed_types']        = 'jpg|jpeg|png';

            $this->load->library('upload', $config);
               if ( ! $this->upload->do_upload('image_3'))
                { 
                  $this->form_validation->set_error_delimiters('<p class="error">', '</p>');
                  $error = array('error' => $this->upload->display_errors());
                }
                else
                { 
                    return $this->upload->data();
                }
          }
      public function upload_file_4()
          {
            $config['upload_path']          = './uploads/';
            $config['allowed_types']        = 'jpg|jpeg|png';

            $this->load->library('upload', $config);
               if ( ! $this->upload->do_upload('image_4'))
                { 
                  $this->form_validation->set_error_delimiters('<p class="error">', '</p>');
                  $error = array('error' => $this->upload->display_errors());
                }
                else
                { 
                    return $this->upload->data();
                }
          }                            
    
    public function check_login()
	     {
	     	  $this->load->library('form_validation');
          $this->form_validation->set_rules('email', 'Email', 'required');
		      $this->form_validation->set_rules('password', 'Password', 'required');

		       if($this->form_validation->run() == FALSE)
		          { 
			         $this->login();
              }
		      else
		          {   
	                if(isset($_POST['email']))
	                 {
			           $user_data = $this->Master->check_login($_POST['email'],$_POST['password']);
			          // echo $this->db->last_query();  print_r($user_data); die();
			            $_SESSION['ad_uid']= $user_data['id']; 
		    	        $_SESSION['ad_uname']= $user_data['email']; 
		              $_SESSION['ad_name']= $user_data['firstname'];

		                redirect('home/dashboard');
		             }
		         else
		             {
		             	$this->login();
		    	     }	
		          }
	     }

    public function logout()
         {
           session_destroy();              //  Used To Destroy All Sessions
           redirect('Home');
           //$this->Home();
         }

    public function dump_session()
    {
       echo'<pre>';
       print_r($_SESSION);
       echo '</pre>';
       die();
    }

   public function getdata() 
   {
      $allcount = $this->Master->getrecordCounttest();
      $data['count'] = $allcount;
      $data['posts'] = $this->Master->getPosts();
      $this->load->view('Category/list', $data); 
   }
   
   public function view($id = NULL)
   {
     $data['property'] = $this->Master->get_faqs($id);
     if (empty($data['property']))
      {
        die('hiii');
      }
    $data['property_name'] = $data['property']['property_name'];
    $this->load->view('Category/view', $data);
   

   }

   public function edit()
   {
    $id = $this->uri->segment(3);
    $data = $this->Master->getdata($id);
    $post = $this->input->post();

    if($this->input->post('flag')=='edit')
      {  
                 $this->form_validation->set_rules('location', 'Location Name', 'required');
                 $this->form_validation->set_rules('property', 'Property Name', 'required');
                 if($this->form_validation->run()==TRUE)
                   { 
                    $id = $this->input->post('id');
                    $update_data['location_name']=$post['location'];
                    $update_data['property_name']=$post['property'];
                    $update_data['area']=$post['area'];
                    $update_data['beds']=$post['beds'];
                    $update_data['garage']=$post['garage'];
                    $update_data['rent']=$post['rent'];
                    $update_data['description']=$post['description'];

            
                    $result=$this->Master->updatedata($id, $update_data);
                  // print_r($this->db->affected_rows()); die();
                    if($this->db->affected_rows() > 0)
                      { 
//                         echo '
//                         <div class="alert alert-dismissible alert-success">
//   <button type="button" class="close" data-dismiss="alert">&times;</button>
//   <strong>Well done!</strong> You successfully read <a href="#" class="alert-link">this important alert message</a>.
// </div>';
                        redirect('Home/getdata/s');
                      }
                   else
                      { 
                          redirect('Home/getdata/f');
                     }
                    
                   }  

   }
  //print_r($data); die();
                   $pass['post'] = $data;
                   $this->load->view('Category/edit', $pass);
 }



function delete($id)
    {
      
       $this->Master->deletedata($id);
          redirect('/Home/getdata');
    }


  
}

