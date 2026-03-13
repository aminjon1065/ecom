<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;
use Inertia\Inertia;
use Inertia\Response;

class InfoPageController extends Controller
{
    public function show(string $page): Response
    {
        abort_unless(Lang::has("info-pages.{$page}.title", 'ru'), 404);

        return Inertia::render('client/info/show', trans("info-pages.{$page}", [], 'ru'));
    }
}
