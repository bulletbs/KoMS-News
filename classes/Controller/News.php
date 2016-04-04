<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Котроллер для вывода главной страницы НОВОСТЕЙ
 */

class Controller_News extends Controller_System_Page
{
    const MOST_CONTENT_INTERVAL_DAYS = 60;

    const MAIN_PAGE_CACHE = 'main_page';
    const MAIN_PAGE_CACHE_TIME = 300;

    public $skip_auto_render = array(
        'main',
        'most',
        'goto',
        'similar',
    );

    public $article_name;
    public $categories;

    public function before(){
        parent::before();

        $this->styles[] = "media/libs/pure-release-0.5.0/menus.css";
//        if($this->auto_render){
//            $this->breadcrumbs->add('Статьи', '/news', 1);
//        }
    }

    /**
     * Output all enabled articles
     */
    public function  action_all()
    {
        $this->styles[] = "media/libs/bxSlider/jquery.bxslider.css";
        $this->scripts[] = 'media/libs/bxSlider/jquery.bxslider.min.js';
        $this->scripts[] = 'media/js/slider.js';

        /* Init Pagination module */
        $count = Model_News::newsOrmFinder()->count_all();
        $pagination = Pagination::factory(array(
            'total_items' => $count,
            'group' => 'news',
        ))->route_params(array(
            'controller' => Request::current()->controller(),
        ));

        /* Meta tags */
        $this->title = htmlspecialchars("Все статьи" .' - '.$this->config->view['title']);

        /* Top slider */
        $slider =  Model_News::newsOrmFinder()->and_where('main','=','1')->limit(5)->find_all()->as_array('id');
        $slide_photos = ORM::factory('NewsPhoto')->articlesPhotoList(array_keys($slider));

        $articles = Model_News::newsOrmFinder()->offset($pagination->offset)->limit($pagination->items_per_page)->find_all()->as_array('id');
        $photos = ORM::factory('NewsPhoto')->articlesPhotoList(array_keys($articles));

        $this->template->content
            ->set('title', "Все статьи")
            ->set('categories', Model_NewsCategory::getCategoriesList())
            ->set('articles', $articles)
            ->set('photos', $photos)
            ->set('slider', $slider)
            ->set('slide_photos', $slide_photos)
            ->set('pagination', $pagination)
        ;
    }

    /**
     *  Output all articles of part categories
     */
    public function action_part(){
        $alias = $this->request->param('part_alias');
        $id = Model_NewsCategory::getPartIdByAlias($alias);
        if($id){
            $this->styles[] = "media/libs/bxSlider/jquery.bxslider.css";
            $this->scripts[] = 'media/libs/bxSlider/jquery.bxslider.min.js';
            $this->scripts[] = 'media/js/slider.js';

            $categories = Model_NewsCategory::getCategoriesByPart($id);

            /* Init Pagination module */
            $count = Model_News::newsOrmFinder()->and_where('category_id','IN',array_keys($categories))->count_all();
            $pagination = Pagination::factory(array(
                'total_items' => $count,
                'group' => 'news',
            ))->route_params(array(
                'controller' => Request::current()->controller(),
                'part_alias'=>$alias,
            ));


            /* Meta tags */
            $this->title = htmlspecialchars( Model_NewsCategory::$parts[$id] .' - '.$this->config->view['title']);

            /* Top slider */
            $slider =  Model_News::newsOrmFinder()->and_where('category_id','IN',array_keys($categories))->and_where('main','=','1')->limit(5)->find_all()->as_array('id');
            $slide_photos = ORM::factory('NewsPhoto')->articlesPhotoList(array_keys($slider));

            $articles = Model_News::newsOrmFinder()->and_where('category_id','IN', array_keys($categories))->offset($pagination->offset)->limit($pagination->items_per_page)->find_all()->as_array('id');
            $photos = ORM::factory('NewsPhoto')->articlesPhotoList(array_keys($articles));

            $this->template->content
                ->set('categories', $categories)
                ->set('part', Model_NewsCategory::$parts[$id])
                ->set('articles', $articles)
                ->set('photos', $photos)
                ->set('slider', $slider)
                ->set('slide_photos', $slide_photos)
                ->set('pagination', $pagination)
            ;
        }
        else{
            $this->redirect('/news');
        }
    }

