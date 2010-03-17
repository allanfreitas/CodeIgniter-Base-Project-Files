<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Ocular
 *
 * A layout system inspired by the Rails system.
 *
 * @package		Ocular Layout Library
 * @author		Lonnie Ezell
 * @copyright	Copyright (c) 2007, Lonnie Ezell
 * @license		http://creativecommons.org/licenses/LGPL/2.1/
 * @link			http://ocular.googlecode.com
 * @version		1.0.1
 * @filesource
 */
 
class Ocular {

	public $ci;
	
	private $view_data			= array();
	public $view_name 			= '';
	private $page_content 		= '';
	private $message				= array();
	private $blocks				= array();
	
	// Page Title Defaults
	public $show_function		= TRUE;
	public $show_controller		= TRUE;
	
	// Javascript variables
	private $inline_scripts		= array();
	private $external_scripts 	= array();
	
	// Styles
	private $styles				= array();
	
	// Profiling
	private $profile			= false;
	
	//------------------------------------------------------------------------
	
	function __construct()
	{
		// Grab a pointer to CodeIgniter
		$this->ci =& get_instance();
		
		// Load our config file
		$this->ci->config->load('ocular');
		
		// Setup our title defaults
		$this->view_data['page_title'] = '';
		
		// Log the action
		log_message('debug', 'Ocular library loaded.');
		
		// Setup default view_path
		if ($this->ci->config->item('OCU_view_dir') == '')
		{
			$this->ci->config->set_item('OCU_view_dir', 'system/application/views');
		}
		
		// Profiling
		$this->profile = $this->ci->config->item('OCU_enable_profiling');
		if ($this->profile)
		{
			$this->ci->output->enable_profiler(TRUE);
		}
	}
	
	//------------------------------------------------------------------------
	
	/**
	 * render() 
	 *
	 * The primary command that renders the layout that the viewer sees.
	 *
	 * @access	public
	 * @param	string (optional) the name of an alternate template to render.
	 * @return  null
	 */
	function render($template_name='')
	{
		if ($this->profile === true)
		{
			$this->ci->benchmark->mark('render_start');
		}
		
		// Prep some vars
		$func = $this->active_function();
		$cont = $this->active_controller(TRUE);
		
		//
		// Determine which template to use
		//
		if (empty($template_name))
		{
			/* 
				Ocular will use a template with the name of the current controller
				if it exists. If it doesn't, then it will use the default template
				as specified in the Ocular config file.
			*/
			$file = getcwd() . "/" . $this->ci->config->item('OCU_view_dir') . "/" . $this->ci->config->item('OCU_template_dir') . "/" . $cont . ".php";
			
			if (file_exists($file))
			{
				// File exists with controller name - use it.
				$template = $this->ci->config->item('OCU_template_dir') . '/' . $cont;
			} else 
			{
				// controller template doesn't exist, so use the application default
				$template = $this->ci->config->item('OCU_template_dir') . "/" . $this->ci->config->item('OCU_default_template');
			}
		} else
		{
			// A template name was passed in, so try to use that template.
			$template = $this->ci->config->item('OCU_template_dir') . "/" . $template_name;
		}
		
		//
		// Set Page Title
		//
		if (empty($this->view_data['page_title']))
		{
			$this->ci->load->helper('inflector');
			
			// Show the controller in the title?
			$this->view_data['page_title'] .= ($this->show_controller) ? humanize($cont) : '';
			
			// Show the function in the title? 
			$this->view_data['page_title'] .= ($this->show_function) ? ' ' . humanize($func) : '';
		}
		
		//
		// Attach the site name to the page title
		//
		if (!empty($this->view_data['page_title'])) 
		{
		 	if ($this->ci->config->item('OCU_site_name_placement') == "append" ) 
		 	{
		 		// Make sure a site name has been supplied before attaching it.
		    	if ($this->ci->config->item('OCU_site_name') !=  '') 
		    	{
		    		$this->view_data['page_title'] .= $this->ci->config->item('OCU_site_name_divider') . $this->ci->config->item('OCU_site_name');
		    	}
		 	} else 
		 	{
		 		// Make sure a site name has been supplied before attaching it.
		    	if ($this->ci->config->item('OCU_site_name') !=  '') 
		    	{
		 			$this->view_data['page_title'] = $this->ci->config->item('OCU_site_name') . $this->ci->config->item('OCU_site_name_divider') . $this->view_data['page_title'];
					}
		 	}
		} else {
			// just show our site name
			$this->view_data['page_title'] = $this->ci->config->item('OCU_site_name');
		}
		
		//
		// Set our body_id and body_class variables
		//    
		
		/* BODY ID */
		if (empty($this->view_data['body_id'])) 
		{
		  $this->view_data['body_id'] = ' id="' . $cont . '" ';
		} else 
		{
		   $this->view_data['body_id'] = ' id="' . $this->view_data['body_id'] . '"';
		}
		
		/* BODY CLASS */
		if (empty($this->view_data['body_class'])) 
		{
			$this->view_data['body_class'] = " ";
		} else 
		{
			$this->view_data['body_class'] = ' class="' . $this->view_data['body_class'] . '" ';
		}
		
		if ($this->profile === true)
		{
			$this->ci->benchmark->mark('render_end');
		}
		
		//
		// Render the view, passing $this->view_data so all
		// user variables are available to the views.
		//
		$this->ci->load->view($template, $this->view_data);
	}
	
