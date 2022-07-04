<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AdminController extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
    public function __construct()
    {
        parent::__construct();
        $data = array();

		$this->load->helper('url_helper');
        $this->load->helper('form');
        $this->load->database();
        $this->load->model('admin_model');	 
		$this->load->library('form_validation');
	
     }

     public function index()
     {
	
		$this->load->view('admin/header');
		$this->load->view('admin/index');
		$this->load->view('admin/footer');
	
     }

	 public function user_registration()
	 {
			$result=array();
			$required=array('name','email_id','password');
			foreach($required as $req)
			{
			$this->form_validation->set_rules($req, $req, 'required');
			}
			if ($this->form_validation->run() == FALSE)
			{
				$response=array('success'=>'error','status'=>'error','title'=>'Required!!','msg'=>'Please Fill all the details in the Form','redirect'=>'');
			}
			else
			{
			$data['name']=$this->input->post('name');
			$data['email_id']=$this->input->post('email_id');
			$data['password']=$this->input->post('password');
			$data['status']='pending';
			$result=$this->admin_model->insert_user($data);
			$response=array('success'=>$result,'status'=>'error','title'=>'Failed!!','msg'=>'User Registration Failed','redirect'=>'');
			if($result)
			$response=array('success'=>$result,'status'=>'success','title'=>'Success!!','msg'=>'Registration Successfull','redirect'=>'verify-email');
			echo json_encode($response);
			}
	 }

	 public function verify_email_view()
	 {
		$this->load->view('admin/header');
		$this->load->view('admin/verify-email');
		$this->load->view('admin/footer'); 
	 }

	 public function send_success_mail($data)
	{
		$this->load->library('email');
		$config=array(
			'mailtype' => 'html',
			'charset'  => 'utf-8',
			'priority' => '1'
		);

		$this->email->initialize($config);
		$this->email->from('princiaks@gmail.com', 'Email Verification Mail');
		$this->email->to($data['email_id']);
		$this->email->subject('Order Success Mail');
		$emaildescription=$this->load->view('email/order_confirm_mail',$data,TRUE);
		$this->email->message($emaildescription);
		$result=$this->email->send();   
		$this->email->from('neworder@pomoservices.com', 'New Order Received Mail');
		$this->email->to('info@pomoservices.com');
		$this->email->subject('Order Success Mail');
		$emaildescription=$this->load->view('email/order_received_mail',$data,TRUE);
		$this->email->message($emaildescription);
		$result=$this->email->send();   
		return $result;
	}






	 public function userpanel()
	 {

		$data=array();

		$table_name='tbl_product_details';
		$columns='*';
		$limit=100;
		$data['productlist']=$this->admin_model->get_lists($table_name,$columns,$limit);

		$data['cart_list']="";
		if(isset($_SESSION['galtech_cart']))
		{
			$data['cart_list']=$_SESSION['galtech_cart'];
			$nearflag=false;
			foreach($data['cart_list'] as $index=>$value)
			{
				$data['cart_list'][$index]['other_details']=$this->admin_model->get_single_item_withid('*',$value['product_id'],'tbl_product_details');
			}
		}
		$this->load->view('admin/header');
		$this->load->view('admin/userpanel',$data);
		$this->load->view('admin/footer'); 
	 }
	
	
	public function add_products()
	{
			$data=array();
			$data['name']=$this->input->post('name');
			$data['price']=$this->input->post('price');
			$data['stock']=$this->input->post('stock');
			$data['status']="active";
			$result= $this->admin_model->insert_product($data);
			$response=array('success'=>$result,'status'=>'error','title'=>'Failed!!','msg'=>'Product Adding Failed','redirect'=>'');
			if($result)
			$response=array('success'=>$result,'status'=>'success','title'=>'Success!!','msg'=>'Product Added Successfully','redirect'=>'adminpanel');
			echo json_encode($response);
	}

	public function add_to_cart()
	{
		$success=0;
		$stock=0;
		$carttotal=0;
		if($this->session->userdata('cart_total'))
		{
			$carttotal=$this->session->userdata('cart_total');
		}
		$product_id=$this->input->post('product_id');
		$product_qty=$this->input->post('product_qty');
		$table="";
		$count_val=0; 
		$singledetails=$this->admin_model->get_single_item_withid('*',$product_id,'tbl_product_details');
		if($singledetails){
			if(!isset($_SESSION['galtech_cart']))
			{
			$_SESSION['galtech_cart']=array();
			}
			
			if(!isset($_SESSION['galtech_cart'][$product_id]))
			{
			$cart=array(
			'product_id'=>$singledetails->id,
			'product_name'=>$singledetails->name,
			'product_price'=>$singledetails->price,
			'product_count'=>$product_qty,
			'product_total'=>$product_qty * $singledetails->price,	
			);
			$_SESSION['galtech_cart'][$product_id]=$cart;
		}
		else
		{
				$product_count=$product_qty;
				if( ($_SESSION['galtech_cart'][$product_id]['product_count']+$product_count) <= $singledetails->stock)
				{
				$product_total=$product_count*$singledetails->price;
				//$total=$total+$value['product_total'];
				//$cart_count=$cart_count+$value['product_count'];
				$_SESSION['galtech_cart'][$product_id]['product_count']=$_SESSION['galtech_cart'][$product_id]['product_count']+$product_count;
				$_SESSION['galtech_cart'][$product_id]['product_total']=($product_total)+$_SESSION['galtech_cart'][$product_id]['product_total'];
				}
				else
				{
					$success=1;
					$stock=$singledetails->stock;
				}
				
		}
		$this->set_cart_countandtotal();
		echo json_encode(array('result'=>$success,'cartlist'=>$_SESSION['galtech_cart'],'stock'=>$stock,'carttotal'=>$carttotal));
	}
	}

	public function set_cart_countandtotal()
	{
		$cart_count=$cart_total=0;
		foreach($_SESSION['galtech_cart'] as $index=>$value)
		{
			$cart_total=$cart_total+$value['product_total'];
			$cart_count=$cart_count+$value['product_count'];
		}
			$this->session->set_userdata('cart_total',$cart_total);
			$this->session->set_userdata('cart_value',$cart_count);

	}

	public function deleteproduct_from_cart()
	{
		$carttotal=0;
		if($this->session->userdata('cart_total'))
		{
			$carttotal=$this->session->userdata('cart_total');
		}
		$cart_id=$this->input->post('cart_id');
		unset($_SESSION['galtech_cart'][$cart_id]);
		$this->set_cart_countandtotal();
		echo $carttotal;
	}

	/////////////////////////////////////////////
}
