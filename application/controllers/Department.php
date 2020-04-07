<?php
class Department extends CI_Controller {
	public function __construct() {
		parent::__construct();
		$this->load->model('CityModel');
	}

	public function list_departments() {
		ob_start( 'ob_gzhandler' );
		header('Content-Type: application/json');
		$departments = $this->CityModel->list_departments();
		echo(json_encode($departments));
	}

	public function get_department_cities() {
		ob_start( 'ob_gzhandler' );
		header('Content-Type: application/json');
		$city_id = $this->input->get('id');
		if (strcasecmp($city_id, '') === 0) {
			http_response_code(400);
			die(json_encode(['cities' => []]));
		}
		$cities = $this->CityModel->get_department_cities($city_id);
		echo(json_encode($cities));
	}
}
