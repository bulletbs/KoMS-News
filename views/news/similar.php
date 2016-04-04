<?php defined('SYSPATH') or die('No direct script access.');?>

<?if(count($articles)):?>
<br>
<div class="line_title">
    <h2><?php echo __('Similar articles')?></h2>
    <div class="clear"></div>
</div>
<ul class="articles_similar">
<?foreach($articles as $article):?>
    <li>
        <?php echo HTML::anchor($article->getUri(), isset($photos[$article->id]) ? $photos[$article->id]->getThumbTag($article->name) : HTML::image(  'media/css/images/noimage_100.png', array('alt'=>$article->name))); ?>
        <strong><?php echo HTML::anchor($article->getUri(), $article->name) ?></strong>
        <div><?php echo $article->brief ?></div>
    </li>
<?endforeach?>
</ul>
<?endif?>