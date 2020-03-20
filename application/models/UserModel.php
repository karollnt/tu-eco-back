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
			->select($user_fields . ", ti.nombre AS tipo_id, pf.nombre AS perfil", false)
			->from("usuario usr")
				->join("tipo_identidad ti", "ti.id = usr.id_tipo_identidad", "inner")
				->join("perfil pf", "pf.id = usr.id_perfil", "inner")
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
}
