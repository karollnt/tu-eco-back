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
