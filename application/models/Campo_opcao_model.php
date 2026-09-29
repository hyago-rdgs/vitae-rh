<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Campo_opcao_model extends CI_Model
{
    private $tabela = 'campo_opcoes';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function listar_por_formulario($formulario_codigo)
    {
        $this->db->select([
            'o.codigo',
            'o.campo_codigo',
            'o.nome',
            'o.valor',
            'o.ordem',
            'o.ativo'
        ]);
        $this->db->from($this->tabela . ' o');
        $this->db->join(
            'formulario_campos c',
            'c.codigo = o.campo_codigo',
            'INNER'
        );
        $this->db->join(
            'formulario_grupos g',
            'g.codigo = c.grupo_codigo',
            'INNER'
        );
        $this->db->join(
            'formulario_secoes s',
            's.codigo = g.secao_codigo',
            'INNER'
        );
        $this->db->where('s.formulario_codigo', $formulario_codigo);
        $this->db->where('o.exclusao IS NULL', NULL, FALSE);
        $this->db->where('c.exclusao IS NULL', NULL, FALSE);
        $this->db->where('g.exclusao IS NULL', NULL, FALSE);
        $this->db->where('s.exclusao IS NULL', NULL, FALSE);
        $this->db->order_by('o.ordem', 'ASC');
        $this->db->order_by('o.codigo', 'ASC');

        return $this->db->get()->result_array();
    }

    public function listar_por_campo($campo_codigo)
    {
        $this->db->select([
            'codigo',
            'campo_codigo',
            'nome',
            'valor',
            'ordem',
            'ativo',
            'cadastro',
            'atualizacao'
        ]);
        $this->db->where('campo_codigo', $campo_codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);
        $this->db->order_by('ordem', 'ASC');
        $this->db->order_by('codigo', 'ASC');

        return $this->db->get($this->tabela)->result_array();
    }

    public function sincronizar($campo_codigo, array $opcoes)
    {
        $this->db->select([
            'codigo',
            'valor',
            'exclusao'
        ]);
        $this->db->where('campo_codigo', $campo_codigo);

        $existentes = $this->db->get($this->tabela)->result_array();
        $existentes_por_codigo = [];

        foreach ($existentes as $existente) {
            $existentes_por_codigo[$existente['codigo']] = $existente;
        }

        $codigos_mantidos = [];

        foreach ($opcoes as $ordem => $opcao) {
            $codigo = $opcao['codigo'] ?? NULL;
            $dados = [
                'nome' => $opcao['nome'],
                'ordem' => $ordem + 1,
                'ativo' => 1,
                'exclusao' => NULL
            ];

            if ($codigo !== NULL) {
                if (!isset($existentes_por_codigo[$codigo])) {
                    return FALSE;
                }

                $dados['atualizacao'] = date('Y-m-d H:i:s');

                $this->db->where('codigo', $codigo);
                $this->db->where('campo_codigo', $campo_codigo);

                if (!$this->db->update($this->tabela, $dados)) {
                    return FALSE;
                }

                $codigos_mantidos[] = (int) $codigo;
                continue;
            }

            $dados['campo_codigo'] = $campo_codigo;
            $dados['valor'] = $this->gerar_valor_unico(
                $campo_codigo,
                $opcao['nome']
            );
            $dados['cadastro'] = date('Y-m-d H:i:s');

            if (!$this->db->insert($this->tabela, $dados)) {
                return FALSE;
            }

            $codigos_mantidos[] = (int) $this->db->insert_id();
        }

        $this->db->where('campo_codigo', $campo_codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        if (!empty($codigos_mantidos)) {
            $this->db->where_not_in('codigo', $codigos_mantidos);
        }

        return $this->db->update(
            $this->tabela,
            [
                'ativo' => 0,
                'atualizacao' => date('Y-m-d H:i:s'),
                'exclusao' => date('Y-m-d H:i:s')
            ]
        );
    }

    public function excluir_por_campo($campo_codigo)
    {
        $this->db->where('campo_codigo', $campo_codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        return $this->db->update(
            $this->tabela,
            [
                'ativo' => 0,
                'atualizacao' => date('Y-m-d H:i:s'),
                'exclusao' => date('Y-m-d H:i:s')
            ]
        );
    }

    private function gerar_valor_unico($campo_codigo, $nome)
    {
        $base = $this->normalizar($nome);
        $valor = $base;
        $sufixo = 2;

        while ($this->valor_em_uso($campo_codigo, $valor)) {
            $valor = $base . '_' . $sufixo;
            $sufixo++;
        }

        return $valor;
    }

    private function valor_em_uso($campo_codigo, $valor)
    {
        $this->db->where('campo_codigo', $campo_codigo);
        $this->db->where('valor', $valor);

        return $this->db->count_all_results($this->tabela) > 0;
    }

    private function normalizar($valor)
    {
        $normalizado = iconv(
            'UTF-8',
            'ASCII//TRANSLIT//IGNORE',
            $valor
        );
        $normalizado = $normalizado !== FALSE
            ? $normalizado
            : $valor;
        $normalizado = strtolower($normalizado);
        $normalizado = preg_replace(
            '/[^a-z0-9]+/',
            '_',
            $normalizado
        );
        $normalizado = trim($normalizado, '_');
        $normalizado = substr($normalizado, 0, 90);

        return $normalizado !== '' ? $normalizado : 'opcao';
    }
}
