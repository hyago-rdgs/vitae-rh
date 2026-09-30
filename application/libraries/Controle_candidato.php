<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Controle_candidato
{
    private $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->library('session');
    }

    public function criar($candidato)
    {
        $this->CI->session->sess_regenerate(TRUE);
        $this->CI->session->set_userdata('candidato', [
            'codigo' => (int) $candidato['codigo'],
            'assinatura_senha' => hash('sha256', $candidato['senha']),
            'logado' => TRUE
        ]);
    }

    public function buscar()
    {
        $sessao = $this->CI->session->userdata('candidato');

        if (!is_array($sessao) || empty($sessao['codigo']) ||
            ($sessao['logado'] ?? FALSE) !== TRUE) {
            return FALSE;
        }

        $this->CI->load->model('candidato_model');
        $candidato = $this->CI->candidato_model->buscar_por_codigo((int) $sessao['codigo']);
        $senha = $candidato ? $this->CI->candidato_model
            ->buscar_senha_por_codigo((int) $sessao['codigo']) : FALSE;

        if (!$candidato || $candidato['status'] !== 'ativo' || !$senha ||
            !is_string($sessao['assinatura_senha'] ?? NULL) ||
            !hash_equals($sessao['assinatura_senha'], hash('sha256', $senha['senha']))) {
            $this->destruir();
            return FALSE;
        }

        return $candidato;
    }

    public function exigir()
    {
        $candidato = $this->buscar();

        if (!$candidato) {
            redirect('candidato/login');
            exit;
        }

        return $candidato;
    }

    public function destruir()
    {
        // A sessão administrativa, se existir no mesmo navegador, permanece intacta.
        $this->CI->session->unset_userdata('candidato');
    }
}
