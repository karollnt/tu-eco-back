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
		$this->db->select('sl.id, sl.fecha, sl.fecha_recogida, sl.comentario, sl.id_solicitante, ' .
			'us.nombre AS nombre_cliente, us.apellido AS apellido_cliente, '.
			'sl.ciudades_id AS id_ciudad, cd.nombre AS ciudad, dp.iddepartamento AS id_departamento, dp.nombre AS departamento')
		->from('solicitudes_ruta rt')
		->join('solicitud sl', 'sl.id = rt.id_solicitud', 'inner')
		->join('usuario us', 'sl.id_solicitante = us.id', 'inner')
		->join("ciudades cd", "cd.id = sl.ciudades_id", "inner")
		->join("departamento dp", "dp.iddepartamento = cd.id_departamento", "inner")
		->where(['rt.id_ruta' => $route_id]);
		$res = $this->db->get();
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

	public function get_boundaries($lat, $lng, $distance = 1, $earthRadius = 6371) {
		$return = array();
		$cardinalCoords = array(
			'north' => '0',
			'south' => '180',
			'east' => '90',
			'west' => '270'
		);

		$rLat = deg2rad($lat);
		$rLng = deg2rad($lng);
		$rAngDist = $distance / $earthRadius;

		foreach ($cardinalCoords as $name => $angle) {
			$rAngle = deg2rad($angle);
			$rLatB = asin(sin($rLat) * cos($rAngDist) + cos($rLat) * sin($rAngDist) * cos($rAngle));
			$rLonB = $rLng + atan2(sin($rAngle) * sin($rAngDist) * cos($rLat), cos($rAngDist) - sin($rLat) * sin($rLatB));

			$return[$name] = array(
				'lat' => (float) rad2deg($rLatB),
				'lng' => (float) rad2deg($rLonB)
			);
		}

		return array(
			'min_lat'  => $return['south']['lat'],
			'max_lat' => $return['north']['lat'],
			'min_lng' => $return['west']['lng'],
			'max_lng' => $return['east']['lng']
		);
	}

	public function calculate_nearest_routes($user_lat, $user_lng) {
		$orders = $this->calculate_nearest_orders($user_lat, $user_lng);
		$routes = [];
		if (count($orders) < 1) {
			return $routes;
		}
		foreach ($orders as $row) {
			$this->db->select('rt.id')
				->from('ruta rt')
				->join('solicitudes_ruta sr', 'sr.id_ruta = rt.id', 'inner')
				->where(['rt.estado' => 1, 'sr.id' => $row->id]);
			$res = $this->db->get();
			foreach ($res->result() as $row2) {
				if ( in_array($row2->id, $routes) ) {
					continue;
				}
				array_push($routes, $row2->id);
			}
		}
		return $routes;
	}

	public function calculate_nearest_orders($user_lat, $user_lng) {
		$box = $this->get_boundaries($user_lat, $user_lng);
		$earthRadius = 6371;
		$this->db->select("({$earthRadius} * ACOS(
				COS( RADIANS(latitude) )
				* COS( RADIANS({$lat}) )
				* COS( RADIANS({$user_lng}) )
				- RADIANS(longitude)
				+ SIN( RADIANS(latitude) )
				* SIN( RADIANS({$lat}) )
			)) AS distance, id")
			->from("solicitudes")
			->where(
				'latitude BETWEEN ' . $box['min_lat']. ' AND ' . $box['max_lat'] . ')' .
				' AND (longitude BETWEEN ' . $box['min_lng']. ' AND ' . $box['max_lng']. ')'
			)
			->having('distance < 1')
			->order_by('distance', 'ASC');
		$res = $this->db->get();
		$orders = [];
		foreach ($res->result() as $row) {
			array_push($orders, $row);
		}
		return $orders;
	}
}
