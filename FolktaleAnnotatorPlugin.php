<?php
/**
 * Folktale Annotator Plugin
 *
 * @copyright Copyright 2008-2012 Iwe Muiser for the Meertens Institute / University of Twente
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

define('FOLKTALEANNOTATOR_PLUGIN_DIR', dirname(__FILE__));
define('FOLKTALEANNOTATOR_HELPERS_DIR', FOLKTALEANNOTATOR_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'helpers');

class FolktaleAnnotatorPlugin extends Omeka_Plugin_AbstractPlugin
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
							'after_save_item'
							);

	/**
     * @var array Filters for the plugin.
     */
    protected $_filters = array('admin_navigation_main',
                                'admin_dashboard_panels',
                                'admin_dashboard_stats',
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
        // Register the select filter controller plugin.
		$front = Zend_Controller_Front::getInstance();
		$front->registerPlugin(new FolktaleAnnotator_Controller_Plugin_InputFieldFilter);
    }
    
    
    /**
     * Define the ACL.
     *
     * @param Omeka_Acl
     */
    public function hookDefineAcl($args)
    {
        $acl = $args['acl'];
        $indexResource = new Zend_Acl_Resource('FolktaleAnnotator_Index');
        $acl->add($indexResource);

        $acl->allow("contributor", "Items", array('makePublic', "edit"));
        
        $acl->deny("admin", "Collections", 'delete');

        $acl->allow("admin", array("Users"));

#        $acl->allow(array('super', 'admin', 'contributor'), array('FolktaleAnnotator_Index'));
#        $pageResource = new Zend_Acl_Resource('FolktaleAnnotator_Page');
#        $acl->add($pageResource);
#        $acl->allow(array('super', 'admin'), 'FolktaleAnnotator_Page', array('add', 'annotate'));
#        $acl->allow('guest', 'Contribution_Contribution', array('show', 'contribute', 'thankyou', 'my-contributions'));        
    }
    
    
    
    
    public function hookAfterSaveItem($args)
    {
        $post = $args['post'];
        $record = $args['record'];
        $recordId = $record->id;

        //retrieve post date data if exist
#        $datePost = $post[''];
    }
    
    /**
     * Add the FolktaleAnnotator link to the admin main navigation.
     * 
     * @param array Navigation array.
     * @return array Filtered navigation array.
     */
    public function filterAdminNavigationMain($nav)
    {
        $nav[] = array(
            'label' => __('FolktaleAnnotator'),
            'uri' => url('folktale-annotator'),
            'resource' => 'FolktaleAnnotator_Index',
            'privilege' => 'add'
        );
        return $nav;
    }
    
    
    /**
     * Appends some more stats to the dashboard
     * 
     * @return void
     **/
    function filterAdminDashboardStats($stats)
    {
    	$vvcollection = get_record_by_id('Collection', 1);
    	$stats[] = array(link_to($vvcollection, null, metadata($vvcollection, 'total_items')), __('Volksverhalen'));
    	$pcollection = get_record_by_id('Collection', 4);
    	$stats[] = array(link_to($pcollection, null, metadata($pcollection, 'total_items')), __('Vertellers'));
    	$tpcollection = get_record_by_id('Collection', 3);
    	$stats[] = array(link_to($tpcollection, null, metadata($tpcollection, 'total_items')), __('Verhaaltypen'));
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
#        $panels2[] = $this->_active_users();
        return $panels2;

    }

