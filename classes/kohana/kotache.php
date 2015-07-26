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
abstract class Kohana_Kotache {

	const VERSION = '1.0.0';

    protected $_partials = array();
    protected $_page = NULL;
    protected $_extension = 'mustache';
    protected $_layout = 'default';

	public static function factory($template, array $partials = NULL) {
		$class = 'View_'.str_replace('/', '_', $template);

		if (!class_exists($class)) {
			throw new Kohana_Exception('View class does not exist: :class', array(
				':class' => $class,
			));
		}

		return new $class($partials);
	}

    public function __construct($template = NULL, array $partials = NULL) {

        if ($template) {
            $this->_page = $template;
        }

        if ($partials) {
            if (!$this->_partials) {
                $this->_partials = $partials;
            } else {
                $this->_partials = array_merge($this->_partials, $partials);
            }
        }
    }

	/**
	 * Magic method, returns the output of [Kotache::render].
	 *
	 * @return  string
	 * @uses    Kotache::render
	 */
	public function __toString() {
		try {
			return $this->render();
		} catch (Exception $e) {
			ob_start();
			// Render the exception
			Kohana_Exception::handler($e);
			return (string) ob_get_clean();
		}
	}

    private function partial($path) {
        $file = Kohana::find_file('views', 'pages/partials/'.$path, 'mustache');
        if (!$file) {
            throw new Kohana_Exception('Template file does not exist: :path', array(
                ':path' => 'templates/'.$path,
            ));
        }

        return file_get_contents($file);
    }

	/**
	 * Assigns a variable by name.
	 *
	 *     // This value can be accessed as {{foo}} within the template
	 *     $view->set('foo', 'my value');
	 *
	 * You can also use an array to set several values at once:
	 *
	 *     // Create the values {{food}} and {{beverage}} in the template
	 *     $view->set(array('food' => 'bread', 'beverage' => 'water'));
	 *
	 * @param   string   variable name or an array of variables
	 * @param   mixed    value
	 * @return  $this
	 */
	public function set($key, $value = NULL) {
		if (is_array($key)) {
			foreach ($key as $name => $value) {
				$this->{$name} = $value;
			}
		} else {
			$this->{$key} = $value;
		}

		return $this;
	}

	/**
	 * Assigns a value by reference. The benefit of binding is that values can
	 * be altered without re-setting them. It is also possible to bind variables
	 * before they have values. Assigned values will be available as a
	 * variable within the template file:
	 *
	 *     // This reference can be accessed as {{ref}} within the template
	 *     $view->bind('ref', $bar);
	 *
	 * @param   string   variable name
	 * @param   mixed    referenced variable
	 * @return  $this
	 */
	public function bind($key, & $value) {
		$this->{$key} = &$value;

		return $this;
	}

    /**
     * Load a template and return it.
     *
     * @param   string  template relative path
     * @return  string
     * @throws  Kohana_Exception  if the template does not exist
     */
    protected function _load_from_views($relpath) {
        $file = VIEWS_DIR.$relpath.'.mustache';

        if (!$file) {
            throw new Kohana_Exception('Template file does not exist: :path', array(
                ':path' => $file,
            ));
        }

        return file_get_contents($file);
    }
	/**
	 * Renders the template using the current view.
	 *
	 * @return  string
	 */
	public function render() {

        $template = $this->_load_from_views("layouts/{$this->_layout}");

        $_partials = array();
        foreach ($this->_partials as $name => $path) {
            $_partials[$name] = $this->partial($path);
        }

        $_engine = $this->_stash();
        $_engine->setPartials($_partials);

		return $_engine->render($template, $this);
	}

	/**
	 * Return a new Mustache for the given template, view, and partials.
	 *
	 * @return  Mustache
	 */
	protected function _stash() {

        if (!$this->_layout) {

            $page = explode('_', strtolower(get_class($this)));
            array_shift($page);
            $this->_layout = implode('/', $page);

        }

		return new Kohana_Mustache(
            array(
                'partials_loader' =>
                    new Kohana_Kotache_Loader(
                        array(
                            'layout' => $this->_layout,
                            'page' => $this->_page,
                            'views_dir' => VIEWS_DIR,
                        )
                    ),
                'escape' => function($value) {
                        return HTML::chars($value);
                    },
                'cache' => Kohana::$cache_dir.DIRECTORY_SEPARATOR.'mustache',
                'charset' => Kohana::$charset,
            )
        );
	}

    /**
     * Detect the template name from the class name.
     *
     * @return  string
     */
    protected function _detect_template() {
        // Start creating the template path from the class name
        $template = explode('_', get_class($this));

        // Remove "View" prefix
        array_shift($template);

        // Convert name parts into a path
        $template = strtolower(implode('/', $template));

        return $template;
    }
}
