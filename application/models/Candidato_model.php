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

    public function listar_para_administracao($filtros, $limite, $offset)
    {
        $this->consultar_para_administracao($filtros);
        $this->db->select([
            'c.codigo',
            'c.nome_completo',
            'c.email',
            'c.telefone',
            'c.status',
            'c.cadastro'
        ]);
        $this->db->order_by('c.cadastro', 'DESC');
        $this->db->order_by('c.codigo', 'DESC');
        $this->db->limit($limite, $offset);

        return $this->db->get()->result_array();
    }

    public function contar_para_administracao($filtros)
    {
        $this->consultar_para_administracao($filtros);
        $this->db->select('c.codigo');

        return $this->db->count_all_results();
    }

    private function consultar_para_administracao($filtros)
    {
        $this->db->from($this->tabela . ' c');
        $this->db->where('c.exclusao IS NULL', NULL, FALSE);

        if ($filtros['termo'] !== '') {
            $this->db->group_start();
            $this->db->like('c.nome_completo', $filtros['termo']);
            $this->db->or_like('c.email', $filtros['termo']);
            $this->db->or_like('c.telefone', $filtros['termo']);
            $termo = $this->db->escape('%' . $filtros['termo'] . '%');
            $this->db->or_where(
                'EXISTS (
                    SELECT 1
                    FROM candidato_formularios cf
                    INNER JOIN candidato_grupos cg
                        ON cg.candidato_formulario_codigo = cf.codigo
                    INNER JOIN candidato_respostas cr
                        ON cr.candidato_grupo_codigo = cg.codigo
                    WHERE cf.candidato_codigo = c.codigo
                      AND cf.codigo = (
                        SELECT MAX(cf_atual.codigo)
                        FROM candidato_formularios cf_atual
                        WHERE cf_atual.candidato_codigo = c.codigo
                      )
                      AND (
                        cr.valor_texto LIKE ' . $termo . '
                        OR cr.valor_json LIKE ' . $termo . '
                        OR CAST(cr.valor_numero AS CHAR) LIKE ' . $termo . '
                        OR CAST(cr.valor_data AS CHAR) LIKE ' . $termo . '
                        OR CAST(cr.valor_booleano AS CHAR) LIKE ' . $termo . '
                      )
                )',
                NULL,
                FALSE
            );
            $this->db->group_end();
        }

        if ($filtros['status'] !== '') {
            $this->db->where('c.status', $filtros['status']);
        }

        if ($filtros['data_inicio'] !== '') {
            $this->db->where(
                'c.cadastro >=',
                $filtros['data_inicio'] . ' 00:00:00'
            );
        }

        if ($filtros['data_fim'] !== '') {
            $this->db->where(
                'c.cadastro <=',
                $filtros['data_fim'] . ' 23:59:59'
            );
        }
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

    public function buscar_senha_por_codigo($codigo)
    {
        $this->db->select('senha');
        $this->db->where('codigo', $codigo);
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
