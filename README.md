# codeigniter 4 base controller

[![GitHub version](https://badge.fury.io/gh/mpmont%2Fci4-adminController.svg)](https://badge.fury.io/gh/mpmont%2Fci4-adminController)

codeigniter-base-controller is an extended `baseController` class to use in your CodeIgniter applications. Any controllers that inherit from `baseController` or `adminController` get intelligent view autoloading and layout support. It's strongly driven by the ideals of convention over configuration, favouring simplicity and consistency over configuration and complexity.

## Synopsis a controller that extends to adminController

    <?php namespace App\Controllers\Admin;
    
    use App\Models\ArticleModel;
    
    class Articles extends adminController
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
        return $this->admin_create();
    }
    
    /**
     * Update a Article
     * @param int $id The article id
     */
    public function update($id)
    {
        return $this->admin_update($id);
    }
    
    /**
     * Delete Article
     * @param int $id The article id
     */
    public function delete($id = null)
    {
        return $this->admin_delete($id);
    }


## Usage

Drag the **adminController.php** file into your _app/Controllers/Admin/_ folder. This way, you have a distinct difference bettwen your backend and front-end. All your controller inside this folder should extend to adminController and your controllers outside this folder should extend to baseController. This way, only your backend controllers will have access to your CRUD functions.

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

If you're still using codeigniter 3 and want something like this that can be found here: [jamierumbelow/codeigniter-base-controller](https://github.com/jamierumbelow/codeigniter-base-controller)