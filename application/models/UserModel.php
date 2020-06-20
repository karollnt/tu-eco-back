<?php
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

class UserModel extends CI_Model {
	protected $current_user;

	public function __construct() {
		$this->current_user = null;
		$this->load->database();
	}

	public function get_user() {
		return $this->current_user;
	}

	public function create_user($user_data) {
		$this->db->insert('usuario', $user_data);
		if ($this->db->affected_rows() > 0) {
			$this->current_user = ['id' => $this->db->insert_id()];
		}
		return $this->db->affected_rows() > 0;
	}

	public function verify_login($email, $passwd, $type = null) {
		$conditions = array('correo' => $email, 'clave' => $passwd);
		if ($type != null) {
			$conditions['id_perfil'] = $type;
		}
		$this->db
			->select("id", false)
			->from("usuario")
			->where($conditions);
		$res = $this->db->get();
		if ($res->num_rows() == 1) {
			$this->current_user = $res->result()[0];
		}
		return $res->num_rows() > 0;
	}

	public function search_user($field, $value) {
		$user_fields = "usr.id, usr.nombre, usr.apellido, usr.identificacion, usr.telefono, "
			. "usr.direccion, usr.correo, usr.foto, usr.placa";
		$conditions = array('usr.' . $field => $value);
		$this->db
			->select($user_fields . ", ti.nombre AS tipo_id, pf.nombre AS perfil, " .
				"cd.id AS id_ciudad, cd.nombre AS ciudad, " .
				"dp.iddepartamento AS id_departamento, dp.nombre AS departamento", false)
			->from("usuario usr")
				->join("tipo_identidad ti", "ti.id = usr.id_tipo_identidad", "inner")
				->join("perfil pf", "pf.id = usr.id_perfil", "inner")
				->join("ciudades cd", "cd.id = usr.id_ciudad", "inner")
				->join("departamento dp", "dp.iddepartamento = cd.id_departamento", "inner")
			->where($conditions);
		$res = $this->db->get();
		if ($res->num_rows() == 1) {
			$this->current_user = $res->result()[0];
			return $this->current_user;
		}
		$users = [];
		foreach ($res->result() as $row) {
			array_push($users, $row);
		}
		return $users;
	}

	public function get_user_city_id($user_id = null) {
		if ($this->current_user != null) {
			return $this->current_user['id_ciudad'];
		}
		if ($user_id == null) {
			return 1;
		}
		$this->db->select("cd.id")
			->from("usuario usr")
			->join("ciudades cd", "cd.id = usr.id_ciudad", "inner")
			->where(["usr.id" => $user_id]);
		$res = $this->db->get();
		if ($res->num_rows() > 0) {
			return $res->result()[0]->id;
		}
		return 1;
	}

	public function edit_data($user_id, $user_data) {
		$this->db->where(['id' => $user_id]);
		$this->db->update('usuario', $user_data);
		return $this->db->affected_rows() > 0;
	}

	public function list_users() {
		$user_fields = "usr.id, usr.nombre, usr.apellido, usr.identificacion, usr.telefono, "
			. "usr.direccion, usr.correo, usr.foto, usr.placa";
		$this->db
			->select($user_fields . ", ti.nombre AS tipo_id, pf.nombre AS perfil, " .
				"cd.id AS id_ciudad, cd.nombre AS ciudad, " .
				"dp.iddepartamento AS id_departamento, dp.nombre AS departamento", false)
			->from("usuario usr")
				->join("tipo_identidad ti", "ti.id = usr.id_tipo_identidad", "inner")
				->join("perfil pf", "pf.id = usr.id_perfil", "inner")
				->join("ciudades cd", "cd.id = usr.id_ciudad", "inner")
				->join("departamento dp", "dp.iddepartamento = cd.id_departamento", "inner");
		$res = $this->db->get();
		$users = [];
		foreach ($res->result() as $row) {
			array_push($users, $row);
		}
		return $users;
	}

	public function list_users_by_type($type = 1) {
		$user_fields = "usr.id, usr.nombre, usr.apellido, usr.identificacion, usr.telefono, "
			. "usr.direccion, usr.correo, usr.foto, usr.placa";
		$this->db
			->select($user_fields . ", ti.nombre AS tipo_id, pf.nombre AS perfil, " .
				"cd.id AS id_ciudad, cd.nombre AS ciudad, " .
				"dp.iddepartamento AS id_departamento, dp.nombre AS departamento", false)
			->from("usuario usr")
				->join("tipo_identidad ti", "ti.id = usr.id_tipo_identidad", "inner")
				->join("perfil pf", "pf.id = usr.id_perfil", "inner")
				->join("ciudades cd", "cd.id = usr.id_ciudad", "inner")
				->join("departamento dp", "dp.iddepartamento = cd.id_departamento", "inner")
			->where(['id_perfil' => $type]);
		$res = $this->db->get();
		$users = [];
		foreach ($res->result() as $row) {
			array_push($users, $row);
		}
		return $users;
	}

	public function update_image($user_id, $file_data) {
		$bucket_name = 'tuecofiles';
		$file_name = $this->random_str(48) . $file_data['extension'];
		$file_url = html_entity_decode('https://' . $bucket_name . '.s3.amazonaws.com/' . $file_name);
		$s3 = new S3Client([
			'version' => 'latest',
			'region'  => 'us-east-2',
			'credentials' => [
				'key' => getenv('S3_KEY'),
				'secret' => getenv('S3_SECRET')
			]
		]);
		try {
			$result = $s3->putObject([
				'Bucket' => $bucket_name,
				'Key'    => $file_name,
				'ACL'    => 'public-read',
				'Body'   => $file_data['file'],
				//'SourceFile' => 'c:\samplefile.png' -- use this if you want to upload a file from a local location
			]);
			$file_url = $result['ObjectURL'];
			return $this->edit_data($user_id, ['foto' => $file_url]);
		} catch (S3Exception $e) {
			echo $e->getMessage() . PHP_EOL;
			return false;
		}
		return false;
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

	public function get_user_coordinates($user_id) {
		if ($user_id == null) {
			return ['latitude' => 0, 'longitude' => 0];
		}
		$this->db->select("usr.latitude, usr.longitude")
			->from("usuario usr")
			->where(["usr.id" => $user_id]);
		$res = $this->db->get();
		if ($res->num_rows() > 0) {
			$row = $res->result()[0];
			return ['latitude' => $row->latitude, 'longitude' => $row->longitude];
		}
		return ['latitude' => 0, 'longitude' => 0];
	}
}
