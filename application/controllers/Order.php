<?php

class Order extends CI_Controller {
	public function __construct() {
		parent::__construct();
		$this->load->model('OrderModel');
		$this->load->model('UserModel');
	}

	public function create_order() {
		ob_start( 'ob_gzhandler' );
		header('Content-Type: application/json');
		$user_id = $this->input->post('user_id');
		if (strcasecmp($user_id, '') === 0) {
			http_response_code(400);
			die(json_encode(['valid' => false]));
		}
		$comment = utf8_decode($this->input->post('comment'));
		$fecha_recogida = utf8_decode($this->input->post('fecha_recogida'));
		$city_id = $this->UserModel->get_user_city_id($user_id);
		$categories = explode('|', $this->input->post('categorias'));
		$categories = array_map(function ($category) {
			$cat_array = explode(';', $category);
			return ['id_categoria' => $cat_array[0], 'valor' => $cat_array[1], 'cantidad' => $cat_array[2]];
		}, $categories);

		$valid = $this->OrderModel->create_order(
			$user_id,
			[
				'comentario' => $comment, 'ciudades_id' => $city_id,
				'fecha_recogida' => $fecha_recogida, 'categorias' => $categories,
				'latitude' => trim($this->input->post('latitude')),
				'longitude' => trim($this->input->post('longitude'))
			]
		);
		if ($valid === false) {
			http_response_code(400);
		}
		echo(json_encode(['valid' => $valid]));
	}

	public function get_order_data() {
		ob_start( 'ob_gzhandler' );
		header('Content-Type: application/json');
		$order_id = $this->input->get('order_id');
		if (strcasecmp($order_id, '') === 0) {
			http_response_code(400);
			die(json_encode([]));
		}
		$order = $this->OrderModel->get_order_data($order_id);
		if (is_array($order)) {
			die(json_encode($order));
		}
		echo(json_encode($order));
	}

	public function get_order_details() {
		ob_start( 'ob_gzhandler' );
		header('Content-Type: application/json');
		$order_id = $this->input->get('order_id');
		if (strcasecmp($order_id, '') === 0) {
			http_response_code(400);
			die(json_encode([]));
		}
		$order = $this->OrderModel->get_order_details($order_id);
		echo(json_encode($order));
	}

	public function get_unassigned_orders() {
		ob_start( 'ob_gzhandler' );
		header('Content-Type: application/json');
		$orders = $this->OrderModel->get_unassigned_orders();
		echo(json_encode($orders));
	}

	public function get_user_orders() {
		ob_start( 'ob_gzhandler' );
		header('Content-Type: application/json');
		$user_id = $this->input->get('user_id');
		$is_recycler = $this->input->get('is_recycler');
		if (strcasecmp($user_id, '') === 0) {
			http_response_code(400);
			die(json_encode([]));
		}
		$orders = $this->OrderModel->get_user_orders($user_id, strcasecmp('true', $is_recycler) == 0);
		echo(json_encode($orders));
	}

	public function set_as_completed() {
		ob_start( 'ob_gzhandler' );
		header('Content-Type: application/json');
		$order_id = $this->input->post('order_id');
		if (strcasecmp($order_id, '') === 0) {
			http_response_code(400);
			die(json_encode(['valid' => false]));
		}
		$valid = $this->OrderModel->set_as_completed($order_id);
		echo(json_encode(['valid' => $valid]));
	}
}
