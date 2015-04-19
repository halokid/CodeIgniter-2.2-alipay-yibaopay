<?php
class News extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('news_model');
    }
    
    public function index()
    {
        $data['news'] = $this->news_model->get_news();
        
        $data['title'] = "News archive";
        
        $this->load->view('news/index', $data);
        
    }
    
    public function view($slug)
    {
        $data['news_item'] = $this->news_model->get_news($slug);
        
          if (empty($data['news_item']))
  {
    show_404();
  }

  $data['title'] = $data['news_item']['title'];

  $this->load->view('news/view', $data);


    }
}
