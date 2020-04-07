<?php
class CityModel extends CI_Model {
	public function __construct() {
		$this->load->database();
	}

	public function list_departments() {
		$this->db->select('iddepartamento AS id, nombre')
			->from('departamento');
		$res = $this->db->get();
		$departments = [];
		foreach ($res->result() as $row) {
			array_push($departments, $row);
		}
		return $departments;
	}

	public function get_department_cities($city_id) {
		$this->db->select('id, nombre')
			->from('ciudades')
			->where(['id_departamento' => $city_id]);
		$res = $this->db->get();
		$cities = [];
		foreach ($res->result() as $row) {
			array_push($cities, $row);
		}
		return $cities;
	}
}
