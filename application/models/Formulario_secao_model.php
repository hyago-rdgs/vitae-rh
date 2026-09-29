<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Formulario_secao_model extends CI_Model
{
    private $tabela = 'formulario_secoes';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function listar_por_formulario($formulario_codigo)
    {
        $this->db->select([
            's.codigo',
            's.formulario_codigo',
            's.titulo',
            's.descricao',
            's.ordem',
            's.ativo',
            's.cadastro',
            's.atualizacao',
            '(SELECT COUNT(*) FROM formulario_grupos g
                WHERE g.secao_codigo = s.codigo
                AND g.exclusao IS NULL) AS total_grupos'
        ], FALSE);
        $this->db->from($this->tabela . ' s');
        $this->db->where('s.formulario_codigo', $formulario_codigo);
        $this->db->where('s.exclusao IS NULL', NULL, FALSE);
        $this->db->order_by('s.ordem', 'ASC');
        $this->db->order_by('s.codigo', 'ASC');

        return $this->db->get()->result_array();
    }

    public function buscar_por_codigo($codigo)
    {
        $this->db->select([
            'codigo',
            'formulario_codigo',
            'titulo',
            'descricao',
            'ordem',
            'ativo',
            'cadastro',
            'atualizacao'
        ]);
        $this->db->where('codigo', $codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        return $this->db->get($this->tabela, 1)->row_array();
    }

    public function titulo_em_uso($formulario_codigo, $titulo, $codigo = NULL)
    {
        $this->db->where('formulario_codigo', $formulario_codigo);
        $this->db->where('titulo', $titulo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        if ($codigo !== NULL) {
            $this->db->where('codigo !=', $codigo);
        }

        return $this->db->count_all_results($this->tabela) > 0;
    }

    public function cadastrar($reg)
    {
        $reg['ordem'] = $this->proxima_ordem(
            $reg['formulario_codigo']
        );
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
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        return $this->db->update($this->tabela, $reg);
    }

    public function atualizar_ordem($codigo, $ordem)
    {
        return $this->atualizar(
            $codigo,
            ['ordem' => (int) $ordem]
        );
    }

    public function buscar_adjacente($secao, $direcao)
    {
        $this->db->select([
            'codigo',
            'formulario_codigo',
            'titulo',
            'descricao',
            'ordem',
            'ativo'
        ]);
        $this->db->where(
            'formulario_codigo',
            $secao['formulario_codigo']
        );
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        if ($direcao === 'subir') {
            $this->db->where('ordem <', $secao['ordem']);
            $this->db->order_by('ordem', 'DESC');
        } else {
            $this->db->where('ordem >', $secao['ordem']);
            $this->db->order_by('ordem', 'ASC');
        }

        $this->db->order_by('codigo', 'ASC');

        return $this->db->get($this->tabela, 1)->row_array();
    }

    public function possui_grupos($codigo)
    {
        $this->db->where('secao_codigo', $codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        return $this->db->count_all_results('formulario_grupos') > 0;
    }

    public function excluir($codigo)
    {
        $dados = [
            'ativo' => 0,
            'atualizacao' => date('Y-m-d H:i:s'),
            'exclusao' => date('Y-m-d H:i:s')
        ];

        $this->db->where('codigo', $codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        if (!$this->db->update($this->tabela, $dados)) {
            return FALSE;
        }

        return $this->db->affected_rows() > 0;
    }

    private function proxima_ordem($formulario_codigo)
    {
        $this->db->select_max('ordem');
        $this->db->where('formulario_codigo', $formulario_codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        $resultado = $this->db->get($this->tabela)->row_array();

        return (int) ($resultado['ordem'] ?? 0) + 1;
    }
}
