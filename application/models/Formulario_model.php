<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Formulario_model extends CI_Model
{
    private $tabela = 'formularios';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function listar()
    {
        return $this->db->where('exclusao IS NULL', NULL, FALSE)
            ->order_by('nome')->get($this->tabela)->result_array();
    }

    public function cadastrar($dados)
    {
        $dados['chave'] = 'form_' . bin2hex(random_bytes(12));
        return $this->db->insert($this->tabela, $dados) ? $this->db->insert_id() : FALSE;
    }

    public function buscar($codigo = NULL)
    {
        $this->db->select([
            'codigo',
            'chave',
            'nome',
            'descricao',
            'cadastro',
            'atualizacao'
        ]);
        if ($codigo !== NULL) $this->db->where('codigo', (int) $codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);
        $this->db->order_by('codigo', 'ASC');

        return $this->db->get($this->tabela, 1)->row_array();
    }

    public function atualizar($codigo, $reg)
    {
        $reg['atualizacao'] = date('Y-m-d H:i:s');

        $this->db->where('codigo', $codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        return $this->db->update($this->tabela, $reg);
    }

    public function bloquear_para_publicacao($codigo)
    {
        $sql = 'SELECT codigo FROM ' . $this->tabela . '
            WHERE codigo = ? AND exclusao IS NULL
            FOR UPDATE';

        return $this->db->query($sql, [(int) $codigo])->row_array();
    }
}
