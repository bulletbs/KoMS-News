<?php defined('SYSPATH') or die('No direct script access.');?>
<div class="tabber" id="tab-container"> 
    <ul class="tabs">
        <li class="active"><a href="#most_popular" class="active">Самое популярное</a></li>
        <li><a href="#most_commented" class="active">Самое обсуждаемое</a></li>
    </ul>
    <div class="panel-container">
        <div class="panel" id="most_popular">
            <?foreach($most['view'] as $article):?>
            <div class="item">
            <?if(isset($photos[$article->id])):?><?php echo HTML::anchor($article->getUri(), $photos[ $article->id ]->getThumbTag($article->name))?> <?endif?>
            <div>
                <?php echo HTML::anchor($article->getUri(), $article->name) ?>
                <span class="ico_views" title="Просмотров"><?php echo $article->views ?></span>
            </div>
            </div><?endforeach?>
        </div>
        <div class="panel" id="most_commented">
            <?foreach($most['comment'] as $article):?>
            <div class="item">
            <?if(isset($photos[$article->id])):?><?php echo HTML::anchor($article->getUri(), $photos[ $article->id ]->getThumbTag($article->name))?> <?endif?>
            <div>
                <?php echo HTML::anchor($article->getUri(), $article->name) ?>
                <span class="ico_comments" title="Комментариев"><?php echo $article->comments ?></span>
            </div>
            </div><?endforeach?>
        </div>
    </div>
    <script type="text/javascript">
        $(function(){
            $("#tab-container").easytabs({
                animationSpeed: 100,
                updateHash: false
            });
        });
    </script>
</div>