	//------------------------------------------------------------------------
	
	/**
	 * Yield
	 *
	 * Renders a view based upon the current controller/function being called.
	 *
	 * It enforces a strict view organization. Each controller must have a 
	 * separate folder in the main views directory with a name matching the 
	 * controller name. The view file itself should have the same name as the
	 * function it is working with, along with a .php file extension.
	 *
	 * If a page has been rendered, then that page is returned instead of
	 * loading a view.
	 *
	 * By default, all views are cached to improve performance.
	 *
	 * @access	public
	 * @param	bool	cache the view or not? 
	 * @return  null
	 */
	public function yield($cache_me=false)
	{
		if ($this->profile === true)
		{
			$this->ci->benchmark->mark('yield_start');
		}
		
		// If a page has been rendered, use that.
		if (!empty($this->page_content))
		{
			echo $this->page_content;
			return;
		}
		
		//
		// Display the appropriate view file
		//
		
		// Our view name is based on the active function, 
		// unless a view name has already been specified.
		$func = (!empty($this->view_name)) ? $this->view_name : $this->active_function();
		
		// Render the view
		$this->fetch_view($this->active_controller(TRUE) . '/' . $func, $cache_me);
		
		if ($this->profile === true)
		{
			$this->ci->benchmark->mark('yield_end');
		}
	}
	
	//------------------------------------------------------------------------	
	
	/**
	 * render_partial()
	 *
	 * Reads a view from cache (if exists) or view directory.
	 *
	 * @access	public
	 * @param	string	name - the name of the view (may include relative path info)
	 * @param	bool		cache_me - whether or not to cache the view.
	 */
	public function render_partial($name='', $cache_me=false)
	{	
		if ($this->profile === true)
		{
			$this->ci->benchmark->mark('partial_start');
		}
		
		$return = false;
		
		if (!empty($name))
		{
			$this->fetch_view($name, $cache_me, $this->view_data);
			$return = '';
		}
		
		if ($this->profile === true)
		{
			$this->ci->benchmark->mark('partial_end');
		}
		return $return;
	}
	
	//------------------------------------------------------------------------	
	
	/**
	 * render_page()
	 *
	 * Parses a markdown file stored in the proper view directory having 
	 * extension .md and stores it in the $this->page_content variable. 
	 * The yield() function will check to see if page_content is available and,
	 * if so, will render that instead of looking for a view. 
	 */
	public function render_page($name='')
	{
		if ($this->profile === true)
		{
			$this->ci->benchmark->mark('render_page_start');
		}
		
		if (!empty($name))
		{
			// Does a cached copy exist? 
			$cache = $this->read_cache($name, '.md');	
			
			if (!empty($cache))
			{
				$this->page_content = $cache;
				return;
			}
		
			$file = $this->ci->config->item('OCU_view_dir') . '/' . $name . '.md';
			if (file_exists($file))
			{
				// Load the file into a string
				$text = file_get_contents($file);
				
				// Load the markdown helper
				$this->ci->load->helper('markdown');
				$this->page_content = Markdown($text);
				
				// Create a cache file for the rendered output
				$this->write_cache($this->page_content, $name);
				
			}	
		}
		
		if ($this->profile === true)
		{
			$this->ci->benchmark->mark('render_page_end');
		}
	}
	
	//------------------------------------------------------------------------
	// VIEW FUNCTIONS
	//------------------------------------------------------------------------
	
