<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Reports extends CI_Controller
{
        public function __construct()
		{
			if (session_id() == "") session_start();
			parent::__construct();
			$this->load->model('Reportsmodel','',TRUE);
			$this->load->model('Masterjson', '', TRUE);
			$this->load->model('Mastermodel','',TRUE);
			$this->load->model('Frontmodel','',TRUE);
			$this->load->model('Emailmodel','',TRUE);
			$this->load->model('Commonfunmodel','',TRUE);
			$this->form_validation->set_error_delimiters('<p class="error_text">','</p>');
			
			$this->Commonfunmodel->checkAdminLogin();
			$this->Commonfunmodel->setTimeZone();
				//error_reporting(E_ALL);
		    // Check https and redirect from http to https
		    $this->Commonfunmodel->check_ssl();
		}
	
	//function for view report result at user side.
        function viewreport($id,$page_no=NULL,$export=NULL)
		{
				//ini_set('max_execution_time', 1800); //1800 seconds = 30 minutes
				//ini_set('memory_limit','500M'); // 500 MB
				$post=array();
				if($id)
				{
						if($this->uri->segment(5) == 'nosearch')
						{			
							$_SESSION['sess_report_search'] = "";
							$_SESSION['sess_report_search_meta'] = "";
							
							$_SESSION["sess_do_order_field"] = "";
							$_SESSION["sess_do_order_type"] = "";	
						}
						$_SESSION["sess_user_paging"] = PER_PAGE_RECORDS_FRONT;//6;
						$this->db->where('report_id',$id);
						$query = $this->db->get('tbl_reports');
						
						$res = $query->row_array();
						$post_pre = $res;
						$post = $res; 
						
						$this->db->where('report_id',$id);
						$this->db->order_by('order','ASC');
						$query1 = $this->db->get('tbl_report_search');
						$search_fields = $query1->result_array();
						   
						$searchArr = array();
						$rangeArr = array();
						
						$crange = 0;
						foreach($search_fields as $k=>$v)
						{
							/**/if($v['field_name'] == 'tbl_cases.country_id' && @$_SESSION['sess_user_country_id']!=''){}
						   elseif($v['field_name'] == 'tbl_cases.zone_id' && @$_SESSION['sess_user_zone_id']!=''){}
						   elseif($v['field_name'] == 'tbl_cases.center_id' && @$_SESSION['sess_user_center_id']!=''){}
						   elseif($v['field_name'] == 'tbl_cases.subcenter_id' && @$_SESSION['sess_user_subcenter_id']!=''){}
						   elseif($v['field_name'] == 'tbl_cases.client_id' && @$_SESSION['sess_user_client_id']!=''){}
						   elseif($v['field_name'] == 'tbl_cases.activity_id' && @$_SESSION['sess_user_activity_id']!=''){}
						   elseif($v['field_name'] == 'tbl_cases.product_id' && @$_SESSION['sess_user_product_id']!=''){}
						   elseif($v['field_name'] == 'tbl_cases.veriftype_id' && @$_SESSION['sess_user_verifytype_id']!=''){}
						   else
						   {			   
							$searchArr[$crange]=$v['field_name'];
							if($v['range']==1)
							{
								$rangeArr[$crange]=$v['range'];
								
							}$crange++;
						   }//
						}
			$post['search'] = $searchArr;
			$post['range'] = $rangeArr;
			//echo '<pre>';print_r($search_fields);print_r($rangeArr);die();
			
			if($this->input->post('flag')=="search")
			{
			   @$_SESSION['report_preview'] = @$_POST;
			   $post = @$_SESSION['report_preview'];
			}else
			{
			   @$_SESSION['report_preview'] = "";
			}
				
			$search_wh="";
			if($post['sql_query']!="")
			{
				
				if(@$post['flag']=='search')
				{
					
					if($post['search'])
						$search_arr = $post['search'];
					else
						$search_arr = $_SESSION['sess_report_search_meta'];
					foreach($search_arr as $field_key=>$column) 
					{								
						$val=@$post[@$column['field_name']];
						@$post['search'][$field_key]['post_val']=$val;
						if(isset($val))
						{
							if($column['field_type']==2)
							$column['field_type'] = 1;
							
							if($column['field_type']==1 || @$column['field_type']==10 || @$column['field_type']==11)
							{
								if($val!="")
								{
									if(@$post['range'][@$field_key]==1)
									{
										if(@$val[0]!="")
										{
											if(@$column['field_type']==10 || @$column['field_type']==11)
											{
											     $where[] = 'DATE_FORMAT('.$column['search_name'].',"%Y-%m-%d")>="'.date('Y-m-d',strtotime(trim(addslashes(@$val[0])))).'"';
											}else
											{
											     $where[] = $column['search_name'].'>='.@$val[0];
											}
										}
										if(@$val[1]!="")
										{
											if(@$column['field_type']==10 || @$column['field_type']==11)
											{
											      $where[] = 'DATE_FORMAT('.$column['search_name'].',"%Y-%m-%d")<="'.date('Y-m-d',strtotime(trim(addslashes(@$val[1])))).'"';
											}else
											{
											      $where[] = $column['search_name'].'<='.@$val[1];
											}
										}
									}
									else
									{
										if(@$column['field_type']==10 || @$column['field_type']==11)
										{
											//print_r($val);
											if(@$val[2]!="")
											$where[] = 'DATE_FORMAT('.$column['search_name'].',"%Y-%m-%d")="'.date('Y-m-d',strtotime(trim(addslashes(@$val[2])))).'"';
										}else
											$where[] = $column['search_name'].' LIKE "%'.trim(addslashes($val)).'%"';
									}
								}
							}
							if($column['field_type']==3)
							{
								if($val!="")
								{
									if(is_array($val))
									{
										$tmpstrarr = array();
										foreach($val as $sk=>$sv)
										{
											if(!empty($sv))
											$tmpstrarr[] =  $column['search_name'].' ="'.trim(addslashes($sv)).'"';
										}
										if(!empty($tmpstrarr))
										$where[] = '('.implode(' OR ',$tmpstrarr).')';
										
									}else
									$where[] = $column['search_name'].' ="'.trim(addslashes($val)).'"';
								}
							}
							if($column['field_type']==5)
							{
								if($val!="")
								{
									$condition=(@$column['condition']!="")?@$column['condition']:"=";
									$where[] = 'DATE_FORMAT('.$column['search_name'].',"%Y-%m-%d") '.$condition.'"'.trim(addslashes($val)).'"';
								}	
							}
							if($column['field_type']==4)
							{
								if($val!="")
									$where[] = $column['search_name'].' ="'.trim(addslashes($val)).'"';
							}
						}
					}//echo '<pre>';print_r($where);
					$serchflg = 0;
					if(!in_array(@$id,json_decode(REPORT_ID_LIST)) && (@$post['created_date'][0]=='' || @$post['created_date'][1]=='')) // @$id!=43
					{
						$data['srch_msg'] = 'Date range is compulsary';$serchflg = 1;
					}
					$d1 = $this->Commonfunmodel->changeDateFormat1(str_replace('-','/',@$post['created_date'][0]));
					$d2 = $this->Commonfunmodel->changeDateFormat1(str_replace('-','/',@$post['created_date'][1]));
					
					$date1 = date_create($d1);
					$date2 = date_create($d2);
					
					$diff12 = date_diff($date1, $date2);
					$fdidd = $diff12->format('%r%a days');
					
					//echo $this->dateDiff($date1,$date2).'<br/>';
					//echo $d1.'___'.$d2.'___'.$fdidd.'<br/>';
					if(@$fdidd>93)
					{
						$data['srch_msg'] = 'Date range should be maximum of 93 days.';$serchflg = 1;
					}
					
					if($serchflg == 0)
					$_SESSION['sess_report_search'] = $where;
					else
					$_SESSION['sess_report_search'] = '';
				}
			    
				if(!empty($_SESSION['sess_report_search']))
					$search_wh=implode(" AND ", $_SESSION['sess_report_search']);
				else
					$search_wh="";
								    
				/*if($page_no == "" && ($id == 41 || $id == 45))
				{
					if($_SESSION['sess_report_search']!="")
					{
						$this->db->select('case_id');
						
						$this->db->where($search_wh,NULL,FALSE);
						$qr = $this->db->get(TABLE_CASES);
						
						$qresult = $qr->result_array();
						//echo count($qresult);die();
						if(!empty($qresult))
						{
							foreach($qresult as $ckey=>$cid)
							{
								$this->Frontmodel->setClientEmpRates($cid['case_id']);////
								//echo $this->db->last_query().'<br/>';
							}
						}
					}
					//echo '<pre>';print_r($qresult);die();					
				}*/			
				    
				if(!empty($_SESSION['sess_report_search']))
				{
					$where_arr=array();
					if($id == '43')
					{
					    if(@$_SESSION['sess_user_country_id']!="")
						    $where_arr[] = "tbl_fe_payout.country_id =".$_SESSION['sess_user_country_id'];
					    if(@$_SESSION['sess_user_zone_id']!="")
						    $where_arr[] = "tbl_fe_payout.zone_id =".$_SESSION['sess_user_zone_id'];
					    if(@$_SESSION['sess_user_center_id']!="")
						    $where_arr[] = "tbl_fe_payout.centre_id =".$_SESSION['sess_user_center_id'];
					    if(@$_SESSION['sess_user_subcenter_id']!="")
						    $where_arr[] = "tbl_fe_payout.subcentre_id =".$_SESSION['sess_user_subcenter_id'];					
					}
					elseif($id == '67')
 					{
 					    if(@$_SESSION['sess_user_country_id']!="")
								$where_arr[] = "tbl_mst_case.case_country_id =".$_SESSION['sess_user_country_id'];
 					}
					else
					{
					    if(@$_SESSION['sess_user_client_id'])
						    $where_arr[]=TABLE_CASES.'.client_id='.$_SESSION['sess_user_client_id'];
					    if(@$_SESSION['sess_user_country_id']!="")
						    $where_arr[] =TABLE_CASES.".country_id =".$_SESSION['sess_user_country_id'];
					    if(@$_SESSION['sess_user_zone_id']!="")
						    $where_arr[] =TABLE_CASES.".zone_id =".$_SESSION['sess_user_zone_id'];
					    if(@$_SESSION['sess_user_center_id']!="")
						    $where_arr[] =TABLE_CASES.".center_id =".$_SESSION['sess_user_center_id'];
					    if(@$_SESSION['sess_user_subcenter_id']!="")
						    $where_arr[] =TABLE_CASES.".subcenter_id =".$_SESSION['sess_user_subcenter_id'];
					    if(@$_SESSION['sess_user_activity_id']!="")
						    $where_arr[] =TABLE_CASES.".activity_id =".$_SESSION['sess_user_activity_id'];
					    if(@$_SESSION['sess_user_product_id']!="")
						    $where_arr[] =TABLE_CASES.".product_id =".$_SESSION['sess_user_product_id'];
					    if(@$_SESSION['sess_user_verifytype_id']!="")
						    $where_arr[] =TABLE_CASES.".veriftype_id =".$_SESSION['sess_user_verifytype_id'];
						 if(@$_SESSION['sess_top_emp_assign'])
					    $where_arr[]=@$_SESSION["sess_top_emp_assign"];
						
						$where_arr[]='('.TABLE_CASES.'.delete_flag IS NULL)';
					}
					
					$where_session = implode(" AND ",$where_arr);
				       
					if($search_wh=="")
						$search_wh = $where_session;
					else
					{
						if($where_session!="")
						$search_wh.=" AND ".$where_session;	
					}
					
					//Updated by Sandeep For add where condition.(Billing MIS =Count of Visit)
					$where="";
		            $subquery=false;		    
	       				if(preg_match("/where\s/i", $post['sql_query']))
					    {
					      $stringQuery=$post['sql_query'];
					      $startIndex= strpos($stringQuery,"where");
                          $lastIndex=strpos($stringQuery,"#where#");
                          
                          $subStr=substr($stringQuery, $startIndex, $lastIndex - $startIndex);
					      
					      $subquery=false;
					      if(preg_match("/group\s/i", $subStr) ||preg_match("/having\s/i", $subStr)  ){
					          
					          if(strrpos($subStr,"WHERE",6)>0){
					             $subquery=false;
					          }else{
					               $subquery=true;
					          }
					          
					         
					      }else{
					          $subquery=false;
					      }
					      if($subquery==false){
					          
					         $where = ($search_wh=="")?"":" AND ".$search_wh;
					      }else{
					          $where = ($search_wh=="")?"":" where ".$search_wh;
					      }
					    }
					else
					{
					    $where = ($search_wh=="")?"":" where ".$search_wh;
					}
				 
					$sql = str_replace('#where#',$where,$post['sql_query']);
					$sel_coalesce='';
					if(@$post_pre['coalesce']==1)
					{
						list($tb,$field)=explode(".",@$post_pre['coalesce_field']);
						$tmp_arr=array();
						if(array_search(@$field,array_keys(@$post))!==FALSE)
						{
							foreach(@$post['search'] as $psearch)
							{
								if(@$psearch['field_type']==10 || $psearch['field_type']==11)
								{
									if(is_array($psearch['post_val']))
									{
										$start_date=$this->Commonfunmodel->changeDateFormat1($psearch['post_val'][0],"-");
										$end_date=$this->Commonfunmodel->changeDateFormat1($psearch['post_val'][1],"-");
										while(true)
										{
											$tmp_arr[]='coalesce(sum(case when date_format ('.@$post_pre['coalesce_field'].',"%Y-%m-%d") = "'.$start_date.'" then no_of_cheques end), 0) as "'.$this->Commonfunmodel->changeDateFormat2($start_date).'"';
											$start_date=date("Y-m-d",strtotime("+1 day",strtotime($start_date)));
											if($start_date>$end_date)
												break;
										}
										$sel_coalesce=implode(",",$tmp_arr);
									}
								}
							}				
						}
					}
					if($sel_coalesce!="")
					{
					    $sql = str_replace('#select#',$sel_coalesce,$sql);
					}
				    
					$sel_count='';
					$sel_count_arr=array();
					$sel_count_total_arr=array();
					if(@$post_pre['countchk']==1 && !empty($post_pre['countfield']))
					{
					    
						list($cnt,$cntfield)=explode(".",$post_pre['countfield']);
						$fDetails = $this->Mastermodel->getDetail(TABLE_CASES_META,'field_name',$cntfield);
						if(!empty($fDetails['field_foreign_key']))
						{
							$cntArr = $this->Mastermodel->getList('tbl_mst_'.@$fDetails['field_foreign_key']);
							//echo $this->db->last_query();die();
							$fieldlbl=$this->Mastermodel->getCustomeFieldValue('tbl_mst_'.@$fDetails['field_foreign_key'].'_meta',"list_order",1,"field_name");
							
						    //$fieldlbl=$this->Masterjson->getjson('tbl_mst_'.@$fDetails['field_foreign_key'].'_meta',5); // list order
					
							if(!empty($cntArr))
							{
								foreach($cntArr as $t=>$m)
								{
									if(!$sel_count_arr[$m[$cntfield]])
									{
										$sel_count_arr[$m[$cntfield]] = 'coalesce(count(case when '.$post_pre['countfield'].' = "'.$m[$cntfield].'" then 1 end), 0) as "'.$m[$fieldlbl].'"';
										$sel_count_total_arr[$m[$cntfield]] = 'coalesce(count(case when '.$post_pre['countfield'].' = "'.$m[$cntfield].'" then 1 end), 0)';
									}
								}
								$sel_count = implode(',',$sel_count_arr);
								$sel_count_total = implode('+',$sel_count_total_arr).' as Total';
								$sel_count = $sel_count.','.$sel_count_total;
							}
						}
						if($sel_count!="")
						   $sql = str_replace('#selectCount#',$sel_count,$sql);
					}
					$query = $this->db->query($sql);
					//echo $this->db->_error_message();die();
		// to print report query use die here
				  	//echo $this->db->last_query(); die();
					
					if($export!='')
					{
						$this->load->dbutil();
						$query = $this->db->query($sql);
						$delimiter = "\t";
						$newline = "\n";
						//$csv_data=$this->dbutil->csv_from_result($query,$delimiter,$newline);
						
						$exarr = $query->result_array();
						$this->load->helper('to_excel');
						to_excel($exarr, 'report');
						
						//echo '<pre>';print_r($csv_data);die();
						//$this->load->helper('download');
						//force_download('report.xls', $csv_data);
						die;
					}
					//echo $this->db->last_query();die();
					$total_count=$query->num_rows();
				    /****************************Pagination*******************************************/
					if( $this->input->post("flag")=="search" && $total_count==0)
					     $msg_display1=lang("NO_RECORD_FOUND");
					$segments = array('user/reports', 'viewreport/'.$id);
					
					$url_name=site_url($segments);
						     
					$page_no=(int)$this->uri->segment(5);
					$per_page_records=((@$_SESSION["sess_user_paging"]=="All")?$total_count:@$_SESSION["sess_user_paging"]);
					$pagination=$this->Commonfunmodel->pagination($total_count,$page_no,$per_page_records,$url_name);
					/*******************End Pagination**********************************************/
					if( empty($page_no) || ( $page_no<1 ) )
						$nextrecord = 0 ;
					else
						$nextrecord = ($page_no-1) * @$_SESSION["sess_user_paging"] ;
						
					if(@$_SESSION["sess_user_paging"]!="All")
					{
						$limit_start=$nextrecord;
						$limit_end=@$_SESSION["sess_user_paging"];
					}
					if(@$_SESSION["sess_user_paging"]!="All")
					{
					    $sql = $sql.' LIMIT '.@$limit_start.','.@$limit_end;
					}
						
					$query = $this->db->query($sql);
					//echo $this->db->last_query();die();
					$records = $query->result_array();			
				}
				$table="<div style='margin: 0px auto;overflow: auto;max-width: 1004px;max-height: 300px;'><table style='border:1px solid green;border-collapse: collapse; width: 100%;'>"; 
				$rec_no=$nextrecord+1;
				foreach($records as $k=>$v)
				{
					if($k==0)
					{
						$hd = '<tr>';
						if(@$post['sr_no']==1)
							{
							  $hd = $hd.'<th style="border-style: solid;border-width: 1px;padding: 8px; background-color: #dedede; width: 100%;">Sr.No.</th>';
							}
						foreach($v as $n=>$m)
						{
							
							$hd = $hd.'<th style="border-style: solid;border-width: 1px;padding: 8px; background-color: #dedede;">'.@$n.'</th>';
							
						}
						$hd = $hd.'</tr>';
						$table = $table.$hd;
					}
					    
					$hd = '<tr>';
					if(@$post['sr_no']==1)
					{
					  $hd = $hd.'<td style="border-style: solid;border-width: 1px;padding: 8px;">'.@$rec_no.'</td>';
					}
					foreach($v as $n=>$m)
					{
						$hd = $hd.'<td style="border-style: solid;border-width: 1px;padding: 8px;">'.@$m.'</td>';
					}
					$hd = $hd.'</tr>';
					$table = $table.$hd;
					$rec_no++;	
				}
				$table=$table.'</table></div>';
				$layout = @$post['layout'];
			    
				if(@$post['flag']!='search')
				{
					@$tbl_name='';
					$search_meta_arr=array();
					foreach($post['search'] as $n=>$m)
					{
						if($m!="")
						{
							list($tbl_name,$fname)=explode(".",$m);
							$this->Reportsmodel->set_table($tbl_name);
							
							$this->db->where('field_name',$fname);
							$query = $this->db->get($this->Reportsmodel->table_meta);
							$arr = $query->row_array();
							//echo '<pre>';print_r($arr);
							$arr['search_name'] = $m;
							$search_meta_arr[]=$arr;
						}
					}
				}
				if(@$post['flag']=='search')
				  $_SESSION['sess_report_search_meta'] =  @$post['search'];
				else
				{ 
					if(empty($_SESSION['sess_report_search_meta'])&&!empty($search_meta_arr))
					{			
					     $_SESSION['sess_report_search_meta'] =  @$search_meta_arr;
					}				 
				}
			     
			
				$data['page_no'] = @$page_no;
				if(!empty($records))
				$data['pagination'] =@$pagination;
				$data['nextrecord'] = @$nextrecord;
				$data['total_count'] = @$total_count;
				//echo '<pre>';print_r($data['pagination']);die();
				$data['sql_query'] = @$post['sql_query'];
				$data['layout'] = @$layout;
				$data['table'] = @$table;
				$data['sr_no'] = @$post['sr_no'];
				$data['search'] = $_SESSION['sess_report_search_meta'];
				
				$data['range'] = @$post['range'];
				$data['export_url']= site_url().'user/reports/viewreport/'.$id.'/0/export';
				$data['submit_url'] = site_url().'user/reports/viewreport/'.$id;
		       }
			$data['vfile']='admin/reports/preview';
                }//echo '<pre>';print_r($data);die();
			$data['report_id'] = @$id;
		    $this->load->view('user/home',@$data);
	}
	
	//to get list from ajax call.
	function getajaxlist($tmodule,$target,$submenuid=NULL,$flag=NULL)
	{
		if(!empty($_POST['selectedval']))
		$tval = implode(',',$_POST['selectedval']);
		
		if(!empty($_POST['clientval']))
		$clienttval = implode(',',$_POST['clientval']);
		//echo '<pre>';print_r($_POST);
		
		if($tmodule=='clientassign')
		{
			$tval = str_replace(array('{','}','[',']'),array('','','','',''),json_decode($_POST["selctv"]));			
			$tval = explode(',',$tval);//
			
			$finalcondArr = array();
			foreach($tval as $r=>$d)
			{
				$d = explode(':',$d);
				$c = explode('-',$d[1]);
				
				$strcon = array();
				foreach($c as $l=>$m)
				{
					if($m!='')
					$strcon[] = $d[0].' = '.$m;
				}
				if(!empty($strcon))
				$finalcondArr[] = '('.implode(' OR ',$strcon).')';
				
			}
			$finalconstr = implode(' AND ',$finalcondArr);
			//echo '<pre>';print_r($finalconstr);die();
		}
		
		
		if($tval!='')
		{
			if($tmodule=='clientassign')
			{
				$records=$this->Mastermodel->dropdown_client($tmodule,$finalconstr,1);
				//echo $this->db->last_query();die();
			}else
			{
				$whstr = "";
				if($clienttval!="")
				$whstr = $target.' in('.$tval.') AND product_id in(select case_product_id from '.TABLE_MST_CASE.' where case_client_id in ('.$clienttval.'))';
				else
				$whstr = $target.' in('.$tval.')';
				$records=$this->Mastermodel->dropdown_new($tmodule,$whstr,1);
				//echo $this->db->last_query();
			}
		}
		
		unset($records['']);
				
		$t_arr=array();
		foreach($records as $key=>$rec)
		{
		$t_arr[]=$key."|".$rec;
		}
		echo implode("#",$t_arr);		
	}
	//ajax function to get activity list.
	function getactivityList($module,$target_field,$client_id=NULL,$country_id=NULL)
	{
		$tablenm_meta = "tbl_mst_".$module."_meta";
			
		//$value=$this->Mastermodel->getCustomeFieldValue($tablenm_meta,"list_order",1,"field_name");
		//$key=$this->Mastermodel->getCustomeFieldValue($tablenm_meta,"field_type",12,"field_name");
		
		$value=$this->Masterjson->getjson($tablenm_meta,5); // list order
		$key=$this->Masterjson->getjson($tablenm_meta,1); // primary key
		
			
		$this->db->join('tbl_mst_'.$module,TABLE_MST_CASE.'.case_'.$target_field.'=tbl_mst_'.$module.'.'.$target_field,'left');
		if($country_id>0)
		{
			$this->db->where('(case_country_id = '.$country_id.')');
		}
		
		if(!empty($_POST['client_id']))
		{
			$wh = array();
			foreach($_POST['client_id'] as $s=>$t)
			{
				if($t!="")
				$wh[] = "case_client_id = ".$t;
			}
			if(@$_SESSION['sess_admin_type']!=2)
			{
					
				if($module=='cluster' && $_SESSION['sess_top_assigned_cluster']!="")
				{
					$this->db->where(' cluster_id in('.$_SESSION['sess_top_assigned_cluster'].') ',NULL,FALSE);					
				}
				if($module=='centre' && $_SESSION['sess_top_assigned_centers']!="")
				{
					$this->db->where(' centre_id in('.$_SESSION['sess_top_assigned_centers'].') ',NULL,FALSE);
				}
				if($module=='subcentre' && $_SESSION['sess_top_assigned_subcenters']!="")
				{
					$this->db->where(' subcentre_id in('.$_SESSION['sess_top_assigned_subcenters'].') ',NULL,FALSE);
				}
				if($module=='client' && $_SESSION['sess_top_assigned_clients']!="")
				{
					$this->db->where(' client_id in('. $_SESSION['sess_top_assigned_clients'].') ',NULL,FALSE);
				}				
				if($module=='product' && $_SESSION['sess_top_assigned_products']!="")
				{
					$this->db->where(' product_id in('. $_SESSION['sess_top_assigned_products'].') ',NULL,FALSE);
				}
				if($module=='verificationtype' && $_SESSION['sess_top_assigned_verifications']!="")
				{
					$this->db->where(' veriftype_id in('. $_SESSION['sess_top_assigned_verifications'].') ',NULL,FALSE);
				}
				
				if($module=='activity' && $_SESSION['sess_user_emp_id']!='7')
				{
					$this->db->where('activity_status',1,NULL);
				}
			}

			
			if(!empty($wh))
			$this->db->where('('.implode(' OR ',$wh).')',NULL,FALSE);
			
			$this->db->where($target_field.' IS NOT NULL',NULL,FALSE);
			$this->db->order_by($value,'asc');
			$query = $this->db->get(TABLE_MST_CASE);
			
			$records=$query->result_array();
			//echo $this->db->last_query();die();
			if($records)
			foreach($records as $rec)
			{
				if(@$rec[$value]!="")
				{
					$result_arr[$rec[$key]]=$rec[$value]; //.((@$tmodule_arr['Dropdown']['value1']!="")?"(".$rec[$tmodule_arr['Dropdown']['value1']].")":"");
				}
			}
		}
		
		$activity = $result_arr;
		
		$t_arr=array();
		if(!empty($activity))
		{
			foreach($activity as $key=>$rec)
			{
			       $t_arr[]=$key."|".$rec;
			}
		}
		echo implode("#",$t_arr);	
	}
}
?>