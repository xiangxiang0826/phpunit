<?php
/*
 |---------------------------------------------------
 |	support定时任务类
 |---------------------------------------------------
 |	author：tjx
 */
class Cms_Crontab_Support
{
    private $_model ;
    private $_main_site_id;
    private $_self_site_id;
    private $_is_open_cache = true;
    private $_syn_cfg = array();
    private $_lang_array = array();
    
    public function __construct($self_site_id)
    {
        $site_info = Cms_Site::getInfoById($self_site_id);
        $this->_self_site_id = $self_site_id;
        $this->_main_site_id = $site_info['main_site_id'];
        
        //获取站点配置
        switch ($this->_main_site_id)
        {
            case 5://www.wondershare.jp
                $syn_type = 'jp_syn';
                break;
            case 6://www.wondershare.de
                $syn_type = 'de_syn';
                break;
            case 11://www.wondershare.fr
                $syn_type = 'fr_syn';
                break;
            case 12://www.wondershare.es
                $syn_type = 'es_syn';
                break;
            case 14://www.wondershare.BR
                $syn_type = 'br_syn';
                break;
            case 17://www.wondershare.it
                $syn_type = 'it_syn';
                break;
            default:
                $syn_type = 'en_syn';
        }
     
        $this->_syn_cfg = Cms_Func::getConfig('support', $syn_type);
        $this->_lang_array = include $this->_syn_cfg->lang->url;
        $this->_model = new Support_Models_Syn();
    }

    /**
     * 从Cbs同步support分类
     */
    public function cbsSynType()
    {
        $cache = Cms_Cache::getInstance();
        $support_cbs_type = $cache->get('support_cbs_type');
        if(empty($support_cbs_type) || !$this->_is_open_cache)
        {
            $cbs_cate_array = (array) json_decode(Cms_Func::httpRequest($this->_syn_cfg->cbs->type_url));
            if(!empty($cbs_cate_array))
            {
                $this->_model->cbsSynType($cbs_cate_array);
                $cache->set('support_cbs_type', 1);
            }
            unset($cbs_cate_array);
        }
    }
    
    /**
     * 从Cbs同步support文章
     */
    public function cbsSynArticle()
    {
        $cache = Cms_Cache::getInstance();
        $support_cbs_article = $cache->get("support_cbs_article_{$this->_main_site_id}");
        if(empty($support_cbs_article) || !$this->_is_open_cache)
        {
            $cbs_article_array = (array) json_decode(Cms_Func::httpRequest($this->_syn_cfg->cbs->article_url));
    
            if(!empty($cbs_article_array))
            {
                $this->_model->cbsSynArticle($this->_main_site_id, $cbs_article_array);
                $cache->set("support_cbs_article_{$this->_main_site_id}", 1);
            }
            unset($cbs_article_array);
        }
    }
    
    /**
     * 从Cbs同步support在线帮助
     */
    public function cbsSynOnhelp()
    {
        $cache = Cms_Cache::getInstance();
        $support_cbs_onhelp = $cache->get("support_cbs_onhelp_{$this->_main_site_id}");
        if(empty($support_cbs_onhelp) || !$this->_is_open_cache)
        {
            if(!empty($this->_syn_cfg->cbs->onhelp_url))
            {
                $cbs_onhelp_array = (array) json_decode(Cms_Func::httpRequest($this->_syn_cfg->cbs->onhelp_url));
                if(!empty($cbs_onhelp_array))
                {
                    $this->_model->cbsSynOnhelp($this->_main_site_id, $cbs_onhelp_array);
                    $cache->set("support_cbs_onhelp_{$this->_main_site_id}", 1);
                }
            }
            unset($cbs_onhelp_array);
        }
    }
    
    /**
     * 从cms生成到模板
     */
    public function cmsSynTemplate()
    {
        $this->_model->cmsSynOrderArticle($this->_main_site_id, $this->_self_site_id, $this->_lang_array);
        $this->_model->cmsSynTechArticle($this->_main_site_id, $this->_self_site_id, $this->_lang_array);
        $this->_model->cmsSynOnhelp($this->_main_site_id, $this->_self_site_id, $this->_lang_array);
        $this->_model->cmsSynProductOverview($this->_main_site_id, $this->_self_site_id, $this->_lang_array);
        $this->_model->cmsSynProductSoftwareUpdate($this->_main_site_id, $this->_self_site_id, $this->_lang_array);
        $this->_model->cmsSynProductSuggest($this->_main_site_id, $this->_self_site_id, $this->_lang_array);
    }
    
    /**
     * 从cbs同步到cms数据
     */
    public function cbsSynCms()
    {
        self::cbsSynType();
        self::cbsSynArticle();
        self::cbsSynOnhelp();
    }
    
    /**
     * 总同步
     */
    public function syn()
    {
        $this->_is_open_cache = false;
        self::cbsSynType();
        self::cbsSynArticle();
        self::cbsSynOnhelp();
        self::cmssyntemplate();
    }
}