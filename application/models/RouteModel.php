<?php
class RouteModel extends CI_Model {
	protected $current_route;

	public function __construct() {
		$this->current_route = null;
		$this->load->database();
	}

	public function get_user_routes($user_id) {
		$this->db->select('rt.id, rt.fecha_creacion, rt.comentario, er.nombre AS estado')
		->from('ruta rt')
		->join('estado_ruta er', 'er.id = rt.id_estado', 'inner')
		->where(['rt.id_reciclatendero' => $user_id]);
		$res = $this->db->get();
		$routes = [];
		foreach ($res->result() as $row) {
			array_push($routes, $row);
		}
		return $routes;
	}

	public function get_route_details($route_id) {
		$this->db->select('rt.id, rt.fecha_creacion, rt.comentario, rt.id_reciclatendero, er.nombre AS estado')
		->from('ruta rt')
		->join('estado_ruta er', 'er.id = rt.id_estado', 'inner')
		->where(['rt.id' => $route_id]);
		$res = $this->db->get();
		if ($res->num_rows() < 1) {
			return [];
		}
		$details = [
			'details' => array_map(function ($row) {
				return $row;
			}, $res->result())
		];
		$this->db->select('sl.id, sl.fecha, sl.comentario, sl.id_solicitante, us.nombre AS nombre_cliente, us.apellido AS apellido_cliente')
		->from('solicitudes_ruta rt')
		->join('solicitud sl', 'sl.id = rt.id_solicitud', 'inner')
		->join('usuario us', 'sl.id_solicitante = us.id', 'inner')
		->where(['rt.id_ruta' => $route_id]);
		$details['orders'] = array_map(function ($row) {
			return $row;
		}, $res->result());
		return $details;
	}

	public function get_all_routes() {
		$this->db->select('rt.id, rt.fecha_creacion, rt.comentario, er.nombre AS estado')
		->from('ruta rt')
		->join('estado_ruta er', 'er.id = rt.id_estado', 'inner');
		$res = $this->db->get();
		$routes = [];
		foreach ($res->result() as $row) {
			array_push($routes, $row);
		}
		return $routes;
	}

	public function get_date_routes($date) {
		$this->db->select('rt.id, rt.fecha_creacion, rt.comentario, er.nombre AS estado')
		->from('ruta rt')
		->join('estado_ruta er', 'er.id = rt.id_estado', 'inner')
		->where(['rt.fecha_creacion' => $date]);
		$res = $this->db->get();
		$routes = [];
		foreach ($res->result() as $row) {
			array_push($routes, $row);
		}
		return $routes;
	}
}
