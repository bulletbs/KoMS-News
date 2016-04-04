<?php defined('SYSPATH') or die('No direct script access.');?>
<h1><? echo $article->name ?></h1>
<small><? echo $article->smart_date() ?></small>
<? if (count($photos) == 1): ?>
    <div class="showroom"><? echo $photo->getPhotoTag($article->name, array('class' => 'center')) ?></div>
<? elseif (count($photos) > 1): ?>
    <div id="showroom" class="showroom">
        <div class="clear"></div>
    </div>
    <div id="showstack" class="showstack"><? foreach ($photos as $photo): ?> <? echo $photo->getPhotoTag($article->name) ?> <? endforeach ?></div>
    <div class="article_gallery ">
        <ul id="article_thumbs" class="thumbs">
            <? foreach ($photos as $photo): ?>
                <li><? echo HTML::anchor($photo->getPhotoUri(), $photo->getThumbTag($article->name)) ?></li>
            <? endforeach ?>
        </ul>
        <div class="clear"></div>
    </div>
<?endif ?>
<? echo $article->content ?>
        <div class="clear"></div>
<script type="text/javascript" src="//yastatic.net/share/share.js" charset="utf-8"></script><div class="yashare-auto-init" data-yashareL10n="ru" data-yashareType="small" data-yashareQuickServices="vkontakte,facebook,twitter,odnoklassniki" data-yashareTheme="counter"></div>
<?if(!$article->nocomment):?>
<? Comments::render($article)?>
<? Comments::form($article)?>
<?endif?>
<?php echo  $similar;?>