<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Token_admin
{
    public function obter()
    {
        $ci = get_instance();
        $token = $ci->session->userdata('token_admin');
        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            $ci->session->set_userdata('token_admin', $token);
        }
        return $token;
    }

    public function exigir()
    {
        $ci = get_instance();
        $token = $ci->input->post('_token_admin');
        if (!is_string($token) || !hash_equals($this->obter(), $token)) {
            show_error('Solicitação expirada. Atualize a página e tente novamente.', 403);
        }
    }
}
