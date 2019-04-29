<?php

class Frontmodel extends CI_Model
{
	var $tablenm="";
	var $table_meta="";
	var $module="";
  	function __construct()
	{
	    //call the model constructor
	    parent::__construct();	    
	}
	/**************************Add Admin user details in database*************************************/
	function set_table($tablename)
	{
		$this->module = $tablename;
		$this->tablenm = "tbl_".$tablename;
		$this->table_meta="tbl_".$tablename."_meta";
	}
	function add($ad_data)
	{
		//echo $this->tablenm; die();
		$this->db->insert($this->tablenm,$ad_data);
		return true;
	}
	
	function update($id=NULL,$ad_data=NULL)
	{
		@$id_field = $this->getPrimaryField();
		$this->db->where($id_field,$id);
		$this->db->update($this->tablenm,$ad_data);
		
		return true;
	}	
        
	function delete($id=NULL)
	{
		
		@$id_field = $this->getPrimaryField();
		$record=$this->getFrontViewDetail($id);
		$fields_arr=$this->getRecordList($this->table_meta,'field_type=6');
		
		//remove images if exist
		if($fields_arr)
		{
			$dir=realpath(UPLOAD_PATH.$this->module);
			foreach($fields_arr as $field_key=>$fields) 
			{
				if($record[$fields['field_name']]!="")
				{
					$file2=$dir.DIRECTORY_SEPARATOR.$record[$fields['field_name']];;
					if(file_exists($file2))
						unlink($file2);
				}
			}
		}

		$this->db->where('case_id',$id);
		$this->db->delete(TABLE_FE_ASSIGN);		

		if($this->tablenm == 'tbl_cases')
		{
			$this->db->select('case_id,parent_case_id,all_case_tat_status');
			$this->db->where('parent_case_id',$id);
			if($_SESSION['sess_user_emp_id']!='7')
			{
				$this->db->where('(delete_flag IS NULL)',NULL,FALSE);
			}
			$this->db->order_by('case_id');
			$qry = $this->db->get($this->tablenm);
			//echo $this->db->last_query();die();
			if($qry->num_rows()>1){
				
				$res = $qry->result_array();//echo '<pre>';print_r($res);die();
				if(!empty($res))
				{
					$parentcasedetails = $res[0];
					$firstcasedetails = $res[1];
					
					$this->db->where('case_id',$firstcasedetails['case_id']);
					if($_SESSION['sess_user_emp_id']!='7')
					{
						$this->db->where('(delete_flag IS NULL)',NULL,FALSE);
					}
					$this->db->update('tbl_cases',array('all_case_tat_status'=>$parentcasedetails['all_case_tat_status']));
					
					$this->db->where('parent_case_id',$parentcasedetails['case_id']);
					if($_SESSION['sess_user_emp_id']!='7')
					{
						$this->db->where('(delete_flag IS NULL)',NULL,FALSE);
					}
					$this->db->update('tbl_cases',array('parent_case_id'=>$firstcasedetails['case_id']));
				}
				
			}
		}

		$this->db->where($id_field,$id);
		$this->db->delete($this->tablenm);
		return true;
	}
	
	function getRecordCount($tmodule=NULL,$where=NULL,$join=NULL,$select=NULL)
	{
		if($select!="")
		return $this->getList($tmodule,2,1,$where,$select,NULL,NULL,NULL,$join);
		else
		return $this->getList($tmodule,2,1,$where,NULL,NULL,NULL,NULL,$join);
	}
	function getRecordList($tmodule=NULL,$where=NULL,$select=NULL,$orderby=NULL,$recordperpage=NULL,$pageno=NULL,$join=NULL)
	{
		return $this->getList($tmodule,1,1,$where,$select,$orderby,$recordperpage,$pageno,$join);
	}
	function getAdminRecordCount($tmodule=NULL,$where=NULL)
	{
		return $this->getList($tmodule,2,2,$where);
	}
	function getAdminRecordList($tmodule=NULL,$where=NULL,$select=NULL,$orderby=NULL,$recordperpage=NULL,$pageno=NULL)
	{
		return $this->getList($tmodule,1,2,$where,$select,$orderby,$recordperpage,$pageno);
	}
	/********************* function to get list  **************************/
	function getList($tmodule,$typeflag=1,$frontflag=NULL,$where=NULL,$select=NULL,$orderby=NULL,$recordperpage=NULL,$pageno=NULL,$join=NULL)
	{
		
		if(stripos($tmodule,"tbl_")===FALSE)
		{
			$tmodule_arr=$this->getModule($tmodule);
			if(empty($tmodule_arr))
				return false;
		}
		else
		{
			$tmodule_arr['DB_name']=$tmodule;
			$tmodule_arr['Join']=$join;
		}
		
		
		if(@$tmodule_arr['Select'])
		{
			$this->db->select(@$tmodule_arr['Select'],FALSE);
		}
		if(@$select)
			$this->db->select($select,FALSE);
		if(@$where)
			$this->db->where($where,NULL,FALSE);
			
		// Updated soft delete
		if($_SESSION['sess_user_emp_id']!='7' && $tmodule_arr['DB_name']=='tbl_cases')
		{
			$this->db->where('(delete_flag IS NULL)',NULL,FALSE);
		}	
		if($tmodule=='user')
		{
				if($this->input->get('flag')=='search')
				{
					$_SESSION["sess_search_get"]=$_GET;
					$where="(nick_name like '%".$_GET['txt_keyword']."%'  or `real_name` like '%".$_GET['txt_keyword']."%'  or `introduction` like '%".$_GET['txt_keyword']."%' or `experience_skill` like '%".$_GET['txt_keyword']."%')";
					if(!is_null($where))	
						$_SESSION["sess_search_where"]=$where;
					else					
						$_SESSION["sess_search_where"]=NULL;
				}
				if($_SESSION["sess_search_where"])
					$this->db->where($_SESSION["sess_search_where"]);
		}	
			
			
		if($frontflag==1 && @$tmodule_arr['STATUS_FIELD'])
			$this->db->where(@$tmodule_arr['STATUS_FIELD'],1);
		if(@$tmodule_arr['Join'])
		{
			$j_arr=@$tmodule_arr['Join'];
			foreach($j_arr as $j_rec)
			{
				$this->db->join($j_rec['table'],$j_rec['condition'],@$j_rec['type']); 
			}
		}
		
		if(!is_null($recordperpage) && !is_null($pageno))
		{
			if( empty($pageno) || ( $pageno<1 ) )
				$nextrecord = 0 ;
			else
				$nextrecord = ($pageno-1) * $recordperpage ;
			$this->db->limit($recordperpage,$nextrecord);	
		}
		
		if(@$tmodule_arr['LIST_WHERE'])
		{
			$this->db->where(@$tmodule_arr['LIST_WHERE'],NULL,FALSE);
		}
		
		if($orderby)
			//$this->db->order_by($orderby,"asc");
			$this->db->order_by($orderby);
		elseif(@$tmodule_arr['Order'])
			$this->db->order_by($tmodule_arr['Order'],"asc");
		$query = $this->db->get($tmodule_arr['DB_name']);
		//echo $this->db->last_query().br(1);
		if($typeflag==2)
			$records = $query->num_rows();
		else
			$records = $query->result_array();

		return $records;
	}
	
	function getFrontViewDetail($id=NULL,$select=NULL)
	{
		//$tmodule_arr=$this->getModule($tmodule);
		@$id_field = $this->getPrimaryField();
		$join_arr=array();
		$select_arr=array();
		$records=$this->getRecordList($this->table_meta,'field_foreign_key!= ""');
		if($records)
		foreach($records as $k=>$recs)
		{
			$tno='t'.$k;
			$pk=$this->Mastermodel->getCustomeFieldValue("tbl_mst_".@$recs['field_foreign_key']."_meta","field_type",12,"field_name");
			$s_value=$this->Mastermodel->getCustomeFieldValue("tbl_mst_".@$recs['field_foreign_key']."_meta","list_order",1,"field_name");
			$join_arr[]=array("table"=>"tbl_mst_".$recs['field_foreign_key'].' as '.$tno,'condition'=>$this->tablenm.'.'.$recs["field_name"].'='.$tno.'.'.$pk,'type'=>'left');
			$select_arr[]=$tno.'.'.$s_value;
		}
		
		$select=NULL;
		if($select_arr)
		{
			$select=$this->tablenm.".*,".implode(",",$select_arr);
		}
		
		
		if(@$join_arr)
		{
			foreach($join_arr as $j_rec)
			{
				$this->db->join($j_rec['table'],$j_rec['condition'],@$j_rec['type']); 
			}
		}
		
		if(@$select)
			$this->db->select($select);
		else
		{
		}
		
		$this->db->where($id_field, $id); 
		$query = $this->db->get($this->tablenm);
		
		$rec_adm=$query->row_array();
		
		if(@$tmodule_arr['SUB_TABLE'])
		{
			foreach(@$tmodule_arr['SUB_TABLE'] as $sub_key=>$sub_value)
			{
				$f_value=(@$sub_value['foreign_value']!="")?@$sub_value['foreign_value']:$id;
				$this->db->where($sub_value['foreign_key'], $f_value); 
				if(@$sub_value['Join'])
				{
					$j_arr1=@$sub_value['Join'];
					foreach($j_arr1 as $j_rec)
					{
						$this->db->join($j_rec['table'],$j_rec['condition'],@$j_rec['type']); 
					}
				}
				$query = $this->db->get($sub_value['db_table']);
				$rec_adm['subtable'][$sub_key]=$query->result_array();
			}
		}
		return @$rec_adm;
	}
	
	function getModule($mmodule=NULL)
	{
		if(!$mmodule)
			die("no module exist");
		$table = lang("TABLE");
		$mmodule_arr=$table[$mmodule];
		return $mmodule_arr;
	}
	
	function getPrimaryField()
	{
		$this->db->where('field_type',12);
		$query = $this->db->get($this->table_meta);
		$t=$query->row_array();
		return $t['field_name'];
	}
	
	function fetchTemplateid($veriftype_id=NULL)
	{
		if($_SESSION['sess_user_activity_id']=="")
			return false;
		//get template
		$veriftype_id=(@$_SESSION['sess_user_verifytype_id']!="")?@$_SESSION['sess_user_verifytype_id']:$veriftype_id;
		$template_id="";
		$where ='case_activity_id='.$_SESSION['sess_user_activity_id'].' AND case_product_id IS NULL AND case_client_id IS NULL AND case_veriftype_id IS NULL';
		$select="case_id";
		$records = $this->getRecordList(TABLE_MST_CASE,$where,$select);
		if(@$records[0]['case_id'])
		{
			$template_id=$records[0]['case_id'];
		}
		if(@$_SESSION['sess_user_product_id']!="")
		{
			$where ='case_activity_id='.$_SESSION['sess_user_activity_id'].' AND case_product_id='.$_SESSION['sess_user_product_id'].' AND case_client_id IS NULL AND case_veriftype_id IS NULL';
			$select="case_id";
			$records = $this->getRecordList(TABLE_MST_CASE,$where,$select);
			if(@$records[0]['case_id'])
			{
				$template_id=$records[0]['case_id'];
			}	
		}
		if(@$_SESSION['sess_user_client_id']!="")
		{
			$where ='case_activity_id='.$_SESSION['sess_user_activity_id'].' AND case_client_id='.$_SESSION['sess_user_client_id'].' AND case_product_id IS NULL AND case_veriftype_id IS NULL';
			$select="case_id";
			$records = $this->getRecordList(TABLE_MST_CASE,$where,$select);
			if(@$records[0]['case_id'])
			{
				$template_id=$records[0]['case_id'];
			}	
		}
		if(@$_SESSION['sess_user_product_id']!="" && @$_SESSION['sess_user_client_id']!="")
		{
			$where ='case_activity_id='.$_SESSION['sess_user_activity_id'].' AND case_product_id='.$_SESSION['sess_user_product_id'].' AND case_client_id='.$_SESSION['sess_user_client_id'].' AND case_veriftype_id IS NULL';
			$select="case_id";
			$records = $this->getRecordList(TABLE_MST_CASE,$where,$select);
			if(@$records[0]['case_id'])
			{
				$template_id=$records[0]['case_id'];
			}
		}
		
		if(@$veriftype_id!="")
		{
			$where ='case_activity_id='.$_SESSION['sess_user_activity_id'].' AND case_veriftype_id='.$veriftype_id.' AND case_client_id IS NULL AND case_product_id IS NULL';
			$select="case_id";
			$records = $this->getRecordList(TABLE_MST_CASE,$where,$select);
			if(@$records[0]['case_id'])
			{
				$template_id=$records[0]['case_id'];
			}
		}
		if(@$_SESSION['sess_user_product_id']!="" && @$veriftype_id!="")
		{
			$where ='case_activity_id='.$_SESSION['sess_user_activity_id'].' AND case_veriftype_id='.$veriftype_id.' AND case_product_id='.$_SESSION['sess_user_product_id'].' AND case_client_id IS NULL';
			$select="case_id";
			$records = $this->getRecordList(TABLE_MST_CASE,$where,$select);
			if(@$records[0]['case_id'])
			{
				$template_id=$records[0]['case_id'];
			}
		}
		if(@$_SESSION['sess_user_client_id']!="" && @$veriftype_id!="")
		{
			$where ='case_activity_id='.$_SESSION['sess_user_activity_id'].' AND case_veriftype_id='.$veriftype_id.' AND case_client_id='.$_SESSION['sess_user_client_id'].' AND case_product_id IS NULL';
			$select="case_id";
			$records = $this->getRecordList(TABLE_MST_CASE,$where,$select);
			if(@$records[0]['case_id'])
			{
				$template_id=$records[0]['case_id'];
			}
		}
		if(@$_SESSION['sess_user_client_id']!="" && @$veriftype_id!="" && @$_SESSION['sess_user_product_id']!="" )
		{
			$where ='case_activity_id='.$_SESSION['sess_user_activity_id'].' AND case_veriftype_id='.$veriftype_id.' AND case_client_id='.$_SESSION['sess_user_client_id'].' AND case_product_id='.$_SESSION['sess_user_product_id'];
			$select="case_id";
			$records = $this->getRecordList(TABLE_MST_CASE,$where,$select);
			if(@$records[0]['case_id'])
			{
				$template_id=$records[0]['case_id'];
			}
		}
		return $template_id;
	}
	
