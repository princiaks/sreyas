<?php 
class Admin_model extends CI_Model{	

	public function __construct(){ 
		$this->load->database();
	}
	public function insert_user($data=array())
	{
		$result= $this->db->insert('tbl_user_details',$data);
		return $this->db->insert_id();
	}

	public function get_single_item_withid($columnlist,$id,$table,$where="")
	{
		   $this->db->select($columnlist)->from($table)->where('id',$id)->where('status !=','Deleted');
		   if($where)
		   $this->db->where($where);
		   return $result=$this->db->get()->row(); 
	}

	public function get_lists($table,$columns,$limit="",$orderby="")
	{
		if($limit !="")
		{
			$limit='limit '.$limit;
		}
		if($orderby=="")
		{
			$orderby=' order by created_on desc';
		}
	  
		$query   = $this->db->query("SELECT $columns from $table where status != 'Deleted' $orderby $limit");
		$results = $query->result();
		return $results;
	}

////////////////////////////////////

	
}