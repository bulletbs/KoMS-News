<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Created by JetBrains PhpStorm.
 * User: butch
 * Date: 09.02.14
 * Time: 19:19
 */

class Model_News extends ORM{

    CONST RSS_PATH = '/rss/news.xml';

    protected  $_uriToMe;

    protected $_belongs_to = array(
        'category' => array(
            'model' => 'NewsCategory',
            'foreign_key' => 'category_id',
        ),
    );

    protected $_has_many = array(
        'photos' => array(
            'model' => 'NewsPhoto',
            'foreign_key' => 'news_id',
        ),
    );

    public function rules(){
        return array(
            'name' => array(
                array('not_empty'),
                array('min_length', array('value:',3)),
            ),
            'content' => array(
                array('max_length', array('value:',65525)),
            ),
//            'enable' => array(
//                array('in_array',array(':value', array('1',null))),
//            ),
        );
    }

    public function filters(){
        return array(
            'title' => array(
                array('trim')
            ),
            'description' => array(
                array('trim')
            ),
            'source' => array(
                array(array($this,'finalizeSource'))
            ),
            'enable' => array(
                array(function($value) {
                    return !is_null($value) ? $value : 0;
                })
            ),
//            'alias' => array(
//                array(array($this,'createAlias'))
//            ),
        );
    }

    public function labels(){
        return array(
            'id' => 'Id',
            'name' => __('News Name'),
            'nameLink' => __('News Name'),
            'alias' => __('Alias'),
            'brief' => __('Brief'),
            'date' => __('Date'),
            'smartDate' => __('Date'),
            'category_id' => __('Category'),
            'content' => __('Text'),
            'enable' => __('Visible'),
            'draft' => __('Draft'),
            'main' => __('On Main'),
            'photo' => __('Photos'),
            'title' => __('News Title'),
            'meta' => __('News Meta'),
            'keywords' => __('News Keywords'),
            'description' => __('Description'),
            'source' => __('Source Url'),
            'nocomment' => __('No comment'),
        );
    }

    /**
     * Добавить фото к объявлению
     * @param $file
     * @param array $coords
     * @return bool
     */
    public function addPhoto( $file , Array $coords = array()){
        if(!$this->loaded() || !Image::isImage($file))
            return false;
        $photo = ORM::factory('NewsPhoto')->values(array(
            'news_id'=>$this->pk(),
        ))->save();
        $photo->savePhoto($file, $coords);
        $photo->saveThumb($file, $coords);
        $photo->savePreview($file, $coords);
        return $photo->update();
    }

    /**
     * Удалить фото
     * @param $id
     * @return bool
     */
    public function deletePhoto($id){
        $photo = ORM::factory('NewsPhoto', $id);
        if($photo){
            $photo->delete();
            return true;
        }
        return false;
    }

    /**
     * @param null $id
     */
    public function setMainPhoto($id = NULL){
        $photo_table = ORM::factory('NewsPhoto')->table_name();
        $main = ORM::factory('NewsPhoto')->where('news_id' ,'=', $this->id)->and_where('main' ,'=', 1)->find();
        $exists = $main->loaded();
        if($id){
            DB::update($photo_table)->set(array('main'=>0))->where('news_id' ,'=', $this->id)->execute();
            $exists = DB::update($photo_table)->set(array('main'=>1))->where('news_id' ,'=', $this->id)->and_where('id' ,'=', $id)->execute();
        }
        if(!$exists){
            $photo = ORM::factory('NewsPhoto')->where('news_id' ,'=', $this->id)->find();
            if($photo)
                DB::update($photo_table)->set(array('main'=>1))->where('news_id' ,'=', $this->id)->and_where('id' ,'=', $photo->id)->execute();
        }
        Model_News::generateRssFeed();
    }

    /**
     * Сохранение
     * @param Validation $validation
     * @return ORM|void
     */
    public function save(Validation $validation = NULL){
        parent::save($validation);
    }