	function getAdminmenus()
	{
		$this->db->select('menu_name, submenu_name, submenu_url');
		$this->db->from(TABLE_SUBMENU);
		$this->db->join(TABLE_MENU,TABLE_MENU.'.menu_id = '.TABLE_SUBMENU.'.submenu_menu_id');
		$this->db->order_by(TABLE_MENU.'.menu_order','asc');
		$this->db->order_by(TABLE_SUBMENU.'.submenu_order','asc');
		$this->db->where(TABLE_SUBMENU.'.submenu_status',1);
		
		$query = $this->db->get();
		$rec = $query->result_array();
		return $rec;
	}
	function getEmpRate($rate_params)
	{
		$where_arr=array();
		$rate_exist=array();
		$emprate=NULL;
		
		$pncodfield = 'pincode_name';
		$pincode_cond = '';
		
		//echo '<pre>';print_r($rate_params);die();
		
		if($rate_params['pincode'] == "")
		$pincode_cond = ' (emprate_pincode_name IS NULL AND icl_ocl IS NULL) ';
		else
		{
			if(in_array(strtoupper($rate_params['pincode']),array('ICL','OCL','BOCL','Non Serviceable')))
			$pncodfield = 'icl_ocl';
			
			$pincode_cond = ' '.$pncodfield.' = "'.$rate_params['pincode'].'"';
		}
		$flag=1;
		if($rate_params['verification']!="")
		{
			if(@$rate_params['product']!="" && @$rate_params['subcenter']!="" && @$rate_params['fe']!="")
			{
				$where = '';
				$where = 'emprate_subcentreid='.$rate_params['subcenter'].' AND emprate_product_name='.$rate_params['product'].' AND emprate_veriftype_name = '.$rate_params['verification'].' AND '.@$pincode_cond.' AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');			
				// : 1
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}				
			}
			if(@$rate_params['activity']!="" && @$rate_params['subcenter']!="" && @$rate_params['fe']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_subcentreid='.$rate_params['subcenter'].' AND emprate_activity_name='.$rate_params['activity'].' AND emprate_product_name IS NULL AND emprate_veriftype_name = '.$rate_params['verification'].' AND '.@$pincode_cond.' AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 2
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}				
			}
			if(@$rate_params['subcenter']!="" && @$rate_params['fe']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_subcentreid='.$rate_params['subcenter'].' AND emprate_activity_name IS NULL AND emprate_product_name IS NULL AND emprate_veriftype_name = '.$rate_params['verification'].' AND '.@$pincode_cond.' AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');			
				// : 3
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			if(@$rate_params['activity']!="" && @$rate_params['product']!="" && @$rate_params['center']!="" && @$rate_params['fe']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_centreid='.$rate_params['center'].' AND emprate_subcentreid IS NULL AND emprate_activity_name ='.$rate_params['activity'].' AND emprate_product_name ='.@$rate_params['product'].' AND emprate_veriftype_name = '.$rate_params['verification'].' AND '.@$pincode_cond.' AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 4
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			
			if(@$rate_params['activity']!="" && @$rate_params['center']!="" && @$rate_params['fe']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_centreid='.$rate_params['center'].' AND emprate_subcentreid IS NULL AND emprate_activity_name ='.@$rate_params['activity'].' AND emprate_product_name IS NULL AND emprate_veriftype_name = '.$rate_params['verification'].' AND '.@$pincode_cond.' AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 5
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			
			if(@$rate_params['center']!="" && @$rate_params['fe']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_centreid ='.@$rate_params['center'].' AND emprate_subcentreid IS NULL AND emprate_activity_name IS NULL AND emprate_product_name IS NULL AND emprate_veriftype_name = '.$rate_params['verification'].' AND '.@$pincode_cond.' AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 6
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			
			if(@$rate_params['zone']!="" && @$rate_params['product']!="" && $rate_params['activity']!="" && @$rate_params['fe']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_clusterid='.$rate_params['zone'].' AND emprate_centreid IS NULL AND  emprate_activity_name='.@$rate_params['activity'].' AND emprate_product_name='.@$rate_params['product'].' AND emprate_veriftype_name = '.$rate_params['verification'].' AND '.@$pincode_cond.' AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 7
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			
			if(@$rate_params['zone']!="" && @$rate_params['activity']!="" && @$rate_params['fe']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_clusterid='.$rate_params['zone'].' AND emprate_centreid IS NULL AND  emprate_activity_name='.@$rate_params['activity'].' AND emprate_product_name IS NULL AND emprate_veriftype_name = '.$rate_params['verification'].' AND '.@$pincode_cond.' AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 8
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			
			if(@$rate_params['zone']!="" && @$rate_params['fe']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_clusterid='.$rate_params['zone'].' AND emprate_centreid IS NULL AND  emprate_activity_name IS NULL AND emprate_product_name IS NULL AND emprate_veriftype_name = '.$rate_params['verification'].' AND '.@$pincode_cond.' AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 9
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			
			if(@$rate_params['country']!="" && @$rate_params['activity']!="" && @$rate_params['product']!="" && @$rate_params['fe']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_country_name='.$rate_params['country'].' AND emprate_clusterid IS NULL AND emprate_activity_name ='.@$rate_params['activity'].' AND emprate_product_name ='.@$rate_params['product'].' AND emprate_veriftype_name = '.$rate_params['verification'].' AND '.@$pincode_cond.' AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 10
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			
			if(@$rate_params['country']!="" && @$rate_params['activity']!="" && @$rate_params['fe']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_country_name='.$rate_params['country'].' AND emprate_clusterid IS NULL AND emprate_activity_name ='.@$rate_params['activity'].' AND emprate_product_name IS NULL AND emprate_veriftype_name = '.$rate_params['verification'].' AND '.@$pincode_cond.' AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 11
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			
			if(@$rate_params['country']!="" && @$rate_params['fe']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_country_name='.$rate_params['country'].' AND emprate_clusterid IS NULL AND emprate_activity_name IS NULL AND emprate_product_name IS NULL AND emprate_veriftype_name = '.$rate_params['verification'].' AND '.@$pincode_cond.' AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 12
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}	
			
			
			/********************** Without FE ***********************************/
			
			
			if(@$rate_params['product']!="" && @$rate_params['subcenter']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_subcentreid='.$rate_params['subcenter'].' AND emprate_product_name='.$rate_params['product'].' AND emprate_veriftype_name = '.$rate_params['verification'].' AND '.@$pincode_cond.' AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');			
				// : 13
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			if(@$rate_params['activity']!="" && @$rate_params['subcenter']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_subcentreid='.$rate_params['subcenter'].' AND emprate_activity_name='.$rate_params['activity'].' AND emprate_product_name IS NULL AND emprate_veriftype_name = '.$rate_params['verification'].' AND '.@$pincode_cond.' AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 14
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			if(@$rate_params['subcenter']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_subcentreid='.$rate_params['subcenter'].' AND emprate_activity_name IS NULL AND emprate_product_name IS NULL AND emprate_veriftype_name = '.$rate_params['verification'].' AND '.@$pincode_cond.' AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');			
				// : 15
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			if(@$rate_params['activity']!="" && @$rate_params['product']!="" && @$rate_params['center']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_centreid='.$rate_params['center'].' AND emprate_subcentreid IS NULL AND emprate_activity_name ='.$rate_params['activity'].' AND emprate_product_name ='.@$rate_params['product'].' AND emprate_veriftype_name = '.$rate_params['verification'].' AND '.@$pincode_cond.' AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 16
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			
			if(@$rate_params['activity']!="" && @$rate_params['center']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_centreid='.$rate_params['center'].' AND emprate_subcentreid IS NULL AND emprate_activity_name ='.@$rate_params['activity'].' AND emprate_product_name IS NULL AND emprate_veriftype_name = '.$rate_params['verification'].' AND '.@$pincode_cond.' AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 17
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			
			if(@$rate_params['center']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_centreid ='.@$rate_params['center'].' AND emprate_subcentreid IS NULL AND emprate_activity_name IS NULL AND emprate_product_name IS NULL AND emprate_veriftype_name = '.$rate_params['verification'].' AND '.@$pincode_cond.' AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 18
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			
			if(@$rate_params['zone']!="" && @$rate_params['product']!="" && $rate_params['activity']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_clusterid='.$rate_params['zone'].' AND emprate_centreid IS NULL AND  emprate_activity_name='.@$rate_params['activity'].' AND emprate_product_name='.@$rate_params['product'].' AND emprate_veriftype_name = '.$rate_params['verification'].' AND '.@$pincode_cond.' AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 19
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			
			if(@$rate_params['zone']!="" && @$rate_params['activity']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_clusterid='.$rate_params['zone'].' AND emprate_centreid IS NULL AND  emprate_activity_name='.@$rate_params['activity'].' AND emprate_product_name IS NULL AND emprate_veriftype_name = '.$rate_params['verification'].' AND '.@$pincode_cond.' AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 20
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			
			if(@$rate_params['zone']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_clusterid='.$rate_params['zone'].' AND emprate_centreid IS NULL AND  emprate_activity_name IS NULL AND emprate_product_name IS NULL AND emprate_veriftype_name = '.$rate_params['verification'].' AND '.@$pincode_cond.' AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 21
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			
			if(@$rate_params['country']!="" && @$rate_params['activity']!="" && @$rate_params['product']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_country_name='.$rate_params['country'].' AND emprate_clusterid IS NULL AND emprate_activity_name ='.@$rate_params['activity'].' AND emprate_product_name ='.@$rate_params['product'].' AND emprate_veriftype_name = '.$rate_params['verification'].' AND '.@$pincode_cond.' AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 22
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			
			if(@$rate_params['country']!="" && @$rate_params['activity']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_country_name='.$rate_params['country'].' AND emprate_clusterid IS NULL AND emprate_activity_name ='.@$rate_params['activity'].' AND emprate_product_name IS NULL AND emprate_veriftype_name = '.$rate_params['verification'].' AND '.@$pincode_cond.' AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 23
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			
			if(@$rate_params['country']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_country_name='.$rate_params['country'].' AND emprate_clusterid IS NULL AND emprate_activity_name IS NULL AND emprate_product_name IS NULL AND emprate_veriftype_name = '.$rate_params['verification'].' AND '.@$pincode_cond.' AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 24
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			//*********************************rate without pincode*******************************************///
			
			
			if(@$rate_params['product']!="" && @$rate_params['subcenter']!="" && @$rate_params['fe']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_subcentreid='.$rate_params['subcenter'].' AND emprate_product_name='.$rate_params['product'].' AND emprate_veriftype_name = '.$rate_params['verification'].' AND emprate_pincode_name IS NULL AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');			
				// : 25
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}				
			}
			if(@$rate_params['activity']!="" && @$rate_params['subcenter']!="" && @$rate_params['fe']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_subcentreid='.$rate_params['subcenter'].' AND emprate_activity_name='.$rate_params['activity'].' AND emprate_product_name IS NULL AND emprate_veriftype_name = '.$rate_params['verification'].' AND emprate_pincode_name IS NULL AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 26
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}				
			}
			if(@$rate_params['subcenter']!="" && @$rate_params['fe']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_subcentreid='.$rate_params['subcenter'].' AND emprate_activity_name IS NULL AND emprate_product_name IS NULL AND emprate_veriftype_name = '.$rate_params['verification'].' AND emprate_pincode_name IS NULL AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');			
				// : 27
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			if(@$rate_params['activity']!="" && @$rate_params['product']!="" && @$rate_params['center']!="" && @$rate_params['fe']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_centreid='.$rate_params['center'].' AND emprate_subcentreid IS NULL AND emprate_activity_name ='.$rate_params['activity'].' AND emprate_product_name ='.@$rate_params['product'].' AND emprate_veriftype_name = '.$rate_params['verification'].' AND emprate_pincode_name IS NULL AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 28
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			
			if(@$rate_params['activity']!="" && @$rate_params['center']!="" && @$rate_params['fe']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_centreid='.$rate_params['center'].' AND emprate_subcentreid IS NULL AND emprate_activity_name ='.@$rate_params['activity'].' AND emprate_product_name IS NULL AND emprate_veriftype_name = '.$rate_params['verification'].' AND emprate_pincode_name IS NULL AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 29
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			
			if(@$rate_params['center']!="" && @$rate_params['fe']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_centreid ='.@$rate_params['center'].' AND emprate_subcentreid IS NULL AND emprate_activity_name IS NULL AND emprate_product_name IS NULL AND emprate_veriftype_name = '.$rate_params['verification'].' AND emprate_pincode_name IS NULL AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 30
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			
			if(@$rate_params['zone']!="" && @$rate_params['product']!="" && $rate_params['activity']!="" && @$rate_params['fe']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_clusterid='.$rate_params['zone'].' AND emprate_centreid IS NULL AND  emprate_activity_name='.@$rate_params['activity'].' AND emprate_product_name='.@$rate_params['product'].' AND emprate_veriftype_name = '.$rate_params['verification'].' AND emprate_pincode_name IS NULL AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 31
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			
			if(@$rate_params['zone']!="" && @$rate_params['activity']!="" && @$rate_params['fe']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_clusterid='.$rate_params['zone'].' AND emprate_centreid IS NULL AND  emprate_activity_name='.@$rate_params['activity'].' AND emprate_product_name IS NULL AND emprate_veriftype_name = '.$rate_params['verification'].' AND emprate_pincode_name IS NULL AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 32
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			
			if(@$rate_params['zone']!="" && @$rate_params['fe']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_clusterid='.$rate_params['zone'].' AND emprate_centreid IS NULL AND  emprate_activity_name IS NULL AND emprate_product_name IS NULL AND emprate_veriftype_name = '.$rate_params['verification'].' AND emprate_pincode_name IS NULL AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 33
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			
			if(@$rate_params['country']!="" && @$rate_params['activity']!="" && @$rate_params['product']!="" && @$rate_params['fe']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_country_name='.$rate_params['country'].' AND emprate_clusterid IS NULL AND emprate_activity_name ='.@$rate_params['activity'].' AND emprate_product_name ='.@$rate_params['product'].' AND emprate_veriftype_name = '.$rate_params['verification'].' AND emprate_pincode_name IS NULL AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 34
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			
			if(@$rate_params['country']!="" && @$rate_params['activity']!="" && @$rate_params['fe']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_country_name='.$rate_params['country'].' AND emprate_clusterid IS NULL AND emprate_activity_name ='.@$rate_params['activity'].' AND emprate_product_name IS NULL AND emprate_veriftype_name = '.$rate_params['verification'].' AND emprate_pincode_name IS NULL AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 35
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			
			if(@$rate_params['country']!="" && @$rate_params['fe']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_country_name='.$rate_params['country'].' AND emprate_clusterid IS NULL AND emprate_activity_name IS NULL AND emprate_product_name IS NULL AND emprate_veriftype_name = '.$rate_params['verification'].' AND emprate_pincode_name IS NULL AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 36
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}	
			
			
			/********************** Without FE ***********************************/
			
			
			if(@$rate_params['product']!="" && @$rate_params['subcenter']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_subcentreid='.$rate_params['subcenter'].' AND emprate_product_name='.$rate_params['product'].' AND emprate_veriftype_name = '.$rate_params['verification'].' AND emprate_pincode_name IS NULL AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');			
				// : 37
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			if(@$rate_params['activity']!="" && @$rate_params['subcenter']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_subcentreid='.$rate_params['subcenter'].' AND emprate_activity_name='.$rate_params['activity'].' AND emprate_product_name IS NULL AND emprate_veriftype_name = '.$rate_params['verification'].' AND emprate_pincode_name IS NULL AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 38
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			if(@$rate_params['subcenter']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_subcentreid='.$rate_params['subcenter'].' AND emprate_activity_name IS NULL AND emprate_product_name IS NULL AND emprate_veriftype_name = '.$rate_params['verification'].' AND emprate_pincode_name IS NULL AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');			
				// : 39
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			if(@$rate_params['activity']!="" && @$rate_params['product']!="" && @$rate_params['center']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_centreid='.$rate_params['center'].' AND emprate_subcentreid IS NULL AND emprate_activity_name ='.$rate_params['activity'].' AND emprate_product_name ='.@$rate_params['product'].' AND emprate_veriftype_name = '.$rate_params['verification'].' AND emprate_pincode_name IS NULL AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 40
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			
			if(@$rate_params['activity']!="" && @$rate_params['center']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_centreid='.$rate_params['center'].' AND emprate_subcentreid IS NULL AND emprate_activity_name ='.@$rate_params['activity'].' AND emprate_product_name IS NULL AND emprate_veriftype_name = '.$rate_params['verification'].' AND emprate_pincode_name IS NULL AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 41
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			
			if(@$rate_params['center']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_centreid ='.@$rate_params['center'].' AND emprate_subcentreid IS NULL AND emprate_activity_name IS NULL AND emprate_product_name IS NULL AND emprate_veriftype_name = '.$rate_params['verification'].' AND emprate_pincode_name IS NULL AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 42
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			
			if(@$rate_params['zone']!="" && @$rate_params['product']!="" && $rate_params['activity']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_clusterid='.$rate_params['zone'].' AND emprate_centreid IS NULL AND  emprate_activity_name='.@$rate_params['activity'].' AND emprate_product_name='.@$rate_params['product'].' AND emprate_veriftype_name = '.$rate_params['verification'].' AND emprate_pincode_name IS NULL AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 43
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			
			if(@$rate_params['zone']!="" && @$rate_params['activity']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_clusterid='.$rate_params['zone'].' AND emprate_centreid IS NULL AND  emprate_activity_name='.@$rate_params['activity'].' AND emprate_product_name IS NULL AND emprate_veriftype_name = '.$rate_params['verification'].' AND emprate_pincode_name IS NULL AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 44
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			
			if(@$rate_params['zone']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_clusterid='.$rate_params['zone'].' AND emprate_centreid IS NULL AND  emprate_activity_name IS NULL AND emprate_product_name IS NULL AND emprate_veriftype_name = '.$rate_params['verification'].' AND emprate_pincode_name IS NULL AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 45
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			
			if(@$rate_params['country']!="" && @$rate_params['activity']!="" && @$rate_params['product']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_country_name='.$rate_params['country'].' AND emprate_clusterid IS NULL AND emprate_activity_name ='.@$rate_params['activity'].' AND emprate_product_name ='.@$rate_params['product'].' AND emprate_veriftype_name = '.$rate_params['verification'].' AND emprate_pincode_name IS NULL AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 46
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			
			if(@$rate_params['country']!="" && @$rate_params['activity']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_country_name='.$rate_params['country'].' AND emprate_clusterid IS NULL AND emprate_activity_name ='.@$rate_params['activity'].' AND emprate_product_name IS NULL AND emprate_veriftype_name = '.$rate_params['verification'].' AND emprate_pincode_name IS NULL AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 47
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			
			if(@$rate_params['country']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_country_name='.$rate_params['country'].' AND emprate_clusterid IS NULL AND emprate_activity_name IS NULL AND emprate_product_name IS NULL AND emprate_veriftype_name = '.$rate_params['verification'].' AND emprate_pincode_name IS NULL AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 48
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			//$where_arr[]="emprate_veriftype_name=".$rate_params['emprate_veriftype_name'];
		}
		if($flag == 1)
		{
			/********************** With FE ****************/
			if(@$rate_params['product']!="" && @$rate_params['subcenter']!="" && @$rate_params['fe']!="" )
			{
				$where = '';
				$where = 'emprate_subcentreid='.$rate_params['subcenter'].' AND emprate_product_name='.$rate_params['product'].' AND emprate_veriftype_name IS NULL AND '.@$pincode_cond.' AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 49
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			if(@$rate_params['activity']!="" && @$rate_params['subcenter']!="" && @$rate_params['fe']!=""  && $flag==1)
			{
				$where = '';
				$where = 'emprate_subcentreid='.$rate_params['subcenter'].' AND emprate_activity_name='.$rate_params['activity'].' AND emprate_product_name IS NULL AND emprate_veriftype_name IS NULL AND '.@$pincode_cond.' AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 50
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			if(@$rate_params['subcenter']!="" && @$rate_params['fe']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_subcentreid='.$rate_params['subcenter'].' AND emprate_activity_name IS NULL AND emprate_product_name IS NULL AND emprate_veriftype_name IS NULL AND '.@$pincode_cond.' AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 51
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}				
			}			
			if(@$rate_params['activity']!="" && @$rate_params['product']!="" && @$rate_params['center']!="" && @$rate_params['fe']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_centreid='.$rate_params['center'].' AND emprate_subcentreid IS NULL AND emprate_activity_name ='.$rate_params['activity'].' AND emprate_product_name ='.@$rate_params['product'].' AND emprate_veriftype_name IS NULL AND '.@$pincode_cond.' AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 52
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}				
			}			
			if(@$rate_params['activity']!="" && @$rate_params['center']!="" && @$rate_params['subcenter']=="" && @$rate_params['fe']!=""  && $flag==1)
			{
				$where = '';
				$where = 'emprate_centreid='.$rate_params['center'].' AND emprate_subcentreid IS NULL AND emprate_activity_name ='.@$rate_params['activity'].' AND emprate_product_name IS NULL AND emprate_veriftype_name  IS NULL AND '.@$pincode_cond.' AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 53
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}				
			}			
			if(@$rate_params['center']!="" && @$rate_params['fe']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_centreid ='.@$rate_params['center'].' AND emprate_subcentreid IS NULL AND emprate_activity_name IS NULL AND emprate_product_name IS NULL AND emprate_veriftype_name  IS NULL AND '.@$pincode_cond.' AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 54
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}				
			}			
			if(@$rate_params['zone']!="" && @$rate_params['product']!="" && @$rate_params['fe']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_clusterid='.$rate_params['zone'].' AND emprate_centreid IS NULL AND  emprate_activity_name='.@$rate_params['activity'].' AND emprate_product_name='.@$rate_params['product'].' AND emprate_veriftype_name IS NULL AND '.@$pincode_cond.' AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 55
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}				
			}			
			if(@$rate_params['zone']!="" && @$rate_params['activity']!="" && @$rate_params['fe']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_clusterid='.$rate_params['zone'].' AND emprate_centreid IS NULL AND  emprate_activity_name='.@$rate_params['activity'].' AND emprate_product_name IS NULL AND emprate_veriftype_name IS NULL AND '.@$pincode_cond.' AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 56
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}				
			}			
			if(@$rate_params['zone']!="" && @$rate_params['fe']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_clusterid='.$rate_params['zone'].' AND emprate_centreid IS NULL AND  emprate_activity_name IS NULL AND emprate_product_name IS NULL AND emprate_veriftype_name IS NULL AND '.@$pincode_cond.' AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');			
				// : 57
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}				
			}			
			if(@$rate_params['country']!="" && @$rate_params['activity']!="" && @$rate_params['product']!="" && @$rate_params['fe']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_country_name='.$rate_params['country'].' AND emprate_clusterid IS NULL AND emprate_activity_name ='.@$rate_params['activity'].' AND emprate_product_name ='.@$rate_params['product'].' AND emprate_veriftype_name  IS NULL AND '.@$pincode_cond.' AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 58
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}				
			}			
			if(@$rate_params['country']!="" && @$rate_params['activity']!="" && @$rate_params['fe']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_country_name='.$rate_params['country'].' AND emprate_clusterid IS NULL AND emprate_activity_name ='.@$rate_params['activity'].' AND emprate_product_name IS NULL AND emprate_veriftype_name  IS NULL AND '.@$pincode_cond.' AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 59
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}				
			}			
			if(@$rate_params['country']!="" && @$rate_params['fe']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_country_name='.$rate_params['country'].' AND emprate_clusterid IS NULL AND emprate_centreid IS NULL AND emprate_subcentreid IS NULL AND emprate_activity_name IS NULL AND emprate_product_name IS NULL AND emprate_veriftype_name  IS NULL AND '.@$pincode_cond.' AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 60
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}				
			}			
			
			//*********************************If rate not exist as per given pincode value*********************************************************
			if(@$rate_params['product']!="" && @$rate_params['subcenter']!="" && @$rate_params['fe']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_subcentreid='.$rate_params['subcenter'].' AND emprate_product_name='.$rate_params['product'].' AND emprate_veriftype_name IS NULL AND emprate_pincode_name IS NULL AND icl_ocl IS NULL  AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 61
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			if(@$rate_params['activity']!="" && @$rate_params['subcenter']!="" && @$rate_params['fe']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_subcentreid='.$rate_params['subcenter'].' AND emprate_activity_name='.$rate_params['activity'].' AND emprate_product_name IS NULL AND emprate_veriftype_name IS NULL AND emprate_pincode_name IS NULL AND icl_ocl IS NULL AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 62
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			if(@$rate_params['subcenter']!="" && @$rate_params['fe']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_subcentreid='.$rate_params['subcenter'].' AND emprate_activity_name IS NULL AND emprate_product_name IS NULL AND emprate_veriftype_name IS NULL AND emprate_pincode_name IS NULL AND icl_ocl IS NULL  AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 63
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}				
			}			
			if(@$rate_params['activity']!="" && @$rate_params['product']!="" && @$rate_params['center']!="" && @$rate_params['fe']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_centreid='.$rate_params['center'].' AND emprate_subcentreid IS NULL AND emprate_activity_name ='.$rate_params['activity'].' AND emprate_product_name ='.@$rate_params['product'].' AND emprate_veriftype_name IS NULL AND emprate_pincode_name IS NULL AND icl_ocl IS NULL AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 64
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}				
			}			
			if(@$rate_params['activity']!="" && @$rate_params['center']!="" && @$rate_params['subcenter']=="" && @$rate_params['fe']!=""  && $flag==1)
			{
				$where = '';
				$where = 'emprate_centreid='.$rate_params['center'].' AND emprate_subcentreid IS NULL AND emprate_activity_name ='.@$rate_params['activity'].' AND emprate_product_name IS NULL AND emprate_veriftype_name  IS NULL AND emprate_pincode_name IS NULL AND icl_ocl IS NULL AND emprate_fe_id = '.@$rate_params['fe'];				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 65
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}				
			}			
			if(@$rate_params['center']!="" && @$rate_params['fe']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_centreid ='.@$rate_params['center'].' AND emprate_subcentreid IS NULL AND emprate_activity_name IS NULL AND emprate_product_name IS NULL AND emprate_veriftype_name  IS NULL AND emprate_pincode_name IS NULL AND icl_ocl IS NULL AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 66
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}				
			}			
			if(@$rate_params['zone']!="" && @$rate_params['product']!="" && @$rate_params['fe']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_clusterid='.$rate_params['zone'].' AND emprate_centreid IS NULL AND  emprate_activity_name='.@$rate_params['activity'].' AND emprate_product_name='.@$rate_params['product'].' AND emprate_veriftype_name IS NULL AND emprate_pincode_name IS NULL AND icl_ocl IS NULL AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 67
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}				
			}			
			if(@$rate_params['zone']!="" && @$rate_params['activity']!="" && @$rate_params['fe']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_clusterid='.$rate_params['zone'].' AND emprate_centreid IS NULL AND  emprate_activity_name='.@$rate_params['activity'].' AND emprate_product_name IS NULL AND emprate_veriftype_name IS NULL AND emprate_pincode_name IS NULL AND icl_ocl IS NULL AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 68
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}				
			}			
			if(@$rate_params['zone']!="" && @$rate_params['fe']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_clusterid='.$rate_params['zone'].' AND emprate_centreid IS NULL AND  emprate_activity_name IS NULL AND emprate_product_name IS NULL AND emprate_veriftype_name IS NULL AND emprate_pincode_name IS NULL AND icl_ocl IS NULL AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 69
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}				
			}			
			if(@$rate_params['country']!="" && @$rate_params['activity']!="" && @$rate_params['product']!="" && @$rate_params['fe']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_country_name='.$rate_params['country'].' AND emprate_clusterid IS NULL AND emprate_activity_name ='.@$rate_params['activity'].' AND emprate_product_name ='.@$rate_params['product'].' AND emprate_veriftype_name  IS NULL AND emprate_pincode_name IS NULL AND icl_ocl IS NULL AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 70
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}				
			}			
			if(@$rate_params['country']!="" && @$rate_params['activity']!="" && @$rate_params['fe']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_country_name='.$rate_params['country'].' AND emprate_clusterid IS NULL AND emprate_activity_name ='.@$rate_params['activity'].' AND emprate_product_name IS NULL AND emprate_veriftype_name  IS NULL AND emprate_pincode_name IS NULL AND icl_ocl IS NULL AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 71
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}				
			}			
			if(@$rate_params['country']!="" && @$rate_params['fe']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_country_name='.$rate_params['country'].' AND emprate_clusterid IS NULL AND emprate_centreid IS NULL AND emprate_subcentreid IS NULL AND emprate_activity_name IS NULL AND emprate_product_name IS NULL AND emprate_veriftype_name  IS NULL AND emprate_pincode_name IS NULL AND icl_ocl IS NULL AND emprate_fe_id = '.@$rate_params['fe'];
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 72
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}				
			}
			
			
			
			/********************** Without FE ****************/
			if(@$rate_params['product']!="" && @$rate_params['subcenter']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_subcentreid='.$rate_params['subcenter'].' AND emprate_product_name='.$rate_params['product'].' AND emprate_veriftype_name IS NULL AND '.@$pincode_cond.' AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 73
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			if(@$rate_params['activity']!="" && @$rate_params['subcenter']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_subcentreid='.$rate_params['subcenter'].' AND emprate_activity_name='.$rate_params['activity'].' AND emprate_product_name IS NULL AND emprate_veriftype_name IS NULL AND '.@$pincode_cond.' AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 74
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			if(@$rate_params['subcenter']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_subcentreid='.$rate_params['subcenter'].' AND emprate_activity_name IS NULL AND emprate_product_name IS NULL AND emprate_veriftype_name IS NULL AND '.@$pincode_cond.' AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 75
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}				
			}			
			if(@$rate_params['activity']!="" && @$rate_params['product']!="" && @$rate_params['center']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_centreid='.$rate_params['center'].' AND emprate_subcentreid IS NULL AND emprate_activity_name ='.$rate_params['activity'].' AND emprate_product_name ='.@$rate_params['product'].' AND emprate_veriftype_name IS NULL AND '.@$pincode_cond.' AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 76
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}				
			}			
			if(@$rate_params['activity']!="" && @$rate_params['center']!="" && @$rate_params['subcenter']==""  && $flag==1)
			{
				$where = '';
				$where = 'emprate_centreid='.$rate_params['center'].' AND emprate_subcentreid IS NULL AND emprate_activity_name ='.@$rate_params['activity'].' AND emprate_product_name IS NULL AND emprate_veriftype_name  IS NULL AND '.@$pincode_cond.' AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 77
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}				
			}			
			if(@$rate_params['center']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_centreid ='.@$rate_params['center'].' AND emprate_subcentreid IS NULL AND emprate_activity_name IS NULL AND emprate_product_name IS NULL AND emprate_veriftype_name  IS NULL AND '.@$pincode_cond.' AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 78
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}				
			}			
			if(@$rate_params['zone']!="" && @$rate_params['product']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_clusterid='.$rate_params['zone'].' AND emprate_centreid IS NULL AND  emprate_activity_name='.@$rate_params['activity'].' AND emprate_product_name='.@$rate_params['product'].' AND emprate_veriftype_name IS NULL AND '.@$pincode_cond.' AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 79
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}				
			}			
			if(@$rate_params['zone']!="" && @$rate_params['activity']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_clusterid='.$rate_params['zone'].' AND emprate_centreid IS NULL AND  emprate_activity_name='.@$rate_params['activity'].' AND emprate_product_name IS NULL AND emprate_veriftype_name IS NULL AND '.@$pincode_cond.' AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 80
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}				
			}			
			if(@$rate_params['zone']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_clusterid='.$rate_params['zone'].' AND emprate_centreid IS NULL AND  emprate_activity_name IS NULL AND emprate_product_name IS NULL AND emprate_veriftype_name IS NULL AND '.@$pincode_cond.' AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');			
				// : 81
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}				
			}			
			if(@$rate_params['country']!="" && @$rate_params['activity']!="" && @$rate_params['product']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_country_name='.$rate_params['country'].' AND emprate_clusterid IS NULL AND emprate_activity_name ='.@$rate_params['activity'].' AND emprate_product_name ='.@$rate_params['product'].' AND emprate_veriftype_name  IS NULL AND '.@$pincode_cond.' AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 82
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}				
			}			
			if(@$rate_params['country']!="" && @$rate_params['activity']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_country_name='.$rate_params['country'].' AND emprate_clusterid IS NULL AND emprate_activity_name ='.@$rate_params['activity'].' AND emprate_product_name IS NULL AND emprate_veriftype_name  IS NULL AND '.@$pincode_cond.' AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 83
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}				
			}			
			if(@$rate_params['country']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_country_name='.$rate_params['country'].' AND emprate_clusterid IS NULL AND emprate_centreid IS NULL AND emprate_subcentreid IS NULL AND emprate_activity_name IS NULL AND emprate_product_name IS NULL AND emprate_veriftype_name  IS NULL AND '.@$pincode_cond.' AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 84
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}				
			}
			
			
			//*********************************If rate not exist as per given pincode value*********************************************************
			
			if(@$rate_params['product']!="" && @$rate_params['subcenter']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_subcentreid='.$rate_params['subcenter'].' AND emprate_product_name='.$rate_params['product'].' AND emprate_veriftype_name IS NULL AND emprate_pincode_name IS NULL AND icl_ocl IS NULL  AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 85
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			if(@$rate_params['activity']!="" && @$rate_params['subcenter']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_subcentreid='.$rate_params['subcenter'].' AND emprate_activity_name='.$rate_params['activity'].' AND emprate_product_name IS NULL AND emprate_veriftype_name IS NULL AND emprate_pincode_name IS NULL AND icl_ocl IS NULL  AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 86
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
				
			}
			if(@$rate_params['subcenter']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_subcentreid='.$rate_params['subcenter'].' AND emprate_activity_name IS NULL AND emprate_product_name IS NULL AND emprate_veriftype_name IS NULL AND emprate_pincode_name IS NULL AND icl_ocl IS NULL  AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 87
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}				
			}			
			if(@$rate_params['activity']!="" && @$rate_params['product']!="" && @$rate_params['center']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_centreid='.$rate_params['center'].' AND emprate_subcentreid IS NULL AND emprate_activity_name ='.$rate_params['activity'].' AND emprate_product_name ='.@$rate_params['product'].' AND emprate_veriftype_name IS NULL AND emprate_pincode_name IS NULL AND icl_ocl IS NULL  AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 88
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}				
			}			
			if(@$rate_params['activity']!="" && @$rate_params['center']!="" && @$rate_params['subcenter']==""  && $flag==1)
			{
				$where = '';
				$where = 'emprate_centreid='.$rate_params['center'].' AND emprate_subcentreid IS NULL AND emprate_activity_name ='.@$rate_params['activity'].' AND emprate_product_name IS NULL AND emprate_veriftype_name  IS NULL AND emprate_pincode_name IS NULL AND icl_ocl IS NULL  AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 89
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}				
			}			
			if(@$rate_params['center']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_centreid ='.@$rate_params['center'].' AND emprate_subcentreid IS NULL AND emprate_activity_name IS NULL AND emprate_product_name IS NULL AND emprate_veriftype_name  IS NULL AND emprate_pincode_name IS NULL AND icl_ocl IS NULL  AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 90
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}				
			}			
			if(@$rate_params['zone']!="" && @$rate_params['product']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_clusterid='.$rate_params['zone'].' AND emprate_centreid IS NULL AND  emprate_activity_name='.@$rate_params['activity'].' AND emprate_product_name='.@$rate_params['product'].' AND emprate_veriftype_name IS NULL AND emprate_pincode_name IS NULL AND icl_ocl IS NULL  AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 91
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}				
			}			
			if(@$rate_params['zone']!="" && @$rate_params['activity']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_clusterid='.$rate_params['zone'].' AND emprate_centreid IS NULL AND  emprate_activity_name='.@$rate_params['activity'].' AND emprate_product_name IS NULL AND emprate_veriftype_name IS NULL AND emprate_pincode_name IS NULL AND icl_ocl IS NULL  AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 92
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}				
			}			
			if(@$rate_params['zone']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_clusterid='.$rate_params['zone'].' AND emprate_centreid IS NULL AND  emprate_activity_name IS NULL AND emprate_product_name IS NULL AND emprate_veriftype_name IS NULL AND emprate_pincode_name IS NULL AND icl_ocl IS NULL  AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 93
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}				
			}			
			if(@$rate_params['country']!="" && @$rate_params['activity']!="" && @$rate_params['product']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_country_name='.$rate_params['country'].' AND emprate_clusterid IS NULL AND emprate_activity_name ='.@$rate_params['activity'].' AND emprate_product_name ='.@$rate_params['product'].' AND emprate_veriftype_name  IS NULL AND emprate_pincode_name IS NULL AND icl_ocl IS NULL  AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 94
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}				
			}			
			if(@$rate_params['country']!="" && @$rate_params['activity']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_country_name='.$rate_params['country'].' AND emprate_clusterid IS NULL AND emprate_activity_name ='.@$rate_params['activity'].' AND emprate_product_name IS NULL AND emprate_veriftype_name  IS NULL AND emprate_pincode_name IS NULL AND icl_ocl IS NULL  AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 95
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}				
			}			
			if(@$rate_params['country']!="" && $flag==1)
			{
				$where = '';
				$where = 'emprate_country_name='.$rate_params['country'].' AND emprate_clusterid IS NULL AND emprate_centreid IS NULL AND emprate_subcentreid IS NULL AND emprate_activity_name IS NULL AND emprate_product_name IS NULL AND emprate_veriftype_name  IS NULL AND emprate_pincode_name IS NULL AND icl_ocl IS NULL  AND emprate_fe_id IS NULL';
				$select="emprate";
				$records = $this->getrecord(TABLE_EMPRATE,$where,$select,'employee');				
				// : 96
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}				
			}			
		}
		//echo $this->db->last_query();die($emprate);
		
		return $emprate;
	}
	function getEmpRate_old($rate_params)
	{
		$where_arr=array();
		$rate_exist=array();
		$emprate=NULL;
		
		$pncodfield = 'emprate_pincode_name';
		if(in_array($rate_params['pincode'],array('ICL','OCL')))
		$pncodfield = 'icl_ocl';
		
		if($rate_params['country']!="")
		{
			$rate=$this->Mastermodel->getCustomeFieldValue(TABLE_EMPRATE,'emprate_country_name',$rate_params['country'],'emprate');
			
			if($rate)
			{
				$emprate=$rate;
				$where_arr[]="emprate_country_name=".$rate_params['country'];
				$rate_exist['country']=$rate_params['country'];
			}
		}
		if($rate_params['zone']!="")
		{
			$rate=$this->Mastermodel->getCustomeFieldValue(TABLE_EMPRATE,'emprate_clusterid',$rate_params['zone'],'emprate');
			if($rate)
			{
				$emprate=$rate;
				$where_arr[]="emprate_clusterid=".$rate_params['zone'];
				$rate_exist['zone']=$rate_params['zone'];
			}
		}
		if($rate_params['center']!="")
		{
			$rate=$this->Mastermodel->getCustomeFieldValue(TABLE_EMPRATE,'emprate_centreid',$rate_params['center'],'emprate');
			if($rate)
			{
				$emprate=$rate;
				$where_arr[]="emprate_centreid=".$rate_params['center'];
				$rate_exist['center']=$rate_params['center'];
			}
		}
		if($rate_params['subcenter']!="")
		{
			$rate=$this->Mastermodel->getCustomeFieldValue(TABLE_EMPRATE,'emprate_subcentreid',$rate_params['subcenter'],'emprate');
			if($rate)
			{
				$emprate=$rate;
				$where_arr[]="emprate_subcentreid=".$rate_params['subcenter'];
				$rate_exist['subcenter']=$rate_params['subcenter'];
			}
		}
		if($rate_params['pincode']!="")
		{
			$rate=$this->Mastermodel->getCustomeFieldValue(TABLE_EMPRATE,@$pncodfield,'"'.$rate_params['pincode'].'"','emprate');
			if($rate)
			{
				$emprate=$rate;
				$where_arr[]= @$pncodfield."='".$rate_params['pincode']."'";
				$rate_exist['pincode']=$rate_params['pincode'];
			}
			
		}
		
		if($rate_params['activity']!="")
		{
			$flag=1;
			if(@$rate_exist['pincode']!="")
			{
				$where = @$pncodfield.'="'.$rate_params['pincode'].'" AND emprate_activity_name='.$rate_params['activity'];
				$select="emprate";
				$records = $this->Mastermodel->getList(TABLE_EMPRATE,$where,$select);
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];
				}
			}
			if(@$rate_exist['subcenter']!="" && $flag==1)
			{
				$where ='emprate_subcentreid='.$rate_params['subcenter'].' AND emprate_activity_name='.$rate_params['activity'];
				$select="emprate";
				$records = $this->Mastermodel->getList(TABLE_EMPRATE,$where,$select);
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];
				}	
			}
			if(@$rate_exist['center']!="" && $flag==1)
			{
				$where ='emprate_centreid='.$rate_params['center'].' AND emprate_activity_name='.$rate_params['activity'];
				$select="emprate";
				$records = $this->Mastermodel->getList(TABLE_EMPRATE,$where,$select);
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];
				}	
			}
			if(@$rate_exist['zone']!="" && $flag==1)
			{
				$where ='emprate_clusterid='.$rate_params['zone'].' AND emprate_activity_name='.$rate_params['activity'];
				$select="emprate";
				$records = $this->Mastermodel->getList(TABLE_EMPRATE,$where,$select);
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];
				}	
			}
			if(@$rate_exist['country']!="" && $flag==1)
			{
				$where ='emprate_country_name='.$rate_params['country'].' AND emprate_activity_name='.$rate_params['activity'];
				$select="emprate";
				$records = $this->Mastermodel->getList(TABLE_EMPRATE,$where,$select);
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];
				}	
			}
			$where_arr[]="emprate_activity_name=".$rate_params['activity'];
		}
		if($rate_params['product']!="")
		{
			$flag=1;
			if(@$rate_exist['pincode']!="")
			{
				
				$where =@$pncodfield.'="'.$rate_params['pincode'].'" AND emprate_product_name='.$rate_params['product'];
				$select="emprate";
				$records = $this->Mastermodel->getList(TABLE_EMPRATE,$where,$select);//echo $this->db->last_query();die();
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];
					
				}
			}
			if(@$rate_exist['subcenter']!="" && $flag==1)
			{
				$where ='emprate_subcentreid='.$rate_params['subcenter'].' AND emprate_product_name='.$rate_params['product'];
				$select="emprate";
				$records = $this->Mastermodel->getList(TABLE_EMPRATE,$where,$select);
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];
				}	
			}
			if(@$rate_exist['center']!="" && $flag==1)
			{
				$where ='emprate_centreid='.$rate_params['center'].' AND emprate_product_name='.$rate_params['product'];
				$select="emprate";
				$records = $this->Mastermodel->getList(TABLE_EMPRATE,$where,$select);
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];
				}	
			}
			if(@$rate_exist['zone']!="" && $flag==1)
			{
				$where ='emprate_clusterid='.$rate_params['zone'].' AND emprate_product_name='.$rate_params['product'];
				$select="emprate";
				$records = $this->Mastermodel->getList(TABLE_EMPRATE,$where,$select);
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];
				}	
			}
			if(@$rate_exist['country']!="" && $flag==1)
			{
				$where ='emprate_country_name='.$rate_params['country'].' AND emprate_product_name='.$rate_params['product'];
				$select="emprate";
				$records = $this->Mastermodel->getList(TABLE_EMPRATE,$where,$select);
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];
				}
				
			}
			
			$where_arr[]="emprate_product_name=".$rate_params['product'];
		}
		
		if($rate_params['verification']!="")
		{
			
			$flag=1;
			if(@$rate_exist['pincode']!="")
			{
				$where = @$pncodfield.'="'.$rate_params['pincode'].'" AND emprate_veriftype_name='.$rate_params['verification'];
				$select="emprate";
				$records = $this->Mastermodel->getList(TABLE_EMPRATE,$where,$select);
				
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];					
				}
			}
			if(@$rate_exist['subcenter']!="" && $flag==1)
			{
				 $where ='emprate_subcentreid='.$rate_params['subcenter'].' AND emprate_veriftype_name='.$rate_params['verification'];
				$select="emprate";
				$records = $this->Mastermodel->getList(TABLE_EMPRATE,$where,$select);
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];
				}
				//echo $this->db->last_query();die("1");	
			}
			if(@$rate_exist['center']!="" && $flag==1)
			{
				$where ='emprate_centreid='.$rate_params['center'].' AND emprate_veriftype_name='.$rate_params['verification'];
				$select="emprate";
				$records = $this->Mastermodel->getList(TABLE_EMPRATE,$where,$select);
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];
				}	
			}
			if(@$rate_exist['zone']!="" && $flag==1)
			{
				$where ='emprate_clusterid='.$rate_params['zone'].' AND emprate_veriftype_name='.$rate_params['verification'];
				$select="emprate";
				$records = $this->Mastermodel->getList(TABLE_EMPRATE,$where,$select);
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];
				}	
			}
			if(@$rate_exist['country']!="" && $flag==1)
			{
				$where ='emprate_country_name='.$rate_params['country'].' AND emprate_veriftype_name='.$rate_params['verification'];
				$select="emprate";
				$records = $this->Mastermodel->getList(TABLE_EMPRATE,$where,$select);
				if($records[0]['emprate'])
				{
					$flag=2;
					$emprate=$records[0]['emprate'];
				}				
			}
		}			
		return $emprate;
	}
	

	function getClientRate($rate_params)
	{
		$where_arr=array();
		$rate_exist=array();
		$clientrate=NULL;
		
		$debug=0;
		
		$pncodfield = 'pincode_name';
		$pincode_cond = '';
		
		$rate_field='clientrate'; // Default
		
		
		// 1) Check pincode in Client Rate present or not (Priority 1)
		$this->db->select('r.clientrate_pincode_name,r.clientrate_id,p.city_limit');
		$this->db->from(TABLE_CLIENTRATE.' as r');
		$this->db->join(TABLE_MST_PINCODE.' as p','p.pincode_id=r.clientrate_pincode_name','left');
		$this->db->where('p.pincode_name',$rate_params['pincode']);
		$chk_count=$this->db->count_all_results();
		
		
		if($debug==1)
		{
			echo "<pre>"; print_r($rate_params);
			
			echo "Pincode Count : "; print_r($chk_count)."<br/>"; 
		}
		
		if($chk_count==0)
		{
			// 2) Check case pincode in pincode master if any (Priority 2)
			$check_pincode=$this->db->select('city_limit')->get_where(TABLE_MST_PINCODE,array('pincode_name'=>$rate_params['pincode']))->row_array();
			
			// Pincode found in Pincode master
			$pincode_master_flag=0;
			if(!empty($check_pincode))
			{
				$pincode_master_flag=1;
	
				if($check_pincode['city_limit']=='0') // ICL
				{
					$rate_field='icl_clientrate';
				}
				elseif($check_pincode['city_limit']=='1')  // OCL
				{
					$rate_field='ocl_clientrate';
				}
				elseif($check_pincode['city_limit']=='2')  // BOCL
				{
					$rate_field='bocl_clientrate';
				}
				elseif($check_pincode['city_limit']=='3')  // Non Serviceable
				{
					$rate_field='nonserice_clientrate';
				}
				
				$categorywise=$rate_field;
			}
		}
		
		
		//echo "Rate Field : ".$rate_field; echo "<br/>"; die;
		
		
		// Priority 3 : cpv  > axis > cc > bv : Rs 100 (no pincode checked)
		// and 
		// Priority 2 : cpv > axis > cc > bv : categorywise : ICL : Rs 40 , OCL : Rs 50, Bocl : Rs 60 , Ns : Rs 70
		if($rate_params['pincode'] == "")
		{
			$pincode_cond = ' (clientrate_pincode_name IS NULL AND icl_ocl IS NULL) ';
		}
		// Priority 1 : cpv > axis > cc > bv : pincodewise : 400056(pincode checked) : Rs 80
		else
		{
			
			if(in_array(strtoupper($rate_params['pincode']),array('ICL','OCL','BOCL','Non serviceable')))
			{
				$pncodfield = 'icl_ocl';			
			}
			
			
			if(!empty($categorywise) && $categorywise!='clientrate')
			{
				$pincode_cond = ' clientrate_pincode_name IS NULL ';
			}
			else
			{
				$pincode_cond = ' '.$pncodfield.' = "'.$rate_params['pincode'].'"';
			}
		}
		
		
		$flag=1;
		if($rate_params['verification']!="")
		{			
			if(@$rate_params['product']!="" && @$rate_params['subcenter']!="")
			{
				$where = '';
				$where = 'clientrate_subcentreid='.$rate_params['subcenter'].'
							AND clientrate_product_name='.$rate_params['product'].'
							AND clientrate_veriftype_name = '.$rate_params['verification'].'
							AND clientrate_client_id = '.$rate_params['client'].'
							AND '.@$pincode_cond;
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);				
				
				
				
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate = $records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo $this->db->last_query();
					
					echo "<br/> 1 <br/>";
				}
				
				
			}
			if(@$rate_params['activity']!="" && @$rate_params['subcenter']!="" && $flag==1)
			{
				$where = '';
				$where = 'clientrate_subcentreid='.$rate_params['subcenter'].'
							AND clientrate_activity_name='.$rate_params['activity'].'
							AND clientrate_product_name IS NULL
							AND clientrate_veriftype_name = '.$rate_params['verification'].'
							AND clientrate_client_id = '.$rate_params['client'].'
							AND '.@$pincode_cond;
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate = $records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo "2 <br/>";
				}
			}
			if(@$rate_params['subcenter']!="" && $flag==1)
			{
				$where = '';
				$where = 'clientrate_subcentreid='.$rate_params['subcenter'].'
							AND clientrate_activity_name IS NULL
							AND clientrate_product_name IS NULL
							AND clientrate_veriftype_name = '.$rate_params['verification'].'
							AND clientrate_client_id = '.$rate_params['client'].'
							AND '.@$pincode_cond;
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate = $records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo "3 <br/>";
				}
			}
			if(@$rate_params['activity']!="" && @$rate_params['product']!="" && @$rate_params['center']!="" && $flag==1)
			{
				$where = '';
				$where = 'clientrate_centreid='.$rate_params['center'].'
						AND clientrate_subcentreid IS NULL AND clientrate_activity_name ='.$rate_params['activity'].'
						AND clientrate_product_name ='.@$rate_params['product'].'
						AND clientrate_veriftype_name = '.$rate_params['verification'].'
						AND clientrate_client_id = '.$rate_params['client'].'
						AND '.@$pincode_cond;
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate = $records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo "4 <br/>";
				}
			}
			
			if(@$rate_params['activity']!="" && @$rate_params['center']!="" && $flag==1)
			{
				$where = '';
				$where = 'clientrate_centreid='.$rate_params['center'].'
							AND clientrate_subcentreid IS NULL
							AND clientrate_activity_name ='.@$rate_params['activity'].'
							AND clientrate_product_name IS NULL
							AND clientrate_veriftype_name = '.$rate_params['verification'].'
							AND clientrate_client_id = '.$rate_params['client'].'
							AND '.@$pincode_cond;
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);				
				//echo $this->db->last_query().br(1);
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate = $records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo "5 <br/>";
				}
			}
			
			if(@$rate_params['center']!="" && $flag==1)
			{
				$where = '';
				$where = 'clientrate_centreid ='.@$rate_params['center'].'
						  AND clientrate_subcentreid IS NULL
						  AND clientrate_activity_name IS NULL
						  AND clientrate_product_name IS NULL
						  AND clientrate_veriftype_name = '.$rate_params['verification'].'
						  AND clientrate_client_id = '.$rate_params['client'].'
						  AND '.@$pincode_cond;
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);				
				//echo $this->db->last_query().br(1);
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate = $records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo "6 <br/>";
				}
			}
			
			if(@$rate_params['zone']!="" && @$rate_params['product']!="" && $rate_params['activity']!="" && $flag==1)
			{
				$where = '';
				$where = 'clientrate_clusterid='.$rate_params['zone'].'
							AND clientrate_centreid IS NULL
							AND  clientrate_activity_name='.@$rate_params['activity'].'
							AND clientrate_product_name='.@$rate_params['product'].'
							AND clientrate_veriftype_name = '.$rate_params['verification'].'
							AND clientrate_client_id = '.$rate_params['client'].'
							AND '.@$pincode_cond;
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);				
				//echo $this->db->last_query().br(1);
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate = $records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo "7 <br/>";
				}
			}
			
			if(@$rate_params['zone']!="" && @$rate_params['activity']!="" && $flag==1)
			{
				$where = '';
				$where = 'clientrate_clusterid='.$rate_params['zone'].'
						AND clientrate_centreid IS NULL
						AND  clientrate_activity_name='.@$rate_params['activity'].'
						AND clientrate_product_name IS NULL
						AND clientrate_veriftype_name = '.$rate_params['verification'].'
						AND clientrate_client_id = '.$rate_params['client'].'
						AND '.@$pincode_cond;
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);				
				//echo $this->db->last_query().br(1);
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate = $records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo "8 <br/>";
				}
			}
			
			if(@$rate_params['zone']!="" && $flag==1)
			{
				$where = '';
				$where = 'clientrate_clusterid='.$rate_params['zone'].'
						AND clientrate_centreid IS NULL
						AND  clientrate_activity_name IS NULL
						AND clientrate_product_name IS NULL
						AND clientrate_veriftype_name = '.$rate_params['verification'].'
						AND clientrate_client_id = '.$rate_params['client'].'
						AND '.@$pincode_cond;
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);				
				//echo $this->db->last_query().br(1);
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate = $records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo "9 <br/>";
				}
			}
			
			if(@$rate_params['country']!="" && @$rate_params['activity']!="" && @$rate_params['product']!="" && $flag==1)
			{
				$where = '';
				$where = 'clientrate_country_name='.$rate_params['country'].'
						AND clientrate_clusterid IS NULL
						AND clientrate_activity_name ='.@$rate_params['activity'].'
						AND clientrate_product_name ='.@$rate_params['product'].'
						AND clientrate_veriftype_name = '.$rate_params['verification'].'
						AND clientrate_client_id = '.$rate_params['client'].'
						AND '.@$pincode_cond;
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);				
				
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate = $records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo "10 <br/>";
				}
				
			}
			
			if(@$rate_params['country']!="" && @$rate_params['activity']!="" && $flag==1)
			{
				$where = '';
				$where = 'clientrate_country_name='.$rate_params['country'].'
				AND clientrate_clusterid IS NULL
				AND clientrate_activity_name ='.@$rate_params['activity'].'
				AND clientrate_product_name IS NULL
				AND clientrate_client_id = '.$rate_params['client'].'
				AND clientrate_veriftype_name = '.$rate_params['verification'].'
				AND '.@$pincode_cond;
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);				
				
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate = $records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo "11 <br/>";
				}
			}
			
			if(@$rate_params['country']!="" && $flag==1)
			{
				$where = '';
				$where = 'clientrate_country_name='.$rate_params['country'].'
				AND clientrate_clusterid IS NULL
				AND clientrate_activity_name IS NULL
				AND clientrate_product_name IS NULL
				AND clientrate_client_id = '.$rate_params['client'].'
				AND clientrate_veriftype_name = '.$rate_params['verification'].' AND '.@$pincode_cond;
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);				
				
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate = $records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo "12 <br/>";
				}
			}
			
			//*********************************If rate not exist as per given pincode value*********************************************************
			if(@$rate_params['product']!="" && @$rate_params['subcenter']!=""  && $flag==1)
			{
				$where = '';
				$where = 'clientrate_subcentreid='.$rate_params['subcenter'].'
							AND clientrate_product_name='.$rate_params['product'].'
							AND clientrate_veriftype_name = '.$rate_params['verification'].'
							AND clientrate_client_id = '.$rate_params['client'].'
							AND clientrate_pincode_name IS NULL';
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);
				//echo $this->db->last_query().br(1);die();
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate = $records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo "13 <br/>";
				}
			}
			if(@$rate_params['activity']!="" && @$rate_params['subcenter']!="" && $flag==1)
			{
				$where = '';
				$where = 'clientrate_subcentreid='.$rate_params['subcenter'].'
							AND clientrate_activity_name='.$rate_params['activity'].'
							AND clientrate_product_name IS NULL
							AND clientrate_veriftype_name = '.$rate_params['verification'].'
							AND clientrate_client_id = '.$rate_params['client'].'
							AND clientrate_pincode_name IS NULL';
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);				
				//echo $this->db->last_query().br(1);
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate = $records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo "14 <br/>";
				}
			}
			if(@$rate_params['subcenter']!="" && $flag==1)
			{
				$where = '';
				$where = 'clientrate_subcentreid='.$rate_params['subcenter'].'
							AND clientrate_activity_name IS NULL
							AND clientrate_product_name IS NULL
							AND clientrate_veriftype_name = '.$rate_params['verification'].'
							AND clientrate_client_id = '.$rate_params['client'].'
							AND clientrate_pincode_name IS NULL';
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);				
				//echo $this->db->last_query().br(1);
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate = $records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo "15 <br/>";
				}
			}
			if(@$rate_params['activity']!="" && @$rate_params['product']!="" && @$rate_params['center']!="" && $flag==1)
			{
				$where = '';
				$where = 'clientrate_centreid='.$rate_params['center'].'
				AND clientrate_subcentreid IS NULL
				AND clientrate_activity_name ='.$rate_params['activity'].'
				AND clientrate_product_name ='.@$rate_params['product'].'
				AND clientrate_client_id = '.$rate_params['client'].'
				AND clientrate_veriftype_name = '.$rate_params['verification'].'
				AND clientrate_pincode_name IS NULL';
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);				
				//echo $this->db->last_query().br(1);
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate = $records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo "16 <br/>";
				}
			}
			
			if(@$rate_params['activity']!="" && @$rate_params['center']!="" && $flag==1)
			{
				$where = '';
				$where = 'clientrate_centreid='.$rate_params['center'].'
				AND clientrate_subcentreid IS NULL
				AND clientrate_activity_name ='.@$rate_params['activity'].'
				AND clientrate_product_name IS NULL
				AND clientrate_client_id = '.$rate_params['client'].'
				AND clientrate_veriftype_name = '.$rate_params['verification'].'
				AND clientrate_pincode_name IS NULL';
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);				
				//echo $this->db->last_query().br(1);
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate = $records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo "17 <br/>";
				}
				
			}
			
			if(@$rate_params['center']!="" && $flag==1)
			{
				$where = '';
				$where = 'clientrate_centreid ='.@$rate_params['center'].'
				AND clientrate_subcentreid IS NULL
				AND clientrate_activity_name IS NULL
				AND clientrate_product_name IS NULL
				AND clientrate_client_id = '.$rate_params['client'].'
				AND clientrate_veriftype_name = '.$rate_params['verification'].'
				AND clientrate_pincode_name IS NULL';
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);				
				//echo $this->db->last_query().br(1);
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate = $records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo "18 <br/>";
				}
			}
			
			if(@$rate_params['zone']!="" && @$rate_params['product']!="" && $rate_params['activity']!="" && $flag==1)
			{
				$where = '';
				$where = 'clientrate_clusterid='.$rate_params['zone'].'
				AND clientrate_centreid IS NULL
				AND  clientrate_activity_name='.@$rate_params['activity'].'
				AND clientrate_product_name='.@$rate_params['product'].'
				AND clientrate_client_id = '.$rate_params['client'].'
				AND clientrate_veriftype_name = '.$rate_params['verification'].'
				AND clientrate_pincode_name IS NULL';
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);				
				//echo $this->db->last_query().br(1);
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate = $records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo "19 <br/>";
				}
			}
			
			if(@$rate_params['zone']!="" && @$rate_params['activity']!="" && $flag==1)
			{
				$where = '';
				$where = 'clientrate_clusterid='.$rate_params['zone'].'
				AND clientrate_centreid IS NULL
				AND  clientrate_activity_name='.@$rate_params['activity'].'
				AND clientrate_product_name IS NULL
				AND clientrate_client_id = '.$rate_params['client'].'
				AND clientrate_veriftype_name = '.$rate_params['verification'].'
				AND clientrate_pincode_name IS NULL';
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);				
				//echo $this->db->last_query().br(1);
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate = $records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo "20 <br/>";
				}
			}
			
			if(@$rate_params['zone']!="" && $flag==1)
			{
				$where = '';
				$where = 'clientrate_clusterid='.$rate_params['zone'].'
				AND clientrate_centreid IS NULL
				AND  clientrate_activity_name IS NULL
				AND clientrate_product_name IS NULL
				AND clientrate_client_id = '.$rate_params['client'].'
				AND clientrate_veriftype_name = '.$rate_params['verification'].'
				AND clientrate_pincode_name IS NULL';
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);				
				//echo $this->db->last_query().br(1);
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate = $records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo "21 <br/>";
				}
			}
			
			if(@$rate_params['country']!="" && @$rate_params['activity']!="" && @$rate_params['product']!="" && $flag==1)
			{
				$where = '';
				$where = 'clientrate_country_name='.$rate_params['country'].'
				AND clientrate_clusterid IS NULL
				AND clientrate_activity_name ='.@$rate_params['activity'].'
				AND clientrate_client_id = '.$rate_params['client'].'
				AND clientrate_product_name ='.@$rate_params['product'].'
				AND clientrate_veriftype_name = '.$rate_params['verification'].'
				AND clientrate_pincode_name IS NULL';
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);				
				
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate = $records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo "22 <br/>";
				}
			}
			
			if(@$rate_params['country']!="" && @$rate_params['activity']!="" && $flag==1)
			{
				$where = '';
				$where = 'clientrate_country_name='.$rate_params['country'].'
				AND clientrate_clusterid IS NULL
				AND clientrate_activity_name ='.@$rate_params['activity'].'
				AND clientrate_client_id = '.$rate_params['client'].'
				AND clientrate_product_name IS NULL
				AND clientrate_veriftype_name = '.$rate_params['verification'].'
				AND clientrate_pincode_name IS NULL';
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);				
				
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate = $records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo "23 <br/>";
				}
			}
			
			if(@$rate_params['country']!="" && $flag==1)
			{
				$where = '';
				$where = 'clientrate_country_name='.$rate_params['country'].'
				AND clientrate_clusterid IS NULL
				AND clientrate_activity_name IS NULL
				AND clientrate_product_name IS NULL
				AND clientrate_client_id = '.$rate_params['client'].'
				AND clientrate_veriftype_name = '.$rate_params['verification'].'
				AND clientrate_pincode_name IS NULL';
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);				
				
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate = $records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo "24 <br/>";
				}
				
			}
		}
		if($flag == 1)
		{		
			if(@$rate_params['product']!="" && @$rate_params['subcenter']!="")
			{
				$where = '';
				$where = 'clientrate_subcentreid='.$rate_params['subcenter'].'
				AND clientrate_product_name='.$rate_params['product'].'
				AND clientrate_client_id = '.$rate_params['client'].'
				AND clientrate_veriftype_name IS NULL
				AND '.@$pincode_cond;
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);				
				
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate = $records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					
					echo $this->db->last_query();
					
					echo "<br/>25 <br/>";
				}
				
			}
			if(@$rate_params['activity']!="" && @$rate_params['subcenter']!="" && $flag==1)
			{
				$where = '';
				$where = 'clientrate_subcentreid='.$rate_params['subcenter'].'
				AND clientrate_activity_name='.$rate_params['activity'].'
				AND clientrate_client_id = '.$rate_params['client'].'
				AND clientrate_product_name IS NULL
				AND clientrate_veriftype_name IS NULL
				AND '.@$pincode_cond;
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);				
				
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate = $records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo $this->db->last_query();
					
					echo "<br/> 26 <br/>";
				}
			}
			if(@$rate_params['subcenter']!="" && $flag==1)
			{
				$where = '';
				$where = 'clientrate_subcentreid='.$rate_params['subcenter'].'
				AND clientrate_activity_name IS NULL
				AND clientrate_product_name IS NULL
				AND clientrate_veriftype_name IS NULL
				AND clientrate_client_id = '.$rate_params['client'].'
				AND '.@$pincode_cond;
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);				
				
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate = $records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo "27 <br/>";
				}
			}			
			if(@$rate_params['activity']!="" && @$rate_params['product']!="" && @$rate_params['center']!="" && $flag==1)
			{
				$where = '';
				$where = 'clientrate_centreid='.$rate_params['center'].'
				AND clientrate_subcentreid IS NULL
				AND clientrate_activity_name ='.$rate_params['activity'].'
				AND clientrate_product_name ='.@$rate_params['product'].'
				AND clientrate_client_id = '.$rate_params['client'].'
				AND clientrate_veriftype_name IS NULL AND '.@$pincode_cond;
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);				
				
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate = $records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo "28 <br/>";
				}
			}			
			if(@$rate_params['activity']!="" && @$rate_params['center']!="" && @$rate_params['subcenter']==""  && $flag==1)
			{
				$where = '';
				$where = 'clientrate_centreid='.$rate_params['center'].'
				AND clientrate_subcentreid IS NULL
				AND clientrate_activity_name ='.@$rate_params['activity'].'
				AND clientrate_product_name IS NULL
				AND clientrate_veriftype_name IS NULL
				AND clientrate_client_id = '.$rate_params['client'].'
				AND '.@$pincode_cond;
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);				
				
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate=$records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo "29 <br/>";
				}
			}			
			if(@$rate_params['center']!="" && $flag==1)
			{
				$where = '';
				$where = 'clientrate_centreid ='.@$rate_params['center'].'
				AND clientrate_subcentreid IS NULL
				AND clientrate_activity_name IS NULL
				AND clientrate_product_name IS NULL
				AND clientrate_veriftype_name  IS NULL
				AND clientrate_client_id = '.$rate_params['client'].'
				AND '.@$pincode_cond;
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);				
				
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate=$records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo "30 <br/>";
				}
			}			
			if(@$rate_params['zone']!="" && @$rate_params['product']!="" && $flag==1)
			{
				$where = '';
				$where = 'clientrate_clusterid='.$rate_params['zone'].'
				AND clientrate_centreid IS NULL
				AND  clientrate_activity_name='.@$rate_params['activity'].'
				AND clientrate_product_name='.@$rate_params['product'].'
				AND clientrate_veriftype_name IS NULL
				AND clientrate_client_id = '.$rate_params['client'].'
				AND '.@$pincode_cond;
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);				
				
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate=$records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo "31 <br/>";
				}
			}			
			if(@$rate_params['zone']!="" && @$rate_params['activity']!="" && $flag==1)
			{
				$where = '';
				$where = 'clientrate_clusterid='.$rate_params['zone'].'
				AND clientrate_centreid IS NULL
				AND  clientrate_activity_name='.@$rate_params['activity'].'
				AND clientrate_product_name IS NULL
				AND clientrate_veriftype_name IS NULL
				AND clientrate_client_id = '.$rate_params['client'].'
				AND '.@$pincode_cond;
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);				
				
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate=$records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo "32 <br/>";
				}
			}			
			if(@$rate_params['zone']!="" && $flag==1)
			{
				$where = '';
				$where = 'clientrate_clusterid='.$rate_params['zone'].'
				AND clientrate_centreid IS NULL
				AND  clientrate_activity_name IS NULL
				AND clientrate_product_name IS NULL
				AND clientrate_veriftype_name IS NULL
				AND clientrate_client_id = '.$rate_params['client'].'
				AND '.@$pincode_cond;
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);				
				
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate=$records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo "33 <br/>";
				}
			}			
			if(@$rate_params['country']!="" && @$rate_params['activity']!="" && @$rate_params['product']!="" && $flag==1)
			{
				$where = '';
				$where = 'clientrate_country_name='.$rate_params['country'].'
				AND clientrate_clusterid IS NULL
				AND clientrate_activity_name ='.@$rate_params['activity'].'
				AND clientrate_product_name ='.@$rate_params['product'].'
				AND clientrate_veriftype_name  IS NULL
				AND clientrate_client_id = '.$rate_params['client'].'
				AND '.@$pincode_cond;
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);				
				
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate=$records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo "34 <br/>";
				}
			}			
			if(@$rate_params['country']!="" && @$rate_params['activity']!="" && $flag==1)
			{
				$where = '';
				$where = 'clientrate_country_name='.$rate_params['country'].'
				AND clientrate_clusterid IS NULL
				AND clientrate_activity_name ='.@$rate_params['activity'].'
				AND clientrate_product_name IS NULL
				AND clientrate_veriftype_name  IS NULL
				AND clientrate_client_id = '.$rate_params['client'].'
				AND '.@$pincode_cond;
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);				
				
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate=$records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo "35 <br/>";
				}
			}			
			if(@$rate_params['country']!="" && $flag==1)
			{
				$where = '';
				$where = 'clientrate_country_name='.$rate_params['country'].'
				AND clientrate_clusterid IS NULL
				AND clientrate_centreid IS NULL
				AND clientrate_subcentreid IS NULL
				AND clientrate_activity_name IS NULL
				AND clientrate_product_name IS NULL
				AND clientrate_veriftype_name  IS NULL
				AND clientrate_client_id = '.$rate_params['client'].'
				AND '.@$pincode_cond;
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);				
				
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate=$records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo "36 <br/>";
				}
			}
			
			
			//*********************************If rate not exist as per given pincode value*********************************************************
			
			if(@$rate_params['product']!="" && @$rate_params['subcenter']!="" && $flag==1)
			{
				$where = '';
				$where = 'clientrate_subcentreid='.$rate_params['subcenter'].'
				AND clientrate_product_name='.$rate_params['product'].'
				AND clientrate_veriftype_name IS NULL
				AND clientrate_pincode_name IS NULL
				AND clientrate_client_id = '.$rate_params['client'].'
				AND icl_ocl IS NULL';
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);				
			
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate=$records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo "37 <br/>";
				}
				
			}
			if(@$rate_params['activity']!="" && @$rate_params['subcenter']!="" && $flag==1)
			{
				$where = '';
				$where = 'clientrate_subcentreid='.$rate_params['subcenter'].'
				AND clientrate_activity_name='.$rate_params['activity'].'
				AND clientrate_product_name IS NULL
				AND clientrate_veriftype_name IS NULL
				AND clientrate_pincode_name IS NULL
				AND clientrate_client_id = '.$rate_params['client'].'
				AND icl_ocl IS NULL';
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);				
				
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate=$records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo "38 <br/>";
				}
				
			}
			if(@$rate_params['subcenter']!="" && $flag==1)
			{
				$where = '';
				$where = 'clientrate_subcentreid='.$rate_params['subcenter'].'
				AND clientrate_activity_name IS NULL
				AND clientrate_product_name IS NULL
				AND clientrate_veriftype_name IS NULL
				AND clientrate_pincode_name IS NULL
				AND clientrate_client_id = '.$rate_params['client'].'
				AND icl_ocl IS NULL';
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);				
				
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate=$records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo "39 <br/>";
				}
			}			
			if(@$rate_params['activity']!="" && @$rate_params['product']!="" && @$rate_params['center']!="" && $flag==1)
			{
				$where = '';
				$where = 'clientrate_centreid='.$rate_params['center'].'
				AND clientrate_subcentreid IS NULL
				AND clientrate_activity_name ='.$rate_params['activity'].'
				AND clientrate_product_name ='.@$rate_params['product'].'
				AND clientrate_veriftype_name IS NULL
				AND clientrate_pincode_name IS NULL
				AND clientrate_client_id = '.$rate_params['client'].'
				AND icl_ocl IS NULL';
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);				
				
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate=$records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo $this->db->last_query();
					
					echo "<br/> 40 <br/> ";
				}
				
				
			}			
			if(@$rate_params['activity']!="" && @$rate_params['center']!="" && @$rate_params['subcenter']==""  && $flag==1)
			{
				
				
				$where = '';
				$where = 'clientrate_centreid='.$rate_params['center'].'
				AND clientrate_subcentreid IS NULL
				AND clientrate_activity_name ='.@$rate_params['activity'].'
				AND clientrate_product_name IS NULL
				AND clientrate_veriftype_name  IS NULL
				AND clientrate_pincode_name IS NULL
				AND clientrate_client_id = '.$rate_params['client'].'
				AND icl_ocl IS NULL';
				$select=$rate_field;
				
				if($debug==1)
				{
					echo "41 <br/>".$where."<br/>"; 
				}
				$this->db->where('clientrate_client_id',$rate_params['client']);
				
				
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);				
				
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate=$records[0][$rate_field];					
				}
				
				
			}			
			if(@$rate_params['center']!="" && $flag==1)
			{
				
				$where = '';
				$where = 'clientrate_centreid ='.@$rate_params['center'].'
				AND clientrate_subcentreid IS NULL
				AND clientrate_activity_name IS NULL
				AND clientrate_product_name IS NULL
				AND clientrate_veriftype_name  IS NULL
				AND pincode_name IS NULL
				AND clientrate_client_id = '.$rate_params['client'].'
				AND icl_ocl IS NULL';
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				
				
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);				
				
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate=$records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo "42 <br/>";
				}
			}			
			if(@$rate_params['zone']!="" && @$rate_params['product']!="" && $flag==1)
			{
				$where = '';
				$where = 'clientrate_clusterid='.$rate_params['zone'].'
				AND clientrate_centreid IS NULL
				AND  clientrate_activity_name='.@$rate_params['activity'].'
				AND clientrate_product_name='.@$rate_params['product'].'
				AND clientrate_veriftype_name IS NULL
				AND pincode_name IS NULL
				AND clientrate_client_id = '.$rate_params['client'].'
				AND icl_ocl IS NULL';
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);				
				
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate=$records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo "43 <br/>";
				}
			}			
			if(@$rate_params['zone']!="" && @$rate_params['activity']!="" && $flag==1)
			{
				$where = '';
				$where = 'clientrate_clusterid='.$rate_params['zone'].'
				AND clientrate_centreid IS NULL
				AND  clientrate_activity_name='.@$rate_params['activity'].'
				AND clientrate_product_name IS NULL
				AND clientrate_veriftype_name IS NULL
				AND pincode_name IS NULL
				AND clientrate_client_id = '.$rate_params['client'].'
				AND icl_ocl IS NULL';
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);				
				
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate=$records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo "44 <br/>";
				}
			}			
			if(@$rate_params['zone']!="" && $flag==1)
			{
				$where = '';
				$where = 'clientrate_clusterid='.$rate_params['zone'].'
				AND clientrate_centreid IS NULL
				AND  clientrate_activity_name IS NULL
				AND clientrate_product_name IS NULL
				AND clientrate_veriftype_name IS NULL
				AND clientrate_pincode_name IS NULL
				AND clientrate_client_id = '.$rate_params['client'].'
				AND icl_ocl IS NULL';
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);				
				
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate=$records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo "45 <br/>";
				}
			}			
			if(@$rate_params['country']!="" && @$rate_params['activity']!="" && @$rate_params['product']!="" && $flag==1)
			{
				$where = '';
				$where = 'clientrate_country_name='.$rate_params['country'].'
				AND clientrate_clusterid IS NULL
				AND clientrate_activity_name ='.@$rate_params['activity'].'
				AND clientrate_product_name ='.@$rate_params['product'].'
				AND clientrate_veriftype_name  IS NULL
				AND clientrate_pincode_name IS NULL
				AND clientrate_client_id = '.$rate_params['client'].'
				AND icl_ocl IS NULL';
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);				
				
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate=$records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo "46 <br/>";
				}
			}			
			if(@$rate_params['country']!="" && @$rate_params['activity']!="" && $flag==1)
			{
				$where = '';
				$where = 'clientrate_country_name='.$rate_params['country'].'
				AND clientrate_clusterid IS NULL
				AND clientrate_activity_name ='.@$rate_params['activity'].'
				AND clientrate_product_name IS NULL
				AND clientrate_veriftype_name  IS NULL
				AND clientrate_pincode_name IS NULL
				AND clientrate_client_id = '.$rate_params['client'].'
				AND icl_ocl IS NULL';
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);				
				
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate=$records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo "47 <br/>";
				}
			}			
			if(@$rate_params['country']!="" && $flag==1)
			{
				$where = '';
				$where = 'clientrate_country_name='.$rate_params['country'].'
				AND clientrate_clusterid IS NULL
				AND clientrate_centreid IS NULL
				AND clientrate_subcentreid IS NULL
				AND clientrate_activity_name IS NULL
				AND clientrate_product_name IS NULL
				AND clientrate_veriftype_name  IS NULL
				AND clientrate_pincode_name IS NULL
				AND clientrate_client_id = '.$rate_params['client'].'
				AND icl_ocl IS NULL';
				$select=$rate_field;
				$this->db->where('clientrate_client_id',$rate_params['client']);
				$records = $this->getrecord(TABLE_CLIENTRATE,$where,$select,'client',$categorywise);				
				
				if($records[0][$rate_field])
				{
					$flag=2;
					$clientrate=$records[0][$rate_field];					
				}
				
				if($debug==1)
				{
					echo "48 <br/>";
				}
			}			
		}
		
		if($debug==1)
		{
			echo "Final Result : "."<br/>";
			//echo $this->db->last_query(); echo "<br/>";		
			echo "Clientrate : ".$clientrate; echo "<br/>";		
			die('End');

		}
		
		
		return $clientrate;
	}
	
	public function send_notification($fe_id,$case_count) 
	{
        $registatoin_ids=$this->get_fe_regId($fe_id);
              
       	// Set POST variables
        $url = 'https://android.googleapis.com/gcm/send';
		$message="Number of cases assign :".$case_count;
		$msg= array("price" => $message);
                 $regId = array($registatoin_ids['gcm_regid']);

        	$fields = array(
           		 'registration_ids' => $regId,
            		'data' => $msg,
      			  );

        	$headers = array(
           		 'Authorization: key=' . GOOGLE_API_KEY,
           		 'Content-Type: application/json'
      			  );
        	// Open connection
      		  $ch = curl_init();

        	// Set the url, number of POST vars, POST data
       		curl_setopt($ch, CURLOPT_URL, $url);
	        curl_setopt($ch, CURLOPT_POST, true);
	        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
        	// Disabling SSL Certificate support temporarly
	        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	
        	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

	        // Execute post
        	$result = curl_exec($ch);
        	if ($result === FALSE)
         	{
			die('Curl failed: ' . curl_error($ch));
        	}

        	// Close connection
        	curl_close($ch);
        	//echo $result;
    	}
	
	function get_fe_regId($fe_id)
	{
		$this->db->select('gcm_regid');
		$this->db->where('fe_id',$fe_id);
		$query = $this->db->get('gcm_users');
		if($query->num_rows()>0)
		{
			$data = $query->row_array();
			return $data;
		}
		else
		{			
			return '-1';
		}
	}
	
	//template for event 
	function fetchTemplateid_down($country_id=NULL,$activity_id=NULL,$product_id=NULL,$client_id=NULL,$veriftype_id=NULL)
	{
		//get template
		$template_id="";
		if(@$country_id && $activity_id)
		{
			$where ='case_country_id='.@$country_id.' AND case_activity_id='.@$activity_id.' AND case_product_id IS NULL AND case_client_id IS NULL AND case_veriftype_id IS NULL';
			$select="case_template_id";
			$records = $this->getRecordList(TABLE_MST_CASE,$where,$select);
		}
		if(@$records[0]['case_template_id'])
		{
			$template_id=$records[0]['case_template_id'];
		}
		if(@$product_id!="" && $activity_id!="")
		{
			$where ='case_country_id='.$country_id.' AND case_activity_id='.$activity_id.' AND case_product_id='.$product_id.' AND case_client_id IS NULL AND case_veriftype_id IS NULL';
			$select="case_template_id";
			$records = $this->getRecordList(TABLE_MST_CASE,$where,$select);
			if(@$records[0]['case_template_id'])
			{
				$template_id=$records[0]['case_template_id'];
			}	
		}
		if(@$client_id!="" && $activity_id!="")
		{
			$where ='case_country_id='.$country_id.' AND case_activity_id='.$activity_id.' AND case_client_id='.$client_id.' AND case_product_id IS NULL AND case_veriftype_id IS NULL';
			$select="case_template_id";
			$records = $this->getRecordList(TABLE_MST_CASE,$where,$select);
			if(@$records[0]['case_template_id'])
			{
				$template_id=$records[0]['case_template_id'];
			}	
		}
		if(@$product_id!="" && @$client_id!="" && $activity_id!="")
		{
			$where ='case_country_id='.$country_id.' AND case_activity_id='.$activity_id.' AND case_product_id='.$product_id.' AND case_client_id='.$client_id.' AND case_veriftype_id IS NULL';
			$select="case_template_id";
			$records = $this->getRecordList(TABLE_MST_CASE,$where,$select);
			if(@$records[0]['case_template_id'])
			{
				$template_id=$records[0]['case_template_id'];
			}
		}
		
		if(@$veriftype_id!="" && $activity_id!="")
		{
			$where ='case_country_id='.$country_id.' AND case_activity_id='.$activity_id.' AND case_veriftype_id='.$veriftype_id.' AND case_client_id IS NULL AND case_product_id IS NULL';
			$select="case_template_id";
			$records = $this->getRecordList(TABLE_MST_CASE,$where,$select);
			if(@$records[0]['case_template_id'])
			{
				$template_id=$records[0]['case_template_id'];
			}
		}
		if(@$product_id!="" && @$veriftype_id!="" && $activity_id!="")
		{
			$where ='case_country_id='.$country_id.' AND case_activity_id='.$activity_id.' AND case_veriftype_id='.$veriftype_id.' AND case_product_id='.$product_id.' AND case_client_id IS NULL';
			$select="case_template_id";
			$records = $this->getRecordList(TABLE_MST_CASE,$where,$select);
			if(@$records[0]['case_template_id'])
			{
				$template_id=$records[0]['case_template_id'];
			}
		}
		if(@$client_id!="" && @$veriftype_id!="" && $activity_id!="")
		{
			$where ='case_country_id='.$country_id.' AND case_activity_id='.$activity_id.' AND case_veriftype_id='.$veriftype_id.' AND case_client_id='.$client_id.' AND case_product_id IS NULL';
			$select="case_template_id";
			$records = $this->getRecordList(TABLE_MST_CASE,$where,$select);
			if(@$records[0]['case_template_id'])
			{
				$template_id=$records[0]['case_template_id'];
			}
		}
		if(@$client_id!="" && @$veriftype_id!="" && @$product_id!="" && $activity_id!="")
		{
			$where ='case_country_id='.$country_id.' AND case_activity_id='.$activity_id.' AND case_veriftype_id='.$veriftype_id.' AND case_client_id='.$client_id.' AND case_product_id='.$product_id;
			$select="case_template_id";
			$records = $this->getRecordList(TABLE_MST_CASE,$where,$select);
			if(@$records[0]['case_template_id'])
			{
				$template_id=$records[0]['case_template_id'];
			}
		}
		//echo $this->db->last_query().'</br>';die;
		return @$template_id;
	}
	
	function get_template_action($action,$veriftype_id=NULL,$add_new=NULL)
	{		
		if(!empty($add_new))
		{
			$pr_id=$_SESSION['sess_add_product_id'];
			$cl_id=$_SESSION['sess_add_client_id'];
		}
		else
		{
			$pr_id=$_SESSION['sess_product_id'];
			$cl_id=$_SESSION['sess_client_id'];
		}
		
		
		if($veriftype_id)
		  $template_id=$this->fetchTemplateid_down(@$_SESSION['sess_user_country_id'],@$_SESSION['sess_user_activity_id'],@$pr_id,@$cl_id,@$veriftype_id);
		else
		  $template_id=$this->fetchTemplateid_down(@$_SESSION['sess_user_country_id'],@$_SESSION['sess_user_activity_id'],@$_SESSION['sess_user_product_id'],@$_SESSION['sess_user_client_id'],@$_SESSION['sess_user_verifytype_id']);
		$flag=1;
		
		if($action!='' && $template_id!="")
		{
			$this->Templatemodel->set_table($template_id);
			//$records=$this->getRecordList($this->Templatemodel->table_meta,$action.'=1');
			$records=$this->getRecordList($this->Templatemodel->table_meta,$action.'=1 AND global_template_id="'.$template_id.'"');
			
			if(!empty($records))
				$flag=2;
			if($flag==1)
			{
				
				$template_id = $this->fetchTemplateid_down($_SESSION['sess_user_country_id'],$_SESSION['sess_user_activity_id'],$pr_id,$cl_id,NULL);
				//$records=$this->getRecordList($this->Templatemodel->table_meta,$action.'=1');
				$records=$this->getRecordList($this->Templatemodel->table_meta,$action.'=1 AND global_template_id="'.$template_id.'"');
				if(!empty($records))
					$flag=2;
			}
			
			if($flag==1)
			{
				$template_id = $this->fetchTemplateid_down($_SESSION['sess_user_country_id'],$_SESSION['sess_user_activity_id'],$pr_id,NULL,NULL);
				//$records=$this->getRecordList($this->Templatemodel->table_meta,$action.'=1');
				$records=$this->getRecordList($this->Templatemodel->table_meta,$action.'=1 AND global_template_id="'.$template_id.'"');
				if(!empty($records))
					$flag=2;
			}
			if($flag==1)
			{
				$template_id = $this->fetchTemplateid_down($_SESSION['sess_user_country_id'],$_SESSION['sess_user_activity_id'],NULL,NULL,NULL);
				//$records=$this->getRecordList($this->Templatemodel->table_meta,$action.'=1');
				$records=$this->getRecordList($this->Templatemodel->table_meta,$action.'=1 AND global_template_id="'.$template_id.'"');
				if(!empty($records))
					$flag=2;
			}			
		}		
		return $template_id;
	}
	//template for event 
	function fetchTemplateid_down_new($country_id=NULL,$activity_id=NULL,$product_id=NULL,$client_id=NULL,$veriftype_id=NULL)
	{
		//get template
		$template_id="";
		if(@$country_id && $activity_id)
		{
			$where ='case_country_id='.@$country_id.' AND case_activity_id='.@$activity_id.' AND case_product_id IS NULL AND case_client_id IS NULL ';
			$select="case_template_id";
			$records = $this->getRecordList(TABLE_MST_CASE,$where,$select);
		}
		
		if(@$records[0]['case_template_id'])
		{
			$template_id=$records[0]['case_template_id'];
		}
		if(@$product_id!="" && $activity_id)
		{
			$where ='case_country_id='.@$country_id.' AND case_activity_id='.@$activity_id.' AND case_product_id='.@$product_id.' AND case_client_id IS NULL ';
			$select="case_template_id";
			$records = $this->getRecordList(TABLE_MST_CASE,$where,$select);
			if(@$records[0]['case_template_id'])
			{
				$template_id=$records[0]['case_template_id'];
			}	
		}
		if(@$client_id!="" && $activity_id)
		{
			$where ='case_country_id='.@$country_id.' AND case_activity_id='.@$activity_id.' AND case_client_id='.@$client_id.' AND case_product_id IS NULL ';
			$select="case_template_id";
			$records = $this->getRecordList(TABLE_MST_CASE,$where,$select);
			if(@$records[0]['case_template_id'])
			{
				$template_id=$records[0]['case_template_id'];
			}	
		}
		if(@$product_id!="" && @$client_id!="" && $activity_id)
		{
			$where ='case_country_id='.@$country_id.' AND case_activity_id='.@$activity_id.' AND case_product_id='.@$product_id.' AND case_client_id='.@$client_id;
			$select="case_template_id";
			$records = $this->getRecordList(TABLE_MST_CASE,$where,$select);
			if(@$records[0]['case_template_id'])
			{
				$template_id=$records[0]['case_template_id'];
			}
		}
		
		if(@$veriftype_id!="" && $activity_id)
		{
			$where ='case_country_id='.$country_id.' AND case_activity_id='.@$activity_id.' AND case_veriftype_id='.@$veriftype_id.' AND case_client_id IS NULL AND case_product_id IS NULL';
			$select="case_template_id";
			$records = $this->getRecordList(TABLE_MST_CASE,$where,$select);
			if(@$records[0]['case_template_id'])
			{
				$template_id=$records[0]['case_template_id'];
			}
		}
		if(@$product_id!="" && @$veriftype_id!="" && $activity_id)
		{
			$where ='case_country_id='.$country_id.' AND case_activity_id='.@$activity_id.' AND case_veriftype_id='.@$veriftype_id.' AND case_product_id='.@$product_id.' AND case_client_id IS NULL';
			$select="case_template_id";
			$records = $this->getRecordList(TABLE_MST_CASE,$where,$select);
			if(@$records[0]['case_template_id'])
			{
				$template_id=$records[0]['case_template_id'];
			}
		}
		if(@$client_id!="" && @$veriftype_id!="" && $activity_id)
		{
			$where ='case_country_id='.$country_id.' AND case_activity_id='.@$activity_id.' AND case_veriftype_id='.@$veriftype_id.' AND case_client_id='.@$client_id.' AND case_product_id IS NULL';
			$select="case_template_id";
			$records = $this->getRecordList(TABLE_MST_CASE,$where,$select);
			if(@$records[0]['case_template_id'])
			{
				$template_id=$records[0]['case_template_id'];
			}
		}
		if(@$client_id!="" && @$veriftype_id!="" && @$product_id!="" && $activity_id)
		{
			$where ='case_country_id='.$country_id.' AND case_activity_id='.@$activity_id.' AND case_veriftype_id='.@$veriftype_id.' AND case_client_id='.@$client_id.' AND case_product_id='.@$product_id;
			$select="case_template_id";
			$records = $this->getRecordList(TABLE_MST_CASE,$where,$select);
			if(@$records[0]['case_template_id'])
			{
				$template_id=$records[0]['case_template_id'];
			}
		}
		return $template_id;
	}
	
	function get_template_action_new($action,$veriftype_id=NULL)
	{		
		$template_id=$this->fetchTemplateid_down_new(@$_SESSION['sess_user_country_id'],@$_SESSION['sess_user_activity_id'],@$_SESSION['sess_user_product_id'],@$_SESSION['sess_user_client_id']);
		$flag=1;
						
		if($action!='' && $template_id!="")
		{
			$this->Templatemodel->set_table($template_id);
			//$records=$this->getRecordList($this->Templatemodel->table_meta,$action.'=1');
			$records=$this->getRecordList($this->Templatemodel->table_meta,$action.'=1 AND global_template_id="'.$template_id.'"');
			if(!empty($records))
				$flag=2;
			if($flag==1)
			{
				
				$template_id = $this->fetchTemplateid_down(@$_SESSION['sess_user_country_id'],@$_SESSION['sess_user_activity_id'],@$_SESSION['sess_user_product_id'],@$_SESSION['sess_user_client_id'],NULL);
				//$records=$this->getRecordList($this->Templatemodel->table_meta,$action.'=1');
				$records=$this->getRecordList($this->Templatemodel->table_meta,$action.'=1 AND global_template_id="'.$template_id.'"');
				if(!empty($records))
					$flag=2;
			}			
			if($flag==1)
			{
				$template_id = $this->fetchTemplateid_down(@$_SESSION['sess_user_country_id'],@$_SESSION['sess_user_activity_id'],@$_SESSION['sess_user_product_id'],NULL,NULL);
				//$records=$this->getRecordList($this->Templatemodel->table_meta,$action.'=1');
				$records=$this->getRecordList($this->Templatemodel->table_meta,$action.'=1 AND global_template_id="'.$template_id.'"');
				if(!empty($records))
					$flag=2;
			}
			if($flag==1)
			{
				$template_id = $this->fetchTemplateid_down(@$_SESSION['sess_user_country_id'],@$_SESSION['sess_user_activity_id'],NULL,NULL,NULL);
				//$records=$this->getRecordList($this->Templatemodel->table_meta,$action.'=1');
				$records=$this->getRecordList($this->Templatemodel->table_meta,$action.'=1 AND global_template_id="'.$template_id.'"');
				if(!empty($records))
					$flag=2;
			}
			
		}
		return $template_id;
	}
	
	function getICL_OCLvalue($pincode_val=NULL)
	{
		if(!empty($pincode_val))
		{
			$this->db->where('pincode_name',$pincode_val);
			$query = $this->db->get(TABLE_MST_PINCODE);
			$t_details = $query->row_array();
			if(!empty($t_details))
			{
				if($t_details['city_limit'] == 0)
					$limit = 'ICL';
				elseif($t_details['city_limit'] == 1)
					$limit = 'OCL';
				elseif($t_details['city_limit'] == 2)
					$limit = 'BOCL';
				elseif($t_details['city_limit'] == 3)
					$limit = 'Non Serviceable';
			}
			return @$limit;
		}else{
			return NULL;
		}
	}
	
	function getICL_OCLvalue_old($template_id=NULL,$caseData=NULL)
	{
		//if($this->db->table_exists('tbl_template_'.$template_id.'_meta'))
		//{
			$this->db->where('assignment_pincode',1);
			//$query = $this->db->get('tbl_template_'.$template_id.'_meta');
			
			$this->db->where('global_template_id',$template_id);
			$query = $this->db->get(TABLE_TEMPLATE_GLOBAL_META);
			
			$t_details = $query->result_array();
			$tmp = array();						
			
			foreach($t_details as $d=>$g)
			{
			    $tmp[] = TABLE_MST_PINCODE.'.pincode_name = "'.$caseData[$g['field_name']].'"';	
			}
			
			if(!empty($tmp))
			{
				$pincodestr = ' ('.implode(' OR ',$tmp).') ';
				$this->db->where($pincodestr,NULL,FALSE);
				$query = $this->db->get(TABLE_MST_PINCODE);
				$t_details = $query->row_array();
				if(!empty($t_details))
				{
					if($t_details['city_limit'] == 0)
						$limit = 'ICL';
					elseif($t_details['city_limit'] == 1)
						$limit = 'OCL';
					elseif($t_details['city_limit'] == 2)
						$limit = 'BOCL';
					elseif($t_details['city_limit'] == 3)
						$limit = 'Non Serviceable';
				}
				return @$limit;
		    }else
				return NULL;
			
		//}
	}
	function getpincodewhere($template_id=NULL)
	{
		//if($this->db->table_exists('tbl_template_'.$template_id.'_meta'))
		//{
			$this->db->where('assignment_pincode',1);
			//$query = $this->db->get('tbl_template_'.$template_id.'_meta');
			$this->db->where('global_template_id',$template_id);
			$query = $this->db->get(TABLE_TEMPLATE_GLOBAL_META);
			
			$t_details = $query->row_array();
			return @$t_details['field_name'];
		//}
		
	}
	function getpincodesystem()
	{
		$where ='';
		$select ='pincode_name';
		$pincode_system1 = $this->getRecordList('tbl_mst_pincode',$where,$select);
		foreach($pincode_system1 as $k=>$v)
		{
			$pincode_system[$k] = $v['pincode_name'];
		}
		return @$pincode_system;
		
	}
	
	// New added for getting assigned or visited pincode field name from Template
	function assign_visit_pincode($template_id=NULL)
	{
		$this->db->distinct();
		$this->db->select('field_name,assignment_pincode,visited_pincode');
		$this->db->where('global_template_id',$template_id);
		$this->db->where('(assignment_pincode="1" OR visited_pincode="1")',NULL,FALSE);
		$this->db->order_by('visited_pincode','desc');
		$t_details = $this->db->get(TABLE_TEMPLATE_GLOBAL_META)->result_array();

		return @$t_details;
	}
	
	
	function setClientEmpRates($caseid=NULL,$flag=NULL)
	{
		//fetch rate
		$casedetails = $this->Mastermodel->getDetail(TABLE_CASES,'case_id',@$caseid);
		$template_id = $casedetails['template_id'];
				
		$rate_param_arr['country']= @$casedetails['country_id'];
		$rate_param_arr['zone']= @$casedetails['zone_id'];
		$rate_param_arr['center']= @$casedetails['center_id'];
		$rate_param_arr['subcenter']= @$casedetails['subcenter_id'];
		$rate_param_arr['client']= @$casedetails['client_id'];
		$rate_param_arr['activity']= @$casedetails['activity_id'];
		$rate_param_arr['product']= @$casedetails['product_id'];
		$rate_param_arr['verification']= @$casedetails['veriftype_id'];
		$rate_param_arr['fe']= @$casedetails['fe_id'];
		
		/* New added for Visited pincode ----------------- :: */
		$pncodetmp_arr = $this->assign_visit_pincode($template_id);
		$pincode_val="";
		if(!empty($pncodetmp_arr))
		{
			foreach($pncodetmp_arr as $x=>$val_arr)
			{
				$pncodetmp="";
				if($val_arr['visited_pincode']==1 && @$casedetails[$val_arr['field_name']]!=""){
					$pncodetmp=$val_arr['field_name'];
					$pincode_val=$casedetails[$pncodetmp];
					break;
				}
				elseif($val_arr['assignment_pincode']==1 && @$casedetails[$val_arr['field_name']]!=""){
					$pncodetmp=$val_arr['field_name'];
					$pincode_val=$casedetails[$pncodetmp];
				}
				
			}
		}
		$rate_param_arr['pincode'] = @$pincode_val;
		/* End of Visited Pincode code -------------- 
			echo "<pre>"; print_r($pncodetmp_arr);
			echo "pincode Field Name : ".$pncodetmp." : Pincode Field Value : ".$pincode_val;
			//die();
		*/
		
		$values = array();
		//echo '<pre>';print_r($rate_param_arr);
		
		// New change :: Online Fe (2622) : Emp Rate always 0 not null or blank
		if($_SESSION['sess_user_country_id']=='11' && $rate_param_arr['fe']=='2622'){
			$emprate=0;
			$emp_query="<br/><b>Online Fe (2622) :: New change emp rate = 0 </b><br/>";
		}
		else{
			$emprate = $this->getEmpRate($rate_param_arr);
			$emp_query="<br/><b>Employee Rate Query :: </b><br/>".$this->db->last_query();
		}
		
		
		
		if(@$casedetails['client_id'])
		{
		   $clientrate = $this->getClientRate($rate_param_arr);
		   $client_query="<br/><b>Client Rate Query :: </b><br/>".$this->db->last_query();
		}	
		$values['emp_rate'] = @$emprate;
		$values['client_rate'] = @$clientrate;
		
		$icl_ocl_flag = $this->getICL_OCLvalue($pincode_val);
		
		if(!empty($icl_ocl_flag)){
		  $values['icl_ocl_flag'] = $icl_ocl_flag;
		}
		
		if(empty($flag))
		{
			$this->Casesmodel->update(TABLE_CASES,'case_id',$caseid,$values);
		}
		
		// For case history table :: Extra params
		$values['case_rate_params']=$rate_param_arr;
		$values['emp_query']=$emp_query;
		$values['client_query']=$client_query;
		
		return $values;
	}
	
	// Updated for Client rate categorywise
	function getrecord($tblname=NULL,$where=NULL,$select=NULL,$type=NULL,$categorywise=NULL)
	{		
		
		if(!empty($categorywise) && $categorywise!='clientrate')
		{
			$where_new=str_replace('AND pincode_name IS NULL','AND clientrate_pincode_name IS NULL',$where);
			$where='';
			$where=$where_new." AND apply_rate='1' AND ".$categorywise." IS NOT NULL";
			
			//echo $where;
		}
		
		
		if($select)
			$this->db->select($select);
		if($where)
			$this->db->where($where,NULL,FALSE);
		if($type=="client" && (empty($categorywise) || $categorywise=='clientrate')) // Updated here
		{
			$this->db->join(TABLE_MST_PINCODE,TABLE_MST_PINCODE.'.pincode_id = '.$tblname.'.clientrate_pincode_name','left');
		}
		if($type=="employee")
		{
			$this->db->join(TABLE_MST_PINCODE,TABLE_MST_PINCODE.'.pincode_id = '.$tblname.'.emprate_pincode_name','left');
		}
		$query = $this->db->get($tblname);
		return $query->result_array();
	}
	
	// Fetch case details for assigned template minimal
	function get_case_data($case_id=NULL)
	{
		$this->db->select('case_id,country_id,activity_id,product_id,client_id,veriftype_id,template_id,redo_flag,fe_id,tele_id');
		$this->db->where('case_id',$case_id);
		if($_SESSION['sess_user_emp_id']!='7')
		{
			$this->db->where('(delete_flag IS NULL)',NULL,FALSE);
		}
		$case_details= $this->db->get(TABLE_CASES)->row_array();
		
		return $case_details;
	}
	
	function setIclOclFlag($caseid=NULL,$flag=NULL)
	{
		//fetch rate
		$casedetails = $this->Mastermodel->getDetail(TABLE_CASES,'case_id',@$caseid);
		$template_id = $casedetails['template_id'];
		
		/* New added for Visited pincode ----------------- :: */
		$pncodetmp_arr = $this->assign_visit_pincode($template_id);
		$pincode_val="";
		if(!empty($pncodetmp_arr))
		{
			foreach($pncodetmp_arr as $x=>$val_arr)
			{
				$pncodetmp="";
				if($val_arr['visited_pincode']==1 && @$casedetails[$val_arr['field_name']]!=""){
					$pncodetmp=$val_arr['field_name'];
					$pincode_val=$casedetails[$pncodetmp];
					break;
				}
				elseif($val_arr['assignment_pincode']==1 && @$casedetails[$val_arr['field_name']]!=""){
					$pncodetmp=$val_arr['field_name'];
					$pincode_val=$casedetails[$pncodetmp];
				}
				
			}
		}
		
		$values = array();
		$icl_ocl_flag = $this->getICL_OCLvalue($pincode_val);
		$values['icl_ocl_flag'] = $icl_ocl_flag;
		
		
		if(empty($flag))
		{
			$this->Casesmodel->update(TABLE_CASES,'case_id',$caseid,$values);
		}
	
		return $values;
	}
	
}
?>