	private function view_exists($view_name='')
	{	
		if ($this->profile === true)
		{
			$this->ci->benchmark->mark('view_exists_start');
		}
		
		if (!empty($view_name))
		{
			if ($this->profile === true)
			{
				$this->ci->benchmark->mark('view_exists_end');
			}

			return file_exists($this->ci->config->item('OCU_view_dir') . '/' . $view_name . '.php');
		}
	}
	
	//------------------------------------------------------------------------	
	
	private function fetch_view($view_name='', $cache_me = false, $data='')
	{
		if ($this->profile === true)
		{
			$this->ci->benchmark->mark('fetch_view_start');
		}

		if (!empty($view_name))
		{ 
			// Should this file be cached?
			if ($cache_me)
			{ 
				// Does a cached copy exist? 
				if ($cache = $this->read_cache($view_name))
				{
					echo $cache;
					return;
				}
				
				// Start output buffering so we can capture it and 
				// save it to the cache.
				ob_start();
			}
		
			if ($this->view_exists($view_name))
			{	
				$file = $this->ci->config->item('OCU_view_dir') . '/' . $view_name . '.php';
				//include($file);
				$this->ci->load->view($view_name, $data);
				
				if ($cache_me)
				{
					// Since we're here, we need to cache this view.
					$cache = ob_get_contents();
					ob_end_clean();
					$this->write_cache($cache, $view_name);
				}
				return; 
			}
		} 
		
		if ($this->profile === true)
		{
			$this->ci->benchmark->mark('fetch_view_end');
		}
		
		return false;
	}
	
	//------------------------------------------------------------------------
	// STYLESHEET FUNCTIONS
	//------------------------------------------------------------------------
	
	/**
	 * Stylesheet Link Tag
	 *
	 * Returns a stylesheet link tag for the sources passed as arguments.
	 * If no extension is supplied, ".css" is automatically appended.
	 *
	 * If no argument is present, it will provide a link to the default
	 * stylesheet as specified in the config file.
	 *
	 * Media types can be specified by prepending a colon to the media type.
	 * ie: stylesheet_link_tag("global.css", ":print");
	 *
	 * @access    public
	 * @return    string
	 */
	 public function stylesheets() {
	  // Our stylesheet tag
	  $tag = '';
	  $media ='all';
	  
	  // Do we have any arguments?
	  if ( func_num_args() == 0 ) {
	    // No arguments. Return link to 'application.css'.
	    $args = $this->default_stylesheets();
	  }
	  
	  if (empty($args)) {
	    // Get our arguments from the parameters
	    $args = func_get_args();
	  }
	  
	  // Check to see if there are any in the local collection
	  if (count($this->styles) > 0)
	  {
	  		$args = array_merge($args, $this->styles);
	  }
	
	  //
	  // DEVELOPMENT MODE
	  //
	  if ($this->ci->config->item('OCU_development_mode') === TRUE)
	  {
			// Loop through each arg, spitting out link tags
			foreach ($args as $arg)
			{
				// Is it a media tag? 
				if (stripos($arg, ":"))
				{
					$media = trim($arg, ":");
				} else
				{
					// Normal tag, create the link
					echo '<link rel="stylesheet" type="text/css" href="' . base_url() . $this->ci->config->item('OCU_stylesheet_path') . $arg . '.css" media="' . $media . '" />' . "\n";
				}
			}
	  } else
	  {	
		  //
		  // PRODUCTION MODE
		  //	
		  // Loop through each, creating to stylesheet string
		  foreach ($args as $arg) 
		  {
		      // Is it a media tag?
		      if ( stripos($arg, ":") === false ) 
		      {
		        $tag .= $arg . '.css,';
		      } else 
		      {
		          // It's a media tag.
		          $arg = trim($arg, ":");
		          $media = $arg;
		      }
		  }
		  
		  // Remove any trailing commas
		  $tag = trim($tag, ',');
		  echo '<link type="text/css" rel="stylesheet" href="/min/b=' . trim($this->ci->config->item('OCU_stylesheet_path'), '/') . '&amp;f=' . $tag . '" media="' . $media . '" />' . "\n";
		}
		
		return; 
	}
	
	//------------------------------------------------------------------------
	
	public function register_styles()
	{
		// Do we have any arguments?
	  if ( func_num_args() != 0 ) {
	    // Add them to the stylesheets array
	    $styles = func_get_args();
	    
	    foreach ($styles as $style)
	    {
	    	$this->styles[] = $style;
	    }
	  }
	}

	//------------------------------------------------------------------------	
	
