<?php defined('SYSPATH') or die('No direct script access.');
return array(
    array(
        'name' => 'news',
        'priority' => '0.5',
        'sources' =>array(
            array(
                'model' => 'NewsCategory',
                'get_links_method' => 'sitemapParts',
            ),
            array(
                'model' => 'NewsCategory',
                'get_links_method' => 'sitemapCategories',
            ),
            array(
                'model' => 'News',
                'get_links_method' => 'sitemapArticles',
            ),
        )
    )
);
