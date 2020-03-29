<?php
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
		return $this->db->affected_rows() > 0;
	}

	public function verify_login($email, $passwd) {
		$conditions = array('correo' => $email, 'clave' => $passwd);
		$this->db
			->select("id", false)
			->from("usuario")
			->where($conditions);
		$res = $this->db->get();
		return $res->num_rows() > 0;
	}

	public function search_user($field, $value) {
		$user_fields = "usr.id, usr.nombre, usr.apellido, usr.identificacion, usr.telefono, "
			. "usr.direccion, usr.correo, usr.foto, usr.placa";
		$conditions = array('usr.' . $field => $value);
		$this->db
			->select($user_fields . ", ti.nombre AS tipo_id, pf.nombre AS perfil, " .
				"cd.id AS id_ciudad, cd.nombre AS ciudad, " .
				"dp.id AS id_departamento, dp.nombre AS departamento", false)
			->from("usuario usr")
				->join("tipo_identidad ti", "ti.id = usr.id_tipo_identidad", "inner")
				->join("perfil pf", "pf.id = usr.id_perfil", "inner")
				->join("ciudades cd", "cd.id = usr.id_ciudad", "inner")
				->join("departamento dp", "dp.id = cd.id_departamento", "inner")
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
		$this->select("cd.id")
			->from("usuario usr")
			->join("ciudades cd", "cd.id = usr.id_ciudad", "inner")
			->where(["usr.id" => $user_id]);
		$res = $this->db->get();
		if ($res->num_rows() > 0) {
			return $res->result()[0];
		}
		return 1;
	}

	public function edit_data($user_id, $user_data) {
		$this->db
			->update('usuarios', $user_data)
			->where(['id' => $user_id]);
		return $this->db->affected_rows() > 0;
	}

	public function list_users() {
		$user_fields = "usr.id, usr.nombre, usr.apellido, usr.identificacion, usr.telefono, "
			. "usr.direccion, usr.correo, usr.foto, usr.placa";
		$this->db
			->select($user_fields . ", ti.nombre AS tipo_id, pf.nombre AS perfil, " .
				"cd.id AS id_ciudad, cd.nombre AS ciudad, " .
				"dp.id AS id_departamento, dp.nombre AS departamento", false)
			->from("usuario usr")
				->join("tipo_identidad ti", "ti.id = usr.id_tipo_identidad", "inner")
				->join("perfil pf", "pf.id = usr.id_perfil", "inner")
				->join("ciudades cd", "cd.id = usr.id_ciudad", "inner")
				->join("departamento dp", "dp.id = cd.id_departamento", "inner");
		$res = $this->db->get();
		$users = [];
		foreach ($res->result() as $row) {
			array_push($users, $row);
		}
		return $users;
	}
}
