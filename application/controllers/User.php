<?php
class User extends CI_Controller {
	public function __construct() {
		parent::__construct();
		$this->load->model('UserModel');
	}

	public function login() {
		ob_start( 'ob_gzhandler' );
		$email = trim($this->input->post('correo'));
		$clave = trim($this->input->post('clave'));
		header('Content-Type: application/json');
		if (strcasecmp($email, '') === 0) {
			http_response_code(400);
			die(json_encode(['valid' => false]));
		}
		$md_pass = md5($clave);
		$response = [
			'valid' => $this->UserModel->verify_login($email, $md_pass)
		];
		echo(json_encode($response));
	}

	public function get_user_data() {
		ob_start( 'ob_gzhandler' );
		header('Content-Type: application/json');
		$id = trim($this->input->get('id'));
		if (strcasecmp($id, '') === 0) {
			http_response_code(400);
			die(json_encode(['message' => 'bad request']));
		}
		$user = $this->UserModel->search_user('id', $id);
		if (count($user) > 1) {
			die(json_encode(['users' => $user]));
		}
		echo(json_encode($user));
	}
}
