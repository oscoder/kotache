# kotache
kotache can be used like as the kostache, but it also support the newest version of mustache, and support the ace-admin framework.

kotache is a [Kohana 3](https://github.com/kohana/kohana) module for using [Mustache](http://mustache.github.com/) templates in your application.

## Usage

To use, simply create a POPO (Plain Old PHP Object) like so:

```php
<?php

class View_Test extends Kotache
{
	public $hello = 'world';

	public function testing()
	{
		return 'foobar';
	}
}
```

And create a mustache renderer. there is no need to pass any paramters.

```php
<?php

$view = new View_Test();
```

And render it:

```php
<?php

$this->response->body($view->render());
```

## Templates

Templates should go in the `views/` directory in your cascading file system. They should have a .mustache extension. And they are also loaded automatically besed on the name used in the template.

## Partials

Partials are loaded automatically also like the rules of the templates. So if you reference `{{>foobar}}` in your template, it will look for that partial in `views/partials/foobar.mustache`.

# Layouts

Kotache supports layouts. To use, just add a `views/layout.mustache` file (a simple one is already provided), and through to setting the protected property _layout in the kotache, like as $this->_layout = 'admin', it will search for the admin.mustache file from the 'views/layouts/' folder automatically. You'll probably want to put a `$title` property in your view class. The layout should include a `{{>content}}` partial to render the body of the page.

# Additional Information

For specific usage and documentation, see:

[PHP Mustache](http://github.com/bobthecow/mustache.php)

[Original Mustache](http://mustache.github.com/)
