<?php
class OrderModel extends CI_Model {
	protected $current_order;

	public function __construct() {
		$this->current_order = null;
		$this->load->database();
	}

	public function create_order($user_id, $order_data) {
		$handler = $this->db->query("INSERT INTO `solicitud`(`id_solicitante`,`id_reciclatendero`, `comentario`, `ciudades_id`, `fecha`, `latitude`, `longitude`) " .
			"VALUES ({$user_id}, null, " . ($this->db->escape($order_data['comentario'])) . ",
				{$order_data['ciudades_id']}, " . ($this->db->escape($order_data['fecha_recogida'])) .
				", {$order_data['latitude']}, {$order_data['longitude']}" .
			")");
		$order_id = $this->db->insert_id();
		if (!$order_id) {
			return false;
		}
		foreach ($order_data['categorias'] as $value) {
			$handler = $this->db->query("INSERT INTO `detalle_solicitud`(`id_solicitud`, `id_categoria`, `valor`, `cantidad`) " .
				"VALUES ({$order_id}, {$value['id_categoria']}, {$value['valor']}, {$value['cantidad']})");
		}
		return true;
	}

	public function get_order_data($order_id) {
		$this->db
			->select('sl.id, sl.fecha, sl.id_solicitante, us.nombre AS nombre_cliente, us.apellido AS apellido_cliente, sl.fecha_recogida, ' .
				'us.direccion, us.id_perfil, sl.id_estado_solicitud, us.telefono, ' .
				'sl.id_reciclatendero, us2.nombre AS nombre_recicla_tendero, us2.apellido AS apellido_recicla_tendero, us2.foto,us2.placa, us2.telefono AS telefono_empleado, ' .
				'sl.comentario, sl.ciudades_id AS id_ciudad, cd.nombre AS ciudad, dp.iddepartamento AS id_departamento, dp.nombre AS departamento')
			->from('solicitud sl')
			->join("ciudades cd", "cd.id = sl.ciudades_id", "inner")
			->join("departamento dp", "dp.iddepartamento = cd.id_departamento", "inner")
			->join('usuario us', 'sl.id_solicitante = us.id', 'inner')
			->join('usuario us2', 'sl.id_reciclatendero = us2.id', 'left')
			->where(['sl.id' => $order_id]);
		$res = $this->db->get();
		if ($res->num_rows() == 1) {
			$this->current_order = $res->result()[0];
			return $this->current_order;
		}
		$orders = [];
		foreach ($res->result() as $row) {
			array_push($orders, $row);
		}
		return $orders;
	}

	public function get_order_details($order_id) {
		$this->db->select('ds.id, ds.valor, ds.cantidad, cg.nombre AS nombre_categoria, ' .
			'cg.precio, tc.nombre AS nombre_tipo, md.nombre AS medida')
		->from('detalle_solicitud ds')
		->join("categoria cg", "cg.id = ds.id_categoria", "inner")
		->join("tipo_categoria tc", "tc.id = cg.id_tipo", "inner")
		->join("medida md", "md.id = cg.id_medida", "inner")
		->where(['ds.id_solicitud' => $order_id]);
		$res = $this->db->get();
		$order_details = [];
		foreach ($res->result() as $row) {
			array_push($order_details, $row);
		}
		return $order_details;
	}

	public function get_unassigned_orders() {
		$this->db
			->select('sl.id, sl.fecha, sl.id_solicitante, us.nombre AS nombre_cliente, us.apellido AS apellido_cliente, ' .
				'sl.id_reciclatendero, us2.nombre AS nombre_recicla_tendero, us2.apellido AS apellido_recicla_tendero, ' .
				'us.direccion, pf.nombre AS nombre_perfil, ' .
				'sl.comentario, sl.ciudades_id AS id_ciudad, cd.nombre AS ciudad, dp.iddepartamento AS id_departamento, ' .
				'dp.nombre AS departamento')
			->from('solicitud sl')
			->join("ciudades cd", "cd.id = sl.ciudades_id", "inner")
			->join("departamento dp", "dp.iddepartamento = cd.id_departamento", "inner")
			->join('usuario us', 'sl.id_solicitante = us.id', 'inner')
			->join('perfil pf', 'us.id_perfil = pf.id', 'inner')
			->join('usuario us2', 'sl.id_reciclatendero = us2.id', 'left')
			->where(['sl.id_reciclatendero' => null]);
		$res = $this->db->get();
		$orders = [];
		foreach ($res->result() as $row) {
			array_push($orders, $row);
		}
		return $orders;
	}

	public function get_user_orders($user_id, $get_recycler = false) {
		$where = ['sl.id_solicitante' => $user_id];
		if ($get_recycler) {
			$where = ['sl.id_reciclatendero' => $user_id];
		}
		$this->db
			->select('sl.id, sl.fecha, sl.id_solicitante, us.nombre AS nombre_cliente, us.apellido AS apellido_cliente, sl.fecha_recogida, ' .
				'sl.id_reciclatendero, us2.nombre AS nombre_recicla_tendero, us2.apellido AS apellido_recicla_tendero, ' .
				'sl.comentario, sl.ciudades_id AS id_ciudad, cd.nombre AS ciudad, dp.iddepartamento AS id_departamento, dp.nombre AS departamento')
			->from('solicitud sl')
			->join("ciudades cd", "cd.id = sl.ciudades_id", "inner")
			->join("departamento dp", "dp.iddepartamento = cd.id_departamento", "inner")
			->join('usuario us', 'sl.id_solicitante = us.id', 'inner')
			->join('usuario us2', 'sl.id_reciclatendero = us2.id', 'left')
			->where($where)
			->order_by('sl.fecha', 'DESC');
		$res = $this->db->get();
		$orders = [];
		foreach ($res->result() as $row) {
			array_push($orders, $row);
		}
		return $orders;
	}

	public function set_as_completed($order_id) {
		$date = new DateTime('now', new DateTimeZone('America/Bogota') );
		$data = [
			'id_estado_solicitud' => 3,
			'fecha_recogida' => $date->format('Y-m-d H:i:s')
		];
		$this->db->where(['id' => $order_id]);
		$this->db->update('solicitud', $data);
		$route_id = $this->get_order_route_id($order_id);
		$this->update_route_status($route_id);
		return $this->db->affected_rows() > 0;
	}

	public function get_order_route_id($order_id) {
		$this->db->select('sr.id_ruta')
			->from('solicitudes_ruta sr')
			->join('solicitud sl', 'sl.id = sr.id_solicitud')
			->where(['sl.id' => $order_id]);
		$res = $this->db->get();
		$routes = [];
		foreach ($res->result() as $row) {
			array_push($routes, $row);
		}
		return $routes[0]->id_ruta;
	}

	public function update_route_status($route_id) {
		$this->db->select('sl.id, sl.id_estado_solicitud')
			->from('solicitudes_ruta sr')
			->join('solicitud sl', 'sl.id = sr.id_solicitud')
			->where(['sr.id_ruta' => $route_id]);
		$res = $this->db->get();
		$order_status = [];
		foreach ($res->result() as $row) {
			if ( in_array($row->id_estado_solicitud, $order_status) ) {
				continue;
			}
			array_push($order_status, $row->id_estado_solicitud * 1);
		}
		if (count($order_status) == 1 && $order_status[0] == 3) {
			$this->db->where(['id' => $route_id]);
			$this->db->update('ruta', ['id_estado' => 3]);
			return true;
		}
		return false;
	}
}
