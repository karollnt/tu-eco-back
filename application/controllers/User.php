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
		$tipo = trim($this->input->post('tipo'));
		if (!isset($tipo) || strcasecmp('', $tipo) == 0) {
			$tipo = null;
		}
		header('Content-Type: application/json');
		if (strcasecmp($email, '') === 0) {
			http_response_code(400);
			die(json_encode(['valid' => false]));
		}
		$md_pass = md5($clave);
		$response = [
			'valid' => $this->UserModel->verify_login($email, $md_pass, $tipo),
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
		$user_id = trim($this->input->post('id'));
		if (strcasecmp($user_id, '') === 0) {
			http_response_code(400);
			die(json_encode(['valid' => false, 'message' => 'bad request']));
		}
		$clave = trim($this->input->post('clave'));
		$user_data = array(
			'id_tipo_identidad'=>trim($this->input->post('id_tipo_identidad')),
			'nombre'=>trim($this->input->post('nombre')), 'apellido'=>trim($this->input->post('apellido')),
			'telefono'=>trim($this->input->post('telefono')), 'placa'=>trim($this->input->post('placa')),
			'id_perfil'=>trim($this->input->post('id_perfil')), 'id_ciudad'=>trim($this->input->post('id_ciudad')),
			'identificacion'=>trim($this->input->post('identificacion')),'direccion'=>trim($this->input->post('direccion'))
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
		$id = trim($this->input->post('user_id'));
		if (strcasecmp($id, '') === 0) {
			http_response_code(400);
			die(json_encode(['valid' => false, 'message' => 'bad request']));
		}
		$clave = trim($this->input->post('clave'));
		if (strcasecmp('', $clave) === 0) {
			http_response_code(400);
			die(json_encode(['valid' => false, 'message' => 'bad request']));
		}
		$user_data = ['clave' => md5($clave)];
		$response = [
			'valid' => $this->UserModel->edit_data($id, $user_data)
		];
		echo(json_encode($response));
	}

	public function list_users() {
		ob_start( 'ob_gzhandler' );
		header('Content-Type: application/json');
		$users = $this->UserModel->list_users();
		echo(json_encode(['users' => $users]));
	}

	public function update_image() {
		ob_start( 'ob_gzhandler' );
		header('Content-Type: application/json');
		$user_id = trim($this->input->post('user_id'));
		if (strcasecmp($user_id, '') === 0) {
			http_response_code(400);
			die(json_encode(['valid' => false, 'message' => 'bad request']));
		}
		$path = './uploads/';
		$this->load->library('upload');
		$this->upload->initialize(array(
			"upload_path"       =>  $path,
			"allowed_types"     =>  "gif|jpg|png|jpeg|bmp|svg|ico",
			"max_size"          =>  '20000000',
			"max_width"         =>  '13684',
			"max_height"        =>  '13684'
		));
		if($this->upload->do_upload('image')) {
			$image = $this->upload->data();
			$file_data = [
				'file' => file_get_contents($image['full_path']),
				'extension' => $image['file_ext']
			];
			$response = [
				'valid' => $this->UserModel->update_image($user_id, $file_data)
			];
			if ($response['valid'] == false) {
				http_response_code(500);
			}
			unlink($image['full_path']);
			echo(json_encode($response));
		}
	}

	public function process_forgot_email() {
		ob_start( 'ob_gzhandler' );
		header('Content-Type: application/json');
		$email = $this->input->post('email');
		if (strcasecmp($email, '') === 0) {
			http_response_code(400);
			die(json_encode(['valid' => false, 'message' => 'bad request']));
		}
		$user = $this->UserModel->search_user('correo', $email);
		if (is_array($user) && count($user) == 0) {
			http_response_code(400);
			die(json_encode(['valid' => false, 'message' => 'data not found']));
		}
		$token = $this->generate_reset_token($user->id);
		$response = [
			'valid' => $this->send_reset_email($email, $token)
		];
		echo(json_encode($response));
	}

	public function reset_password() {
		$token = $this->input->get('token');
		if (!isset($token) || ! file_exists(APPPATH.'views/pages/reset-password.php')) {
			show_404();
		}
		$data['token'] = $token;
		$this->load->view('pages/reset-password', $data);
	}

	public function update_reset_password() {
		$token = $this->input->post('token');
		$password = $this->input->post('password');
		if (!isset($token)) {
			$data['response'] = 'No es posible actualizar la clave en este momento';
		} else {
			$user = $this->UserModel->search_user('reset_token', $token);
			if (is_array($user) && count($user) == 0) {
				$data['response'] = 'No es posible actualizar la clave en este momento';
			} else {
				$was_updated = $this->UserModel->edit_data($user->id, ['clave' => md5($password), 'reset_token' => '']);
				$text = 'Se ha restablecido su clave';
				if (!$was_updated) {
					$text = 'No se pudo restablecer su clave';
				}
				$data['response'] = $text;
			}
		}
		$this->load->view('pages/reset-password', $data);
	}

	private function random_str($length) {
		$keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_';
		$str = '';
		$max = strlen($keyspace) - 1;
		for ($i = 0; $i < $length; ++$i) {
			$str .= $keyspace[rand(0, $max)];
		}
		return $str;
    }

	private function generate_reset_token($user_id) {
		$token = $this->random_str(24);
		$was_updated = $this->UserModel->edit_data($user_id, ['reset_token' => $token]);
		return $token;
	}

	private function send_reset_email($email, $token) {
		$subject = 'Restablecer clave';
		$from = 'noreply-tueco@yopmail.com';
		$link = 'https://tueco.herokuapp.com/user/reset_password/?token=' . $token;
		$message = "Hola!<br>Puedes restablecer tu clave en el siguiente enlace:<br>{$link}";
		$api_key = getenv('sendgrid_api_key');
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => "https://api.sendgrid.com/v3/mail/send",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => "{\"personalizations\":[{\"to\":[{\"email\":\"{$email}\"}],\"subject\":\"{$subject}\"}],\"from\":{\"email\":\"{$from}\",\"name\":\"Waoo Technology\"},\"content\":[{\"type\":\"text/html\",\"value\":\"{$message}\"}]}",
			CURLOPT_HTTPHEADER => array(
				"authorization: Bearer {$api_key}",
				"content-type: application/json"
			),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			// echo "cURL Error #:" . $err;
			return false;
		}
		$json = json_decode($response);
		if (isset($json->errors)) {
			var_dump($json);
			return false;
		}
		return true;
	}
}
