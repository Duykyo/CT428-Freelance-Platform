<?php
class ProjectController extends Controller {
    public function index()  { $this->view('projects/index'); }
    public function mine()   { $this->view('projects/mine'); }
    public function create() { $this->view('projects/create'); }
    public function show()   { $this->view('projects/show'); }
    public function edit()   { $this->view('projects/edit'); }
}
