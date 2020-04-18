<?php
namespace App\Controllers\admin;

/**
 * Class BaseController
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 * For security be sure to declare any new methods as protected or private.
 * @package CodeIgniter
 *
 * @author Marco Monteiro @marcogmonteiro
 */
use CodeIgniter\Controller;
use CodeIgniter\Router;

class AdminController extends Controller
{

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = ['form', 'error']; // Load helpers
    protected $view = null; // Set default yield view
    protected $data = []; // Set default data array
    protected $directory = 'admin'; // Set default directory
    protected $layout = 'layouts/backend'; // Set default layout
    protected $arguments = []; // arguments that will be sent to the methods
    protected $model_class = null; // Models class used to default CRUD

    /**
     * Constructor.
     */
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        //--------------------------------------------------------------------
        // Preload any models, libraries, etc, here.
        //--------------------------------------------------------------------
        // E.g.:
        // $this->session = \Config\Services::session();
        $this->session = \Config\Services::session();

        //--------------------------------------------------------------------
        // Start base vars
        //--------------------------------------------------------------------

        //--------------------------------------------------------------------
        // Check for flashdata
        //--------------------------------------------------------------------
        $this->data['confirm'] = $this->session->getFlashdata('confirm');
        $this->data['errors'] = $this->session->getFlashdata('errors');

        // Arguments to be used in the callback remap
        $segments = $request->uri->getSegments();
        $this->arguments = array_slice($segments, 3);
    }

    /**
     * ******************
     *   REMAP AUTOLOAD VIEWS
     * ******************
     *
     * Remap the CI request, running the method
     * and loading the view automagically
     * @param string $method The method we're trying to load
     */
    public function _remap($method = null)
    {
        $router = service('router');

        $controller_full_name = explode('\\', $router->controllerName());
        $view_folder = strtolower('admin/' . end($controller_full_name));

        if (method_exists($this, $method)) {
            $redirect = call_user_func_array(array($this, $method), $this->arguments);
        } else {
            show_404(strtolower(get_class($this)) . '/' . $method);
        }

        if (isset($redirect['url'])) {
            $confirm = (isset($redirect['confirm'])) ? $redirect['confirm'] : null;
            if (!empty($confirm)) {
                return redirect()->to($redirect['url'])->with('confirm', $redirect['confirm']);
            }
            $errors = (isset($redirect['errors'])) ? $redirect['errors'] : null;
            if (!empty($errors)) {
                return redirect()->to($redirect['url'])->with('errors', $redirect['errors']);
            }
            return redirect()->to($redirect['url']);
        }

        if ($this->view !== false) {
            $this->data['yield'] = (!empty($this->view)) ? $this->view : strtolower($view_folder . '/' . $router->methodName());

            if ($this->layout === false) {
                echo view($this->data['yield'], $this->data);
            } else {
                echo view($this->layout, $this->data);
            }
        }
    }

    /**
     * ******************
     *   CRUD FUNCTIONS
     * ******************
     */

    /**
     * deefault create function
     */
    protected function admin_create()
    {
        if ($_POST) {
            try {
                $this->{$this->model_class}->insert($this->request->getPost());
                return [
                    'url' => '/' . $this->request->uri->getSegment(1) . '/' . $this->request->uri->getSegment(2),
                    'confirm' => 'The item was created',
                ];
            } catch (\Exception $e) {
                $this->data['errors'][] = $e->getMessage();
                $this->data['errors'][] = $this->{$this->model_class}->errors();
            }
        }
    }

    /**
     * Default update function
     * @param int $id The id we're working with
     */
    protected function admin_update($id)
    {
        if (is_null($id)) {
            show_404();
        }
        $this->data['record'] = $this->{$this->model_class}->find($id);
        if ($_POST) {
            try {
                $this->{$this->model_class}->update($id, $this->request->getPost());
                return [
                    'url' => '/' . $this->request->uri->getSegment(1) . '/' . $this->request->uri->getSegment(2),
                    'confirm' => 'The item was updated.',
                ];
            } catch (\Exception $e) {
                $this->data['errors'][] = $e->getMessage();
                $this->data['errors'][] = $this->{$this->model_class}->errors();
            }
        }
    }

    /**
     * Default delete function
     * @param int $id The id we're working with
     */
    protected function admin_delete($id)
    {
        if (is_null($id)) {
            show_404();
        }
        $this->{$this->model_class}->delete($id);
        return [
            'url' => '/' . $this->request->uri->getSegment(1) . '/' . $this->request->uri->getSegment(2),
            'confirm' => 'The item was deleted.',
        ];
    }

}