    /**
     *  Output all category articles
     */
    public function action_category(){
        $part_alias = $this->request->param('part_alias');
        $part_id = Model_NewsCategory::getPartIdByAlias($part_alias);
        $alias = $this->request->param('cat_alias');
        $id = Model_NewsCategory::getCategoryIdByAlias($alias);
        $category = ORM::factory('NewsCategory', $id);
        if($category->loaded() && $category->part_id == $part_id){
            $this->styles[] = "media/libs/bxSlider/jquery.bxslider.css";
            $this->scripts[] = 'media/libs/bxSlider/jquery.bxslider.min.js';
            $this->scripts[] = 'media/js/slider.js';

            $this->breadcrumbs->add(Model_NewsCategory::$parts[$category->part_id], Model_NewsCategory::getPartUri($category->part_id));

            /* Meta tags */
            $this->title = htmlspecialchars( $category->name .' - '.$this->config->view['title']);

            /* Init Pagination module */
            $count = ORM::factory('News')->where('category_id','=', $id)->and_where('enable','=','1')->count_all();
            $pagination = Pagination::factory(array(
                'total_items' => $count,
                'group' => 'news',
            ))->route_params(array(
                'controller' => Request::current()->controller(),
                'part_alias'=>$part_alias,
                'cat_alias'=>$alias,
            ));

            /* Top slider */
            $slider =  Model_News::newsOrmFinder()->and_where('category_id','=',$id)->and_where('main','=','1')->limit(5)->find_all()->as_array('id');
            $slide_photos = ORM::factory('NewsPhoto')->articlesPhotoList(array_keys($slider));

            $articles = Model_News::newsOrmFinder()->and_where('category_id','=', $id)->offset($pagination->offset)->limit($pagination->items_per_page)->find_all()->as_array('id');
            $photos = ORM::factory('NewsPhoto')->articlesPhotoList(array_keys($articles));

            $this->template->content
                ->set('categories', array($id=>$category))
                ->set('category', $category->name)
                ->set('articles', $articles)
                ->set('photos', $photos)
                ->set('slider', $slider)
                ->set('slide_photos', $slide_photos)
                ->set('pagination', $pagination)
            ;
        }
        else{
            $this->redirect('news');
        }
    }

    /**
     * Article output
     * @throws HTTP_Exception_404
     */
    public function action_article(){
        $id = $this->request->param('id');
        $article = Model_News::newsOrmFinder()->and_where('id','=',$id)->find();
        if($article->loaded() && $article->enable==1){
            /* Views increment */
            DB::update($article->table_name())->set(array('views'=>DB::expr('views+1')))->where('id', '=', $id)->execute();

            /* breadcrumbs & similar articles */
            $category = ORM::factory('NewsCategory', $article->category_id);
            if($category->loaded()){
                $this->breadcrumbs->add(Model_NewsCategory::$parts[$category->part_id], Model_NewsCategory::getPartUri($category->part_id), 2);
                $this->breadcrumbs->add($category->name,$category->getUri(), 3);

            }

            /* Similar articles */
            $similar_uri = Route::get('news_similar')->uri(array(
                'article_id' => $article->id,
            ));
            $similar = Request::factory($similar_uri)->post(array('title'=>$article->name))->execute();

            /* Meta tags */
            $this->title = trim(htmlspecialchars( !empty($article->title) ? $article->title : $article->name));
            $this->description = trim( !empty($article->description) ? $article->description : strip_tags($article->brief));
            $this->keywords = !empty($article->keywords) ? $article->keywords : $this->config->view['keywords'];

            /* Photos */
            $photos = ORM::factory('NewsPhoto')->where('news_id', '=', $article->id)->find_all()->as_array('id');
            if(count($photos > 1)){
                $this->styles[] = "media/libs/bxSlider/jquery.bxslider.css";
                $this->scripts[] = 'media/libs/bxSlider/jquery.bxslider.min.js';
                $this->scripts[] = 'media/js/article_gallery.js';
            }
            list(,$photo) = each($photos);
            $this->template->content
                ->set('photo', $photo)
                ->set('photos', $photos)
                ->set('similar', $similar)
                ->set('article', $article);
        }
        else{
            throw new HTTP_Exception_404('Requested page not found');
        }
    }

