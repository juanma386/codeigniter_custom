<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Usuario extends CI_Controller {

	// use UsuarioRule;
	

	public function __construct()
	{
		parent::__construct();
		
		$this->load->helper('form_ci');
		$this->load->library('form_validation');
		$this->load->library('rules/Usuario_rule');		
		$this->load->library('Utils');
		// $this->load->library('lib_log');
		$this->load->model('usuario_model');

	}

	public function index()
	{
		$data = array();		
		$data["usuarios"] = $this->usuario_model->listUsuario();
		$this->load->view('usuario/index', $data);
	}

	public function lista()
	{
		$data = array();		
		$data["usuarios"] = $this->usuario_model->listUsuario();
		$this->load->view('usuario/listaUsuarios', $data);
	}

	public function listaAjax()
	{
		$usuario = $this->input->post('usuario');

		$data = array();

		if ( $usuario != null && sizeof($usuario)>0 && $usuario['label']!='' && $usuario['text']!='' )
		{			
			$data["usuarios"] = $this->usuario_model->searchUsuario($usuario['label'], $usuario['text']);
		} else
		{
			$data["usuarios"] = $this->usuario_model->listUsuario();
		}

		$data["result"] = 1;
		$this->utils->json($data);
	}


	public function createUsuario()
	{
		$usuario = $this->input->post("usuario");

		$this->form_validation->set_rules($this->usuario_rule->apply());
		$this->form_validation->set_rules('usuario[cuenta]', 'Cuenta', 'callback__verificar_cuenta_repetida');
		$this->form_validation->set_rules('usuario[rep_password]', 'Repetir Contraseña', 'trim|required|callback__validar_repetir_password['.$usuario["password"].']');

		if ( $this->form_validation->run() ) 
		{
			unset($usuario["rep_password"]);
			$usuario["password"] = md5($usuario["password"]);

			$this->usuario_model->insert($usuario);
			$this->session->set_flashdata('message', [ "success"=>"Se creo el usuario" ]);
			redirect('usuario/lista');
		} else
		{			
			$this->session->set_flashdata('message', [ "error"=>validation_errors() ]);
			$this->lista();
		}

	}

	

	public function editUsuario()
	{
		$usuario = $this->input->post("usuario");

		$this->form_validation->set_rules($this->usuario_rule->apply());
		$this->form_validation->set_rules('usuario[id_usuario]', 'ID', 'trim|required|callback__verificar_id_usuario');

		if ( isset($usuario["password"]) && $usuario["password"] != '')
		{
			$this->form_validation->set_rules('usuario[password]', 'Contraseña', 'trim|required');			
			$this->form_validation->set_rules('usuario[rep_password]', 'Repetir Contraseña', 'trim|required|callback__validar_repetir_password['.$usuario["password"].']');		
			$usuario["password"] = md5($usuario["password"]);
				
		} else
		{
			unset($usuario["password"]);
		}
		

		if ( $this->form_validation->run() )
		{
	
			unset($usuario["rep_password"]);			

			$this->usuario_model->updateById( $usuario, $usuario["id_usuario"]);
			$this->session->set_flashdata('message', [ "success"=>"Se edito el registro" ]);
		} else
		{
			$this->session->set_flashdata('message', [ "error"=>validation_errors() ]);

		}
		redirect('usuario/lista','refresh');

	}

	

	public function desconectar()
	{
		$this->session->sess_destroy();
		$this->config->load('SystemSupervisor/config');
		redirect($this->config->item('page_login'),'refresh');
	}




























	public function getUsuarioAjax()
	{
		$data = array();

		$usuario = $this->input->post("usuario");
		$data["usuario"] = $this->usuario_model->getById($usuario["id_usuario"]);  

		$this->output
        ->set_status_header(200)
        ->set_content_type('application/json', 'utf-8')
        ->set_output(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
        ->_display();
		exit;
	}

	public function createAjax()
	{
		$usuario = $this->input->post("usuario");

		$this->form_validation->set_rules($this->usuario_rule->apply());
		$this->form_validation->set_rules('usuario[cuenta]', 'Cuenta', 'callback__verificar_cuenta_repetida');
		$this->form_validation->set_rules('usuario[rep_password]', 'Repetir Contraseña', 'trim|required|callback__validar_repetir_password['.$usuario["password"].']');

		if ( $this->form_validation->run() ) 
		{
			unset($usuario["rep_password"]);
			$usuario["password"] = md5($usuario["password"]);

			$this->usuario_model->insert($usuario);
			$data['result'] = 1;
			$data['message'] = "Se creo el usuario";
			
		} else
		{						
			$data['result'] = 0;
			$data['message'] = validation_errors();
		}

		$this->utils->json($data);
	}

	public function editAjax()
	{
		$usuario = $this->input->post("usuario");

		$this->form_validation->set_rules($this->usuario_rule->apply());
		$this->form_validation->set_rules('usuario[cuenta]', 'Cuenta', 'trim|required|callback__verificar_cuenta_repetida['.$usuario['id_usuario'].']');
		$this->form_validation->set_rules('usuario[id_usuario]', 'ID', 'trim|required|callback__verificar_id_usuario');

		if ( isset($usuario["password"]) && $usuario["password"] != '')
		{
			$this->form_validation->set_rules('usuario[password]', 'Contraseña', 'trim|required');			
			$this->form_validation->set_rules('usuario[rep_password]', 'Repetir Contraseña', 'trim|required|callback__validar_repetir_password['.$usuario["password"].']');		
			$usuario["password"] = md5($usuario["password"]);
				
		} else
		{
			unset($usuario["password"]);
		}
		

		if ( $this->form_validation->run() )
		{
	
			unset($usuario["rep_password"]);			

			$this->usuario_model->updateById( $usuario, $usuario["id_usuario"] );

			$data['result'] = 1;
			$data['message'] = "Se edito el registro";
		} else
		{
			
			$data['result'] = 0;
			$data['message'] = validation_errors();
		}
		$this->utils->json($data);
	}

	public function deleteUsuarioAjax()
	{
		$usuario = $this->input->post("usuario");
		$data = array();

		$this->form_validation->set_rules('usuario[id_usuario]', 'ID', 'trim|required|callback__verificar_id_usuario');

		if ( $this->form_validation->run() )
		{
			$this->usuario_model->deleteById($usuario["id_usuario"]);
			$data["result"] = 1;
		} else
		{
			$data["result"] = 0;
			$data["msg"] = validation_errors();
		}
		
		$this->utils->json($data);
	}

	public function getRelationWithRolAjax()
	{
		$data    = array();
		$usuario = $this->input->post('usuario');
		$rols    = $this->rol_model->getRolByUsuario( $usuario['id_usuario'] );
		$data['rols']   = $rols;
		$data['result'] = 1;
		$this->utils->json($data);	
	}

	public function assignRolToUsuarioAjax()
	{
		$this->load->model('rol_asignado_model');

		$data = array();
		$rols     = $this->input->post('rol');
		$usuario = $this->input->post('usuario');

		if ( $usuario['id_usuario']>0 )
		{
			$this->rol_asignado_model->deleteAllByUsuario( $usuario['id_usuario'] );

			foreach ($rols as $rol) {
				$this->rol_asignado_model->insert( [ 'id_usuario'=>$usuario['id_usuario'] , 'id_rol'=>$rol['id_rol'] ] );
			}
			$data['result'] = 1;
		} else
		{
			$data['result'] = 0;
		}

		$this->utils->json($data);

	}

	public function getRelationWithRutaAjax()
	{
		$data    = array();
		$usuario = $this->input->post('usuario');
		$rutas    = $this->ruta_model->getRutaByUsuario( $usuario['id_usuario'] );
		$data['rutas']  = $rutas;
		$data['result'] = 1;
		$this->utils->json($data);	
	}

	public function assignRutaToUsuarioAjax()
	{
		$this->load->model('ruta_asignado_model');

		$data = array();
		$rutas   = $this->input->post('ruta');
		$usuario = $this->input->post('usuario');

		if ( $usuario['id_usuario']>0 )
		{
			$this->ruta_asignado_model->deleteAllByUsuario( $usuario['id_usuario'] );

			foreach ($rutas as $ruta) {
				$this->ruta_asignado_model->insert( [ 'id_usuario'=>$usuario['id_usuario'] , 'id_ruta'=>$ruta['id_ruta'] ] );
			}
			$data['result'] = 1;
		} else
		{
			$data['result'] = 0;
		}

		$this->utils->json($data);

	}






















	public function _verificar_cuenta_repetida($cuenta, $idUsuario=0)
	{
		$this->form_validation->set_message(__FUNCTION__, 'Ya existe la Cuenta');
		if ( $idUsuario==0 )
		{
			$cantidad = $this->usuario_model->count( [ 'cuenta'=>$cuenta ] );
		} else
		{
			$cantidad = $this->usuario_model->count( [ 'cuenta'=>$cuenta, 'id_usuario!='=>$idUsuario ] );
		}
		return ( $cantidad==0 );
	}

	public function _validar_repetir_password($repPassword, $password)
	{
		$this->form_validation->set_message(__FUNCTION__, 'Repetir el mismo password escrito');
		return ($repPassword == $password);		
	}

	public function _verificar_id_usuario($idUsuario)
	{
		$this->form_validation->set_message(__FUNCTION__, 'No existe el identificador del usuario');
		if ($idUsuario > 0)
		{			
			$usuario = $this->usuario_model->getById($idUsuario);			
			return ( isset($usuario) );
		} else
		{
			return FALSE;
		}
	}


	

}

/* End of file usuario.php */
/* Location: ./application/controllers/usuario.php */