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
        $this->db->where('p.exclusao IS NULL', NULL, FALSE);
        $this->db->order_by('p.versao', 'DESC');

        return $this->db->get()->result_array();
    }

    public function buscar_atual($formulario_codigo)
    {
        $this->db->select([
            'p.codigo',
            'p.formulario_codigo',
            'p.versao',
            'p.estrutura',
            'p.observacao',
            'p.usuario_codigo',
            'p.cadastro',
            'u.nome AS usuario_nome'
        ]);
        $this->db->from($this->tabela . ' p');
        $this->db->join('formulario_vigencia v', 'v.publicacao_codigo = p.codigo AND v.codigo = 1', 'INNER');
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

    public function buscar_vigente()
    {
        return $this->db->select('p.*')->from($this->tabela . ' p')
            ->join('formulario_vigencia v', 'v.publicacao_codigo = p.codigo AND v.codigo = 1')
            ->where('p.exclusao IS NULL', NULL, FALSE)->get()->row_array();
    }

    public function conferir_vigente($codigo)
    {
        $reg = $this->db->query('SELECT publicacao_codigo FROM formulario_vigencia WHERE codigo = 1 FOR UPDATE')->row_array();
        return $reg && (int) $reg['publicacao_codigo'] === (int) $codigo;
    }

    public function gerenciar($codigo, $excluir = FALSE)
    {
        $this->db->trans_begin();
        $vigencia = $this->db->query('SELECT publicacao_codigo FROM formulario_vigencia WHERE codigo = 1 FOR UPDATE')->row_array();
        $publicacao = $this->db->where('codigo', $codigo)->where('exclusao IS NULL', NULL, FALSE)
            ->get($this->tabela)->row_array();
        if (!$vigencia || !$publicacao) {
            $this->db->trans_rollback();
            return FALSE;
        }
        if ($excluir) {
            $ok = $this->db->where('codigo', $codigo)->update($this->tabela, ['exclusao' => date('Y-m-d H:i:s')]);
            if ((int) $vigencia['publicacao_codigo'] === (int) $codigo) {
                $ok = $ok && $this->db->where('codigo', 1)->update('formulario_vigencia', ['publicacao_codigo' => NULL]);
            }
        } else {
            $ok = $this->db->where('codigo', 1)->update('formulario_vigencia', ['publicacao_codigo' => $codigo]);
        }
        if (!$ok || !$this->db->trans_status()) {
            $this->db->trans_rollback();
            return FALSE;
        }
        return $this->db->trans_commit();
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
            'exclusao',
            'observacao',
            'usuario_codigo',
            'cadastro'
        ]);
        $this->db->where('codigo', $codigo);

        return $this->db->get($this->tabela, 1)->row_array();
    }
}
