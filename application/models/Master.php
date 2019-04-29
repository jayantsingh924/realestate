<?php
class Master extends CI_Model
{
    public function __construct() 
    {

       parent::__construct();

    
    
    }


   	  public function check_login($user_name,$password)
   	     {
   	   
            $sql = $this->db->get_where('admin',array('email'=>$user_name,'password'=>$password));
            return $sql->row_array();
         
         }


      public function client_login($user_name,$password)
         {
       
            $sql = $this->db->get_where('client',array('email'=>$user_name,'password'=>$password));
            return $sql->row_array();
         
         }    

      public function addtest($add_data=NULL)
         { 
           $this->db->insert('property', $add_data);
           return $this->db->insert_id();
        }
      public function addclient($add_data=NULL)
         { 
           $this->db->insert('client', $add_data);
           return $this->db->insert_id();
        }

      public function getPosts()
         {
           $this->db->select("*"); 
           $this->db->from('property');
           $query = $this->db->get();
           return $query->result();
        }

      public function getlatestPosts()
          {
           // $this->db->select("*"); 
           // $this->db->from('property');
           // $query = $this->db->get();
           $query = "SELECT * FROM property  ORDER BY id DESC LIMIT 3";
           $query = $this->db->query($query);
        // echo $this->db->last_query(); die();
           return $query->result();
          }

     public function get_faqs($id = FALSE)
          {
            if ($id === FALSE)
          {
             $this->db->select("*"); 
             $this->db->from('property');
             $query = $this->db->get();
             return $query->result_array();
          }
            $query = $this->db->get_where('property', array('id' => $id));
             return $query->row_array();
             //echo $this->db->last_query(); die();
          }

function getdata($id)
    {
        $this->db->select('*');
        $this->db->where('id',$id);
        return $this->db->get('property')->row_array();
       // echo $this->db->last_query(); die();
    }

    public function updatedata($id, $update_data)
    { //  print_r($id); die();
        $this->db->where('id', $id);
        $this->db->update('property', $update_data);
     //   echo $this->db->last_query(); die();
        return $id;
    }

  function deletedata($id=NULL)
    {
        $this->db->where('id', $id);
        $this->db->delete('property');
         return $this->db->affected_rows();
    }
      // Select total records
    public function getrecordCounttest() {

      $this->db->select('count(*) as allcount');
        $this->db->from('property');
      
       

        $query = $this->db->get();
        $result = $query->result_array();
      
        return $result[0]['allcount'];
    }
   	}