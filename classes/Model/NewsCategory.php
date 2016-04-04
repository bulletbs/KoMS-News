<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Created by JetBrains PhpStorm.
 * User: butch
 * Date: 09.02.14
 * Time: 19:19
 */

class Model_NewsCategory extends ORM{

    const CATEGORY_CACHE_TIME = 86400;
    const CATEGORY_OPTIONS_CACHE = 'category_options_cache';
    const CATEGORY_TREE_CACHE = 'category_tree_cache';
    const CATEGORY_LIST_CACHE = 'category_list_cache';
    CONST CATEGORY_MENUARRAY_CACHE = 'category_menu_array';

    protected $_table_name = 'news_categories';
    protected $_reload_on_wakeup   = FALSE;

    protected $_uriToMe;

    public static $parts = array(
//        0=>'Основная лента',
        1=>'Новости',
        2=>'Статьи',
    );

    public static $parts_uri = array(
        'news'=>1,
        'articles'=>2,
    );

    public function rules(){
        return array(
            'name' => array(
                array('not_empty'),
                array('min_length', array('value:',3)),
                array('max_length', array('value:',50)),
            ),
        );
    }

    public function labels(){
        return array(
            'id' => __('Id'),
            'name' => __('Name'),
            'alias' => __('Alias'),
            'part_id' => __('Part'),
            'partname' => __('Part Name'),
        );
    }


    public function filters(){
        return array(
            'alias' => array(
                array(array($this,'generateAlias'))
            ),
        );
    }

    /**
     * Generate transliterated alias
     */
    public function generateAlias($alias){
        $alias = trim($alias);
        if(empty($alias))
            $alias = Text::transliterate($this->name, true);
        return $alias;
    }

    /**
     * Getting category list
     * @return array|mixed
     */
    public static function getCategoriesList(){
        if(!$categories = Cache::instance()->get(Model_NewsCategory::CATEGORY_LIST_CACHE)){
            $categories = ORM::factory('NewsCategory')->find_all()->as_array('id');
            Cache::instance()->set(Model_NewsCategory::CATEGORY_LIST_CACHE, $categories, Model_NewsCategory::CATEGORY_CACHE_TIME);
        }
        return $categories;
    }

    /**
     * Getting category ID by Alias
     * @param string $alias
     * @return int|null
     */
    public static function getCategoryIdByAlias($alias){
        $categories = self::getCategoriesList();
        foreach($categories as $category)
            if($category->alias == $alias)
                return $category->id;
        return NULL;
    }

    /**
     * Getting category ID by Alias
     * @param string $alias
     * @return int|null
     */
    public static function getPartIdByAlias($alias){
        if(isset(self::$parts_uri[$alias]))
            return self::$parts_uri[$alias];
        return NULL;
    }

    /**
     * Getting category list by part_id
     * @param $part_id
     * @return array
     */
    public static function getCategoriesByPart($part_id){
        $result = array();
        $categories = self::getCategoriesList();
        foreach($categories as $category){
            if($category->part_id == $part_id)
                $result[$category->id] = $category;
        }
        return $result;
    }

    /**
     * Getting category IDs list by part_id
     * @param $part_id
     * @return array
     */
    public static function getCategoriesIdsByPart($part_id){
        $result = array();
        $categories = self::getCategoriesList();
        foreach($categories as $category){
            if($category->part_id == $part_id)
                $result[] = $category->id;
        }
        return $result;
    }

    /**
     * Getting category options list for HTML::select
     * @return array|mixed
     */
    public function getOptionList(){
        if(!$options = Cache::instance()->get(Model_NewsCategory::CATEGORY_OPTIONS_CACHE)){
            $options = array();
            $categories = ORM::factory('NewsCategory')->find_all();
            foreach($categories as $category)
                $options[Model_NewsCategory::$parts[$category->part_id]][$category->id] = $category->name;
            Cache::instance()->set(Model_NewsCategory::CATEGORY_OPTIONS_CACHE, $options, Model_NewsCategory::CATEGORY_CACHE_TIME);
        }
        return $options;
    }

