<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Candidato_formulario_model extends CI_Model
{
    private $tabela = 'candidato_formularios';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function buscar_por_candidato($candidato_codigo)
    {
        $this->db->select([
            'cf.codigo',
            'cf.candidato_codigo',
            'cf.formulario_publicacao_codigo',
            'cf.status',
            'cf.iniciado_em',
            'cf.concluido_em',
            'cf.cadastro',
            'cf.atualizacao',
            'fp.versao AS formulario_versao'
        ]);
        $this->db->from($this->tabela . ' cf');
        $this->db->join(
            'formulario_publicacoes fp',
            'fp.codigo = cf.formulario_publicacao_codigo',
            'INNER'
        );
        $this->db->where('cf.candidato_codigo', $candidato_codigo);
        $this->db->order_by('cf.codigo', 'DESC');
        $this->db->limit(1);

        return $this->db->get()->row_array();
    }

    public function buscar_por_codigo($codigo)
    {
        $this->db->select([
            'codigo',
            'candidato_codigo',
            'formulario_publicacao_codigo',
            'status',
            'iniciado_em',
            'concluido_em',
            'cadastro',
            'atualizacao'
        ]);
        $this->db->where('codigo', $codigo);
        $this->db->limit(1);

        return $this->db->get($this->tabela)->row_array();
    }

    public function cadastrar($reg)
    {
        $agora = date('Y-m-d H:i:s');

        $reg['status'] = $reg['status'] ?? 'rascunho';
        $reg['iniciado_em'] = $reg['iniciado_em'] ?? $agora;
        $reg['cadastro'] = $agora;

        if (!$this->db->insert($this->tabela, $reg)) {
            return FALSE;
        }

        return $this->db->insert_id();
    }

    public function atualizar_status($codigo, $status)
    {
        $this->db->where('codigo', $codigo);

        return $this->db->update(
            $this->tabela,
            [
                'status' => $status,
                'atualizacao' => date('Y-m-d H:i:s')
            ]
        );
    }

    public function concluir($codigo)
    {
        $agora = date('Y-m-d H:i:s');

        $this->db->where('codigo', $codigo);

        return $this->db->update(
            $this->tabela,
            [
                'status' => 'concluido',
                'concluido_em' => $agora,
                'atualizacao' => $agora
            ]
        );
    }
}
