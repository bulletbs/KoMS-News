<?php defined('SYSPATH') or die('No direct script access.');?>
<div class="well well-small" id="photoInputs">

    <?if(isset($advanced_data['photos'])):?>
    <?foreach($advanced_data['photos'] as $photo):?>
        <div class="span-2">
            <?= HTML::anchor($photo->getPhotoUri(), $photo->getThumbTag('',array('class'=>'thumbnail')), array('target'=>'_blank')) ?>
            <?= FORM::checkbox('delphotos[]', $photo->id, FALSE)?> удалить<br>
            <?= FORM::radio('setmain', $photo->id, $photo->main == 1)?> основная
        </div>
    <?endforeach;?>
    <div class="clearfix"></div>
    <?endif?>
    <br><br>
    <div class="span-5">
    <?for($i=1; $i < 9; $i++):?>
        <div class="col-lg-5">
            <div class="input-group">
            <span class="input-group-addon">
            <?php echo Form::radio('setNewMain', $i, FALSE, array('title'=>'Выбрать основной')) ?>
            </span>
            <?php echo Form::file('photos['.$i.']', array('class'=>'form-control', 'id'=>'photos_'.$i)) ?>
            </div>
        </div>
        <?php echo Form::hidden('crops['.$i.'][thumb_x]', NULL, array('id'=>'thumb_x_'.$i))?>
        <?php echo Form::hidden('crops['.$i.'][thumb_y]', NULL, array('id'=>'thumb_y_'.$i))?>
        <?php echo Form::hidden('crops['.$i.'][thumb_w]', NULL, array('id'=>'thumb_w_'.$i))?>
        <?php echo Form::hidden('crops['.$i.'][thumb_h]', NULL, array('id'=>'thumb_h_'.$i))?>
        <?php echo Form::hidden('crops['.$i.'][prev_x]', NULL, array('id'=>'prev_x_'.$i))?>
        <?php echo Form::hidden('crops['.$i.'][prev_y]', NULL, array('id'=>'prev_y_'.$i))?>
        <?php echo Form::hidden('crops['.$i.'][prev_w]', NULL, array('id'=>'prev_w_'.$i))?>
        <?php echo Form::hidden('crops['.$i.'][prev_h]', NULL, array('id'=>'prev_h_'.$i))?>
        <div class="clear"></div>
        <?if($i%4 == 0 && $i>0):?>
        </div>
        <div class="span-5">
        <?endif?>
    <?endfor?>
    </div>
    <div class="clearfix"></div>
    <br><br>
    <div class="span-16 hide" id="thumbZone"></div>
    <div class="span-16 hide" id="previewZone"></div>
    <div class="clearfix"></div>
</div>