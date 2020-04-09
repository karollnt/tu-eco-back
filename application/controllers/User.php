<?php
class User extends CI_Controller {
	public function __construct() {
		parent::__construct();
		$this->load->model('UserModel');
	}

	public function create_user() {
		ob_start( 'ob_gzhandler' );
		header('Content-Type: application/json');
		$clave = trim($this->input->post('clave'));
		$user_data = array(
			'clave'=>md5($clave),'id_tipo_identidad'=>trim($this->input->post('id_tipo_identidad')),
			'nombre'=>trim($this->input->post('nombre')), 'apellido'=>trim($this->input->post('apellido')),
			'telefono'=>trim($this->input->post('telefono')), 'correo'=>trim($this->input->post('email')),
			'foto'=>trim($this->input->post('foto')), 'placa'=>trim($this->input->post('placa')),
			'id_perfil'=>trim($this->input->post('id_perfil')), 'id_ciudad'=>trim($this->input->post('id_ciudad')),
			'identificacion'=>trim($this->input->post('identificacion')),'direccion'=>trim($this->input->post('direccion'))
		);
		$response = [
			'valid' => $this->UserModel->create_user($user_data),
			'id' => $this->UserModel->get_user()['id'],
		];
		echo(json_encode($response));
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
			'valid' => $this->UserModel->verify_login($email, $md_pass),
			'id' => $this->UserModel->get_user()->id,
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
		if (is_array($user)) {
			die(json_encode(['users' => $user]));
		}
		echo(json_encode($user));
	}

	public function edit_data() {
		ob_start( 'ob_gzhandler' );
		header('Content-Type: application/json');
		$id = trim($this->input->put('id'));
		if (strcasecmp($id, '') === 0) {
			http_response_code(400);
			die(json_encode(['valid' => false, 'message' => 'bad request']));
		}
		$clave = trim($this->input->put('clave'));
		$user_data = array(
			'id_tipo_identidad'=>trim($this->input->put('id_tipo_identidad')),
			'nombre'=>trim($this->input->put('nombre')), 'apellido'=>trim($this->input->put('apellido')),
			'telefono'=>trim($this->input->put('telefono')), 'email'=>trim($this->input->put('email')),
			'foto'=>trim($this->input->put('foto')), 'placa'=>trim($this->input->put('placa')),
			'id_perfil'=>trim($this->input->put('id_perfil')), 'id_ciudad'=>trim($this->input->put('id_ciudad')),
			'identificacion'=>trim($this->input->put('identificacion')),'direccion'=>trim($this->input->put('direccion'))
		);
		if (strcasecmp('', $clave) !== 0) {
			$user_data['clave'] = md5($clave);
		}
		$response = [
			'valid' => $this->UserModel->edit_data($user_id, $user_data)
		];
		echo(json_encode($response));
	}

	public function update_password() {
		ob_start( 'ob_gzhandler' );
		header('Content-Type: application/json');
		$id = trim($this->input->put('id'));
		if (strcasecmp($id, '') === 0) {
			http_response_code(400);
			die(json_encode(['valid' => false, 'message' => 'bad request']));
		}
		$clave = trim($this->input->put('clave'));
		if (strcasecmp('', $clave) === 0) {
			http_response_code(400);
			die(json_encode(['valid' => false, 'message' => 'bad request']));
		}
		$user_data = ['clave' => md5($clave)];
		$response = [
			'valid' => $this->UserModel->edit_data($user_id, $user_data)
		];
		echo(json_encode($response));
	}

	public function list_users() {
		ob_start( 'ob_gzhandler' );
		header('Content-Type: application/json');
		$users = $this->UserModel->list_users();
		echo(json_encode(['users' => $users]));
	}
}
