<?php
class Route extends CI_Controller {
	public function __construct() {
		parent::__construct();
		$this->load->model('RouteModel');
		$this->load->model('UserModel');
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

	public function get_all_routes() {
		ob_start( 'ob_gzhandler' );
		header('Content-Type: application/json');
		$routes = $this->RouteModel->get_all_routes();
		echo(json_encode(['routes' => $routes]));
	}

	public function get_date_routes() {
		ob_start( 'ob_gzhandler' );
		header('Content-Type: application/json');
		$date = $this->input->get('date');
		$routes = $this->RouteModel->get_date_routes($date);
		echo(json_encode(['routes' => $routes]));
	}

	public function obtain_routes() {
		ob_start( 'ob_gzhandler' );
		header('Content-Type: application/json');
		$latitude = trim($this->input->get('latitude'));
		$longitude = trim($this->input->get('longitude'));
		$user_id = trim($this->input->get('user_id'));
		$position = [
			'latitude' => $latitude,
			'longitude' => $longitude,
		];
		$this->UserModel->edit_data($user_id, $position);
		$routes = $this->RouteModel->get_user_routes($user_id);
		echo(json_encode(['routes' => $routes]));
	}

	public function geolocate_routes() {
		ob_start( 'ob_gzhandler' );
		header('Content-Type: application/json');
		$latitude = trim($this->input->get('latitude'));
		$longitude = trim($this->input->get('longitude'));
		$user_id = trim($this->input->get('user_id'));
		$position = [
			'latitude' => $latitude,
			'longitude' => $longitude,
		];
		$this->UserModel->edit_data($user_id, $position);
		$routes = $this->RouteModel->calculate_nearest_routes($latitude, $longitude);
		echo(json_encode(['routes' => $routes]));
	}
}
