<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Usuario_recuperacao_model extends CI_Model
{
    private $tabela = 'usuario_recuperacoes';

    public function solicitar($usuario_codigo, $token_hash)
    {
        $this->db->where('usuario_codigo', $usuario_codigo);
        $this->db->where('cadastro >=', date('Y-m-d H:i:s', time() - 3600));

        if ($this->db->count_all_results($this->tabela) >= 3) {
            return FALSE;
        }

        return $this->db->insert($this->tabela, [
            'usuario_codigo' => $usuario_codigo,
            'token_hash' => $token_hash,
            'expira_em' => date('Y-m-d H:i:s', time() + 3600),
            'cadastro' => date('Y-m-d H:i:s')
        ]);
    }

    public function buscar_valido($token_hash)
    {
        $this->db->where('token_hash', $token_hash);
        $this->db->where('expira_em >', date('Y-m-d H:i:s'));
        $this->db->where('utilizado_em IS NULL', NULL, FALSE);
        $this->db->limit(1);

        return $this->db->get($this->tabela)->row_array();
    }

    public function consumir($token_hash, $usuario_codigo)
    {
        $this->db->where('token_hash', $token_hash);
        $this->db->where('usuario_codigo', $usuario_codigo);
        $this->db->where('expira_em >', date('Y-m-d H:i:s'));
        $this->db->where('utilizado_em IS NULL', NULL, FALSE);
        $this->db->update($this->tabela, ['utilizado_em' => date('Y-m-d H:i:s')]);

        return $this->db->affected_rows() === 1;
    }

    public function invalidar_outros($usuario_codigo)
    {
        $this->db->where('usuario_codigo', $usuario_codigo);
        $this->db->where('utilizado_em IS NULL', NULL, FALSE);

        return $this->db->update($this->tabela, ['utilizado_em' => date('Y-m-d H:i:s')]);
    }
}