    /**
     * Удаление модели
     * @return ORM|void
     */
    public function delete(){
        foreach( $this->photos->find_all()  as $photo)
            $photo->delete();
        if(is_dir(DOCROOT."/media/upload/catalog/". $this->id))
            rmdir(DOCROOT."/media/upload/catalog/". $this->id);
        Model_News::generateRssFeed();
        parent::delete();
    }

    /**
     * Flip article status
     */
    public function flipStatus(){
        $this->enable = $this->enable == 0 ? 1 : 0;
        $this->update();
        Model_News::generateRssFeed();
    }

    /**
     * @return bool|string
     */
    public function smart_date() {
        $monthes = array(
            '', 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня',
            'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'
        );

        //Время
//        $time = ' G:i';
        $time = '';
        $date = $this->date;

        //Сегодня, вчера, завтра
        if(date('Y') == date('Y',$date)) {
            if(date('z') == date('z', $date)) {
                $result_date = date('Сегодня'.$time, $date);
            } elseif(date('z') == date('z',mktime(0,0,0,date('n',$date),date('j',$date)+1,date('Y',$date)))) {
                $result_date = date('Вчера'.$time, $date);
            } elseif(date('z') == date('z',mktime(0,0,0,date('n',$date),date('j',$date)-1,date('Y',$date)))) {
                $result_date = date('Завтра'.$time, $date);
            }

            if(isset($result_date)) return $result_date;
        }

        //Месяца
        $month = $monthes[date('n',$date)];

        //Года
        if(date('Y') != date('Y', $date)) $year = 'Y г.';
        else $year = '';

        $result_date = date('j '.$month.' '.$year.$time, $date);
        return $result_date;
    }

    /**
     * Getting article uri
     * @return string
     */
    public function getUri(){
        if(is_null($this->_uriToMe)){
            $this->_uriToMe = Route::get('news_article')->uri(array(
                'id' => $this->id,
                'cat_alias' => Model_NewsCategory::getField('alias', $this->category_id),
                'part_alias' => Model_NewsCategory::getPartAlias( Model_NewsCategory::getField('part_id', $this->category_id) ),
                'alias' => !empty($this->alias) ? $this->alias : Text::transliterate($this->name, true),
            ));
        }
        return $this->_uriToMe;
    }


    /**
     * Return formated source link
     * @param array $parameters
     * @return null|string
     */
    public function getSourceLink(Array $parameters = array()){
        if(!empty($this->source)){
            $name = str_replace('http://','',$this->source);
            $name = str_replace('www.','',$name);
            $name = preg_replace('/\/.*/u','',$name);
            $parameters['target'] = '_blank';
            return HTML::anchor('/news/goto/' . $this->id, $name, $parameters);
        }
        return NULL;
    }

    /**
     * Finalize entered source before saving model
     * @param $source
     * @return string
     */
    public function finalizeSource($source){
        if(!empty($source) && !strstr($source, 'http://'))
            $source = 'http://'.$source;
        return $source;
    }

    /**
     * Creates article alias and check if any dublicates exists
     * @param $alias
     * @return string
     */
    public function createAlias($alias){
        if(empty($alias))
            $alias = Text::transliterate($this->name, true);
        $duplicate = DB::select(DB::expr('id'))->from($this->table_name())->where('alias', '=', $alias)->and_where('id', '<>', $this->id)->execute();
        if(count($duplicate) > 0){
            $id = $this->id;
            if(!$id){
                $increment = DB::select('AUTO_INCREMENT')->from('INFORMATION_SCHEMA.TABLES')->where('TABLE_NAME', '=', $this->table_name())->execute();
                $id = $increment[0];
            }
            $alias .= '-'.$id;
        }
        return $alias;
    }

    /**
     * Redirection to source url
     */
    public function gotoSource(){
        if(!empty($this->source)){
            header("Location: ". $this->source);
        }
        else{
            header("Location: ". $this->getUri());
        }
        die();
    }

