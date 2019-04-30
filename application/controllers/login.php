<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		session_start();
		$this->load->model('Master');
		$this->load->library('form_validation');
	
	}


	public function index()
	{

	}

	public function client()
       { 
        $this->load->view('client');
       }
		
	public function client_login()
       { 
          $this->load->library('form_validation');
          $this->form_validation->set_rules('email', 'Email', 'required');
          $this->form_validation->set_rules('password', 'Password', 'required');

           if($this->form_validation->run() == FALSE)
              {  
               $this->client();
              }
          else
              { 
                  if(isset($_POST['email']))
                    {            
                      $user_data = $this->Master->client_login($_POST['email'],$_POST['password']);
                    
                         if (!empty($user_data)) 
                          {
                            $_SESSION['ad_uid']= $user_data['id']; 
                            $_SESSION['ad_uname']= $user_data['email']; 
                            $_SESSION['ad_name']= $user_data['firstname'];

                              redirect('home/client_dash');
                          }
                          else  { $this->client();}
                          
                    }
                 else
                    { 
                      die('haaqaa');
                      $this->client();
                    }  
              }
       

	}

}

/* End of file login.php */
/* Location: ./application/controllers/login.php */