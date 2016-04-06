<?php defined('SYSPATH') or die('No direct script access.');

/**
 * MInion task for board ads importation
 *
 * MySQL updates (data fix)
    update jb_board set date_add = date_add + interval (2014 - date_format(date_add, '%Y')) year WHERE date_format(date_add, '%Y')<2014
    update jb_board set id = id + (select max(id) from ads)
 */
class Task_NewsYakievImport extends Minion_Task
{
    CONST ALL_AMOUNT = 100000;
    CONST ONE_STEP_AMOUNT = 2000;
//    CONST ALL_AMOUNT = 50000;
//    CONST ONE_STEP_AMOUNT = 2000;
//    CONST ALL_AMOUNT = 50;
//    CONST ONE_STEP_AMOUNT = 10;
//    CONST ALL_AMOUNT = 5;
//    CONST ONE_STEP_AMOUNT = 1;

    /**
     * Generate sitemaps
     */
    protected function _execute(Array $params){
        Kohana::$environment = $_SERVER['XDG_CURRENT_DESKTOP'] == 'GNOME' ? Kohana::DEVELOPMENT : Kohana::PRODUCTION;
        $start = time();
        $amount = 0;
        $imported = 0;
        $last_row_id = 0;

        $_SERVER['DOCUMENT_ROOT'] = __DIR__ . '/../../../../';
        $photo_path = $_SERVER['DOCUMENT_ROOT'] . '/temp/news/block/';

        /**
         * Loading categories
         */
        $source_categories = array();
        $result = DB::select()->from('news_block_section')->order_by('record_id', 'ASC')->as_assoc()->execute();
        foreach($result as $res)
            $source_categories[$res['name']] = $res['record_id'];

        $cat2cat = array();
        $result = DB::select()->from('news_categories')->order_by('id', 'ASC')->as_assoc()->execute();
        foreach($result as $res){
            if(isset($source_categories[ $res['name'] ]))
                $cat2cat[ $source_categories[ $res['name'] ] ] = $res['id'];
        }



        /**
         * Importing
         */
        for($i=0; $i*self::ONE_STEP_AMOUNT < self::ALL_AMOUNT; $i++){
            if($last_row_id)
                $pos = $last_row_id;
            else{
                $pos = DB::select(DB::expr('max(id) max'))->from('news')->execute();
                $pos = $pos[0]['max'] ? $pos[0]['max'] : 0;
            }

            $sql = DB::select()
                ->from('news_block')
                ->where('record_id','>', (int) $pos)
                ->order_by('record_id','ASC')
                ->limit( self::ONE_STEP_AMOUNT )
                ->as_assoc();
            $result = $sql->execute();
            $amount += count($result);
            print 'Taking '. ($i+1).' portion of '. self::ONE_STEP_AMOUNT  .' rows to import (ID from '. $pos .')'.PHP_EOL;
            foreach($result as $row){
                try{
                    /* Category & Part */
                    if(!isset( $cat2cat[$row['section']] ))
                        continue;
                    $row['category_id'] = $cat2cat[$row['section']];

                    $model = ORM::factory('News')->values(array(
                        'id' => $row['record_id'],
                        'category_id' => $row['category_id'],
                        'date' => strtotime($row['time']),
                        'name' => $row['title'],
                        'brief' => $row['brief'],
                        'content' => $row['message'],
                        'source' => $row['source'],
                        'enable' => $row['enable'],
                        'views' => $row['views_amount'],

                        'title' => $row['meta_title'],
                        'keywords' => $row['keywords'],
                        'description' => $row['description'],
                    ));
                    $model->save();
                    if($model->id != $row['record_id']){
                        $model->id = $row['record_id'];
                        $model->update();
                    }

                    /* Photos */
                    if(!empty($row['photo']) && is_file($photo_path . $row['record_id'] .'b.'.$row['photo'])){
                        $model->addPhoto($photo_path . $row['record_id'] .'b.'.$row['photo']);
                    }

                    $imported++;
                }
                catch(ORM_Validation_Exception $e){
                    file_put_contents(DOCROOT . 'minion_error.log', $row['id'] .' - '. implode(', ', $e->errors('validation/error')).PHP_EOL, FILE_APPEND);
                }
                $last_row_id = $row['record_id'];
            }
            unset($result);
        }

        print 'Operation taken '. (time() - $start) .' seconds for '. $imported . ' (of '. $amount .') records'.PHP_EOL;
    }
}