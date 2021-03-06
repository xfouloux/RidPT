<?php

namespace App\Controllers;

use Rid\Http\AbstractController;

class IndexController extends AbstractController
{
    public function index()
    {
        // Get Last News from redis cache
        $news = container()->get('redis')->get('Site:recent_news');
        if ($news === false) { // Get news from Database and cache it in redis
            $news = container()->get('dbal')->prepare('SELECT * FROM blogs ORDER BY `create_at` DESC LIMIT :max')->bindParams([
                'max' => config('base.max_news_sum')
            ])->fetchAll();
            container()->get('redis')->set('Site:recent_news', $news, 86400);
        }

        // Get All Links from redis cache
        $links = container()->get('redis')->get('Site:links');
        if ($links === false) {
            $links = container()->get('dbal')->prepare("SELECT `name`, `title`, `url` FROM links WHERE `status` = 'enabled' ORDER BY id ASC")->fetchAll();
            container()->get('redis')->set('Site:links', $links, 86400);
        }

        return $this->render('index', ['news' => $news, 'links' => $links]);
    }
}
