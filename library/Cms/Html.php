<?php
/**
 * HTML
 * 
 * @author tjx
 */
class Cms_Html
{
    private $_tag_array = array();//记录标签数组
    private $_tag_line = 1;//记录行数
    private $_tag_prompt = array();//错误提示数组
    
    /**
     * 验证html标签是否闭合或者开启
     * @param string    $content  要验证的html内容
     */
	public function checkHtmlTag($content)
	{
	    preg_replace_callback('/<(\/)?([a-z\d]+)([^>]*)>|(\n)/i', array('self', '_callbackHtmlTag'), $content);
       
	    if(!empty($this->_tag_array))
	    {
	        foreach($this->_tag_array AS $value)
	        {
	            $this->_tag_prompt[$value[0]] = Cms_L::_('the_line_tag_error_1',array('line' => $value[0], 'tag' => $value[1]));
	        }
	    }
	    
	    $tag_prompt_str = '';
	    if(!empty($this->_tag_prompt))
	    {
	        ksort($this->_tag_prompt);
	        foreach ($this->_tag_prompt AS $value)
	        {
	            $tag_prompt_str .= $value;
	        }
	        $tag_prompt_str = Cms_L::_('check_tag_prompt').'<br>'.$tag_prompt_str;
	    }
	    return $tag_prompt_str;
	}
	
	/**
	 * 验证html标签回调函数
	 * @param array    $matches  匹配数组
	 */
    private  function _callbackHtmlTag($matches)
	{
	    if(substr($matches[3], -1) == '/')
	    {
	        return false;
	    }
	    
	    if(!empty($matches[2]))
	    {
	        if($matches[1] == '/')//为闭合html标签
	        {
	            $value = array(0, '');
	            
	            if(!empty($this->_tag_array))
	            {
	                $value = $this->_tag_array[count($this->_tag_array) - 1];
	            }
	            
	            if($value[1] != $matches[2])
	            {
	                $this->_tag_prompt[$this->_tag_line] = Cms_L::_('the_line_tag_error_2', array('line' => $this->_tag_line, 'tag' => $matches[2]));
	            }
	            else 
	            {
	                array_pop($this->_tag_array);
	            }
	        }
	        else 
	        {
	            array_push($this->_tag_array, array($this->_tag_line, $matches[2]));
	        }
	    }
	    else
	    {
	        $this->_tag_line++;
	    }
	}
}