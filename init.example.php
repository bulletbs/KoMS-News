<?php defined('SYSPATH') or die('No direct script access.');

if(!Route::cache()){
    Route::set('news', 'news(/<action>(/<id>)(/p<page>.html))', array('action' => '(all|most|main)', 'page' => '[0-9]+'))
        ->defaults(array(
            'controller' => 'news',
            'action' => 'all',
        ));

    Route::set('news_article', '<part_alias>/<cat_alias>/<id>-<alias>.html', array( 'part_alias' => '(blogs|news|articles)', 'cat_alias' => '[\d\w\-_]+', 'id' => '[0-9]+', 'alias' => '[\d\w\-_]+'))
        ->defaults(array(
            'controller' => 'news',
            'action' => 'article',
        ));

    Route::set('news_part', '<part_alias>(/p<page>).html', array('part_alias' => '(blogs|news|articles)', 'page' => '[0-9]+'))
        ->defaults(array(
            'controller' => 'news',
            'action' => 'part',
        ));

    Route::set('news_cat', '<part_alias>/<cat_alias>(/p<page>).html', array('part_alias' => '(blogs|news|articles)', 'cat_alias' => '[\d\w\-_]+'))
        ->defaults(array(
            'controller' => 'news',
            'action' => 'category',
        ));

    Route::set('news_similar', 'news/similar/(<article_id>)', array('article_id' => '[\d]+'))
        ->defaults(array(
            'controller' => 'news',
            'action' => 'similar',
        ));
}
