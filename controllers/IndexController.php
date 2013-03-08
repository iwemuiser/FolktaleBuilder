<?php
/**
 * Meta-Meta data plugin
 *
 * @copyright Copyright 2008-2012 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * The Meta-Meta data index controller class.
 *
 * @package MetaMetaData
 */
class FolktaleBuilder_IndexController extends Omeka_Controller_AbstractActionController
{    
    public function init()
    {
        // Set the model class so this controller can perform some functions, 
        // such as $this->findById()
        $this->_helper->db->setDefaultModelName('FolktaleBuilder');
    }
    
    public function indexAction()
    {
        // Always go to browse.
        $this->_helper->redirector('add');
        return;
    }
    
    public function addAction()
    {
        // Create a new metametadata Item.
#        $mmdI = new MetaMetaData;
        
        // Set the created by user ID.
#        $mmdI->created_by_user_id = current_user()->id;
#        $mmdI->$element_id = get_current_record('item')->id; #WORKS?
#        $mmdI->$element_id = ; #WORKS?
#        $mmdI->added = date('Y-m-d H:i:s')
#        $this->view->form = $this->_getForm($page);        
#        $this->_processPageForm($page, 'add');
    }
    
    public function editAction()
    {
        // Get the requested page.
#        $page = $this->_helper->db->findById();
#        $this->view->form = $this->_getForm($page);
#        $this->_processPageForm($page, 'edit');
    }
    
    protected function _getForm($page = null)
    { 
    }
}
