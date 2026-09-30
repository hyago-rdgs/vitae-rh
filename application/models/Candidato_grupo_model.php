<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Candidato_grupo_model extends CI_Model
{
    private $tabela = 'candidato_grupos';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function listar_por_candidato_formulario(
        $candidato_formulario_codigo
    )
    {
        $this->db->select([
            'codigo',
            'candidato_formulario_codigo',
            'grupo_codigo',
            'indice',
            'cadastro',
            'atualizacao'
        ]);
        $this->db->where(
            'candidato_formulario_codigo',
            $candidato_formulario_codigo
        );
        $this->db->order_by('grupo_codigo', 'ASC');
        $this->db->order_by('indice', 'ASC');

        return $this->db->get($this->tabela)->result_array();
    }

    public function buscar_por_codigo($codigo)
    {
        $this->db->select([
            'codigo',
            'candidato_formulario_codigo',
            'grupo_codigo',
            'indice',
            'cadastro',
            'atualizacao'
        ]);
        $this->db->where('codigo', $codigo);
        $this->db->limit(1);

        return $this->db->get($this->tabela)->row_array();
    }

    public function buscar_por_ocorrencia(
        $candidato_formulario_codigo,
        $grupo_codigo,
        $indice
    ) {
        $this->db->where(
            'candidato_formulario_codigo',
            $candidato_formulario_codigo
        );
        $this->db->where('grupo_codigo', $grupo_codigo);
        $this->db->where('indice', $indice);
        $this->db->limit(1);

        return $this->db->get($this->tabela)->row_array();
    }

    public function cadastrar($reg)
    {
        $reg['indice'] = $reg['indice'] ?? 1;
        $reg['cadastro'] = date('Y-m-d H:i:s');

        if (!$this->db->insert($this->tabela, $reg)) {
            return FALSE;
        }

        return $this->db->insert_id();
    }

    public function atualizar($codigo, $reg)
    {
        $reg['atualizacao'] = date('Y-m-d H:i:s');

        $this->db->where('codigo', $codigo);

        return $this->db->update($this->tabela, $reg);
    }
}
