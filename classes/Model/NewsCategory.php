<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Created by JetBrains PhpStorm.
 * User: butch
 * Date: 09.02.14
 * Time: 19:19
 */

class Model_NewsCategory extends ORM{

    const CATEGORY_CACHE_TIME = 2592000;
    const CATEGORY_OPTIONS_CACHE = 'category_options_cache';
    const CATEGORY_TREE_CACHE = 'category_tree_cache';
    const CATEGORY_LIST_CACHE = 'category_list_cache';
    CONST CATEGORY_MENUARRAY_CACHE = 'category_menu_array';

    protected $_table_name = 'news_categories';
    protected $_reload_on_wakeup   = FALSE;

    protected $_uriToMe;

    public static $parts;
    public static $parts_uri;

    public static $fields;

    public function __construct($id = NULL)
    {
        parent::__construct($id);
    }

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
        $uris = self::partsUri();
        if(isset($uris[$alias]))
            return $uris[$alias];
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
                $options[Model_NewsCategory::parts($category->part_id)][$category->id] = $category->name;
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
        $parts_uris = array_flip(Model_NewsCategory::partsUri());
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
            $parts_uris = array_flip(self::partsUri());
            $this->_uriToMe = Route::get('news_cat')->uri(array(
                'part_alias' => $parts_uris[$this->part_id],
                'cat_alias' => $this->alias,
            ));
        }
        return $this->_uriToMe;
    }

    /**
     * Request module parts links array for sitemap generation
     * @return array
     */
    public function sitemapParts(){
        $links = array();
        $parts_uris = array_flip(self::partsUri());
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
        $parts_uris = array_flip(self::partsUri());
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

    /**
     * Returns parts alias
     * @param $id
     * @return null
     */
    public static function getPartAlias($id){
        $uris = array_flip(self::partsUri());
        if(isset($uris[$id]))
            return $uris[$id];
        return NULL;
    }

    /**
     * Returns parts array or one part if ID included
     * @param null $id
     * @return null
     * @throws Kohana_Exception
     */
    public static function parts($id = NULL){
        if(is_null(self::$parts))
            self::$parts = Kohana::$config->load('news')->parts;
        if(!is_null($id))
            return isset(self::$parts[$id]) ? self::$parts[$id] : NULL;
        return self::$parts;
    }

    /**
     * Returns parts uris array or one part if ID included
     * @return mixed
     * @throws Kohana_Exception
     */
    public static function partsUri($id = NULL){
        if(is_null(self::$parts_uri))
            self::$parts_uri = Kohana::$config->load('news')->parts_uri;
//        echo Debug::vars(self::$parts_uri);
        if(!is_null($id))
            return isset(self::$parts_uri[$id]) ? self::$parts_uri : NULL;
        return self::$parts_uri;

    }

    /**
     * Creates cached array width ID as key and Field value as value
     * @param $field
     * @param $id
     * @return array|mixed
     */
    public static function getField($field, $id = null){
        if(!isset(self::$fields[$field]) && NULL === self::$fields[$field] = Cache::instance()->get('NewsCategoryFieldArray'.ucfirst($field))){
            $array = ORM::factory('NewsCategory')->find_all()->as_array('id', $field);
            Cache::instance()->set('NewsCategoryFieldArray'.ucfirst($field), $array, self::CATEGORY_CACHE_TIME);
            self::$fields[$field] = $array;
        }
        if(!is_null($id))
            return isset(self::$fields[$field][$id]) ? self::$fields[$field][$id] : NULL;
        return self::$fields[$field];
    }
}