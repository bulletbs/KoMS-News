<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Admin_News extends Controller_Admin_Crud
{
    public $submenu = 'AdminNewsMenu';

    public $skip_auto_render = array(
        'delete',
        'status',
        'import',
    );

    protected $_item_name = 'item';
    protected $_crud_name = 'Site News';

    protected $_model_name = 'News';
    protected $_orderby_field = 'date';

    public $list_fields = array(
        'id',
        'nameLink',
        'smartDate',
    );

    protected $_sort_fields = array(
        'category_id' => array(
            'label' => 'Показать категорию',
            'type' => 'select',
        ),
        'name' => array(
            'label' => 'Найти',
            'type'=>'text',
            'oper'=>'like',
        ),
        'content' => array(
            'label' => 'Текст',
            'type'=>'text',
            'oper'=>'like',
        ),
        'id' => array(
            'label' => 'ID',
            'type'=>'text',
            'oper'=>'=',
        ),
    );

    public $_form_fields = array(
        'name' => array('type'=>'text'),
        'alias' => array('type'=>'text'),
        'category_id' => array(
            'type'=>'select',
            'data'=>array('options'=>array())
        ),
        'main' => array('type'=>'checkbox'),
        'enable' => array('type'=>'checkbox'),
        'nocomment' => array('type'=>'checkbox'),
        'source' => array('type'=>'text'),
        'date' => array('type'=>'datetime'),
        'brief' => array('type'=>'editor', 'config'=>'admin-120'),
        'content' => array('type'=>'editor'),
        'photo' => array(
            'type'=>'call_view',
            'data'=>'admin/news/photos',
            'advanced_data'=>array(
                'photos'=>array(),
            )
        ),
        'meta' => array('type'=>'legend', 'name'=>'Meta tags'),
        'title' => array('type'=>'text'),
        'keywords' => array('type'=>'text'),
        'description' => array('type'=>'text'),
    );

    protected $_advanced_list_actions = array(
        array(
            'action'=>'status',
            'label'=>'On/Off',
            'icon'=>array(
                'field'=>'enable',
                'values' => array(
                    '0' => 'eye-close',
                    '1' => 'eye-open',
                ),
            ),
        ),
    );


    public function action_index(){
        /* Filter Parent_id initialize  */
        $this->_sort_fields['category_id']['data']['options'][0] = 'Все категории';
        $this->_sort_fields['category_id']['data']['options'] = array_merge($this->_sort_fields['category_id']['data']['options'], ORM::factory('NewsCategory')->getOptionList());

        if(!isset($this->_sort_values['category_id']))
            $this->_sort_values['category_id'] = 0;
        $this->_sort_fields['category_id']['data']['selected'] = $this->_sort_values['category_id'];
        $this->_sort_fields['name']['data'] = $this->_sort_values['name'];
        $this->_sort_fields['content']['data'] = $this->_sort_values['content'];
        $this->_sort_fields['id']['data'] = $this->_sort_values['id'];

        parent::action_index();
    }

    /**
     * Form preloader
     * @param $model
     * @param array $data
     * @return array|bool|void
     */
    protected function _processForm($model, $data = array()){
        /* Setting categories select field */
        $this->_form_fields['category_id']['data']['options'] = ORM::factory('NewsCategory')->getOptionList();
        $this->_form_fields['category_id']['data']['selected'] = $model->category_id;

        /* Setting photos field */
        $this->_form_fields['photo']['advanced_data']['photos'] = ORM::factory('NewsPhoto')->where('news_id', '=', $model->id)->find_all()->as_array('id');

        $this->styles[] = '/media/libs/jquery-image-crop/css/imgareaselect-animated.css';
        $this->scripts[] = '/media/libs/jquery-image-crop/js/jquery.imgareaselect.pack.js';
        $this->scripts[] = '/media/js/admin/news_photos.js';

        if(!$model->id){
            $model->date = time();
            $model->enable = true;
        }

        parent::_processForm($model);
    }

    /**
     * Saving Model Method
     * @param $model
     */
    protected function _saveModel($model){
//        echo Debug::vars($_POST);
//        die();
        if(isset($_POST['date']))
            $_POST['date'] = strtotime($_POST['date']);

        parent::_saveModel($model);

        /* Save photos */
        $files = Arr::get($_FILES, 'photos', array('tmp_name' => array()));
        $coords = Arr::get($_POST, 'crops', array());
        $setNewMain = Arr::get($_POST, 'setNewMain');
        foreach($files['tmp_name'] as $k=>$file){
            $photo = $model->addPhoto($file, $coords[$k]);
            if($setNewMain == $k)
                $setmain = $photo->id;
        }

        /* Deleting photos */
        $files = Arr::get($_POST, 'delphotos', array());
        foreach($files as $file_id)
            $model->deletePhoto($file_id);

        /* Setting up main photo */
        if(!isset($setmain))
            $setmain = Arr::get($_POST, 'setmain');
        $model->setMainPhoto($setmain);
    }

    /**
     * On/Off item
     */
    public function action_status(){
        $article = ORM::factory('News', $this->request->param('id'));
        if($article->loaded()){
            $article->flipStatus();
        }
        $this->redirect($this->_crud_uri . URL::query());
    }

    /**
     * Loading model to render form
     * @param null $id
     * @return ORM
     */
    protected function _loadModel($id = NULL){
        $model = ORM::factory($this->_model_name, $id);
//        $this->_form_fields['photo']['data'] = $model->getThumb();

        return $model;
    }

    public function action_import(){
        $news = ORM::factory('News')->where('date','>', time() - 86400*7)->find_all();
        foreach($news as $new){
            $new->brief = preg_replace("/<\/?span[^>]*\>/i", "", $new->brief);
            $new->save();
        }
    }
}
