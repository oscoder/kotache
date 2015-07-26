<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Mustache templates for Kohana.
 *
 * @package    Kotache
 * @category   Base
 * @author     Oscoder <guangju.he@linstrong.com>
 * @copyright  (c) 2015 Oscoder
 * @license    MIT
 */
class Kohana_Kotache_Loader
    extends Mustache_Loader_FilesystemLoader
    implements Mustache_Loader, Mustache_Loader_MutableLoader
{
    private $_extension = 'mustache';
    private $_templates = array();

    public function __construct($options = array()) {
        if (isset($options['extension'])) {
            $this->_extension = ltrim($options['extension'], '.');
        }

        $this->_page_name = isset($options['page']) ? $options['page'] : '';
        $this->_layout_name = isset($options['layout']) ? $options['layout'] : '';
        $this->_views_dir = isset($options['views_dir']) ? $options['views_dir'] : '';

        parent::__construct($this->_views_dir, $options);
    }

    public function load($name) {

        $parts = explode('.', $name, 2);
        $type = $parts[0];

        if ($type != 'page' AND $type != 'layout') {
            if ($parts[0] === 'content') {
                return parent::load("/pages/{$this->_page_name}.mustache");
            } else {
                $pname = str_replace('.', '/', $name);
                return parent::load("/pages/partials/{$pname}.mustache");
            }
        }

        $item_name = '_'.$type.'_name';
        $item_name = $this->$item_name;

        $file = str_replace('.', '/', $parts[1]);
        if($type == 'page' AND $file == 'content') {
            return parent::load("/{$type}s/{$this->_page_name}.mustache");
        }

        $path = "/{$type}s/partials/{$item_name}/{$file}.mustache";
        if(!is_file($this->_views_dir.$path)) {
            //look in the upper folder, which contains partials for all pages or layouts
            $path = "/{$type}s/partials/_shared/{$file}.mustache";
            if(!is_file($this->_views_dir.$path)) {
                return '';
            }
        }
        return parent::load($path);
    }

    /**
     * Set an associative array of Template sources for this loader.
     *
     * @param array $templates
     */
    public function setTemplates(array $templates) {

        $this->_templates = array_merge($this->_templates, $templates);
        foreach ($this->_templates as $name => $template) {
            if (!$template) {
                unset($this->_templates[$name]);
            }
        }
    }

    /**
     * Set a Template source by name.
     *
     * @param string $name
     * @param string $template Mustache Template source
     */
    public function setTemplate($name, $template) {
        if (!$template) {
			unset($this->_templates[$name]);
        } else {
            $this->_templates[$name] = $template;
        }
    }
}
