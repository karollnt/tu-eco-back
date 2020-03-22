<?php
class Route extends CI_Controller {
	public function __construct() {
		parent::__construct();
		$this->load->model('RouteModel');
	}

	public function get_user_routes() {
		ob_start( 'ob_gzhandler' );
		header('Content-Type: application/json');
		$user_id = $this->input->get('user_id');
		if (strcasecmp($user_id, '') === 0) {
			http_response_code(400);
			die(json_encode(['routes' => []]));
		}
		$routes = $this->RouteModel->get_user_routes($user_id);
		echo(json_encode(['routes' => $routes]));
	}

	public function get_route_details() {
		ob_start( 'ob_gzhandler' );
		header('Content-Type: application/json');
		$route_id = $this->input->get('route_id');
		if (strcasecmp($route_id, '') === 0) {
			http_response_code(400);
			die(json_encode(['routes' => []]));
		}
		$route = $this->RouteModel->get_route_details($route_id);
		echo(json_encode(['route' => $route]));
	}
}
