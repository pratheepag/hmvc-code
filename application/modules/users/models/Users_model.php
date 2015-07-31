<?php
class Users_model extends CI_Model{
	
	private $table_name = "news_users";
    /**
    * Validate the login's data with the database
    * @param string $user_name
    * @param string $password
    * @return void
    */
	function validate($user_name, $password)
	{
		$this->db->where('email', $user_name);
		$this->db->where('password', $password);
		$query = $this->db->get($this->table_name);
		
		if($query->num_rows == 1)
		{
			return true;
		}		
	}

    /**
    * Store the new user's data into the database
    * @return boolean - check the insert
    */	
	function create_member()
	{

		$this->db->where('email', $this->input->post('email'));
		$query = $this->db->get($this->table_name);

        if($query->num_rows > 0){
        	echo '<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a><strong>';
			echo "Email already registered";	
			echo '</strong></div>';
		}else{
			$hash = md5(rand(0, 1000));
			$new_member_insert_data = array(
				'email' => $this->input->post('email'),			
				'user_name' => $this->input->post('username'),
				'hash' => $hash,
			);
			$ci = get_instance();
			$ci->load->library('email');
		/*	$config['protocol'] = "smtp";
			$config['smtp_host'] = "ssl://smtp.gmail.com";
			$config['smtp_port'] = "465";
			$config['smtp_user'] = "pratheepa.vnr@gmail.com"; 
			$config['smtp_pass'] = "123456";
			$config['charset'] = "utf-8";
			$config['mailtype'] = "html";
			$config['newline'] = "\r\n";

			$ci->email->initialize($config);   */

			$ci->email->from($this->config->item('admin_email'), 'News Publishing System'); 
			$list = array($this->input->post('email'));
			$ci->email->to($list);
			//$this->email->reply_to('pratheepa.vnr@gmail.com', 'Reply To');
			$ci->email->subject('Email Verification News Publishing System');
			$message = "Hi ".$this->input->post('username').",<br><br>";
			$message .= "Please click the link below to activate the account ".base_url()."verify_email/".urlencode($this->input->post('email'))."/".$hash;
			$message .= "<br><br> Regards, <br>";
			$message .= "News Publishing Team";
			$ci->email->message($message);
			$ci->email->send();

			$insert = $this->db->insert($this->table_name, $new_member_insert_data);
		    return $insert;
		}
	      
	}//create_member
	/**
    * Verify the user's email into the database
    * @return boolean - check the update
    */
	function verify_email($email, $hash)
	{
		$this->db->where('email', $email);
		$this->db->where('hash', $hash);
		$this->db->where('active', '0');
		$query = $this->db->get($this->table_name);
		
		if($query->num_rows == 1)
		{
			return true;
		}		
	}
	/**
    * Store the new user's password into the database
    * @return boolean - check the update
    */	
	function save_password($email, $hash)
	{
		$data = array('password' => md5($this->input->post('password')),
						'active' =>  '1');
		$this->db->where('email', $email);
		$this->db->where('hash', $hash);
		$this->db->update($this->table_name, $data);
		$report = array();
		$report['error'] = $this->db->_error_number();
		$report['message'] = $this->db->_error_message();
		if($report !== 0){
			return true;
		}else{
			return false;
		}
   
	}
	
	/**
    * Verify the user's account active into the database
    * @return boolean - check the update
    */
	function verify_account_active($email, $hash)
	{
		$this->db->select('active');
		$this->db->from($this->table_name);
		$this->db->where('email', $email);
		$this->db->where('hash', $hash);
		$query = $this->db->get();
		$row = $query->row();
		if($row->active == 1){
			return true;
		}else{
			return false;
		}
	}
	
	function get_id_by_email($email)
	{
		$this->db->select('id');
		$this->db->from($this->table_name);
		$this->db->where('email', $email);
		$query = $this->db->get();
		$row = $query->row();
		return $row->id;
	}
	
	function get_username_by_id($id)
	{
		$this->db->select('user_name');
		$this->db->from($this->table_name);
		$this->db->where('id', $id);
		$query = $this->db->get();
		$row = $query->row();
		return $row->id;
	}
	
}

