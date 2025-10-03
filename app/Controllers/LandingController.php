<?php
namespace App\Controllers;

class LandingController
{
    public function index()
    {
        require __DIR__ . '/../Views/landing.php';
    }
}
