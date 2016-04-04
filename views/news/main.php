<?php defined('SYSPATH') or die('No direct script access.');?>

<div id="slider" class="row">
    <?foreach($slider as $slide):?>
    <div class="slide">
    <?if(isset($slide_photos[$slide->id])):?><?php echo HTML::anchor($slide->getUri(), $slide_photos[$slide->id]->getPhotoTag($slide->name)) ?><?endif?>
    <span><?php echo HTML::anchor($categories[$slide->category_id]->getUri(), $categories[$slide->category_id]->name.' »')?></span>
    <div class="transbg"></div>
    <h4><?php echo HTML::anchor($slide->getUri(), $slide->name) ?></h4>
    </div><?endforeach?>
</div>

<div class="row">
    <?$_i = 1;?>
    <?foreach($articles['c4'] as $article):?>
    <?if($_i%2):?>
</div>
<div class="row">
    <?endif?>
    <?++$_i;?>
    <div class="col_half_content article_preview_half">
        <div class="image_prev">
            <?if(isset($photos[$article->id])):?><?php echo HTML::anchor($article->getUri(), $photos[ $article->id ]->getPreviewTag($article->name)) ?><?endif?>
            <?if(isset($categories[$article->category_id])):?><span><?php echo HTML::anchor($categories[$article->category_id]->getUri(), $categories[$article->category_id]->name.' »')?></span> <?endif?>
        </div>
        <small><?php echo $article->smart_date()?></small>
        <h3><?php echo HTML::anchor($article->getUri(), $article->name) ?></h3>
    </div>
    <?endforeach?>
    <div class="clear"></div>
</div>

<div class="row">
    <div class="part_title">
        <h2><?php echo HTML::anchor($categories[3]->getUri(), 'Исследования')?></h2>
        <span><?php echo HTML::anchor($categories[3]->getUri(), 'Смотреть все')?></span>
        <div class="clear"></div>
    </div>
    <ul class="horizontal_slider" id="slider_horizontal">
        <?foreach($articles['c3'] as $slide):?>
            <li class="slide">
            <?if(isset($photos[$slide->id])):?><?php echo HTML::anchor($slide->getUri(), $photos[$slide->id]->getPreviewTag($slide->name)) ?><?endif?>
            <span><?php echo HTML::anchor($slide->getUri(), $slide->name) ?></h4></span>
            </li><?endforeach?>
    </ul>
    <div class="clear"></div>
</div>

<div class="row">
    <div class="col_half_content article_preview_half">
        <div class="image_prev">
            <?if(isset($photos[$articles['c5']->id])):?><?php echo HTML::anchor($articles['c5']->getUri(), $photos[ $articles['c5']->id ]->getPreviewTag($articles['c5']->name)) ?><?endif?>
            <?if(isset($categories[$articles['c5']->category_id])):?><span><?php echo HTML::anchor($categories[$articles['c5']->category_id]->getUri(), $categories[$articles['c5']->category_id]->name.' »')?></span> <?endif?>
        </div>
        <small><?php echo $articles['c5']->smart_date()?></small>
        <h3><?php echo HTML::anchor($articles['c5']->getUri(), $articles['c5']->name) ?></h3>
        <?php echo $articles['c5']->brief?>
    </div>
    <div class="col_half_content">
        <?foreach($articles['c2'] as $article):?>
        <div class="article_preview_three">
            <?if(isset($photos[$article->id])):?><?php echo HTML::anchor($article->getUri(), $photos[ $article->id ]->getPreviewTag($article->name)) ?><?endif?>
            <div class="brief">
                <span><?php echo HTML::anchor($categories[$article->category_id]->getUri(), $categories[$article->category_id]->name.' »')?></span>
                <small><?php echo $article->smart_date()?></small>
                <h3><?php echo HTML::anchor($article->getUri(), $article->name) ?></h3>
            </div>
            <div class="clear"></div>
        </div>
        <?endforeach?>
    </div>
    <div class="clear"></div>
</div>

<div class="row">
    <?$_i = 1;?>
    <?foreach($articles['p2'] as $article):?>
    <?if($_i%2):?>
</div>
<div class="row">
    <?endif?>
    <?++$_i;?>
    <div class="col_half_content article_preview_half">
        <div class="image_prev">
            <?if(isset($photos[$article->id])):?><?php echo HTML::anchor($article->getUri(), $photos[ $article->id ]->getPreviewTag($article->name)) ?><?endif?>
            <span><?php echo HTML::anchor($categories[$article->category_id]->getUri(), $categories[$article->category_id]->name.' »')?></span>
        </div>
        <small><?php echo $article->smart_date()?></small>
        <h3><?php echo HTML::anchor($article->getUri(), $article->name) ?></h3>
    </div>
    <?endforeach?>
    <div class="clear"></div>
</div>