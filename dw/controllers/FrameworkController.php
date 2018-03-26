<?php

namespace dw\controllers;

use dw\core\Controller;

class FrameworkController extends Controller
{
    public function index()
    {
        return $this->render('framework');
    }
}

