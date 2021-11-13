<?php
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

class Category extends CI_Controller {
	public function __construct() {
		parent::__construct();
		$this->load->model('CategoryModel');
	}

	public function get_categories() {
		ob_start( 'ob_gzhandler' );
		header('Content-Type: application/json');
		$category_type = $this->input->get('tipo');
		$category_type = isset($category_type) ? $category_type : null;
		$categories = $this->CategoryModel->get_categories($category_type);
		echo(json_encode($categories));
	}

	public function create_category() {
		ob_start( 'ob_gzhandler' );
		header('Content-Type: application/json');
		$foto = $this->upload_category_image();
		$category_data = [
			'nombre' => trim($this->input->post('nombre')),
			'precio' => trim($this->input->post('precio')),
			'id_tipo' => trim($this->input->post('id_tipo')),
			'id_medida' => trim($this->input->post('id_medida')),
			'foto' => $foto,
			'id_marcas' => trim($this->input->post('id_marcas')),
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

	public function borrar_categoria() {
		$mensaje = "";
		$id = $this->input->post('id');
		$mensaje = $this->CategoryModel->borrar_categoria($id);
		$resp = array("msg"=>html_entity_decode($mensaje));
		echo json_encode($resp);
	}

	public function create_brand() {
		ob_start( 'ob_gzhandler' );
		header('Content-Type: application/json');
		$brand_data = [
			'nombre' => trim($this->input->post('nombre'))
		];
		$response = $this->CategoryModel->create_brand($brand_data);
		echo(json_encode($response));
	}

	public function create_measurement() {
		ob_start( 'ob_gzhandler' );
		header('Content-Type: application/json');
		$measurement_data = [
			'nombre' => trim($this->input->post('nombre'))
		];
		$response = $this->CategoryModel->create_measurement($measurement_data);
		echo(json_encode($response));
	}

	public function get_brands() {
		ob_start( 'ob_gzhandler' );
		header('Content-Type: application/json');
		$response = $this->CategoryModel->get_brands();
		echo(json_encode($response));
	}

	public function get_measurements() {
		ob_start( 'ob_gzhandler' );
		header('Content-Type: application/json');
		$response = $this->CategoryModel->get_measurements();
		echo(json_encode($response));
	}

	public function delete_measurement() {
		$mensaje = "";
		$id = $this->input->post('id');
		$mensaje = $this->CategoryModel->delete_measurement($id);
		$resp = array("msg"=>html_entity_decode($mensaje));
		echo json_encode($resp);
	}

	public function delete_brand() {
		$mensaje = "";
		$id = $this->input->post('id');
		$mensaje = $this->CategoryModel->delete_brand($id);
		$resp = array("msg"=>html_entity_decode($mensaje));
		echo json_encode($resp);
	}

	public function get_category_types() {
		ob_start( 'ob_gzhandler' );
		header('Content-Type: application/json');
		$response = $this->CategoryModel->get_category_types();
		echo(json_encode($response));
	}

	private function upload_category_image() {
		$path = './uploads/';
		$this->load->library('upload');
		$this->upload->initialize(array(
			"upload_path"       =>  $path,
			"allowed_types"     =>  "gif|jpg|png|jpeg|bmp|svg|ico",
			"max_size"          =>  '20000000',
			"max_width"         =>  '13684',
			"max_height"        =>  '13684'
		));
		if($this->upload->do_upload('image')) {
			$image = $this->upload->data();
			$file_data = [
				'file' => file_get_contents($image['full_path']),
				'extension' => $image['file_ext']
			];
			$response = $this->upload_image($file_data);
			unlink($image['full_path']);
			return $response;
		}
		return '';
	}

	private function random_str($length) {
		$keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_';
		$str = '';
		$max = strlen($keyspace) - 1;
		for ($i = 0; $i < $length; ++$i) {
			$str .= $keyspace[rand(0, $max)];
		}
		return $str;
	}

	private function upload_image($file_data) {
		$bucket_name = 'tuecofiles';
		$file_name = $this->random_str(48) . $file_data['extension'];
		$file_url = html_entity_decode('https://' . $bucket_name . '.s3.amazonaws.com/' . $file_name);
		$s3 = new S3Client([
			'version' => 'latest',
			'region'  => 'us-east-2',
			'credentials' => [
				'key' => getenv('S3_KEY'),
				'secret' => getenv('S3_SECRET')
			]
		]);
		try {
			$result = $s3->putObject([
				'Bucket' => $bucket_name,
				'Key'    => $file_name,
				'ACL'    => 'public-read',
				'Body'   => $file_data['file'],
				//'SourceFile' => 'c:\samplefile.png' -- use this if you want to upload a file from a local location
			]);
			return $result['ObjectURL'];
		} catch (S3Exception $e) {
			echo $e->getMessage() . PHP_EOL;
			return false;
		}
		return false;
	}
}
