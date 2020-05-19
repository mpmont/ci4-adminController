<?php
namespace App\Controllers;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 *
 * @
 * @package CodeIgniter
 *
 * @author Marco Monteiro @marcogmonteiro
 * @license    https://opensource.org/licenses/MIT  MIT License
 *
 * @link       https://github.com/mpmont/ci4-adminController
 * @link       https://blog.marcomonteiro.net
 */

use CodeIgniter\Controller;

class BaseController extends Controller
{

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = [];
    protected $view = null; // Set default yield view
    protected $data = []; // Set default data array
    protected $layout = 'layouts/application'; // Set default layout
    protected $arguments = []; // arguments that will be sent to the methods

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
        // Required if you're using flashdata
        $this->session = \Config\Services::session();

        //--------------------------------------------------------------------
        // Check for flashdata
        //--------------------------------------------------------------------
        $this->data['confirm'] = $this->session->getFlashdata('confirm');
        $this->data['errors'] = $this->session->getFlashdata('errors');

        // Arguments to be used in the callback remap
        $segments = $request->uri->getSegments();
        $this->arguments = array_slice($segments, 2);
    }

    /**
     * --------------------------------------------------------------------
     *   REMAP AUTOLOAD VIEWS
     * --------------------------------------------------------------------
     */

    /**
     * Remap the CI request, running the method
     * and loading the view automagically
     * @param string $method The method we're trying to load
     */
    public function _remap($method = null)
    {
        $router = service('router');

        $controller_full_name = explode('\\', $router->controllerName());
        $view_folder = strtolower(end($controller_full_name));

        if (method_exists($this, $method)) {
            $redirect = call_user_func_array(array($this, $method), $this->arguments);
        } else {
            show_404(strtolower(get_class($this)) . '/' . $method);
        }

        if (isset($redirect) && is_object($redirect) && get_class($redirect) === 'CodeIgniter\HTTP\RedirectResponse') {
            return $redirect;
        } else if (isset($redirect['url'])) {
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

            if ($this->layout == false) {
                echo view($this->data['yield'], $this->data);
            } else {
                echo view($this->layout, $this->data);
            }
        }
    }

}
