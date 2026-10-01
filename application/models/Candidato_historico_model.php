<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Candidato_historico_model extends CI_Model
{
    private $situacoes = [
        'recebido' => 'Recebido',
        'em_analise' => 'Em análise',
        'contatado' => 'Contatado',
        'selecionado' => 'Selecionado',
        'nao_selecionado' => 'Não selecionado',
        'banco_talentos' => 'Banco de talentos'
    ];

    public function situacoes()
    {
        return $this->situacoes;
    }

    public function situacao_valida($situacao)
    {
        return is_string($situacao) && isset($this->situacoes[$situacao]);
    }

    public function listar_por_candidato($candidato_codigo)
    {
        $this->db->select([
            'h.codigo',
            'h.tipo',
            'h.situacao_anterior',
            'h.situacao_nova',
            'h.anotacao',
            'h.cadastro',
            'u.nome AS usuario_nome'
        ]);
        $this->db->from('candidato_historicos h');
        $this->db->join('usuarios u', 'u.codigo = h.usuario_codigo', 'LEFT');
        $this->db->where('h.candidato_codigo', $candidato_codigo);
        $this->db->order_by('h.codigo', 'DESC');
        $this->db->limit(100);

        return $this->db->get()->result_array();
    }

    public function registrar_situacao($candidato_codigo, $usuario_codigo, $anterior, $nova)
    {
        return $this->db->insert('candidato_historicos', [
            'candidato_codigo' => $candidato_codigo,
            'usuario_codigo' => $usuario_codigo,
            'tipo' => 'situacao',
            'situacao_anterior' => $anterior,
            'situacao_nova' => $nova,
            'cadastro' => date('Y-m-d H:i:s')
        ]);
    }

    public function registrar_anotacao($candidato_codigo, $usuario_codigo, $anotacao)
    {
        return $this->db->insert('candidato_historicos', [
            'candidato_codigo' => $candidato_codigo,
            'usuario_codigo' => $usuario_codigo,
            'tipo' => 'anotacao',
            'anotacao' => $anotacao,
            'cadastro' => date('Y-m-d H:i:s')
        ]);
    }
}
