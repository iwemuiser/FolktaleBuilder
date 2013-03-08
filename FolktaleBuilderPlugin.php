<?php
/**
 * Folktale Builder Plugin
 *
 * @copyright Copyright 2008-2012 Iwe Muiser for the Meertens Institute / University of Twente
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

define('FOLKTALEBUILDER_PLUGIN_DIR', dirname(__FILE__));
define('FOLKTALEBUILDER_HELPERS_DIR', FOLKTALEBUILDER_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'helpers');

class FolktaleBuilderPlugin extends Omeka_Plugin_AbstractPlugin
{
	
    /**
     * @var array Hooks for the plugin.
     */
    protected $_hooks = array('install', 
							  'uninstall', 
							'initialize',
#							'admin_items',
							'define_acl', 
#							'define_routes',
#							'config_form',  
#							'config',
#							'admin_items_show_sidebar',
#							'admin_items_panel_buttons',
#							'after_save_item'
							);

#    add_filter('admin_dashboard_stats', 'folktale_builder_dashboard_stats'); #Add statistics about the amount of folktales
#    add_filter('admin_dashboard_panels', 'folktale_builder_dashboard_panel'); #Add a panel

	/**
     * @var array Filters for the plugin.
     */
    protected $_filters = array('admin_navigation_main',
                                'admin_dashboard_panels',
                                'admin_dashboard_stats',
#                                'simple_vocab_routes',
                                );

    /**
    * Install the plugin.
    */
    public function hookInstall()
    {
    }

        /**
     * Uninstall the plugin.
     */
    public function hookUninstall()
    {
    }

    public function hookInitialize()
    {
    }
    
    
    /**
     * Define the ACL.
     *
     * @param Omeka_Acl
     */
    public function hookDefineAcl($args)
    {
        $acl = $args['acl'];
        $indexResource = new Zend_Acl_Resource('FolktaleBuilder_Index');
        $acl->add($indexResource);

        $acl->allow("contributor", "Items", array('makePublic', "edit"));
        
        $acl->deny("admin", "Collections", 'delete');
        $acl->deny("admin", "Users");
        $acl->allow("admin", "Users");
#        $acl->allow(array('super', 'admin', 'contributor'), array('FolktaleBuilder_Index'));
        
        #nieuwe gebruiker aanmaken
#        $acl->addRole('invoerder', 'contributor');
#        $acl->allow('invoerder', 'Items', array('add', 'tag', 'batch-edit', 'batch-edit-save', 
#                                                  'delete-confirm', 'editSelf', 'deleteSelf', 
#                                                  'showSelfNotPublic', 'makePublic', 'edit'));

        $acl->allow('admin', array( 'Plugins'));
        
#        $pageResource = new Zend_Acl_Resource('FolktaleBuilder_Page');
#        $acl->add($pageResource);
#        $acl->allow(array('super', 'admin'), 'FolktaleBuilder_Page', array('add', 'build'));
#        $acl->allow('guest', 'Contribution_Contribution', array('show', 'contribute', 'thankyou', 'my-contributions'));        
    }
    
    /**
     * Add the FolktaleBuilder link to the admin main navigation.
     * 
     * @param array Navigation array.
     * @return array Filtered navigation array.
     */
    public function filterAdminNavigationMain($nav)
    {
        $nav[] = array(
            'label' => __('FolktaleBuilder'),
            'uri' => url('folktale-builder'),
            'resource' => 'FolktaleBuilder_Index',
            'privilege' => 'add'
        );
        return $nav;
    }
    
    
    /**
     * Append routes that render element text form input.
     *
     * @param array $routes
     * @return array
     */
    public function filterSimpleVocabRoutes($routes)
    {
       
        $routes[] = array('module' => 'folktale-builder',
                          'controller' => 'folktale-builder',
                          'actions' => array('type-form', 'build'));
        return $routes;
    }
    
    
    /**
     * Appends some more stats to the dashboard
     * 
     * @return void
     **/
    function filterAdminDashboardStats($stats)
    {
    	$collection = get_record_by_id('Collection', 1);
    	$stats[] = array(link_to($collection, null, metadata($collection, 'total_items')), __('folktales'));
        return $stats;
    }

    function print_pre($whatever){
    	print "<pre>";
    	print_r($whatever);
    	print "</pre>";
    }


    /**
     * Append search to dashboard
     * 
     * @return void
     **/
    function filterAdminDashboardPanels($panels){
        $panels2[] = $this->_addDashboardBrowseEtc($panels);
        $panels2[] = $this->_addDashboardSearchEtc($panels);
        $panels2[] = $this->_pimped_recent_items();
        return $panels2;

    }

    function _pimped_recent_items(){
        $recent_html = '<h2>' . __('Recent Items') . '</h2>';
        set_loop_records('items', get_recent_items(5));
            foreach (loop('items') as $item){
                $recent_html .= '<div class="recent-row">';
                $recent_html .= '<p class="recent">' . metadata($item, array('Dublin Core', 'Identifier')) . ' - '. link_to_item() . '</p>';
                $recent_html .= '<p class="recent">' . (metadata($item, 'item_type_name') ? metadata($item, 'item_type_name') : "NO ITEMTYPE!") . 
                                " in " . (metadata($item, 'collection_name') ? metadata($item, 'collection_name') : "NO COLLECTION !") . '</p>';
                if (is_allowed($item, 'edit')){
                    $recent_html .= '<p class="dash-edit">' . link_to_item(__('Edit'), array(), 'edit') . '</p>';
                }
                $recent_html .= '</div>';
            }
        return $recent_html;
    }
    
