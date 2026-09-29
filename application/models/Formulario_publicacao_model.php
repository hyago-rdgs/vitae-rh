<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Formulario_publicacao_model extends CI_Model
{
    private $tabela = 'formulario_publicacoes';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function listar_por_formulario($formulario_codigo)
    {
        $this->db->select([
            'p.codigo',
            'p.formulario_codigo',
            'p.versao',
            'p.observacao',
            'p.usuario_codigo',
            'p.cadastro',
            'u.nome AS usuario_nome'
        ]);
        $this->db->from($this->tabela . ' p');
        $this->db->join(
            'usuarios u',
            'u.codigo = p.usuario_codigo',
            'LEFT'
        );
        $this->db->where('p.formulario_codigo', $formulario_codigo);
        $this->db->order_by('p.versao', 'DESC');

        return $this->db->get()->result_array();
    }

    public function buscar_atual($formulario_codigo)
    {
        $this->db->select([
            'p.codigo',
            'p.formulario_codigo',
            'p.versao',
            'p.observacao',
            'p.usuario_codigo',
            'p.cadastro',
            'u.nome AS usuario_nome'
        ]);
        $this->db->from($this->tabela . ' p');
        $this->db->join(
            'usuarios u',
            'u.codigo = p.usuario_codigo',
            'LEFT'
        );
        $this->db->where('p.formulario_codigo', $formulario_codigo);
        $this->db->order_by('p.versao', 'DESC');
        $this->db->limit(1);

        return $this->db->get()->row_array();
    }

    public function proxima_versao($formulario_codigo)
    {
        $this->db->select_max('versao');
        $this->db->where('formulario_codigo', $formulario_codigo);

        $resultado = $this->db->get($this->tabela)->row_array();

        return (int) ($resultado['versao'] ?? 0) + 1;
    }

    public function cadastrar($reg)
    {
        $reg['cadastro'] = date('Y-m-d H:i:s');

        if (!$this->db->insert($this->tabela, $reg)) {
            return FALSE;
        }

        return $this->db->insert_id();
    }

    public function buscar_por_codigo($codigo)
    {
        $this->db->select([
            'codigo',
            'formulario_codigo',
            'versao',
            'estrutura',
            'observacao',
            'usuario_codigo',
            'cadastro'
        ]);
        $this->db->where('codigo', $codigo);

        return $this->db->get($this->tabela, 1)->row_array();
    }
}