	public function image_path()
	{
		return base_url() . $this->ci->config->item('OCU_images_path');
	}
	
	//------------------------------------------------------------------------
	
	/**
	 * Get default stylesheets string
	 *
	 * Returns an array of stylesheet names. Used internally by the
	 * 'stylesheet_link_tag' function in ocular_helper.
	 *
	 * @access	private
	 * @return	array
	 */
	
	private function default_stylesheets() {
	  return explode(", ", $this->ci->config->item('OCU_stylesheet_default_collection'));
	}
	
	//------------------------------------------------------------------------
	
	//------------------------------------------------------------------------
	// JAVASCRIPT FUNCTIONS
	//------------------------------------------------------------------------
	
	/**
    * scripts()
    *
    * Inserts links for external scripts and the internal scripts themselves
    * into the view.
    */
   function javascripts()
   {
		$this->external_scripts();
      $this->inline_scripts();
   }
   
   //------------------------------------------------------------------------	
   
   public function register_script($script='', $inline=false)
   {
      if (!empty($script))
      {
         // Save it to the array of scripts
         if ($inline===true)
         {
            $this->inline_scripts[] = $script;
            return true;
         } else
         {
            $this->external_scripts[] = $script;
            return true;
         }
      }
         
      return false;
   }
   
   //------------------------------------------------------------------------	
   
   function external_scripts()
   {
   	$tag = '';
   
		// Do we have any arguments?
		if ( func_num_args() == 0 ) {
			// No arguments. Grab link to the defaults.
			$args = $this->default_javascripts();
		} else
		{
	    // Get our arguments from the parameters
	    $args = func_get_args();
	  }
	  
	  // Has the user added any scripts? 
	  if (count($this->external_scripts))
	  {
			// Merge the two arrays
			$args = array_merge($args, $this->external_scripts);
	  }
   
   	// Are we in dev mode or production?
   	if ($this->ci->config->item('OCU_development_mode') == TRUE)
   	{	
   		//
   		// DEVELOPMENT MODE
   		//
	      foreach ($args as $script)
	      {
	         echo '<script type="text/javascript" src="/' . $this->ci->config->item('OCU_javascript_path') . $script.'.js" ></script>' . "\n";
	      }
      } else 	// PRODUCTION MODE
      {
      	foreach ($args as $arg)
      	{
      		// Add our tag to the list.
	    		$tag .= $arg . '.js,';
	    	}
	    	
			$tag = trim($tag, ',');
			echo '<script type="text/javascript" src="/min/b=' . trim($this->ci->config->item('OCU_javascript_path'), '/') . '&amp;f=' . $tag . '" /></script>' . "\n";
		}
    }
   
   //------------------------------------------------------------------------
   
   public function inline_scripts()
   {
      // Are there any scripts to include? 
      if (count($this->inline_scripts) > 0)
      {
         // Create our shell opening
         echo '<script type="text/javascript">' . "\n";
         echo $this->ci->config->item('OCU_inline_javascript_opener') ."\n\n";
         
         // Loop through all available scripts
         // inserting them inside the shell.
         foreach($this->inline_scripts as $script)
         {
            echo $script . "\n";
         }
         
         // Close the shell.
         echo "\n" . $this->ci->config->item('OCU_inline_javascript_closer') . "\n";
         echo '</script>' . "\n";
         
         return true;
      }
      return false;
   }
	
	//------------------------------------------------------------------------
	
	/**
	 * Get default javascripts string
	 *
	 * Returns an array of javascript names. Used internally by the
	 * 'javascripts()' function.
	 *
	 * @access	private
	 * @return	array
	 */
	
	private function default_javascripts() {
	  return explode(", ", $this->ci->config->item('OCU_javascript_default_collection'));
	}
	
	//------------------------------------------------------------------------
	// CACHEING FUNCTIONS
	//------------------------------------------------------------------------	
	
	private function cache_path()
	{
		// Modify our filename to reflect the correct cache_path
		if ($this->ci->config->item('cache_path') != '')
		{
			$cache_path = getcwd() . '/' . $this->ci->config->item('cache_path') . 'views/';
		} else 
		{
			$cache_path = BASEPATH . 'cache/views';
		}
		
		return $cache_path;
	}
	
	//------------------------------------------------------------------------	
	
	private function write_cache($content, $filename)
	{			
		$cache_path = $this->cache_path();
		
		// Try to write the cache file out.
		if(!$fp = $this->fopen_recursive($cache_path . $filename, 'w'))
		{
			show_error('Ocular is unable to write to the cache file: ' . $cache_path . $filename);
			return false;
		} else 
		{
			fwrite($fp, $content);
			fclose($fp);
			return true;
		}
	}	
	
