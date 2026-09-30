<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Candidato_model extends CI_Model
{
    private $tabela = 'candidatos';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function buscar_por_codigo($codigo)
    {
        $this->db->select([
            'codigo',
            'nome_completo',
            'email',
            'telefone',
            'foto_arquivo',
            'curriculo_arquivo',
            'curriculo_nome_original',
            'curriculo_mime',
            'curriculo_tamanho',
            'consentimento_lgpd',
            'consentimento_lgpd_versao',
            'consentimento_lgpd_em',
            'status',
            'ultimo_acesso',
            'cadastro',
            'atualizacao'
        ]);
        $this->db->where('codigo', $codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);
        $this->db->limit(1);

        return $this->db->get($this->tabela)->row_array();
    }

    public function buscar_para_autenticacao($email)
    {
        $this->db->select([
            'codigo',
            'nome_completo',
            'email',
            'senha',
            'status'
        ]);
        $this->db->where('email', trim($email));
        $this->db->where('status', 'ativo');
        $this->db->where('exclusao IS NULL', NULL, FALSE);
        $this->db->limit(1);

        return $this->db->get($this->tabela)->row_array();
    }

    public function email_em_uso($email, $codigo = NULL)
    {
        $this->db->where('email', trim($email));

        if ($codigo !== NULL) {
            $this->db->where('codigo !=', $codigo);
        }

        return $this->db->count_all_results($this->tabela) > 0;
    }

    public function cadastrar($reg)
    {
        $reg['email'] = trim($reg['email']);
        $reg['cadastro'] = date('Y-m-d H:i:s');

        if (!$this->db->insert($this->tabela, $reg)) {
            return FALSE;
        }

        return $this->db->insert_id();
    }

    public function atualizar($codigo, $reg)
    {
        if (isset($reg['email'])) {
            $reg['email'] = trim($reg['email']);
        }

        $reg['atualizacao'] = date('Y-m-d H:i:s');

        $this->db->where('codigo', $codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        return $this->db->update($this->tabela, $reg);
    }

    public function atualizar_ultimo_acesso($codigo)
    {
        $this->db->where('codigo', $codigo);
        $this->db->where('status', 'ativo');
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        return $this->db->update(
            $this->tabela,
            ['ultimo_acesso' => date('Y-m-d H:i:s')]
        );
    }

    public function excluir($codigo)
    {
        $agora = date('Y-m-d H:i:s');

        $this->db->where('codigo', $codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        if (!$this->db->update(
            $this->tabela,
            [
                'status' => 'inativo',
                'atualizacao' => $agora,
                'exclusao' => $agora
            ]
        )) {
            return FALSE;
        }

        return $this->db->affected_rows() > 0;
    }
}