    /**
     * Adds getting partname value
     * @param string $column
     * @return mixed|null
     */
    public function get($column){
        if($column == 'partname'){
            if(!is_null($this->part_id) && isset(Model_NewsCategory::$parts[$this->part_id]))
                return Model_NewsCategory::$parts[$this->part_id];
            return NULL;
        }
        else
            return parent::get($column);
    }

    /**
     * Get part url by part ID
     * @param $id
     * @return string
     */
    public static function getPartUri($id){
        $parts_uris = array_flip(Model_NewsCategory::$parts_uri);
        return Route::get('news_part')->uri(array(
            'part_alias' => $parts_uris[$id],
        ));
    }

    /**
     * Get category url
     * @return string
     */
    public function getUri(){
        if(is_null($this->_uriToMe)){
            $parts_uris = array_flip(Model_NewsCategory::$parts_uri);
            $this->_uriToMe = Route::get('news_cat')->uri(array(
                'part_alias' => $parts_uris[$this->part_id],
                'cat_alias' => $this->alias,
            ));
        }
        return $this->_uriToMe;
    }

    /**
     * Creating menu two-dimensional array
     * part_id  =>  item1
     *              item2
     *               ...
     * @return array
     */
    public static function createMenuArray(){
//        Cache::instance()->delete(Model_NewsCategory::CATEGORY_MENUARRAY_CACHE);
        if(!$menu = Cache::instance()->get(Model_NewsCategory::CATEGORY_MENUARRAY_CACHE)){
            $menu = array();
            $categories = self::getCategoriesList();
            foreach(Model_NewsCategory::getCategoriesByPart(1) as $category)
                $menu [0][$category->id * 10] = array($category->name, $category->getUri());
            $menu [0][2] = array(Model_NewsCategory::$parts[2], Model_NewsCategory::getPartUri(2));
            foreach(Model_NewsCategory::getCategoriesByPart(2) as $category)
                $menu [$category->part_id][$category->id] = array($category->name, $category->getUri());
            Cache::instance()->set(Model_NewsCategory::CATEGORY_MENUARRAY_CACHE, $menu, Model_NewsCategory::CATEGORY_CACHE_TIME);
        }
        return $menu;
    }

    /**
     * Request module parts links array for sitemap generation
     * @return array
     */
    public function sitemapParts(){
        $links = array();
        $parts_uris = array_flip(Model_NewsCategory::$parts_uri);
        foreach(self::$parts as $key=>$val){
            $links[] = Model_NewsCategory::getPartUri($key);
            $count = Model_News::newsOrmFinder()->and_where('category_id', 'IN', Model_NewsCategory::getCategoriesIdsByPart($key))->count_all();
            $pagination = Pagination::factory(array('group' => 'news', 'total_items'=>$count), Request::factory());
            $route = Route::get('news_part');
            for ($i = 2; $i <= $pagination->total_pages; $i++)
                $links[] = $route->uri(array(
                    'part_alias' => $parts_uris[$key],
                    'page' => $i,
                ));
        }
        return $links;
    }

    /**
     * Request module categories links array for sitemap generation
     * @return array
     */
    public function sitemapCategories(){
        $links = array();
        $parts_uris = array_flip(Model_NewsCategory::$parts_uri);
        foreach($this->getCategoriesList() as $key=>$model){
            $links[] = $model->getUri();
            $count = Model_News::newsOrmFinder()->and_where('category_id', '=', $model->id)->count_all();
            $pagination = Pagination::factory(array('group' => 'news', 'total_items'=>$count), Request::factory());
            $route = Route::get('news_cat');
            for ($i = 2; $i <= $pagination->total_pages; $i++)
                $links[] = $route->uri(array(
                    'part_alias' => $parts_uris[$model->part_id],
                    'cat_alias' => $model->alias,
                    'page' => $i,
                ));
        }
        return $links;
    }
}