    /**
     * HMVC action that find and render similar articles
     */
    public function action_similar(){
        if(Request::initial() === Request::current())
            $this->redirect(URL::site('news'));
        $article_id = $this->request->param('article_id');
        $article_ids = array();
        $title = $this->request->post('title');

        /* Creates relevant IDs array */
        $ids = DB::select('id', array(DB::expr("round(MATCH (name) AGAINST (:str))")->param(':str',     $title), 'rel'))
            ->from(ORM::factory('News')->table_name())
            ->where('id','<>',$article_id)->and_where('enable','=','1')->and_where('date', '<=', time())
            ->and_where(DB::expr('MATCH (`name`)'), 'AGAINST', DB::expr("(:str)")->param(':str', $title))
            ->order_by('rel', 'desc')->order_by('date', 'desc')
            ->limit(10)->as_assoc()->execute();
        foreach($ids as $id)
            $article_ids[] = $id['id'];

        /* Creates articles array order by date */
        $articles = array();
        $photos = array();
        if(count($article_ids)){
            $articles = ORM::factory('News')
                ->where('id','IN',$article_ids)
                ->order_by('date', 'desc')
                ->find_all()->as_array('id')
            ;
            $photos = ORM::factory('NewsPhoto')->articlesPhotoList(array_keys($articles));
        }
        $this->response->body(View::factory('news/similar', array(
            'articles' => $articles,
            'photos' => $photos,
        ))->render());
    }


    /**
     * HMVC action for rendering most viewed and commented articles
     */
    public function action_most(){
        if(Request::initial() === Request::current())
            $this->redirect(URL::site('news'));

        $most = array(
            'comment' => array(),
            'view' => array(),
        );
        $most['comment'] = ORM::factory('News')->where('enable','=','1')->and_where('date', '<=', time())->and_where('comments', '>', 0)->and_where('date', '>', time()-86400*self::MOST_CONTENT_INTERVAL_DAYS)->order_by('comments', 'DESC')->order_by('date', 'DESC')->limit(10)->find_all()->as_array('id');
        $most['view'] = ORM::factory('News')->where('enable','=','1')->and_where('date', '<=', time())->and_where('views', '>', 0)->and_where('date', '>', time()-86400*self::MOST_CONTENT_INTERVAL_DAYS)->order_by('views', 'DESC')->order_by('date', 'DESC')->limit(10)->find_all()->as_array('id');
        $photos = ORM::factory('NewsPhoto')->articlesPhotoList(
            array_merge(array_keys($most['comment']), array_keys($most['view']))
        );
        $this->response->body(View::factory('news/most', array(
            'most' => $most,
            'photos' => $photos,
        ))->render());
    }

    /**
     * HMVC action for rendering news on mainpage
     */
    public function action_main(){
//        Cache::instance()->delete(self::MAIN_PAGE_CACHE);
        if(!$content = Cache::instance()->get(self::MAIN_PAGE_CACHE)){

            $parts = Model_NewsCategory::$parts;
            $categories = Model_NewsCategory::getCategoriesList();

            /* Top slider */
            $slider =  Model_News::newsOrmFinder()->and_where('category_id','=','1')->limit(5)->find_all()->as_array('id');
            $slide_photos = ORM::factory('NewsPhoto')->articlesPhotoList(array_keys($slider));

            /* Last articles by part or category */
            $articles = Model_News::getArticlesList( Kohana::$config->load('news.main_list') );

            /* Getting all articles ids and load photos */
            $articles_ids = array();
            foreach($articles as $_article){
                if(is_array($_article))
                    foreach($_article as $_res)
                        $articles_ids[] = $_res->id;
                else
                    $articles_ids[] = $_article->id;
            }
            $photos = ORM::factory('NewsPhoto')->articlesPhotoList($articles_ids);

            $content = View::factory('news/main')
                ->set('parts', $parts)
                ->set('categories', $categories)
                ->set('slider', $slider)
                ->set('slide_photos', $slide_photos)
                ->set('articles', $articles)
                ->set('photos', $photos)
                ->render()
            ;
            Cache::instance()->set(self::MAIN_PAGE_CACHE, $content, self::MAIN_PAGE_CACHE_TIME);
        }

        $this->response->body($content);
    }

    /**
     * Redirection to article source
     * @throws HTTP_Exception_404
     */
    public function action_goto(){
        $id = $this->request->param('id');
        $article = ORM::factory('News', $id);
        if($article->loaded() && $article->enable==1 && !empty($article->source)){
            $article->gotoSource();
        }
        else{
            throw new HTTP_Exception_404('Requested page not found');
        }
    }
}