/*
    <?php ob_start(); ?>
    <h2><?php echo __('Recent Collections'); ?></h2>
    <?php
        $collections = get_recent_collections(5);
        set_loop_records('collections', $collections);
        foreach (loop('collections') as $collection):
    ?>
        <div class="recent-row">
            <p class="recent"><?php echo link_to_collection(); ?></p>
            <?php if (is_allowed($collection, 'edit')): ?>
            <p class="dash-edit"><?php echo link_to_collection(__('Edit'), array(), 'edit'); ?></p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
        <?php if (is_allowed('Collections', 'add')): ?>
        <div class="add-new-link"><p><a class="add-collection" href="<?php echo html_escape(url('collections/add')); ?>"><?php echo __('Add a new collection'); ?></a></p></div>
        <?php endif; ?>
    <?php $panels[] = ob_get_clean(); ?>

    <?php $panels = apply_filters('admin_dashboard_panels', $panels, array('view' => $this)); ?>
    <?php for ($i = 0; $i < count($panels); $i++): ?>
    <section class="five columns <?php echo ($i & 1) ? 'omega' : 'alpha'; ?>">
        <div class="panel">
            <?php echo $panels[$i]; ?>
        </div>
    </section>
    <?php endfor; ?>*/

    function _addDashboardSearchEtc($panels){
#        $db = get_db();
        $zoeken_html = "<H1>Snelzoeken</H1><br>";
        $zoeken_html .= '<form id="' . url(array('controller'=>'items', 'action'=>'browse')). '" action="/vb/admin/items/browse" method="GET">';
        $zoeken_html .= '<label>Zoek in Volksverhaal : tekst</label>';
        $zoeken_html .= '<input type="hidden" name="advanced[0][element_id]" id="advanced[0][element_id]" value="1">';
        $zoeken_html .= '<input type="hidden" name="advanced[0][type]" id="advanced[0][type]" value="contains">';
        $zoeken_html .= '<input type="text" name="advanced[0][terms]" id="advanced[0][terms]" value="" size="30">';
        $zoeken_html .= '<input type="submit" class="submit small green button" name="submit_search" id="submit_search_advanced" value="';
        $zoeken_html .= __('search') . '">';
        $zoeken_html .= "</form>";

        $zoeken_html .= '<form id="' . url(array('controller'=>'items', 'action'=>'browse')). '" action="/vb/admin/items/browse" method="GET">';
        $zoeken_html .= '<label>Zoek in Verhaaltypen : beschrijving</label>';
        $zoeken_html .= '<input type="hidden" name="advanced[0][element_id]" id="advanced[0][element_id]" value="41" >';
        $zoeken_html .= '<input type="hidden" name="advanced[0][type]" id="advanced[0][type]" value="contains" >';
        $zoeken_html .= '<input type="hidden" name="collection" id="collection" value="3" >';
        $zoeken_html .= '<input type="text" name="advanced[0][terms]" id="advanced[0][terms]" value="" size="30">';
        $zoeken_html .= '<input type="submit" class="submit small green button" name="submit_search" id="submit_search_advanced" value="';
        $zoeken_html .= __('search') . '">';
        $zoeken_html .= "</form>";
        
        $zoeken_html .= '<form id="' . url(array('controller'=>'items', 'action'=>'browse')). '" action="/vb/admin/items/browse" method="GET">';
        $zoeken_html .= '<label>Zoek een Verteller op naam</label>';
        $zoeken_html .= '<input type="hidden" name="advanced[0][element_id]" id="advanced[0][element_id]" value="50" >';
        $zoeken_html .= '<input type="hidden" name="advanced[0][type]" id="advanced[0][type]" value="contains" >';
        $zoeken_html .= '<input type="hidden" name="collection" id="collection" value="4" >';
        $zoeken_html .= '<input type="text" name="advanced[0][terms]" id="advanced[0][terms]" value="" size="30">';
        $zoeken_html .= '<input type="submit" class="submit small green button" name="submit_search" id="submit_search_advanced" value="';
        $zoeken_html .= __('search') . '">';
        $zoeken_html .= "</form>";

    	return $zoeken_html;
    }

    function _addDashboardBrowseEtc($panels){
        $all_tales_browse = url(array('module'=>'items','controller'=>'browse'), 
                                'default',
                                array("search" => "",
                                    "submit_search" => "Zoeken",
                                    "collection" => "1",
                                    )
                                );
        $private_tales_browse = url(array('module'=>'items','controller'=>'browse'), 
                                'default',
                                array("search" => "",
                                    "submit_search" => "Zoeken",
                                    "collection" => "1",
                                    "public" => "0",
                                    )
                                );
        
        $item_toevoegen = 
        $folktale_html = "";
        $folktale_html .= "<H1>Volksverhalenbank functies</H1><br>";

        $folktale_html .= "<H2> <a href = '$all_tales_browse'>Browse <b>alle</b> volksverhalen</a></H2>";
        $folktale_html .= "<H2> <a href = '$private_tales_browse'>Browse <b>prive</b> volksverhalen</H2>";
        
        $folktale_html .= "<br><br>";
        $folktale_html .= "<a class='small blue advanced-search-link button' href='/vb/admin/items/search'>Geavanceerd zoeken</a>";
        $folktale_html .= "<br>";
        $folktale_html .= "<a href='/vb/admin/items/add' class='add button small green'>Voeg een item toe</a>";

        return $folktale_html;
    }

    function _count_items($collection = null)
    {
        if($collection) {
    		if(is_numeric($collection)) {
    		    $collectionId = $collection;
    		} else {
    		    $collectionId = $collection->id;
    		}
    		$count = get_db()->getTable('Item')->count(array('collection'=>$collectionId));
    	    } else {
    	        $count = get_db()->getTable('Item')->count();
        	}
        return $count;
    }
    
}