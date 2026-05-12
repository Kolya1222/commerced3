<?php

namespace roilafx\Commerced3\Controllers;


class Commerced3Controller
{
    public function index()
    {
        $data = [
            'name' => 'Commerce Дашборд'
        ];
        return view('commerced3::index', $data);
    }
}
