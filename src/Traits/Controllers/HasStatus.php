<?php

namespace ThemisMin\AdminConfig\Traits\Controllers;

trait HasStatus
{
    protected $statesFilter = [
        0 => '草稿',
        1 => '发布',
    ];

    protected $statesSwitch = [
        'off' => ['value' => 0, 'text' => '草稿', 'color' => 'danger'],
        'on'  => ['value' => 1, 'text' => '发布', 'color' => 'success'],
    ];
}
