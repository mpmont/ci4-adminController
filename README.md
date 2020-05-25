# codeigniter 4 base controller

[![GitHub version](https://badge.fury.io/gh/mpmont%2Fci4-adminController.svg)](https://badge.fury.io/gh/mpmont%2Fci4-adminController)

codeigniter-base-controller is an extended `BaseController` class to use in your CodeIgniter applications. Any controllers that inherit from `BaseController` or `AdminController` get intelligent view autoloading and layout support. It's strongly driven by the ideals of convention over configuration, favouring simplicity and consistency over configuration and complexity.

## Synopsis a controller that extends to adminController

    <?php namespace App\Controllers\Admin;
    
    use App\Models\ArticleModel;
    
    class Articles extends AdminController
    {
    
        public function __construct()
        {
            $this->article = model('App\Models\ArticleModel');
            $this->model_class = 'article';
        }
        
        /**
         * List Articles
         */
        public function index()
        {
            $this->data['articles'] = $this->article->findAll();
        }
        
        /**
         * Create Article
         */
        public function create()
        {
            return $this->adminCreate($this->request->getPost());
        }
        
        /**
         * Update a Article
         * @param int $id The article id
         */
        public function update($id)
        {
            return $this->adminUpdate($id, $this->request->getPost());
        }
        
        /**
         * Delete Article
         * @param int $id The article id
         */
        public function delete($id = null)
        {
            return $this->adminDelete($id);
        }
        
    }


## Usage

Drag the **AdminController.php** file into your _app/Controllers/Admin/_ folder. This way, you have a distinct difference bettwen your backend and front-end. All your controller inside this folder should extend to adminController and your controllers outside this folder should extend to baseController. This way, only your backend controllers will have access to your CRUD functions.

## Views and Layouts

Views will be loaded automatically based on the current controller and action name. Any variables set in `$this->data` will be passed through to the view and the layout. By default, the class will look for the view in _app/views/controller/action.php_.

In order to prevent the view being automatically rendered, set `$this->view` to `false`.

    $this->view = false;

Or, to load a different view than the automatically guessed view:

    $this->view = 'some_path/some_view.php';

Views will be loaded into a layout. The class will look for an _app/views/layouts/backend.php_ layout file or _app/views/layouts/application.php_ depending if it's the baseController or the adminController.

In case you want to override this in your controller just set your layout to whatever you want. 

    $this->layout = 'layouts/yourlayout.php'

In order to specify where in your layout you'd like to output the view, the rendered view will be stored in a `$yield` variable:

    <h1>Header</h1>
    
    <div id="page">
        <?php echo view($yield) ?>
    </div>
    
    <p>Footer</p>

If you wish to disable the layout entirely and only display the view - a technique especially useful for AJAX requests - you can set `$this->layout` to `FALSE`.

    $this->layout = FALSE;

Like with `$this->view`, `$this->layout` can also be used to specify an unconventional layout file:

    $this->layout = 'layouts/mobile.php';

Any variables set in `$this->data` will be passed through to both the view and the layout files.


## Loading Helpers in your controllers

If you want to load helpers in your controllers in a global scope and not inside a function all your have to do is declare the helpers property as array with all your helpers, like so:


 
    <?php namespace App\Controllers;
    
    class Home extends AdminController
    {
        protected $helpers = ['url'];
    
        public function index()
        {
        }
    
    }


## Redirects and flashdata

If you're using a structure like this you cannot redirect directly in your controllers, that must be handled by your adminController. 

Let's say you have a controller method called contacts and you want to redirect your user in case he submits a form to it.

    /**
     * Contact form
     */
    public function contacts()
    {
        if ( $_POST) {
            $email = \Config\Services::email();
            
            $email->setFrom('your@example.com', 'Your Name');
            $email->setTo('someone@example.com');
            
            $email->setSubject('Email Test');
            $email->setMessage('Testing the email class.');
            
            if ($email->send() ) {
                return [
                    'url' => '/homepage',
                ];
            }
        }
    }

If you want to append errors or success messages to your redirect all your need to do is specify that in your return array.

    return [
        'url' => '/homepage',
        'success' => 'Your email was sent!'
    ];

If you want to return errors then just change the confirm key to errors, however that one should be an array.

    return [
        'url' => '/homepage',
        'errors' => [
            'There was a problem while sending your email',
            'Please try again later'
        ]
    ];

In case you need to return a message that is neither a success or an error you can do that with the _mgs_ return key.

    return [
        'url' => '/homepage',
        'msg' => 'Star Trek is great, specially when Chewbacca shows up!'
    ];

In any case if you just want to do a readirect like you would normally do in your controller method that is supported too. In the same example presented you would just do:

    if ($email->send() ) {
        redirect()->to('/homepage');
    }


## AdminController CRUD

There's a few functions that you can use in your adminController that are not avaiable in your baseController. To do that some rules must be followed. First you need to load the main model your controller will be working with. Let's say you have a Articles controller and a ArticleModel first you load your model.

    $this->article = model('App\Models\ArticleModel');

As you can see I'm loading the model into a class property called article. In this case my _$model_class_ property should also be called article. 

    $this->model_class = 'article';

This way if your _Article_ controller needs access to a update functionallity all you need to do is create a update function like so:

    public function update($id)
    {
        return $this->adminUpdate($id, $this->request->getPost());
    }

This update function should always return the update result that was set on your adminController function. 

In case you want to send the admin_update method something else other than your post data you can do it just like this:

    public function update($id)
    {
        $data = $this->request->getPost();
        $data['my_new_field'] = 'foobar';
        return $this->adminUpdate($id, $this->request->getPost());
    }

This is specially usefull if you want to add some extra data that was not given by the post.

By default the success action of this function will always redirect to your index function in your controller. Using this structure always assume that you have a index function.

This redirect will also set a confirm flashdata automatically that can be used in your views. 

In case you need to override this behavior that can be done by returning a diferent result.

    public function update($id)
    {
        $this->adminUpdate($id, $this->request->getPost());
        return [
            'url' => '/admin/list_articles',
            'success' => 'Your article was updated.'
        ];
    }

This way you're redirecting the user to your /admin/list_articles with the flashdata "your article was updated.".

### Use AdminController in root directory

In case you want to use AdminController in root directory, or any other directory change the $directory property.

    protected $directory = ''; // Set default directory

## Add your own language variables

The CRUD methods now support the use of language variables. Those should be se in your _app/languages_ folder depending on your locales settings. For now the following languages are supported:

* English
* Portuguese

## Error Helper

In your _adminController.php_ there's a custom helper being loaded called error helper. That should be placed in your helpers folder and it's only purpose is to serve as a shortcut for the 404 exception. 

    // This is just the same thing
    show_404();
    // as 
    throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

You can now use this on your controllers too everytime you need to show a 404 error. Since its autoloaded on your adminController.

## Roadmap

* Add better error messages based the class name, so we can say something like "Your article was update" instead of item;

## Codeigniter 3 version, no CRUD controller, only autoload views

If you're still using codeigniter 3 and want something like this that can be found here: [jamierumbelow/codeigniter-base-controller](https://github.com/jamierumbelow/codeigniter-base-controller)