/*    function _active_users(){
        $users = get_db()->getTable('Users');
        $recent_html = '<h2>' . __('Actieve gebruikers') . '</h2>';
#        set_loop_records('users', get_recent_items(10));
        foreach ($users as $key => $user){
#         foreach( $users as $key => $user )
            $recent_html .= '<div class="recent-row">';
            $recent_html .= '<p class="recent">' . $user->username . ' - '.  '</p>';
            print_r($key);
#             $recent_html .= '<p class="recent">' . (metadata($item, 'item_type_name') ? metadata($item, 'item_type_name') : "NO ITEMTYPE!") . 
#                             " in " . (metadata($item, 'collection_name') ? metadata($item, 'collection_name') : "NO COLLECTION !") . '</p>';
#             if (is_allowed($item, 'edit')){
#                 $recent_html .= '<p class="dash-edit">' . link_to_item(__('Edit'), array(), 'edit') . '</p>';
#             }
            $recent_html .= '</div>';
        }
        return $recent_html;
    }
*/

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


    function _addDashboardSearchEtc($panels){
#        $db = get_db();

        $zoeken_html = "<H1>Snelzoeken</H1><br>";
        
        $zoeken_html .= '<H2>Zoek in Volksverhalen</H2>';
        
        $zoeken_html .= '<form id="' . url(array('controller'=>'items', 'action'=>'browse')). '" action="/vb/admin/items/browse" method="GET">';
        $zoeken_html .= '<label>Zoek in tekst</label><br>';
        $zoeken_html .= '<input type="hidden" name="advanced[0][element_id]" id="advanced[0][element_id]" value="1">';
        $zoeken_html .= '<input type="hidden" name="advanced[0][type]" id="advanced[0][type]" value="contains">';
        $zoeken_html .= '<input type="hidden" name="collection" id="collection" value="1" >';
        $zoeken_html .= '<input type="text" name="advanced[0][terms]" id="advanced[0][terms]" value="" size="30">';
        $zoeken_html .= '<input type="submit" class="submit small green button" name="submit_search" id="submit_search_advanced" value="';
        $zoeken_html .= __('search') . '">';
        $zoeken_html .= "</form>";

        $zoeken_html .= '<form id="' . url(array('controller'=>'items', 'action'=>'browse')). '" action="/vb/admin/items/browse" method="GET">';
        $zoeken_html .= '<label>Zoek in tags</label><br>';
        $zoeken_html .= '<input type="hidden" name="advanced[0][element_id]" id="advanced[0][element_id]" value="">';
        $zoeken_html .= '<input type="hidden" name="collection" id="collection" value="1" >';
        $zoeken_html .= '<input type="text" name="tags" id="tags" value="" size="30">';
        $zoeken_html .= '<input type="submit" class="submit small green button" name="submit_search" id="submit_search_advanced" value="';
        $zoeken_html .= __('search') . '">';
        $zoeken_html .= "</form>";

        /*zoeken in velden 63 65 66 (exclusive)*/
        $zoeken_html .= '<form id="' . url(array('controller'=>'items', 'action'=>'browse')). '" action="/vb/admin/items/browse" method="GET">';
        $zoeken_html .= '<label>Zoek in named entities</label><br>';
        $zoeken_html .= '<select name="advanced[0][element_id]" id="advanced[0][element_id]" style="width: 140px">
                            <option value="63">Generiek (oud)</option>
                            <option value="66">Namen</option>
                            <option value="65">Plaatsen</option>
                        </select>';
#        $zoeken_html .= '<input type="hidden" name="advanced[0][element_id]" id="advanced[0][element_id]" value="63">';
        $zoeken_html .= '<input type="hidden" name="advanced[0][type]" id="advanced[0][type]" value="contains">';
        $zoeken_html .= '<input type="text" name="advanced[0][terms]" id="advanced[0][terms]" value="" size="10">';
        $zoeken_html .= '<input type="submit" class="submit small green button" name="submit_search" id="submit_search_advanced" value="';
        $zoeken_html .= __('search') . '">';
        $zoeken_html .= "</form>";

        $zoeken_html .= '<H2>Zoek in Verhaaltypen</H2>';
        
        $zoeken_html .= '<form id="' . url(array('controller'=>'items', 'action'=>'browse')). '" action="/vb/admin/items/browse" method="GET">';
        $zoeken_html .= '<label>Beschrijving</label><br>';
        $zoeken_html .= '<input type="hidden" name="advanced[0][element_id]" id="advanced[0][element_id]" value="41" >';
        $zoeken_html .= '<input type="hidden" name="advanced[0][type]" id="advanced[0][type]" value="contains" >';
        $zoeken_html .= '<input type="hidden" name="collection" id="collection" value="3" >';
        $zoeken_html .= '<input type="text" name="advanced[0][terms]" id="advanced[0][terms]" value="" size="30">';
        $zoeken_html .= '<input type="submit" class="submit small green button" name="submit_search" id="submit_search_advanced" value="';
        $zoeken_html .= __('search') . '">';
        $zoeken_html .= "</form>";

        $zoeken_html .= '<form id="' . url(array('controller'=>'items', 'action'=>'browse')). '" action="/vb/admin/items/browse" method="GET">';
        $zoeken_html .= '<label>Verhaaltypenummer (Aanduiding)</label>';
        $zoeken_html .= '<input type="hidden" name="advanced[0][element_id]" id="advanced[0][element_id]" value="43" >';
        $zoeken_html .= '<input type="hidden" name="advanced[0][type]" id="advanced[0][type]" value="contains" >';
        $zoeken_html .= '<input type="hidden" name="collection" id="collection" value="3" >';
        $zoeken_html .= '<input type="text" name="advanced[0][terms]" id="advanced[0][terms]" value="" size="30">';
        $zoeken_html .= '<input type="submit" class="submit small green button" name="submit_search" id="submit_search_advanced" value="';
        $zoeken_html .= __('search') . '">';
        $zoeken_html .= "</form>";

        $zoeken_html .= '<H2>Zoek in Vertellers</H2>';
        
        $zoeken_html .= '<form id="' . url(array('controller'=>'items', 'action'=>'browse')). '" action="/vb/admin/items/browse" method="GET">';
        $zoeken_html .= '<label>Op naam</label><br>';
        $zoeken_html .= '<input type="hidden" name="advanced[0][element_id]" id="advanced[0][element_id]" value="50" >';
        $zoeken_html .= '<input type="hidden" name="advanced[0][type]" id="advanced[0][type]" value="contains" >';
        $zoeken_html .= '<input type="hidden" name="collection" id="collection" value="4" >';
        $zoeken_html .= '<input type="text" name="advanced[0][terms]" id="advanced[0][terms]" value="" size="30">';
        $zoeken_html .= '<input type="submit" class="submit small green button" name="submit_search" id="submit_search_advanced" value="';
        $zoeken_html .= __('search') . '">';
        $zoeken_html .= "</form>";

    	return $zoeken_html;
    }

    function _verhaaltype_lijst($maker){
        return url(array('module'=>'items','controller'=>'browse'), 
                                'default',
                                array("search" => "",
                                    "submit_search" => "Zoeken",
                                    "collection" => "3",
                                    "advanced[0][element_id]" => "39",
                                    "advanced[0][type]" => "is exactly",
                                    "advanced[0][terms]" => $maker,
                                    )
                                );
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
        $public_tales_browse = url(array('module'=>'items','controller'=>'browse'), 
                                'default',
                                array("search" => "",
                                    "submit_search" => "Zoeken",
                                    "collection" => "1",
                                    "public" => "1",
                                    )
                                );
        $own_tales_browse = url(array('module'=>'items','controller'=>'browse'), 
                                'default',
                                array("search" => "",
                                    "submit_search" => "Zoeken",
                                    "collection" => "1",
                                    "user" => current_user()->id
                                    )
                                );
        $item_toevoegen = 
        $folktale_html = "";
        $folktale_html .= "<H1>Volksverhalenbank functies</H1><br>";
        $folktale_html .= "<a class='small blue advanced-search-link button' href='/vb/admin/items/search'>Geavanceerd zoeken</a>";
        $folktale_html .= "<a href='/vb/admin/items/add' class='add button small green'>Voeg een item toe</a><br>";

        $folktale_html .= "<H2>Invoerhulp websites / lijsten</H2><br>";
        $folktale_html .= '<UL STYLE="list-style-type: disc;">';
        $folktale_html .= "<li><a target='manual' href='http://bookstore.ewi.utwente.nl/docs/Handleiding%20Nieuwe%20Volksverhalenbank%20Versie%202.pdf'><b>Handleiding</b> invoer Nederlandse Volksverhalenbank</a><br>";
        $folktale_html .= "<li><a target='motieven' href='http://www.dinor.demon.nl/Thompson/'>Browse/zoek <b>Thompson movieven</b> (website Dirk Kramer)</a><br>";
        $folktale_html .= "<li><a target='kloekenummers' href='http://www.meertens.knaw.nl/kloeke/'>Zoek <b>Kloeke nummers</b> (website Meertens)</a><br>";
        $folktale_html .= "</UL><br>";
                
        $folktale_html .= "<H2>Volksverhalen lijsten</H2><br>";
        $folktale_html .= '<UL STYLE="list-style-type: disc;">';
        $folktale_html .= "<li><a href = '$all_tales_browse'>Browse <b>alle</b> volksverhalen</a><br>";
        $folktale_html .= "<li><a href = '$private_tales_browse'>Browse <b>prive</b> volksverhalen</a><br>";
        $folktale_html .= "<li><a href = '$public_tales_browse'>Browse <b>publieke</b> volksverhalen</a><br>";
        $folktale_html .= "<li><a href = '$own_tales_browse'>Browse <b>zelf toegevoegde</b> volksverhalen</a><br>";
        $folktale_html .= "</ul><br>";
        
        $folktale_html .= "<H2>Volksverhaaltype lijsten</H2><br>";
        $folktale_html .= '<UL STYLE="list-style-type: disc;">';
        $folktale_html .= "<li><a href = '".$this->_verhaaltype_lijst("Theo Meder")."'>Browse <b>Theo Meder</b> Verhaaltypen</a><br>";
        $folktale_html .= "<li><a href = '".$this->_verhaaltype_lijst("ATU")."'>Browse <b>ATU</b> Verhaaltypen</a><br>";
        $folktale_html .= "<li><a href = '".$this->_verhaaltype_lijst("Aarne Thompson")."'>Browse <b>Aarne Thompson</b> Verhaaltypen</a><br>";
        $folktale_html .= "<li><a href = '".$this->_verhaaltype_lijst("Brunvand")."'>Browse <b>Brunvand</b> Verhaaltypen</a><br>";
        $folktale_html .= "<li><a href = '".$this->_verhaaltype_lijst("Sinninghe")."'>Browse <b>Sinninghe</b> Verhaaltypen</a><br>";
        $folktale_html .= "<li><a href = '".$this->_verhaaltype_lijst("Van der Kooi")."'>Browse <b>Van der Kooi</b> Verhaaltypen</a><br>";
        $folktale_html .= "</UL><br>";
    
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