	//------------------------------------------------------------------------
	
	/**
	 * read_cache()
	 *
	 * Returns a file from the cache.
	 *
	 * The cacheing mechanism checks the dates on the original view file
	 * and the cached file (if it exists). It returns whichever is most
	 * current. 
	 *
	 * This means that when you modify a view, you don't need to clear
	 * Ocular's cache, though you might need to clear CodeIgniter's 
	 * if you're using it.
	 *
	 * @access	private
	 * @param	string	filename (may include paths)
	 * @param	string	the file extension to add (defaults to .php)
	 * @param	string	true=load the view, false=return a string
	 */
	private function read_cache($filename, $ext='.php', $include=false)
	{	
		// Load the file helper
		$this->ci->load->helper('file');

		$cache_path = $this->cache_path();
		
		if (file_exists($cache_path . $filename))
		{	
			// Get the modification date of our cached file
			$cache_date = filemtime($cache_path . $filename);
			
			// Get the modification date of our original view.
			$orig_date = filemtime($this->ci->config->item('OCU_view_dir') . '/' . $filename . $ext);
			
			// Send the cache file if it's newer. Otherwise, return false
			// so that the file will be rebuilt.
			if ($orig_date < $cache_date)
			{
			   // Include or return contents?
			   if ($include)
			   {
			      include_once($cache_path . $filename);
			      return true;
			   } else
			   {
				  return file_get_contents($cache_path . $filename);
			   }
			}
		}
		return false;
	}
	
	//------------------------------------------------------------------------
	
	private function fopen_recursive($path, $mode, $chmod=0755)
    {
    
        $directory = dirname($path);
        $file = basename($path);
        if (!is_dir($directory)) {
            if (!mkdir($directory, $chmod, 1)) {
                return FALSE;
            }
        }
        return fopen ($path, $mode);
    }
	
	//------------------------------------------------------------------------
	// UTILITY FUNCTIONS
	//------------------------------------------------------------------------
	
	public function get($data='')
	{
		if (isset($this->view_data[$data]))
		{
			return $this->view_data[$data];
		}
		
		return false;
	}
	
	//------------------------------------------------------------------------	
	
        public function set($title=array(), $data = '')
        {
            if ($data != '' && is_string($title))
            {
                $title = array($title => $data);
            }

            if(is_array($title) && count($title) > 0)
            {
                foreach ($title as $key => $value)
                {
                    $this->view_data[$key] = $value;
                }
                return;
            }

            return false;
        }
	
	//------------------------------------------------------------------------	
	
	public function set_message($message='', $type='information', $close_btn=true)
	{
		if (!empty($message) && is_string($message))
		{
			$this->_message['message'] = $message;
			$this->_message['type'] = $type;
			$this->_message['close_btn'] = $close_btn; 
		}
	}
	
	//------------------------------------------------------------------------
	
	public function message()
	{
		if (isset($this->_message) && is_array($this->_message))
		{
			echo '<div class="notification png_bg ' . $this->_message['type'] . '">';
			
			if ($this->_message['close_btn'] === true)
			{
				echo '<a href="#" class="close">';
				echo '<img src="/public/manager/images/icons/cross_grey_small.png" title="Close this notification" alt="close" />';
				echo '</a>';
			}
			
			echo '<div>' . $this->_message['message'] . '</div>';
			echo '</div>';
		}
	}
	
	//------------------------------------------------------------------------	
	
	/**
	 * active_controller()
	 *
	 * Returns the name of the current controller.
	 *
	 * @access	public
	 * @return	string
	 */
	public function active_controller($get_path=FALSE)
	{
		// Grab the CI Router class loaded in memory already.
		global $RTR;
		
		$controller = $RTR->fetch_class();
		
		if ($get_path === TRUE)
		{
			$controller = $RTR->fetch_directory() . $this->ci->config->item('OCU_layout_dir') . $controller;
		}
		
		return $controller;
	}
	
	//------------------------------------------------------------------------
	
	/**
	 * active_function()
	 *
	 * Returns the name of the current function.
	 *
	 * @access	public
	 * @return	string
	 */
	public function active_function()
	{
		global $RTR;
		
		return $RTR->fetch_method();
	}
}

/* End of file Ocular.php */
/* Location: ./application/libraries/Ocular.php */