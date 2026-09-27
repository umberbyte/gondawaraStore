<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment('伝統の味、権田原佃煮。');
})->purpose('Display an inspiring quote');
