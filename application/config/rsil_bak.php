<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Rsil extends CI_Controller {
        public $rsilClientID;
	public $rsilProductType;
	//public $rsilActivityType;
	public function __construct()
	{
		if (session_id() == "") session_start();
		parent::__construct();
		$this->load->model('Frontmodel','',TRUE);
		$this->load->model('Templatemodel','',TRUE);
		$this->load->model('Casesmodel','',TRUE);
		$this->load->model('Commonfunmodel','',TRUE);
		$this->load->model('Mastermodel','',TRUE);
		$this->lang->load('rsil', 'english');
		$this->form_validation->set_error_delimiters('<p class="error_text">','</p>');
	
		$this->Commonfunmodel->checkAdminLogin();
		$this->Commonfunmodel->setTimeZone();
		$rsilClientID = 65;
	}
        function rsilcases($flag=NULL,$order=NULL)
        {
			$message = '';
			if($this->uri->segment(4) == 'a')
			{			
				$message = "Successfully Added";				
			}
			$orderby = '';
			if(in_array($flag,array('notificationid','verificationtype','applicantname','applicanttype','location','pincode','created_date')))
			{			
				$orderby = $flag." ".$order;				
			}
                                           
                        $RecordsArr = $this->Frontmodel->getRecordList('tbl_mst_rsil_cases','flag IS NULL',NULL,@$orderby);
                       	
			//echo $this->db->last_query();die();		
			$data['message'] = $message;
                        $data['records'] = $RecordsArr;
                        $data['vfile'] = 'rsil/caseList';
                        $this->load->view('admin/layout',$data);
        }
       
        function acceptCase()
        {            
		//echo '<pre>';print_r($_POST);die();
		$post = $_POST;
		if(!empty($post['chkaccept']))
		{
			$error_msg = array();
			
		    $msg ='';
		    foreach($post['chkaccept'] as $k=>$v)
		    {
			$caseArr = array();
			$caseDet = $this->Mastermodel->getDetail('tbl_mst_rsil_cases','case_id',$v);
			$PincodeRec  = $this->Mastermodel->getDetail('tbl_mst_pincode','pincode_name',@$caseDet['pincode']);			
			$adrArr = array();
			//echo strlen($caseDet['address']);die();
			
			
			if(!empty($PincodeRec))
			{
				if($caseDet['verificationtype']=='R')
				{
					$template_id = 112;
					$templateDetails = $this->Mastermodel->getDetail('tbl_mst_case','case_template_id',112);
					
					$caseArr = array('created_date'=>date('Y-m-d H:i:s'),
							'modified_date'=>date('Y-m-d H:i:s'),
							'created_id'=>@$_SESSION['sess_user_emp_id'],
							'modified_id'=>@$_SESSION['sess_user_emp_id'],
							'country_id'=>(@$PincodeRec['pincode_country_name'])?@$PincodeRec['pincode_country_name']:NULL,
							'zone_id'=>(@$PincodeRec['pincode_clusterid'])?@$PincodeRec['pincode_clusterid']:NULL,
							'center_id'=>(@$PincodeRec['pincode_centreid'])?@$PincodeRec['pincode_centreid']:NULL,
							'subcenter_id'=>(@$PincodeRec['pincode_subcentreid'])?@$PincodeRec['pincode_subcentreid']:NULL,
							'product_id'=>(@$templateDetails['case_product_id'])?@$templateDetails['case_product_id']:NULL,
							'activity_id'=>(@$templateDetails['case_activity_id'])?@$templateDetails['case_activity_id']:NULL,
							'client_id'=> 65,
							//'client_id'=>(@$templateDetails['case_client_id'])?@$templateDetails['case_client_id']:NULL,
							'template_id'=>(@$template_id)?@$template_id:NULL,
							'applicants_name'=>(@$caseDet['applicantname'])?@$caseDet['applicantname']:NULL,
							'ref_no'=>(@$caseDet['notificationid'])?@$caseDet['notificationid']:NULL,
							'veriftype_id'=>11,
							'field51'=>(@$caseDet['applicanttype'])?@$caseDet['applicanttype']:NULL,
							'pan_card_no'=>(@$caseDet['pan'])?@$caseDet['pan']:NULL,
							'resi_add1'=>(@$caseDet['address'])?@$caseDet['address']:NULL,
							'resi_land_mark'=>(@$caseDet['landmark'])?@$caseDet['landmark']:NULL,
							'resi_pincode'=>(@$caseDet['pincode'])?@$caseDet['pincode']:NULL,
							'resi_city'=>(@$caseDet['location'])?@$caseDet['location']:NULL,
							'state'=>(@$caseDet['state'])?@$caseDet['state']:NULL,
							'resph'=>(@$caseDet['mobilenumber'])?@$caseDet['mobilenumber']:NULL,
							//'branch_name'=>(@$caseDet['officename'])?@$caseDet['officename']:NULL,
							'field52'=>(@$caseDet['applicant_category'])?@$caseDet['applicant_category']:NULL,
							'field53'=>(@$caseDet['primary_occupation'])?@$caseDet['primary_occupation']:NULL,
							'field55'=>(@$caseDet['locality'])?@$caseDet['locality']:NULL
							);
					//echo $caseArr['applicants_address'];die();
					//if(strlen($caseArr['applicants_address'])>60){}
					
						$adrArr = explode('|',$caseArr['resi_add1']);
						//echo '<pre>';print_r($adrArr);die();
						
						if($adrArr[0]!="")
						$caseArr['resi_add1'] = $adrArr[0];
						
						if(@$adrArr[1]!="")
						$caseArr['resi_add2'] = $adrArr[1];
						
						if(@$adrArr[2]!="")
						$caseArr['resi_add3'] = $adrArr[2];
					
					
					
					
				}
				if($caseDet['verificationtype']=='B')
				{
					$template_id = 110;
					$templateDetails = $this->Mastermodel->getDetail('tbl_mst_case','case_template_id',110);
					
					$caseArr = array('created_date'=>date('Y-m-d H:i:s'),
							'modified_date'=>date('Y-m-d H:i:s'),
							'created_id'=>@$_SESSION['sess_user_emp_id'],
							'modified_id'=>@$_SESSION['sess_user_emp_id'],
							'country_id'=>(@$PincodeRec['pincode_country_name'])?@$PincodeRec['pincode_country_name']:NULL,
							'zone_id'=>(@$PincodeRec['pincode_clusterid'])?@$PincodeRec['pincode_clusterid']:NULL,
							'center_id'=>(@$PincodeRec['pincode_centreid'])?@$PincodeRec['pincode_centreid']:NULL,
							'subcenter_id'=>(@$PincodeRec['pincode_subcentreid'])?@$PincodeRec['pincode_subcentreid']:NULL,
							'product_id'=>(@$templateDetails['case_product_id'])?@$templateDetails['case_product_id']:NULL,
							'activity_id'=>(@$templateDetails['case_activity_id'])?@$templateDetails['case_activity_id']:NULL,
							//'client_id'=>(@$templateDetails['case_client_id'])?@$templateDetails['case_client_id']:NULL,
							'client_id'=>65,
							'template_id'=>(@$template_id)?@$template_id:NULL,
							'applicants_name'=>(@$caseDet['applicantname'])?@$caseDet['applicantname']:NULL,
							'ref_no'=>(@$caseDet['notificationid'])?@$caseDet['notificationid']:NULL,
							'veriftype_id'=>12,
							'field26'=>(@$caseDet['applicanttype'])?@$caseDet['applicanttype']:NULL,
							'pan_card_no'=>(@$caseDet['pan'])?@$caseDet['pan']:NULL,
							'off_add1'=>(@$caseDet['address'])?@$caseDet['address']:NULL,
							'off_land_mark'=>(@$caseDet['landmark'])?@$caseDet['landmark']:NULL,
							'off_pincode'=>(@$caseDet['pincode'])?@$caseDet['pincode']:NULL,
							'off_city'=>(@$caseDet['location'])?@$caseDet['location']:NULL,
							'state'=>(@$caseDet['state'])?@$caseDet['state']:NULL,
							'offph'=>(@$caseDet['mobilenumber'])?@$caseDet['mobilenumber']:NULL,
							'field4'=>(@$caseDet['officename'])?@$caseDet['officename']:NULL,
							'field40'=>(@$caseDet['applicant_category'])?@$caseDet['applicant_category']:NULL,
							'field41'=>(@$caseDet['primary_occupation'])?@$caseDet['primary_occupation']:NULL,
							'field42'=>(@$caseDet['locality'])?@$caseDet['locality']:NULL
							);
					
					//if(strlen($caseArr['applicants_address'])>60){}
					
						$adrArr = explode('|',$caseArr['off_add1']);
						//echo '<pre>';print_r($adrArr);die();
						
						if($adrArr[0]!="")
						$caseArr['off_add1'] = $adrArr[0];
						
						if(@$adrArr[1]!="")
						$caseArr['off_add2'] = $adrArr[1];
						
						if(@$adrArr[2]!="")
						$caseArr['off_add3'] = $adrArr[2];
				}
				//$this->Templatemodel->update('tbl_mst_rsil_cases','case_id',@$caseDet['case_id'],array('flag'=>'A'));	
				//echo '<pre>';print_r($casedata);
			}else{
				if($caseDet['verificationtype']=='R')
				{
					$template_id = 112;
					$templateDetails = $this->Mastermodel->getDetail('tbl_mst_case','case_template_id',112);
					
					$caseArr = array('created_date'=>date('Y-m-d H:i:s'),
							'modified_date'=>date('Y-m-d H:i:s'),
							'created_id'=>@$_SESSION['sess_user_emp_id'],
							'modified_id'=>@$_SESSION['sess_user_emp_id'],							
							'country_id'=>(@$_SESSION['sess_user_country_id'])?@$_SESSION['sess_user_country_id']:NULL,
							'zone_id'=>(@$_SESSION['sess_user_zone_id'])?@$_SESSION['sess_user_zone_id']:NULL,
							'center_id'=>(@$_SESSION['sess_user_center_id'])?@$_SESSION['sess_user_center_id']:NULL,
							'subcenter_id'=>(@$_SESSION['sess_user_subcenter_id'])?@$_SESSION['sess_user_subcenter_id']:NULL,							
							'product_id'=>(@$templateDetails['case_product_id'])?@$templateDetails['case_product_id']:NULL,
							'activity_id'=>(@$templateDetails['case_activity_id'])?@$templateDetails['case_activity_id']:NULL,
							
							'client_id'=> 65,
							//'client_id'=>(@$templateDetails['case_client_id'])?@$templateDetails['case_client_id']:NULL,
							'template_id'=>(@$template_id)?@$template_id:NULL,
							'applicants_name'=>(@$caseDet['applicantname'])?@$caseDet['applicantname']:NULL,
							'ref_no'=>(@$caseDet['notificationid'])?@$caseDet['notificationid']:NULL,
							'veriftype_id'=>11,
							'field51'=>(@$caseDet['applicanttype'])?@$caseDet['applicanttype']:NULL,
							'pan_card_no'=>(@$caseDet['pan'])?@$caseDet['pan']:NULL,
							'resi_add1'=>(@$caseDet['address'])?@$caseDet['address']:NULL,
							'resi_land_mark'=>(@$caseDet['landmark'])?@$caseDet['landmark']:NULL,
							'resi_pincode'=>(@$caseDet['pincode'])?@$caseDet['pincode']:NULL,
							'resi_city'=>(@$caseDet['location'])?@$caseDet['location']:NULL,
							'state'=>(@$caseDet['state'])?@$caseDet['state']:NULL,
							'resph'=>(@$caseDet['mobilenumber'])?@$caseDet['mobilenumber']:NULL,
							//'branch_name'=>(@$caseDet['officename'])?@$caseDet['officename']:NULL,
							'field52'=>(@$caseDet['applicant_category'])?@$caseDet['applicant_category']:NULL,
							'field53'=>(@$caseDet['primary_occupation'])?@$caseDet['primary_occupation']:NULL,
							'field55'=>(@$caseDet['locality'])?@$caseDet['locality']:NULL
							);
					
					//if(strlen($caseArr['applicants_address'])>60){}
					
						$adrArr = explode('|',$caseArr['resi_add1']);
						
						if($adrArr[0]!="")
						$caseArr['resi_add1'] = $adrArr[0];
						
						if(@$adrArr[1]!="")
						$caseArr['resi_add2'] = $adrArr[1];
						
						if(@$adrArr[2]!="")
						$caseArr['resi_add3'] = $adrArr[2];
					

				}
				if($caseDet['verificationtype']=='B')
				{
					$template_id = 110;
					$templateDetails = $this->Mastermodel->getDetail('tbl_mst_case','case_template_id',110);
					
					$caseArr = array('created_date'=>date('Y-m-d H:i:s'),
							'modified_date'=>date('Y-m-d H:i:s'),
							'created_id'=>@$_SESSION['sess_user_emp_id'],
							'modified_id'=>@$_SESSION['sess_user_emp_id'],
							'country_id'=>(@$_SESSION['sess_user_country_id'])?@$_SESSION['sess_user_country_id']:NULL,
							'zone_id'=>(@$_SESSION['sess_user_zone_id'])?@$_SESSION['sess_user_zone_id']:NULL,
							'center_id'=>(@$_SESSION['sess_user_center_id'])?@$_SESSION['sess_user_center_id']:NULL,
							'subcenter_id'=>(@$_SESSION['sess_user_subcenter_id'])?@$_SESSION['sess_user_subcenter_id']:NULL,
							'product_id'=>(@$templateDetails['case_product_id'])?@$templateDetails['case_product_id']:NULL,
							'activity_id'=>(@$templateDetails['case_activity_id'])?@$templateDetails['case_activity_id']:NULL,
							'client_id'=>65,
							'template_id'=>(@$template_id)?@$template_id:NULL,
							'applicants_name'=>(@$caseDet['applicantname'])?@$caseDet['applicantname']:NULL,
							'ref_no'=>(@$caseDet['notificationid'])?@$caseDet['notificationid']:NULL,
							'veriftype_id'=>12,
							'field26'=>(@$caseDet['applicanttype'])?@$caseDet['applicanttype']:NULL,
							'pan_card_no'=>(@$caseDet['pan'])?@$caseDet['pan']:NULL,
							'off_add1'=>(@$caseDet['address'])?@$caseDet['address']:NULL,
							'off_land_mark'=>(@$caseDet['landmark'])?@$caseDet['landmark']:NULL,
							'off_pincode'=>(@$caseDet['pincode'])?@$caseDet['pincode']:NULL,
							'off_city'=>(@$caseDet['location'])?@$caseDet['location']:NULL,
							'state'=>(@$caseDet['state'])?@$caseDet['state']:NULL,
							'offph'=>(@$caseDet['mobilenumber'])?@$caseDet['mobilenumber']:NULL,
							'field4'=>(@$caseDet['officename'])?@$caseDet['officename']:NULL,
							'field40'=>(@$caseDet['applicant_category'])?@$caseDet['applicant_category']:NULL,
							'field41'=>(@$caseDet['primary_occupation'])?@$caseDet['primary_occupation']:NULL,
							'field42'=>(@$caseDet['locality'])?@$caseDet['locality']:NULL
							);
						$adrArr = explode('|',$caseArr['off_add1']);
						
						if($adrArr[0]!="")
						$caseArr['off_add1'] = $adrArr[0];
						
						if(@$adrArr[1]!="")
						$caseArr['off_add2'] = $adrArr[1];
						
						if(@$adrArr[2]!="")
						$caseArr['off_add3'] = $adrArr[2];
					
				}
				
			}
			//echo '<pre>';print_r($caseArr);die();
			if(!empty($caseArr))
			{
				$this->db->trans_start();
				// Check if for that case, activity has : FE/Tele Seperation option ticked in activity master
				$sess_separate_tele_operation=$this->db->select('separate_tele_operation')
									->from(TABLE_MASTER_ACTIVITY)
									->where('activity_id',13)
									->get()->row_array();
				if($sess_separate_tele_operation['separate_tele_operation'] != '')
				{
					$caseArr['case_operation_status'] = 1; //  Status -> 5 : when any case is submitted by FE
				}
				$this->Templatemodel->add('tbl_cases',$caseArr);
				$insID = $this->db->insert_id();
				$parent_case_id = $insID;
				
				$case_no = array('case_no'=>@$insID,'parent_case_id'=>@$parent_case_id);
														
				$this->Templatemodel->update('tbl_cases','case_id',@$insID,@$case_no);
				$this->Templatemodel->update('tbl_mst_rsil_cases','case_id',@$caseDet['case_id'],array('flag'=>'A'));
				//Add entry in history log table.
                $this->Casesmodel->set_history($insID,'RSIL case accept',$caseArr);
				
				$this->db->trans_complete();
				$msg = 'a';
			}			
		    }
		   
		}
		redirect('user/rsil/rsilcases/'.@$msg);
        }
	 function chkjson()
	{
		$str = "<?xml version='1.0' encoding='UTF-8'?>
<xml><RES_HEADER><NID>1875896</NID><CUST_NAME>JAVED  SAMTUL</CUST_NAME><VER_TYPE>R</VER_TYPE></RES_HEADER><ERR_DET><ERROR_CODE>2000</ERROR_CODE><ERROR_DESC>'INV_COMMTS' value 'lkajsdalksdalksdj alkdj alksjd aklsjd alskjd al;sj 
dkandjal ksdj 
lak jdlaj a' should not contain Special characters except(.,#/-&amp; and space).</ERROR_DESC></ERR_DET></xml>";
	$str= html_entity_decode($str);
	$str=str_replace(array('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body><ns2:inputResponse xmlns:ns2="http://webservice.fi.pdglos.indus.com/"><return>','</return></ns2:inputResponse></soap:Body></soap:Envelope>','&'),'',$str);
	
	$xml = simplexml_load_string(html_entity_decode($str));
	
	$json = json_encode($xml);
	$array = json_decode($json,TRUE);//echo '<pre>';print_r($array);die();


	}
	function sendtoclient($flag=NULL,$order=NULL)
	{
		
		if($flag=='nosearch')
		{		    
		      $_SESSION["sess_rsil_search_wh"]="";
		      $_SESSION["sess_rsil_search_post"]="";
		      $_SESSION["sess_do_order_field"] = "";
		      $_SESSION["sess_do_order_type"] = "";
		      
		}
		if($flag=='c')
		$data['close_msg'] = 'Cases Closed successfully.';
		$errorArr = array();	
		$caseArr = array();
		$post = $this->input->post();

		if(@$post['exportflag']=="sendclient")
		{
			if(!empty($post['rsilcases']))
			{
				$casesIndex = $post['rsilcases'];
				foreach($casesIndex as $k=>$v)
				{					
					$caseArr[] = $this->Mastermodel->getDetail('tbl_cases','case_id',$v);
				}
				if(!empty($caseArr))
				{
					$respArr = array();
					$respArr = $this->sendTo($caseArr);
					//echo '<pre>';print_r($respArr);die();
					if(!empty($respArr))
					{
						foreach($respArr as $s=>$v)
						{
							$str= html_entity_decode($v);
							$str=str_replace(array('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body><ns2:inputResponse xmlns:ns2="http://webservice.fi.pdglos.indus.com/"><return>','</return></ns2:inputResponse></soap:Body></soap:Envelope>','&'),'',$str);
							//echo $str;die();
							$xml = simplexml_load_string(html_entity_decode($str));
							
							$json = json_encode($xml);
							$array = json_decode($json,TRUE);
							//echo '<pre>';print_r($array);
							if($array['ERR_DET']['ERROR_CODE'] == '0000')
							{
								/*$a = array('send_to_client_time'=>date('Y-m-d H:i:s'),'modified_id'=>@$_SESSION['sess_user_emp_id'],'all_close'=>1);
					
								$this->db->where('case_id',$s);
								$this->db->update(TABLE_CASES,$a);
								
								$holdcaseValue = $this->Mastermodel->getCustomeFieldValue(TABLE_CASES,"case_id",@$s,"hold_parent_case_id");
								if($holdcaseValue != NULL || $holdcaseValue != "")
								{
									$this->db->where('case_id',$holdcaseValue);
									$this->db->update(TABLE_CASES,$a);
								}*/
								$this->settat($s);
								//Add entry in history log table.
                                $this->Casesmodel->set_history($s,'RSIL case sent');
								
								$errorArr[@$s][] = 'Successfully Sent.';
							}else
							{
								
								if($array['ERR_DET']['ERROR_DESC'])
								{
									if(is_array($array['ERR_DET']['ERROR_DESC']))
									{
										foreach($array['ERR_DET']['ERROR_DESC'] as $k1=>$v1)
										{
											$errorArr[@$s][] = $v1;
										}
									}else{
										$errorArr[@$s][] = $array['ERR_DET']['ERROR_DESC'];
									}								
															
								}else
								{
									foreach($array['ERR_DET'] as $k=>$v)
									{
										if(is_array($array['ERR_DET'][$k]['ERROR_DESC']))
										{
												foreach($array['ERR_DET'][$k]['ERROR_DESC'] as $p=>$t)
												$errorArr[@$s][] = $t;
										}else{
												$errorArr[@$s][] = $v['ERROR_DESC'];
										}
									}
								}
							//echo '<pre>';print_r($array['ERR_DET']);print_r($errorArr);die();									
							}
						}//echo '<pre>';print_r($errorArr);die();
						//redirect('user/rsil/sendtoclient/nosearch');
					}
				}//die();
				//echo '<pre>';print_r($_POST);die();
			}
		}	
		if($post['exportflag'] == 'close')
		{
			//echo '<pre>';print_r($post);die();
			if(!empty($post['rsilcases']))
			{
				foreach($post['rsilcases'] as $j=>$h)
				{
					$this->settat($h);
					
				}//die();	
				redirect('user/rsil/sendtoclient/c');			
			}
			redirect('user/rsil/sendtoclient/nosearch');
		}	
		$module="cases";
		$this->Frontmodel->set_table($module);
		$table_meta=$this->Frontmodel->table_meta;
		$table=$this->Frontmodel->tablenm;
		
		$template_id = 112;
		if(@$template_id)
		{
			$this->Templatemodel->set_table($template_id);
			
			$tno="t1";
			$join_arr=array();
			$join_arr[]=array("table"=>$table_meta.' as '.$tno, 'condition' => $this->Templatemodel->table_meta.'.field_name'.'='.$tno.'.field_name','type'=>'left');
			
			$select_arr=$this->Templatemodel->table_meta.'.*, '.$tno.'.field_foreign_key';
			//$search_fields=$this->Frontmodel->getRecordList($this->Templatemodel->table_meta,$this->Templatemodel->table_meta.'.send_to_client_search=1',$select_arr,'field_row,field_column, meta_id ',NULL,NULL,$join_arr);
			//$meta_records=$this->Frontmodel->getRecordList($this->Templatemodel->table_meta,$this->Templatemodel->table_meta.'.list_show=1',$select_arr,'field_row,field_column, meta_id ',NULL,NULL,$join_arr);
			$search_fields=$this->Frontmodel->getRecordList($this->Templatemodel->table_meta,$this->Templatemodel->table_meta.'.send_to_client_search=1 AND global_template_id="'.$template_id.'"',$select_arr,'field_row,field_column, meta_id ',NULL,NULL,$join_arr);
			$meta_records=$this->Frontmodel->getRecordList($this->Templatemodel->table_meta,$this->Templatemodel->table_meta.'.list_show=1 AND global_template_id="'.$template_id.'"',$select_arr,'field_row,field_column, meta_id ',NULL,NULL,$join_arr);
		}
		
		if(@$post['flag']=="search")
		{
			//echo '<pre>';print_r($_POST);die();
			$_SESSION["sess_rsil_search_post"]=$this->input->post();
			        foreach($search_fields as $field_key=>$column) 
				{
					
					$val=$this->input->post($column['field_name']);
					if(isset($val))
					{ 
						if(@$val!="")
						{
							if($column['field_type']==1 || $column['field_type']==2)
							{
								if($val!="")
									$where[] = $table.'.'.$column['field_name'].' LIKE "%'.trim(addslashes($val)).'%"';
							}
							if($column['field_type']==3)
							{
								if($val!="")
									$where[] = $table.'.'.$column['field_name'].' ="'.trim(addslashes($val)).'"';
							}
							if($column['field_type']==10)
							{
								if($val!="")
								{
									
									if($column['search_range']==1)
									{
									   if($val[0]!="")	
									   $where[] = 'DATE_FORMAT('.$table.'.'.$column['field_name'].',"%Y-%m-%d") >= "'.date('Y-m-d',strtotime($val[0])).'"';
									   if($val[1]!="")	
									   $where[] = 'DATE_FORMAT('.$table.'.'.$column['field_name'].',"%Y-%m-%d") <= "'.date('Y-m-d',strtotime($val[1])).'"';
									   
									}else
									{
									   $condition=(@$column['condition']!="")?@$column['condition']:"=";
									   $where[] = 'DATE_FORMAT('.$table.'.'.$column['field_name'].',"%Y-%m-%d") '.$condition.'"'.trim(addslashes(date('Y-m-d',strtotime($val)))).'"';
									}
								}
							}
						}
					}
				}	
				if(!empty($where))
					$_SESSION["sess_rsil_search_wh"]=implode(" AND ", $where);
				else
					$_SESSION["sess_rsil_search_wh"]="";
		}
		
		$join_arr=array();
		$select_arr=array();
		$this->db->where('field_foreign_key != ""',NULL,NULL);
		$query = $this->db->get(@$table_meta);
		$records=$query->result_array();
		if($records)
		foreach($records as $k=>$recs)
		{
			$tno='t'.$k;
			$pk=$this->Mastermodel->getCustomeFieldValue("tbl_mst_".@$recs['field_foreign_key']."_meta","field_type",12,"field_name");
			$s_value=$this->Mastermodel->getCustomeFieldValue("tbl_mst_".@$recs['field_foreign_key']."_meta","list_order",1,"field_name");
			$join_arr[]=array("table"=>"tbl_mst_".$recs['field_foreign_key'].' as '.$tno,'condition'=>@$table.'.'.$recs["field_name"].'='.$tno.'.'.$pk,'type'=>'left');
			
			//show fname+laname of employee.
			if(@$recs['field_foreign_key']=='employee' && $s_value == 'fname')
			        $select_arr[] = 'concat('.$tno.'.'.$s_value.'," ",'.$tno.'.lname) as '.$recs["field_name"];
			else
				$select_arr[] = $tno.'.'.$s_value.' as '.$recs["field_name"];
		}		
		$select=NULL;
		if($select_arr)
		{
			$select=$table.".*,".implode(",",$select_arr);
		}
		$where_arr=array();
		$where_arr[] = $table.".client_id = 65";
		$where_arr[] = $table.".case_status = 'Close'";
		$where_arr[] = $table.".send_to_client_time IS NULL";
		
		//$where_arr[] = $table.".case_hold_time IS NULL";
		
		$where = implode(" AND ",$where_arr);
		if(@$_SESSION["sess_rsil_search_wh"]!="")
			$where.=' AND '.@$_SESSION["sess_rsil_search_wh"];
		
		$orderby = 'case_id desc, case_id ';
		if(in_array($flag,array('case_id','ref_no','applicants_name','veriftype_name')))
		{
		     $orderby = $flag.' '.$order;
		}
		
		$records=$this->Frontmodel->getRecordList($table,$where,$select, $orderby,NULL,NULL,@$join_arr);
		//echo $this->db->last_query();die();
		$data['errorArr'] = @$errorArr;
		$data['exportList'] = $records;
		$data['actionURL'] = base_url().'user/rsil/sendtoclient';
		$data['typeflag'] = 'sendclient';
		$data['search_post'] =@$_SESSION["sess_rsil_search_post"];
		$data["search_fields"]=@$search_fields;
		$data['vfile'] = 'rsil/sendtoclientlist';
		$this->load->view('user/home',$data);
	}
	function sendTo($cases=NULL)
	{
		$arr = array();
		//echo '<pre>';print_r($cases);die();
		foreach($cases as $k=>$v)
		{//echo '<pre>';print_r($v);die();
			$v = array_map('trim', $v);
			$FEDetails = $this->Mastermodel->getDetail('tbl_mst_employee','empslno',@$v['fe_id']);
			$str = "";
			$filepath = $_SERVER["DOCUMENT_ROOT"]."/uploads/cases/";
			//echo '<pre>';print_r($v);
			if($v['veriftype_id'] == 11)
			{
				$img1 = '';
				$img2 = '';
				$img3 = '';
				$googlemap = '';
				
				if (@$v['image1']!="" && file_exists($filepath.@$v['image1']))
				 $img1 = base64_encode(file_get_contents($filepath.@$v['image1']));
				
				if (@$v['image_2']!="" && file_exists($filepath.@$v['image_2']))
				 $img2 = base64_encode(file_get_contents($filepath.@$v['image_2']));
				
				if (@$v['applicant_signature']!="" && file_exists($filepath.@$v['applicant_signature']))
				 $img3 = base64_encode(file_get_contents($filepath.@$v['applicant_signature']));
				
				if(@$v['fe_submit_latitude']!="" && @$v['fe_submit_longitude']!="")
				{
					$tmp = $this->Viewgooglemap(@$v['fe_submit_latitude'],@$v['fe_submit_longitude']);
					if($tmp!="")
					$googlemap = base64_encode($tmp);
				}				
				
				$app_relation = '';
				if(@$v['field53'] == 'Individul with Salaried')
				{
					$app_relation = array_search(@$v['relation_with_applicant'],lang('relation_with_applicant_salaried'));
					
				}elseif(@$v['field53'] == 'Individual with Self employeed' || @$v['field53'] == 'Organization')
				{
					$app_relation = array_search(@$v['if_others_relationship_with_applicant'],lang('relation_with_applicant_selfemployeed'));
					
				}
				/*$c_city = $this->db->select('Citycode')->get_where('tbl_mst_city',array('CityDesc'=>trim($v['resi_city'])))->row_array();
				$pmt_city = $this->db->select('Citycode')->get_where('tbl_mst_city',array('CityDesc'=>trim($v['pmt_city'])))->row_array();
				
				$c_locality = $this->db->select('SUBURBAN_CODE')->get_where('tbl_mst_suburban',array('SUBURBAN_DESC'=>trim($v['field55'])))->row_array();
				$pmt_locality = $this->db->select('SUBURBAN_CODE')->get_where('tbl_mst_suburban',array('SUBURBAN_DESC'=>trim($v['field56'])))->row_array();	*/			
				$c_city = trim($v['resi_city']);
				$pmt_city = trim($v['pmt_city']);
				
				$c_locality = trim($v['field55']);
				$pmt_locality = trim($v['field56']);	
				$str1 = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:web="http://webservice.extagency.indus.com/">
						<soapenv:Header/>
								<soapenv:Body>
								<web:input>										
								<arg0>
								<![CDATA[<FI_RESULT>
									<REQ_HEADER>
									    <NID>'.@$v['ref_no'].'</NID>
									    <CUST_NAME>'.@$v['applicants_name'].'</CUST_NAME>
									    <VER_TYPE>R</VER_TYPE>
									</REQ_HEADER>
									<VER_DET>
									    <VER_AGNT>'.@$FEDetails['fname'].' '.@$FEDetails['lname'].'</VER_AGNT>
									    <DOV>'.((@$v['fe_submit_date']!= "")?(date('Y-m-d',strtotime(@$v['fe_submit_date'])).'T'.date('H:i:s',strtotime(@$v['fe_submit_date']))):NULL).'</DOV>
									    <NOV>1</NOV>
									    <BRANCH>'.@$v['branch_name'].'</BRANCH>
									</VER_DET>
									<CUR_ADDR>
									    <C_ADDR1>'.@$v['resi_add1'].'</C_ADDR1>
									    <C_ADDR2>'.@$v['resi_add2'].'</C_ADDR2>
									    <C_STATE>'.@$v['state'].'</C_STATE>
										<C_SUBURBAN>'.@$c_locality.'</C_SUBURBAN>
										<C_CITY>'.@$c_city.'</C_CITY>									
									    <C_PIN>'.@$v['resi_pincode'].'</C_PIN>
									    <C_STDCODE>'.@$v['contact_telephone_numbers'].'</C_STDCODE>
									    <C_PHONE>'.@$v['resph'].'</C_PHONE>
									    <C_LMARK>'.@$v['resi_land_mark'].'</C_LMARK>
									    <C_ACCESS>'.array_search(@$v['ease_of_locating_office'],lang('approachable')).'</C_ACCESS>
									    <C_ADDR_VERIF>'.array_search(@$v['address_confirmed'],lang('address_verified')).'</C_ADDR_VERIF>
									    <C_NEIG_REF>'.array_search(@$v['neighbours_reference'],lang('neighbour_reference')).'</C_NEIG_REF>
									    <C_NEIG_REF_YES>'.@$v['field12'].'</C_NEIG_REF_YES>
									    <C_OTH_PROP>'.array_search(@$v['field13'],lang('have_another_property')).'</C_OTH_PROP>
									    <C_OTH_PROP_DET>'.@$v['field14'].'</C_OTH_PROP_DET>
									</CUR_ADDR>
									<PER_ADDR>
									    <P_ADDR1>'.@$v['pmt_add1'].'</P_ADDR1>
									    <P_ADDR2></P_ADDR2>
									    <P_STATE>'.@$v['field5'].'</P_STATE>
										<P_SUBURBAN>'.@$pmt_locality.'</P_SUBURBAN>
									    <P_CITY>'.@$pmt_city.'</P_CITY>										
									    <P_PIN>'.@$v['pmt_pincode'].'</P_PIN>
									    <P_STDCODE>'.@$v['field8'].'</P_STDCODE>
									    <P_PHONE>'.@$v['telephone'].'</P_PHONE>
									    <P_LMARK>'.@$v['pmt_land_mark'].'</P_LMARK>
									</PER_ADDR>
									<APPL_DET>
									    <GEN>'.array_search(@$v['field15'],lang('sex')).'</GEN>
									    <DOB>'.(($v['date_of_birth_age_of_applicant']!="")?@$v['date_of_birth_age_of_applicant']:'').'</DOB>
									    <EDU_LVL>'.array_search(@$v['educational_qualification'],lang('education_level')).'</EDU_LVL>
									    <EDU_PROF_DET>'.@$v['field16'].'</EDU_PROF_DET>
									    <MARITAL>'.array_search(@$v['marital_status'],lang('marital_status')).'</MARITAL>
									    <NOD>'.@$v['dependent_adults'].'</NOD>
									    <PASS_NO>'.@$v['passport'].'</PASS_NO>
									    <PAN>'.@$v['pan_card_no'].'</PAN>
									    <AADHAR_NO>'.@$v['aadhar_card'].'</AADHAR_NO>
									    <VOTER_ID>'.@$v['voters_id'].'</VOTER_ID>
									    <APP_RELATION>'.@$app_relation.'</APP_RELATION>
									    <APP_RELATION_OTH>'.@$v['field21'].'</APP_RELATION_OTH>
									    <NAME_VER_FROM>'.array_search(@$v['name_verified_from'],lang('name_verified_from')).'</NAME_VER_FROM>
									    <FMLY_TYPE>'.array_search(@$v['field22'],lang('family_type')).'</FMLY_TYPE>
									    <EXSIST_CUST>'.array_search(@$v['field23'],lang('existing_customer')).'</EXSIST_CUST>
									    <EXSIST_CUST_DET>'.@$v['field24'].'</EXSIST_CUST_DET>
									    <LOAN_PUR>'.array_search(@$v['field25'],lang('loan_purpose')).'</LOAN_PUR>
									</APPL_DET>
									<RES_DET>
									    <LOCA_TYPE>'.array_search(@$v['locality_of_residence'],lang('locality')).'</LOCA_TYPE>
									    <LOCA_STAT>'.array_search(@$v['field27'],lang('locality_status')).'</LOCA_STAT>
									    <LOCA_STAT_OTH>'.@$v['field28'].'</LOCA_STAT_OTH>
									    <CONST_TYP>'.array_search(@$v['field29'],lang('type_of_construction')).'</CONST_TYP>
									    <OWN>'.array_search(@$v['office_ownership'],lang('ownership')).'</OWN>
									    <OWN_OTH>'.@$v['field31'].'</OWN_OTH>
									    <RENT>'.@$v['field32'].'</RENT>
									    <RESI_SIZE>'.array_search(@$v['carpet_area_in_sq_ft_approx'],lang('residence_size')).'</RESI_SIZE>
									    <RESI_YEAR>'.@$v['years_lived_at_current_address'].'</RESI_YEAR>
									    <RESI_TYPE>'.array_search(@$v['type_of_residence'],lang('residence_type')).'</RESI_TYPE>
									    <RESI_TYPE_OTH>'.@$v['field35'].'</RESI_TYPE_OTH>
									    <ROOF_TYPE>'.array_search(@$v['any_other_reason'],lang('roofing_type')).'</ROOF_TYPE>
									    <SOL>'.array_search(@$v['field37'],lang('living_standard')).'</SOL>
									    <ENT_PERMIT>'.array_search(@$v['field38'],lang('entry_permitted')).'</ENT_PERMIT>
									    <POL_SIGN>'.array_search(@$v['whether_any_display_of_affiliation_political_party_seen'],lang('political_photo_seen')).'</POL_SIGN>
									    <CAR_PARK>'.array_search(@$v['field39'],lang('carpark_available')).'</CAR_PARK>
									    <FURN_DET>'.@$v['field40'].'</FURN_DET>
									</RES_DET>
									<DURABLE_VISIBLE>
									    <TV>'.((strpos($v['field41'],'TV') !== false)?'Y':'N').'</TV>
									    <FRIDGE>'.((strpos($v['field41'],'Fridge') !== false)?'Y':'N').'</FRIDGE>
									    <WM>'.((strpos($v['field41'],'Washing Machine') !== false)?'Y':'N').'</WM>
									    <AC>'.((strpos($v['field41'],'AC') !== false)?'Y':'N').'</AC>
									    <SYS>'.((strpos($v['field41'],'Computer') !== false)?'Y':'N').'</SYS>
									    <MIXIE>'.((strpos($v['field41'],'Mixie') !== false)?'Y':'N').'</MIXIE>
									</DURABLE_VISIBLE>
									<VEHICLES>
									    <TW>'.((strpos($v['field41'],'Two Wheeler') !== false)?'Y':'N').'</TW>
									    <FW>'.((strpos($v['field41'],'Four Wheeler') !== false)?'Y':'N').'</FW>
									    <COMVEH>'.((strpos($v['field41'],'Commercial Vehicle') !== false)?'Y':'N').'</COMVEH>
									    <OTH>'.((strpos($v['field41'],'Others') !== false)?'Y':'N').'</OTH>
									</VEHICLES>
									<REL_CONT_PERSON>
									    <FNAME>'.@$v['person_contacted'].'</FNAME>
									    <LNAME>'.@$v['person_met'].'</LNAME>
									    <PC_RELATION>'.array_search(@$v['field43'],lang('relation_with_applicant')).'</PC_RELATION>
									    <PC_RELATION_OTH>'.@$v['field44'].'</PC_RELATION_OTH>
									    <NO_FAM_MEM>'.@$v['no_of_family_members'].'</NO_FAM_MEM>
									</REL_CONT_PERSON>
									<OTH_EARN_MEM>
									    <EMNAME>'.@$v['field45'].'</EMNAME>
									    <INCOME_SRC>'.array_search(@$v['field46'],lang('income_source')).'</INCOME_SRC>
									    <EM_RELATION>'.array_search(@$v['field47'],lang('relation_with_applicant')).'</EM_RELATION>
									    <EM_RELATION_OTH>'.@$v['field48'].'</EM_RELATION_OTH>
									    <MONTHLY_EAR>'.@$v['salary_drawn'].'</MONTHLY_EAR>
									</OTH_EARN_MEM>
									<VER_STATUS>
									    <INV_COMMTS>'.@$v['supervisor_remarks'].'</INV_COMMTS>
									    <CONT_STAT>'.array_search(@$v['field58'],lang('contact_status')).'</CONT_STAT>
									    <RESULT_STAT>'.((@$v['case_result']=='Positive')?'1':'2').'</RESULT_STAT>
									    <NEG_REA>'.array_search(@$v['reason_for_not_recommended'],lang('negative_reason')).'</NEG_REA>
									    <NEG_REA_OTH>'.@$v['any_other_reason'].'</NEG_REA_OTH>
									</VER_STATUS>';
							if(@$v['image1']!="")
							{
							  $str1 = $str1.'<IMAGE_DET>
										<IMG_NAME>'.@$v['image1'].'</IMG_NAME>
										<IMAGE>'.$img1.'</IMAGE>
									</IMAGE_DET>';
							}
							
							if(@$v['image_2']!="")
							{
							$str1 = $str1.'<IMAGE_DET>
										<IMG_NAME>'.@$v['image_2'].'</IMG_NAME>
										<IMAGE>'.$img2.'</IMAGE>
									</IMAGE_DET>';
							}
							if(@$v['applicant_signature']!="")
							{
							$str1 = $str1.'<IMAGE_DET>
										   <IMG_NAME>'.@$v['applicant_signature'].'</IMG_NAME>
										   <IMAGE>'.$img3.'</IMAGE>
									</IMAGE_DET>';
							}
							if(@$googlemap!="")
							{
							$str1 = $str1.'<IMAGE_DET>
										   <IMG_NAME>googlemap.jpg</IMG_NAME>
										   <IMAGE>'.@$googlemap.'</IMAGE>
									</IMAGE_DET>';
							}
						$str1 = $str1.'</FI_RESULT>]]>
								</arg0>
						      </web:input>
						    </soapenv:Body>
						</soapenv:Envelope>';/**/
						$arr[@$v['case_id']] = $this->callCurl($str1);
						//echo $str1;die();
			}
			if($v['veriftype_id'] == 12)
			{
				$img1 = '';
				$img2 = '';
				$googlemap = '';
				
				if (@$v['image1']!="" && file_exists($filepath.@$v['image1']))
				 $img1 = base64_encode(file_get_contents($filepath.@$v['image1']));
				//echo base64_decode($img1);echo '<br/>';echo '<br/>';echo '<br/>';echo '<br/>';
				
				if (@$v['image_2']!="" && file_exists($filepath.@$v['image_2']))
				 $img2 = base64_encode(file_get_contents($filepath.@$v['image_2']));
				
				if(@$v['fe_submit_latitude']!="" && @$v['fe_submit_longitude']!="")
				{
					$tmp = $this->Viewgooglemap(@$v['fe_submit_latitude'],@$v['fe_submit_longitude']);
					if($tmp!="")
					$googlemap = base64_encode($tmp);
				}
				/*$city = $this->db->select('Citycode')->get_where('tbl_mst_city',array('CityDesc'=>trim($v['off_city'])))->row_array();				
				$locality = $this->db->select('SUBURBAN_CODE')->get_where('tbl_mst_suburban',array('SUBURBAN_DESC'=>trim($v['field42'])))->row_array();*/
				$city = trim($v['off_city']);				
				$locality = trim($v['field42']);
				

				$str2 = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:web="http://webservice.extagency.indus.com/">
						<soapenv:Header/>
								<soapenv:Body>
								<web:input>										
								<arg0>
								<![CDATA[<FI_RESULT>
										<REQ_HEADER>
											<NID>'.@$v['ref_no'].'</NID>
											<CUST_NAME>'.@$v['applicants_name'].'</CUST_NAME>
											<VER_TYPE>O</VER_TYPE>
										    </REQ_HEADER>
										    <VER_DET>
											<VER_AGNT>'.@$FEDetails['fname'].' '.@$FEDetails['lname'].'</VER_AGNT>
											<DOV>'.((@$v['fe_submit_date']!= "")?(date('Y-m-d',strtotime(@$v['fe_submit_date'])).'T'.date('H:i:s',strtotime(@$v['fe_submit_date']))):NULL).'</DOV>
											<NOV>1</NOV>
											<BRANCH>'.@$v['branch_name'].'</BRANCH>
										    </VER_DET>
										    <EMP_CONT_DET>
											<ADDR1>'.@$v['off_add1'].'</ADDR1>
											<ADDR2>'.@$v['off_add2'].'</ADDR2>
											<STATE>'.@$v['state'].'</STATE>
											<SUBURBAN>'.@$locality.'</SUBURBAN>
											<CITY>'.@$city.'</CITY>											
											<PIN>'.@$v['off_pincode'].'</PIN>
											<STDCODE>'.@$v['contact_telephone_numbers'].'</STDCODE>
											<PHONE>'.@$v['offph'].'</PHONE>
											<E_LMARK>'.@$v['off_land_mark'].'</E_LMARK>
										    </EMP_CONT_DET>
										    <OFF_VER>
											<COMP_NAME>'.@$v['name_of_company_i_business'].'</COMP_NAME>
											<ADDR_VERIF>'.array_search(@$v['address_confirmed'],lang('address_verified')).'</ADDR_VERIF>
											<ORG_TYPE>'.array_search(@$v['type_of_business'],lang('type_of_organization')).'</ORG_TYPE>
											<OFF_OWN>'.array_search(@$v['office_ownership'],lang('office_ownership')).'</OFF_OWN>
											<OFF_OWN_OTH>'.@$v['field5'].'</OFF_OWN_OTH>
											<RENT>'.@$v['field6'].'</RENT>
											<BIZ_ACT>'.array_search(@$v['business_activity_level'],lang('type_of_busi_activity')).'</BIZ_ACT>
											<BIZ_NAT>'.array_search(@$v['nature_of_business'],lang('nature_of_busi_activity')).'</BIZ_NAT>
											<COMP_PHO>'.@$v['telephone'].'</COMP_PHO>
											<OTH_BRANCH>'.@$v['field9'].'</OTH_BRANCH>
											<SERV_YEAR>'.@$v['number_of_years_in_present_employment_i_business'].'</SERV_YEAR>
											<EARL_JOB>'.@$v['previous_employment_details'].'</EARL_JOB>
											<OV_DESG>'.@$v['designation_of_the_applicant'].'</OV_DESG>
											<IND_TYPE>'.@$v['field11'].'</IND_TYPE>
											<ASSET_SEEN>'.@$v['asset_seen'].'</ASSET_SEEN>
											<NOOF_EMP_MENT>'.@$v['no_of_employees_working_in_office_business'].'</NOOF_EMP_MENT>
											<NOOF_EMP_OBS>'.@$v['no_of_employees_sighted_in_premises'].'</NOOF_EMP_OBS>
										    </OFF_VER>
										    <OFF_CONT_PERSON>
											<FNAME>'.@$v['person_contacted'].'</FNAME>
											<LNAME>'.@$v['person_met'].'</LNAME>
											<OCP_DESG>'.@$v['designation_of_the_person_met'].'</OCP_DESG>
										    </OFF_CONT_PERSON>
										    <OBSERVATION>
											<O_LMARK>'.@$v['landmark'].'</O_LMARK>
											<OFF_BUIL_TYPE>'.array_search(@$v['type_of_office'],lang('office_building')).'</OFF_BUIL_TYPE>
											<ACCESS>'.array_search(@$v['ease_of_locating_office'],lang('approachable')).'</ACCESS>
											<LOCA_TYPE>'.array_search(@$v['locality_of_office'],lang('locality')).'</LOCA_TYPE>
											<LOCA_STAT>'.array_search(@$v['field12'],lang('locality_status')).'</LOCA_STAT>
											<VISIB>'.array_search(@$v['field13'],lang('visibility')).'</VISIB>
											<AUTO_LEVEL>'.@$v['field14'].'</AUTO_LEVEL>
											<OFF_FURN>'.array_search(@$v['field15'],lang('office_furnishing')).'</OFF_FURN>
											<STOCK>'.@$v['field16'].'</STOCK>
											<STOCK_APP>'.@$v['field17'].'</STOCK_APP>
											<PEAK_BIZ_HRS>'.@$v['field18'].'</PEAK_BIZ_HRS>
											<MARK_HOIL>'.@$v['field19'].'</MARK_HOIL>
											<BIZ_DEAL>'.@$v['field20'].'</BIZ_DEAL>
											<MARK_REP_DEAL>'.@$v['field21'].'</MARK_REP_DEAL>
											<ACT_LVL>'.@$v['field22'].'</ACT_LVL>
											<ACT_DETLS>'.@$v['field23'].'</ACT_DETLS>
											<CONCERN>'.@$v['field24'].'</CONCERN>
										    </OBSERVATION>
										    <VER_STATUS>
											<VER_RES>'.array_search(@$v['case_result'],lang('verification_result')).'</VER_RES>
											<INV_COMMTS>'.@$v['remarks'].'</INV_COMMTS>
											<CONT_STAT>'.array_search(@$v['field28'],lang('contact_status')).'</CONT_STAT>
											<NEG_REA>'.array_search(@$v['reason_for_not_recommended'],lang('negative_reason')).'</NEG_REA>
										    </VER_STATUS>';
									if(@$v['image1']!="")
									{	    
									$str2 = $str2.'<IMAGE_DET>
											<IMG_NAME>'.@$v['image1'].'</IMG_NAME>
											<IMAGE>'.$img1.'</IMAGE>
											</IMAGE_DET>';
									}
									if(@$v['image_2']!="")
									{
									$str2 = $str2.'<IMAGE_DET>
											<IMG_NAME>'.@$v['image_2'].'</IMG_NAME>
											<IMAGE>'.$img2.'</IMAGE>
										</IMAGE_DET>';
									}
									if(@$googlemap!="")
									{
									$str2 = $str2.'<IMAGE_DET>
										   <IMG_NAME>googlemap.jpg</IMG_NAME>
										   <IMAGE>'.@$googlemap.'</IMAGE>
										</IMAGE_DET>';
									}	
								 $str2 = $str2.'</FI_RESULT>]]>
									    </arg0>
								  </web:input>
								</soapenv:Body>
						</soapenv:Envelope>';
						//echo $str2;die();
						$arr[@$v['case_id']] = $this->callCurl($str2);
			}
		}
		return @$arr;
	}
	function callCurl($postdata=NULL)
	{
			//$url = site_url()."webservice/rsil/receiveRequest";
			//$url = "http://54.169.99.151/pdglos/services/extagency";
			
			//UAT Environment.
			//$url = "https://nissanfinanceuat.nrfsi.com/pdglosint/services/extagency";
			
			//Live Environment.
			$url = "https://nissanfinance.nrfsi.com/pdglos/services/extagency";
			$curl = curl_init($url); 
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			
			curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata); 
			$response = curl_exec( $curl );
			if (empty($response)) {
			    // some kind of an error happened
			    die(curl_error($curl));
			    curl_close($curl); // close cURL handler
			} else {
			    $info = curl_getinfo($curl);
			    //    echo "Time took: " . $info['total_time']*1000 . "ms\n";
			    curl_close($curl); // close cURL handler
				
			}
		       return $response;//die();
	}
	
	function Viewgooglemap($lat=NULL,$long=NULL)
	{
		$url = 'https://maps.googleapis.com/maps/api/staticmap?zoom=14&size=800x300&maptype=roadmap&markers=color:red%7Clabel:%7C'.@$lat.','.@$long;
		$timeout = 60;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
	
	function accepted_cases()
	{
		$module="cases";
		$this->Frontmodel->set_table($module);
		$table_meta=$this->Frontmodel->table_meta;
		$table=$this->Frontmodel->tablenm;
		
		$join_arr=array();
		$select_arr=array();
		$this->db->where('field_foreign_key != ""',NULL,NULL);
		$query = $this->db->get(@$table_meta);
	        
		if($_POST)
		{
			if($_POST['flag'] == 'save')
			{
				//echo '<pre>';print_r($_POST);die();
				if(!empty($_POST['movecase']))
				{
					foreach($_POST['movecase'] as $k=>$v)
					{
						$values = array('zone_id'=>(@$_POST['zone_id']!="")?@$_POST['zone_id']:NULL,
								'center_id'=>(@$_POST['center_id']!="")?@$_POST['center_id']:NULL,
								'subcenter_id'=>(@$_POST['subcenter_id'])?@$_POST['subcenter_id']:NULL,);
						
						$this->db->where('case_id',$v);
						if($_SESSION['sess_user_emp_id']!='7')
						{
							$this->db->where('delete_flag IS NULL',NULL,FALSE);
						}
						$this->db->update(TABLE_CASES,$values);
						$data['message'] = "Saved Sucessfully.";
					}
				}
			}
		}		
		
		$records=$query->result_array();
		if($records)
		foreach($records as $k=>$recs)
		{
			$tno='t'.$k;
			$pk=$this->Mastermodel->getCustomeFieldValue("tbl_mst_".@$recs['field_foreign_key']."_meta","field_type",12,"field_name");
			$s_value=$this->Mastermodel->getCustomeFieldValue("tbl_mst_".@$recs['field_foreign_key']."_meta","list_order",1,"field_name");
			$join_arr[]=array("table"=>"tbl_mst_".$recs['field_foreign_key'].' as '.$tno,'condition'=>@$table.'.'.$recs["field_name"].'='.$tno.'.'.$pk,'type'=>'left');
			$select_arr[]=$tno.'.'.$s_value.' as '.$recs["field_name"];
		}
	        $select=NULL;
		if($select_arr)
		{
			$select = "tbl_cases.case_id,tbl_cases.case_no,tbl_cases.assign_status,tbl_cases.case_status,tbl_cases.ref_no,tbl_cases.applicants_name,tbl_cases.created_date,tbl_cases.veriftype_id,tbl_cases.country_id,tbl_cases.client_id,tbl_cases.activity_id,tbl_cases.product_id,".implode(",",$select_arr);
			//$select=$table.".*,".implode(",",$select_arr);
		}
		
		$where_arr=array();
		//condition to check whether all cases should be closed(done send to client) which having same parent_case_id excepting hold cases.			
		//$where_arr[] = "(parent_case_id in(SELECT ct6.parent_case_id FROM tbl_cases ct6 WHERE ct6.center_id = tbl_cases.center_id AND date(ct6.created_date) = date(tbl_cases.created_date) GROUP BY  ct6.parent_case_id HAVING MIN( IF( (ct6.send_to_client_time IS NULL && case_hold_time IS NULL) , 0, 1 ) ) =1))";
		
		$where_arr[] =  'send_to_client_time IS NULL';
		$where_arr[] =  'tbl_cases.client_id = 65';
		$where = implode(" AND ",$where_arr);		
		
		$records=$this->Frontmodel->getRecordList($table,$where,$select,@$order,NULL,@$page_no,$join_arr);
		//echo $this->db->last_query();die();
		$data['records'] = $records;
		
	        $data['vfile'] = 'rsil/pending_cases';
                $data['module_title'] = 'RSIL Pending cases';
		$this->load->view('user/home',$data);
		
	}
	//set tat
	function settat($caseid=NULL)
	{
//error_reporting(E_ALL);
		$d = $caseid;
		$a = array('send_to_client_time'=>date('Y-m-d H:i:s'),'modified_id'=>@$_SESSION['sess_user_emp_id']);					
		$this->db->where('case_id',$d);
		if($_SESSION['sess_user_emp_id']!='7')
		{
		    $this->db->where('delete_flag IS NULL',NULL,FALSE);
		}
		$this->db->update(TABLE_CASES,$a);
		//Add entry in history log table.
		$this->Casesmodel->set_history($d,'send to client',$a);
		
		//Also close the parent case which has been put on hold and reassigned as new case.
		$holdcaseValue = $this->Mastermodel->getCustomeFieldValue(TABLE_CASES,"case_id",@$d,"hold_parent_case_id");
		if($holdcaseValue != NULL || $holdcaseValue != "")
		{
			$this->db->where('case_id',$holdcaseValue);
			if($_SESSION['sess_user_emp_id']!='7')
			{
			    $this->db->where('delete_flag IS NULL',NULL,FALSE);
			}
			$this->db->update(TABLE_CASES,$a);
		}
		$parentcaseIDValue = $this->Mastermodel->getCustomeFieldValue(TABLE_CASES,'case_id',@$d,'parent_case_id');
							
		$this->db->select('case_id,parent_case_id,send_to_client_time');
		$this->db->where('parent_case_id',$parentcaseIDValue);
		$this->db->where('send_to_client_time IS NULL',NULL,FALSE);
		if($_SESSION['sess_user_emp_id']!='7')
		{
		    $this->db->where('delete_flag IS NULL',NULL,FALSE);
		}
		$qry = $this->db->get(TABLE_CASES);
		$det = $qry->result_array();
		if(empty($det))
		{
			$closeArr = array('all_close'=>1);
			$this->db->where('parent_case_id',$parentcaseIDValue);
			if($_SESSION['sess_user_emp_id']!='7')
			{
			    $this->db->where('delete_flag IS NULL',NULL,FALSE);
			}
			$this->db->update(TABLE_CASES,$closeArr);
		}
		//Calculate TAT(Turn Around Time)
		$select = 'template_id,case_id,hold_parent_case_id,created_date,send_to_client_time,country_id,client_id,activity_id,product_id,case_hold_time,hold_parent_case_id';
		$CaseData = $this->Frontmodel->getRecordList(TABLE_CASES,'case_id = '.@$d,$select,NULL,NULL,NULL,NULL);		
		$temp = $CaseData[0];
		$TAT = 0;
		// Fetch Pincode field in Global Template for a template id
		$pincode_field=$this->db->select('field_name')
				->where('global_template_id',$temp['template_id'])
				->where('assignment_pincode IS NOT NULL',NULL,FALSE)
				->get(TABLE_TEMPLATE_GLOBAL_META)
				->row_array();
		if(!empty($pincode_field))
		{
			// Fetch Pincode according to case id and Pincode field name
			$this->db->select($pincode_field['field_name']);
			$this->db->where('case_id',@$d);
					
			if($_SESSION['sess_user_emp_id']!='7')
			{						
				$this->db->where('(delete_flag IS NULL)',NULL,FALSE);
			}	
			$select_pincode_val=$this->db->get(TABLE_CASES)->row_array();
			
			if($select_pincode_val[$pincode_field['field_name']]=="")
				$tatchk = 1;
			else
			{						
					  $this->db->where('pincode_name',$select_pincode_val[$pincode_field['field_name']]);
					//$this->db->where('pincode_country_name',$select_pincode_val['']);
					//$pincode_master = $this->db->count_all_results(TABLE_MST_PINCODE);				
					$qry = $this->db->get(TABLE_MST_PINCODE);
					$pincode_master = $qry->row_array();
											
					// If Pincode field is blank and given pincode doesn't exist in pincode master
					if($qry->num_rows() > 0)
					{							
						$tatchk = 2;
					}else
						$tatchk = 1;
			}
		}else
			$tatchk = 1;
		/*
		if "Case Cutoff Time" is greater then "Client Start Time" i.e Time Limit,then
		  case created date is considered as "Case Cutoff Time"
		otherwise
		  it is considered as case created date(default).
	*/
		$r = $this->Casesmodel->getTAT($temp['country_id'],$temp['client_id'],$temp['activity_id'],$temp['product_id']);
		//echo '<pre>';print_r($r);die();
		if($tatchk==2)
		{
			if($pincode_master['city_limit']==0)
				$considerTAT = $r['TAT3'];
			elseif($pincode_master['city_limit']==1)
				$considerTAT = $r['TAT1'];
			elseif($pincode_master['city_limit']==2)
				$considerTAT = $r['TAT2'];
			elseif($pincode_master['city_limit']==3)
				$considerTAT = $r['TAT4'];
			else
				$considerTAT = $r['TAT3'];
			
		}else
				$considerTAT = $r['TAT3'];
		
		if($r['client_start_time']!="")
		{
			$client_start_time  = strtotime($r['client_start_time']);
			$client_cutoff_time = strtotime($r['tat_start_time']);
			if(strtotime(date('H:i',strtotime($temp['created_date']))) < $client_start_time)
			{
				$created_date = date('Y-m-d', strtotime($temp['created_date'])).' '.$r['client_start_time'].":00";
			}else
			{
				if($r['tat_start_time']!="")
				{
					if(strtotime(date('H:i',strtotime($temp['created_date']))) < $client_cutoff_time)
						$created_date = $temp['created_date'];
					else
					{
						$nextworkingday = $this->findnextworkingday($temp['created_date'],$temp);
						$created_date = $nextworkingday.' '.$r['client_start_time'];
						
					}
				}
			}
		}else
			$created_date = $temp['created_date'];
		
		
		if($temp['hold_parent_case_id']!="")
		{
			$parentcasedetails = $this->Mastermodel->getDetail(TABLE_CASES,'case_id',$temp['hold_parent_case_id']);
			
			if($r['client_start_time']!="")
			{
				$client_start_time  = strtotime($r['client_start_time']);
				$client_cutoff_time = strtotime($r['tat_start_time']);
				if(strtotime(date('H:i',strtotime($parentcasedetails['created_date']))) < $client_start_time)
				{
					$parentcreated_date = date('Y-m-d', strtotime($parentcasedetails['created_date'])).' '.$r['client_start_time'].":00";
				}else
				{
					if($r['tat_start_time']!="")
					{
						if(strtotime(date('H:i',strtotime($parentcasedetails['created_date']))) < $client_cutoff_time)
							$parentcreated_date = $parentcasedetails['created_date'];
						else
						{
							$parentnextworkingday = $this->findnextworkingday($parentcasedetails['created_date'],$temp);
							$parentcreated_date = $parentnextworkingday.' '.$r['client_start_time'];
							
						}
					}
				}
			}else
				$parentcreated_date = $parentcasedetails['created_date'];
				
			$tm1 = $this->Casesmodel->getTATtime($parentcreated_date,$parentcasedetails['case_hold_time']);
			//$tm2 = $this->getTATtime($temp['created_date'],$_SESSION['TAT_current_date']);
			$tm2 = $this->Casesmodel->getTATtime($created_date,$temp['send_to_client_time']);
			
			$TAT = ($tm1+$tm2);
		}else{
			
			//$TAT = $this->getTATtime($temp['created_date'],$_SESSION['TAT_current_date']);
			$TAT = $this->Casesmodel->getTATtime($created_date,$temp['send_to_client_time']);
		}
		$tat_time = date('Y-m-d H:i:s');
		$r = $this->Casesmodel->getTAT($temp['country_id'],$temp['client_id'],$temp['activity_id'],$temp['product_id']);
		
		if($considerTAT!="" && $considerTAT>0)
		{
			
			//$real_TAT = ($r['TAT3']*60);
			$real_TAT = ($considerTAT*60); 
			if($real_TAT>=$TAT)
			{
				@$values = array('case_tat_status'=>'within');
			}
			else
			{
				@$values = array('case_tat_status'=>'outside');
			}
			@$values['case_tat'] = $TAT;
			@$values['tat_calculate_time'] = @$tat_time;
			$this->db->where('case_id',$temp['case_id']);
			 if($_SESSION['sess_user_emp_id']!='7')
			 {
			$this->db->where('(delete_flag IS NULL)',NULL,FALSE);
			 }
			$this->db->update('tbl_cases',@$values);
		}else
		{
			$values['case_tat_status'] = 'no tat';
			@$values['case_tat'] = NULL;
			@$values['tat_calculate_time'] = @$tat_time;
			$this->db->where('case_id',$temp['case_id']);
			if($_SESSION['sess_user_emp_id']!='7')
			{
			   $this->db->where('(delete_flag IS NULL)',NULL,FALSE);
			}
			$this->db->update('tbl_cases',@$values);
		}
			
		//$parentcaseIDValue = @$temp['parent_case_id'];
		$this->db->select('case_id,parent_case_id,send_to_client_time,case_tat_status');
		$this->db->where('parent_case_id',$parentcaseIDValue);
		$this->db->where('case_tat_status IS NULL',NULL,FALSE);
		if($_SESSION['sess_user_emp_id']!='7')
		{
			$this->db->where('(delete_flag IS NULL)',NULL,FALSE);
		}
		$qry1 = $this->db->get(TABLE_CASES);//die($parentcaseIDValue); echo $det1;
		$det1 = $qry1->num_rows();
		if($det1 == 0)
		{
				$this->db->select('case_id,parent_case_id,send_to_client_time,case_tat_status');
				$this->db->where('parent_case_id',$parentcaseIDValue);
				$this->db->where('case_tat_status LIKE "%no tat%"',NULL,FALSE);
				if($_SESSION['sess_user_emp_id']!='7')
				{
					$this->db->where('(delete_flag IS NULL)',NULL,FALSE);
				} 
				$qry2 = $this->db->get(TABLE_CASES);
				if($qry2->num_rows > 0)
				{
						$closeArr = array('all_case_tat_status'=>3);
						$this->db->where('case_id',$parentcaseIDValue);
						if($_SESSION['sess_user_emp_id']!='7')
						{
							$this->db->where('(delete_flag IS NULL)',NULL,FALSE);
						} 
						$this->db->update(TABLE_CASES,$closeArr);
				}else
				{						   
						$this->db->select('case_id,parent_case_id,send_to_client_time,case_tat_status');
						$this->db->where('parent_case_id',$parentcaseIDValue);
						$this->db->where('case_tat_status LIKE "%outside%"',NULL,FALSE);
						if($_SESSION['sess_user_emp_id']!='7')
						{
							$this->db->where('(delete_flag IS NULL)',NULL,FALSE);
						} 
						$qry3 = $this->db->get(TABLE_CASES);
						if($qry3->num_rows > 0)
						{
								$closeArr = array('all_case_tat_status'=>2);
								$this->db->where('case_id',$parentcaseIDValue);
								if($_SESSION['sess_user_emp_id']!='7')
								{
									$this->db->where('(delete_flag IS NULL)',NULL,FALSE);
								} 
								$this->db->update(TABLE_CASES,$closeArr);
						}
						else
						{
								$closeArr = array('all_case_tat_status'=>1);
								$this->db->where('case_id',$parentcaseIDValue);
								if($_SESSION['sess_user_emp_id']!='7')
								{
									$this->db->where('(delete_flag IS NULL)',NULL,FALSE);
								} 
								$this->db->update(TABLE_CASES,$closeArr);
						}
				}
				
		}
	}
	//Find the next working of given date. Function used in TAT calculation.
	function findnextworkingday($created_date,$casedata=NULL)
	{
		$res=$this->Casesmodel->get_holiday(@$created_date,@$casedata['country_id'],@$casedata['zone_id'],@$casedata['center_id']);
		$holiday_arr = array();
		if(!empty($res))
		{
			foreach($res as $k=>$v)
			$holiday_arr[] = $v['holiday_date'];
		}
		$holiday_arr = array_unique($holiday_arr);
		
		$weekOFarr = $this->Commonfunmodel->weeklyoff(@$casedata['country_id'],@$casedata['zone_id'],@$casedata['center_id'],@$casedata['subcenter_id']);
		$day_str = array();
		if(!empty($weekOFarr))
		{
			$week_days = lang('week_days_arr');
			foreach($weekOFarr as $k=>$v)
			{
				$s = array_search($v,$week_days);
				$day_str[] = $s;
			}
		}
		
		if(array_search(date("N",strtotime($nextday)),$day_str)!==false || array_search(date("Y-m-d",strtotime($nextday)),$holiday_arr)!==false)
		{
			$this->findnextworkingday($nextday,$casedata);
		}else{
			
			//echo $nextday;die();
			return $nextday;
		}
		//echo '<pre>';print_r($holiday_arr);print_r($day_str);die();
	}
}

?>