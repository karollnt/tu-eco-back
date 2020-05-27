<?php
class CategoryModel extends CI_Model {
	public function __construct() {
		$this->load->database();
	}

	public function get_category_data($category_id) {
		$this->db->select('ct.id, ct.nombre, ct.precio, ct.id_medida, md.nombre AS medida, ct.id_tipo, tp.nombre AS tipo,ct.foto')
			->from('categoria ct')
			->join('medida md', 'md.id = ct.id_medida', 'inner')
			->join('tipo_categoria tp', 'tp.id = ct.id_tipo', 'inner')
			->where(['id' => $category_id]);
		$res = $this->db->get();
		if ($res->num_rows() == 1) {
			return $res->result()[0];
		}
		$categories = [];
		foreach ($res->result() as $row) {
			array_push($categories, $row);
		}
		return $categories;
	}

	public function get_categories($category_type = null) {
		$this->db->select('ct.id, ct.nombre, ct.precio, ct.id_medida, md.nombre AS medida, ct.id_tipo, tp.nombre AS tipo,ct.foto')
			->from('categoria ct')
			->join('medida md', 'md.id = ct.id_medida', 'inner')
			->join('tipo_categoria tp', 'tp.id = ct.id_tipo', 'inner');
		if ($category_type != null) {
			$this->db->where(['id_tipo' => $category_type]);
		}
		$res = $this->db->get();
		$categories = [];
		foreach ($res->result() as $row) {
			array_push($categories, $row);
		}
		return $categories;
	}

	public function create_category($category_data) {
		$this->db->insert('categoria', $category_data);
		return $this->db->affected_rows() > 0;
	}

	public function get_measurements() {
		$this->db->select('id, nombre')
		->from('medida');
		$res = $this->db->get();
		$categories = [];
		foreach ($res->result() as $row) {
			array_push($categories, $row);
		}
		return $categories;
	}

	public function get_category_types() {
		$this->db->select('id, nombre')
		->from('tipo_categoria');
		$res = $this->db->get();
		$categories = [];
		foreach ($res->result() as $row) {
			array_push($categories, $row);
		}
		return $categories;
	}
}
