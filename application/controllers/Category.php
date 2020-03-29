<?php
class Category extends CI_Controller {
	public function __construct() {
		parent::__construct();
		$this->load->model('CategoryModel');
	}

	public function get_categories() {
		ob_start( 'ob_gzhandler' );
		header('Content-Type: application/json');
		$categories = $this->CategoryModel->get_categories();
		echo(json_encode(['categories' => $categories]));
	}

	public function create_category() {
		ob_start( 'ob_gzhandler' );
		header('Content-Type: application/json');
		$category_data = [
			'nombre' => trim($this->input->post('nombre')),
			'precio' => trim($this->input->post('precio')),
			'id_tipo' => trim($this->input->post('id_tipo')),
			'id_medida' => trim($this->input->post('id_medida'))
		];
		$response = [
			'valid' => $this->CategoryModel->create_category($category_data)
		];
		echo(json_encode($response));
	}

	public function get_category_data() {
		ob_start( 'ob_gzhandler' );
		header('Content-Type: application/json');
		$category_id = $this->input->get('category_id');
		if (strcasecmp($category_id, '') === 0) {
			http_response_code(400);
			die(json_encode(['category' => []]));
		}
		$category = $this->CategoryModel->get_category_details($category_id);
		echo(json_encode(['category' => $category]));
	}
}
