<?php
class Dbfenli extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->read_db = $this->load->database('default', true);
        $this->write_db = $this->load->database('writedb', true );
    }
    
    function index()
    {
        $query_read = $this->read_db->get('news');
        print_r($query_read->result_array());
        
        echo '<hr>';
        
        $query_write = $this->write_db->get('news');
        print_r($query_write->result_array());
    }
}
