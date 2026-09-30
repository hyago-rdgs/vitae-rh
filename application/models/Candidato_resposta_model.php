<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Candidato_resposta_model extends CI_Model
{
    private $tabela = 'candidato_respostas';

    private $colunas_valor = [
        'valor_texto',
        'valor_numero',
        'valor_data',
        'valor_booleano',
        'valor_json'
    ];

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
}
    public function listar_por_grupo($candidato_grupo_codigo)
    {
        $this->db->select([
            'codigo',
            'candidato_grupo_codigo',
            'campo_chave',
            'valor_texto',
            'valor_numero',
            'valor_data',
            'valor_booleano',
            'valor_json',
            'cadastro',
            'atualizacao'
        ]);
        $this->db->where('candidato_grupo_codigo', $candidato_grupo_codigo);
        $this->db->order_by('codigo', 'ASC');

        return $this->db->get($this->tabela)->result_array();
    }

    public function buscar_por_campo($candidato_grupo_codigo, $campo_chave)
    {
        $this->db->where('candidato_grupo_codigo', $candidato_grupo_codigo);
        $this->db->where('campo_chave', $campo_chave);
        $this->db->limit(1);

        return $this->db->get($this->tabela)->row_array();
    }

    public function salvar($candidato_grupo_codigo, $campo_chave, $valor)
    {
        $reg = array_fill_keys($this->colunas_valor, NULL);

        foreach ($this->colunas_valor as $coluna) {
            if (array_key_exists($coluna, $valor)) {
                $reg[$coluna] = $valor[$coluna];
            }
        }

        $existente = $this->buscar_por_campo(
            $candidato_grupo_codigo,
            $campo_chave
        );

        if ($existente) {
            $reg['atualizacao'] = date('Y-m-d H:i:s');
            $this->db->where('codigo', $existente['codigo']);

            return $this->db->update($this->tabela, $reg)
                ? (int) $existente['codigo']
                : FALSE;
        }

        $reg['candidato_grupo_codigo'] = $candidato_grupo_codigo;
        $reg['campo_chave'] = $campo_chave;
        $reg['cadastro'] = date('Y-m-d H:i:s');

        if (!$this->db->insert($this->tabela, $reg)) {
            return FALSE;
        }

        return $this->db->insert_id();
    }

}
