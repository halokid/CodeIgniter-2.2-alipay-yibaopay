<?php
class Pages extends CI_Controller
{
    public function view($page='home')
    {
         $this->load->view('pages/'.$page);
    }
}
