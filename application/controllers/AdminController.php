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
			$data['password']=sha1($this->input->post('password'));
			$data['status']='pending';
			$result=$this->admin_model->insert_user($data);
			$response=array('success'=>$result,'status'=>'error','title'=>'Failed!!','msg'=>'User Registration Failed','redirect'=>'');
			if($result)
			$this->email_code = sha1((string)$data['email_id']);
    		$data['email_code'] = $this->email_code;
			$_SESSION['emailcode']=$data['email_code'];
			//$this->send_success_mail($data);
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

	 public function verify_email($email_id,$email_code)
	 {

		$result=$this->admin_model->get_user_details($email_id,$email_code);
		print_r($email_id);

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
		$this->email->subject('Sreyas-please verify your email address
		');
		$emaildescription=$this->load->view('email/email_verification_mail',$data,TRUE);
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

	/////////////////////////////////////////////
}
