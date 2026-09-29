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

    public function buscar()
    {
        $this->db->select([
            'codigo',
            'chave',
            'nome',
            'descricao',
            'cadastro',
            'atualizacao'
        ]);
        $this->db->where('chave', 'perfil_candidato');
        $this->db->where('exclusao IS NULL', NULL, FALSE);

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
