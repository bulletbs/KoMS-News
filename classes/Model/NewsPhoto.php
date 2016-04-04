<?php

class Model_NewsPhoto extends ORM{

    CONST THUMB_WIDTH = 100;
    CONST THUMB_HEIGHT = 100;

    CONST PREVIEW_WIDTH = 300;
    CONST PREVIEW_HEIGHT = 200;

    CONST PHOTO_WIDTH = 300;
    CONST PHOTO_HEIGHT = 200;


    protected $_table_name = 'news_photo';

	protected $_belongs_to = array(
        'news'=> array(
            'model' => 'News',
            'foreign_key' => 'news_id',
        ),
    );

    public function labels(){
        return array(
            'id'        => __('Id'),
            'news_id'   => __('NewId'),
            'width'     => __('Width'),
            'height'    => __('Height'),
            'ext'       => __('Extension'),
        );
    }

    public function delete(){
        if($this->getPhoto())
            unlink($this->getPhoto());
        if($this->getThumb())
            unlink($this->getThumb());
        parent::delete();
    }

    public function savePhoto($file){
        if(!$this->loaded() || !is_file($file))
            return;
        $image = Image::factory($file);
        if(!$this->ext)
            $this->ext = $image->findExtension();
        $image->image_set_max_edges(800);
        $this->width = $image->width;
        $this->height = $image->height;
        echo $this->getPhoto(true);
        $image->save($this->getPhoto(true));
    }

    public function saveThumb($file, Array $coords = array()){
        if(!$this->loaded() || !is_file($file))
            return;
        $image = Image::factory($file);
        if(count($coords) && isset($coords['thumb_w'], $coords['thumb_h'], $coords['thumb_x'], $coords['thumb_y']) ){
            $image->crop($coords['thumb_w'], $coords['thumb_h'], $coords['thumb_x'], $coords['thumb_y']);
            $image->resize(100);
        }
        else{
            $image->resize(100, 100, Image::INVERSE);
            $image->crop(100, 100, NULL, 0);
        }
        $image->save($this->getThumb(true));
    }

    public function savePreview($file, Array $coords = array()){
        if(!$this->loaded() || !is_file($file))
            return;
        $image = Image::factory($file);
        if(count($coords) && isset($coords['prev_w'], $coords['prev_h'], $coords['prev_x'], $coords['prev_y']) ){
            $image->crop($coords['prev_w'], $coords['prev_h'], $coords['prev_x'], $coords['prev_y']);
            $image->resize(300);
        }
        else{
            $image->resize(300);
            $image->crop(300, 200, NULL, 0);
        }
        $image->save($this->getPreview(true));
    }

    public function getPhoto($getName = false){
        if($getName===TRUE || is_file($this->getPhotoPath() . $this->id .'.'.$this->ext))
            return $this->getPhotoPath() . $this->id .'.'.$this->ext;
        return;
    }
    public function getThumb($getName = false){
        if($getName===TRUE || is_file($this->getThumbPath() . $this->id .'_thumb.'.$this->ext))
            return $this->getThumbPath() . $this->id .'_thumb.'.$this->ext;
        return;
    }
    public function getPreview($getName = false){
        if($getName===TRUE || is_file($this->getPreviewPath() . $this->id .'_thumb.'.$this->ext))
            return $this->getPreviewPath() . $this->id .'_prev.'.$this->ext;
        return;
    }

    public function getPhotoPath(){
        if(!file_exists(DOCROOT."/media/upload/news/". $this->news_id ."/"))
            mkdir(DOCROOT."/media/upload/news/". $this->news_id);
        return DOCROOT . "/media/upload/news/". $this->news_id ."/";
    }

    public function getThumbPath(){
        return $this->getPhotoPath();
    }


    public function getPreviewPath(){
        return $this->getPhotoPath();
    }

    public function getPhotoUri(){
        if(is_file($this->getThumbPath() . $this->id .'.'.$this->ext))
            return Kohana::$base_url."media/upload/news/". $this->news_id . "/" . $this->id . '.' . $this->ext;
        return NULL;
    }

    public function getThumbUri(){
        if(is_file($this->getThumbPath() . $this->id .'_thumb.'.$this->ext))
            return Kohana::$base_url."media/upload/news/". $this->news_id . "/" . $this->id . '_thumb.' . $this->ext;
        return NULL;
    }

    public function getPreviewUri(){
        if(is_file($this->getPreviewPath() . $this->id .'_prev.'.$this->ext))
            return Kohana::$base_url."media/upload/news/". $this->news_id . "/" . $this->id . '_prev.' . $this->ext;
        return NULL;
    }

    public function getPhotoTag($alt = '', Array $attributes = array()){
        $photo = $this->getPhotoUri();
        if($photo)
            return HTML::image($photo, Arr::merge(array(
                'alt'=>$alt,
                'title'=>$alt,
            ), $attributes));
        return NULL;
    }

    public function getPreviewTag($alt='', Array $attributes = array()){
        $photo = $this->getPreviewUri();
        if($photo)
            return HTML::image($photo, Arr::merge(array(
                'alt'=>$alt,
                'title'=>$alt,
            ), $attributes));
        return NULL;
    }

    public function getThumbTag($alt='', Array $attributes = array()){
        $photo = $this->getThumbUri();
        if($photo)
            return HTML::image($photo, Arr::merge(array(
                'alt'=>$alt,
                'title'=>$alt,
            ), $attributes));
        return NULL;
    }

    /**
     * Find list of photos by requested articles ids
     * @param array $ids
     * @return array|object
     */
    public function articlesPhotoList(Array $ids){
        $photos = array();
        if(count($ids)){
            $db_photos = DB::select()
                ->distinct('news_id')
                ->from($this->_table_name)
                ->where('news_id', 'IN', $ids)
                ->and_where('main', '=', 1)
                ->as_object('Model_NewsPhoto')
                ->execute();
            ;
            foreach($db_photos as $photo)
                $photos[$photo->news_id] = $photo;
        }
        return $photos;
    }
}