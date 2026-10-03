<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Candidato_filtro_model extends CI_Model
{
    private $catalogo = NULL;

    public function campos()
    {
        if ($this->catalogo !== NULL) return $this->catalogo;
        // Campos de formulários anteriores continuam disponíveis quando possuem respostas.
        $sql = "SELECT fc.*, f.nome AS formulario_nome, f.codigo AS formulario_codigo
            FROM formulario_campos fc
            INNER JOIN formulario_grupos fg ON fg.codigo = fc.grupo_codigo
            INNER JOIN formulario_secoes fs ON fs.codigo = fg.secao_codigo
            INNER JOIN formularios f ON f.codigo = fs.formulario_codigo
            WHERE fc.filtravel = 1 AND EXISTS (
                SELECT 1 FROM candidato_respostas cr
                INNER JOIN candidato_grupos cg ON cg.codigo = cr.candidato_grupo_codigo
                INNER JOIN candidato_formularios cf ON cf.codigo = cg.candidato_formulario_codigo
                INNER JOIN candidatos c ON c.codigo = cf.candidato_codigo
                WHERE cr.campo_chave = fc.chave AND c.exclusao IS NULL
                  AND (cr.valor_texto IS NOT NULL AND cr.valor_texto <> ''
                    OR cr.valor_numero IS NOT NULL OR cr.valor_data IS NOT NULL
                    OR cr.valor_booleano IS NOT NULL
                    OR cr.valor_json IS NOT NULL AND cr.valor_json <> '[]')
            ) ORDER BY f.nome, fc.ordem, fc.nome";
        $campos = [];
        foreach ($this->db->query($sql)->result_array() as $campo) {
            $campo['opcoes'] = $this->db->where('campo_codigo', $campo['codigo'])
                ->order_by('ordem')->get('campo_opcoes')->result_array();
            $campos[$campo['chave']] = $campo;
        }
        $this->catalogo = $campos;
        return $campos;
    }

    public function validar($entrada, $campos, &$erros)
    {
        $resultado = [];
        if (!is_array($entrada)) return $resultado;
        foreach ($campos as $chave => $campo) {
            if (!isset($entrada[$chave]) || !is_array($entrada[$chave])) continue;
            $dados = $entrada[$chave];
            $filtro = [];
            $intervalo = in_array($campo['tipo'], ['numero', 'data'], TRUE);
            foreach ($intervalo ? ['min', 'max'] : ['valor'] as $parte) {
                $valor = $dados[$parte] ?? '';
                if ($valor === '') continue;
                if (!is_string($valor) || strlen($valor) > 150) {
                    $erros[] = 'Filtro inválido: ' . $campo['nome'];
                    continue;
                }
                $valor = trim($valor);
                if ($valor === '') continue;
                $valido = TRUE;
                if ($campo['tipo'] === 'numero') {
                    $valido = (bool) preg_match('/^-?[0-9]{1,11}(\\.[0-9]{1,4})?$/D', $valor);
                } elseif ($campo['tipo'] === 'data') {
                    $data = DateTime::createFromFormat('!Y-m-d', $valor);
                    $valido = $data && $data->format('Y-m-d') === $valor;
                } elseif ($campo['tipo'] === 'sim_nao') {
                    $valido = in_array($valor, ['0', '1'], TRUE);
                } elseif (in_array($campo['tipo'], ['selecao_unica', 'multipla_selecao'], TRUE)) {
                    $valido = in_array($valor, array_map('strval', array_column($campo['opcoes'], 'valor')), TRUE);
                }
                if ($valido) $filtro[$parte] = $valor;
                else $erros[] = 'Filtro inválido: ' . $campo['nome'];
            }
            if (isset($filtro['min'], $filtro['max']) &&
                ($campo['tipo'] === 'numero' ? (float) $filtro['min'] > (float) $filtro['max'] : $filtro['min'] > $filtro['max'])) {
                $erros[] = 'Intervalo invertido: ' . $campo['nome'];
                $filtro = [];
            }
            if ($filtro) $resultado[$chave] = $filtro;
        }
        return $resultado;
    }

    public function aplicar($filtros, $campos)
    {
        foreach ($filtros as $chave => $filtro) {
            if (!isset($campos[$chave])) continue;
            $campo = $campos[$chave];
            $condicoes = ['cr.campo_chave = ' . $this->db->escape($chave)];
            if (in_array($campo['tipo'], ['numero', 'data'], TRUE)) {
                $coluna = $campo['tipo'] === 'numero' ? 'valor_numero' : 'valor_data';
                foreach (['min' => '>=', 'max' => '<='] as $parte => $operador) {
                    if (isset($filtro[$parte])) $condicoes[] = 'cr.' . $coluna . ' ' . $operador . ' ' . $this->db->escape($filtro[$parte]);
                }
            } elseif (isset($filtro['valor'])) {
                $valor = $filtro['valor'];
                if ($campo['tipo'] === 'multipla_selecao') {
                    $condicoes[] = 'JSON_CONTAINS(cr.valor_json, ' . $this->db->escape(json_encode($valor)) . ') = 1';
                } elseif (in_array($campo['tipo'], ['sim_nao', 'selecao_unica'], TRUE)) {
                    $coluna = $campo['tipo'] === 'sim_nao' ? 'valor_booleano' : 'valor_texto';
                    $condicoes[] = 'cr.' . $coluna . ' = ' . $this->db->escape($valor);
                } else {
                    $condicoes[] = 'cr.valor_texto LIKE ' . $this->db->escape('%' . $this->db->escape_like_str($valor) . '%') . " ESCAPE '!'";
                }
            }
            // Usa a última revisão em que o campo fazia parte da publicação.
            // Uma resposta limpa não reutiliza um valor antigo; campos retirados preservam a última resposta.
            $sql = 'EXISTS (SELECT 1 FROM candidato_respostas cr
                INNER JOIN candidato_grupos cg ON cg.codigo = cr.candidato_grupo_codigo
                INNER JOIN candidato_formularios cf ON cf.codigo = cg.candidato_formulario_codigo
                INNER JOIN formulario_publicacoes fp ON fp.codigo = cf.formulario_publicacao_codigo
                WHERE cf.candidato_codigo = c.codigo AND cf.codigo = (
                    SELECT MAX(cf2.codigo) FROM candidato_formularios cf2
                    INNER JOIN formulario_publicacoes fp2 ON fp2.codigo = cf2.formulario_publicacao_codigo
                    WHERE cf2.candidato_codigo = c.codigo AND fp2.formulario_codigo = fp.formulario_codigo
                    AND JSON_SEARCH(fp2.estrutura, ' . $this->db->escape('one') . ', ' .
                        $this->db->escape($this->db->escape_like_str($chave)) . ', ' . $this->db->escape('!') . ', ' .
                        $this->db->escape('$.secoes[*].grupos[*].campos[*].chave') . ') IS NOT NULL
                ) AND ' . implode(' AND ', $condicoes) . ')';
            $this->db->where($sql, NULL, FALSE);
        }
    }
}
