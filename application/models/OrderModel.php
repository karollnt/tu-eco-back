<?php
class OrderModel extends CI_Model {
	protected $current_order;

	public function __construct() {
		$this->current_order = null;
		$this->load->database();
	}

	public function create_order($user_id, $order_data) {
		$handler = $this->db->query("INSERT INTO `solicitud`(`id_solicitante`,`id_reciclatendero`, `comentario`, `ciudades_id`, `fecha`) " .
			"VALUES ({$user_id}, null, " . ($this->db->escape($order_data['comentario'])) . ", {$order_data['ciudades_id']}, " . ($this->db->escape($order_data['fecha_recogida'])) . ")");
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
				'sl.id_reciclatendero, us2.nombre AS nombre_recicla_tendero, us2.apellido AS apellido_recicla_tendero, ' .
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
				'sl.comentario, sl.ciudades_id AS id_ciudad, cd.nombre AS ciudad, dp.iddepartamento AS id_departamento, dp.nombre AS departamento')
			->from('solicitud sl')
			->join("ciudades cd", "cd.id = sl.ciudades_id", "inner")
			->join("departamento dp", "dp.iddepartamento = cd.id_departamento", "inner")
			->join('usuario us', 'sl.id_solicitante = us.id', 'inner')
			->join('usuario us2', 'sl.id_reciclatendero = us2.id', 'left')
			->where(['sl.id_reciclatendero' => null]);
		$res = $this->db->get();
		$orders = [];
		foreach ($res->result() as $row) {
			array_push($orders, $row);
		}
		return $orders;
	}

	public function get_user_orders($user_id) {
		$this->db
			->select('sl.id, sl.fecha, sl.id_solicitante, us.nombre AS nombre_cliente, us.apellido AS apellido_cliente, sl.fecha_recogida, ' .
				'sl.id_reciclatendero, us2.nombre AS nombre_recicla_tendero, us2.apellido AS apellido_recicla_tendero, ' .
				'sl.comentario, sl.ciudades_id AS id_ciudad, cd.nombre AS ciudad, dp.iddepartamento AS id_departamento, dp.nombre AS departamento')
			->from('solicitud sl')
			->join("ciudades cd", "cd.id = sl.ciudades_id", "inner")
			->join("departamento dp", "dp.iddepartamento = cd.id_departamento", "inner")
			->join('usuario us', 'sl.id_solicitante = us.id', 'inner')
			->join('usuario us2', 'sl.id_reciclatendero = us2.id', 'left')
			->where(['sl.id_solicitante' => $user_id])
			->order_by('sl.fecha', 'DESC');
		$res = $this->db->get();
		$orders = [];
		foreach ($res->result() as $row) {
			array_push($orders, $row);
		}
		return $orders;
	}
}
