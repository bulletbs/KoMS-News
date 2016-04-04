<?php defined('SYSPATH') or die('No direct script access.');?>
<h1><?php echo $category ?></h1>

<?if(count($slider)):?>
<div id="slider" class="row">
    <?foreach($slider as $slide):?>
        <div class="slide">
        <?if(isset($slide_photos[$slide->id])):?><?php echo HTML::anchor($slide->getUri(), $slide_photos[$slide->id]->getPhotoTag($slide->name)) ?><?endif?>
        <span><?php echo HTML::anchor($categories[$slide->category_id]->getUri(), $categories[$slide->category_id]->name.' »')?></span>
        <div class="transbg"></div>
        <h4><?php echo HTML::anchor($slide->getUri(), $slide->name) ?></h4>
        </div><?php $_i =1; ?><?endforeach?>
</div>
<?endif?>

<div class="row">
    <?$_i = 1;?>
    <?foreach($articles as $article):?>
    <?if($_i++>1 && $_i%2 == 0):?>
    <div class="clear"></div>
</div>
<div class="row">
    <?endif?>
    <div class="col_half_content article_preview_half">
        <div class="image_prev">
            <?if(isset($photos[$article->id])):?><?php echo HTML::anchor($article->getUri(), $photos[ $article->id ]->getPreviewTag($article->name))?><?else:?><?php echo HTML::anchor($article->getUri(),'',array('class'=>'no_preview_half'))?><?endif?>
            <?if(isset($categories[$article->category_id])):?><span><?php echo HTML::anchor($categories[$article->category_id]->getUri(), $categories[$article->category_id]->name.' »')?></span> <?endif?>
        </div>
        <small><?php echo $article->smart_date()?></small>
        <h3><?php echo HTML::anchor($article->getUri(), $article->name) ?></h3>
        <?php echo $article->brief ?>
    </div>
    <?endforeach?>
    <div class="clear"></div>
</div>

<?= $pagination->render()?>