    /**
     * Generate RSS Feed
     */
    public static function generateRssFeed(){
        $config = Kohana::$config->load('global');
        $TestFeed = Feeder::factory('RSS2');

        $TestFeed->setTitle($config->view['title']);
        $TestFeed->setDescription($config->view['description']);
        $TestFeed->setLink('http://'. $_SERVER['SERVER_NAME'] . Kohana::$base_url);
        $TestFeed->setChannelElement('language', 'ru');
        $TestFeed->setChannelElement('pubDate', date("r", time()));

        $articles = ORM::factory('News')
            ->where('enable','=','1')
            ->and_where('date','<=', time())
            ->and_where('date','>', time() - Date::DAY * 8)
            ->order_by('date', 'desc')
            ->find_all()
            ->as_array('id')
        ;
        $photos = ORM::factory('NewsPhoto')->articlesPhotoList(array_keys($articles));

        foreach($articles as $article){
            if(isset($photos[$article->id]))
                $article->brief = HTML::image('http://'. $_SERVER['SERVER_NAME'] . $photos[$article->id]->getPhotoUri()) . $article->brief;
//                $article->brief = HTML::image('http://'. $_SERVER['SERVER_NAME'] . $photos[$article->id]->getPreviewUri()) . $article->brief;
            $newItem = $TestFeed->createNewItem();
            $newItem->setTitle($article->name);
            $newItem->setDescription($article->brief);
            $newItem->setDate(date( "r", $article->date));
            $newItem->setLink('http://'. $_SERVER['SERVER_NAME'] . Kohana::$base_url . $article->getUri());
            $newItem->setId('http://'. $_SERVER['SERVER_NAME'] . Kohana::$base_url . $article->getUri());
            $TestFeed->addItem($newItem);
        }
        ob_start();
        $TestFeed->generateFeed();
        $feed = ob_get_contents();
        ob_clean();
        file_put_contents(DOCROOT . self::RSS_PATH, $feed);
    }

    /**
     * Mainpage article list loader
     * @param array $config
     * @return array
     */
    public static function getArticlesList(Array $config = array()){
        $articles = array();

        /* Getting each part categories ids */
        $part_categories_ids = array();
        foreach(Model_NewsCategory::getCategoriesList() as $_category)
            $part_categories_ids[$_category->part_id][] = $_category->id;

        foreach($config as $_k=>$_v){
            $orm = Model_News::newsOrmFinder();
            if(substr($_k, 0, 1) == 'p'){
                $categories = $part_categories_ids[(int) substr($_k, 1)];
            }
            else
                $categories = array((int) substr($_k, 1));
            $orm->and_where('category_id', 'IN', $categories);
            if(isset($_v['exclude'])){
                $orm->and_where('category_id', 'NOT IN', $_v['exclude']);
            }
            if($_v['count']>1)
                $articles[$_k] = $orm->limit($_v['count'])->find_all()->as_array('id');
            else
                $articles[$_k] = $orm->find();
        }
        return $articles;
    }

    /**
     * Returns ORM Object with ordering
     * and most useful conditions
     * @return $this
     */
    public static function newsOrmFinder(){
        return ORM::factory('News')
            ->order_by('date', 'desc')
            ->where('enable','=','1')
            ->and_where('date', '<=', time())
        ;
    }

    /**
     * Smart article field getter
     * @param string $name
     * @return mixed|string
     */
    public function __get($name){
        if($name == 'nameLink'){
            return HTML::anchor($this->getUri(), $this->name, array('target'=>'_blank'));
        }
        if($name == 'smartDate'){
            return $this->smart_date();
        }
        return parent::__get($name);
    }

    /**
     * Request links array for sitemap generation
     * @return array
     */
    public function sitemapArticles(){
        $links = array();
        $articles = Model_News::newsOrmFinder()->find_all();
        foreach($articles as $model)
            $links[] = $model->getUri();
        return $links;
    }
}