<?php

/**
 * InlineEdit Action Plugin
 *
 * 
 * @author     peterfromearth
 */

if (!defined('DOKU_INC')) die();
class action_plugin_inlineedit extends DokuWiki_Action_Plugin {

    /**
     * Register the eventhandlers
     */
    function register(Doku_Event_Handler $controller) {
//         $controller->register_hook('TOOLBAR_DEFINE', 'AFTER', $this, 'insert_button', array ());
        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE',  $this, '_ajax_call');
    }
    
    /**
     * Inserts the toolbar button
     */
    function insert_button(Doku_Event $event, $param) {
        $event->data[] = array(    
            'type'   => 'format',
            'title' => 'InlineEdit',
            'icon'   => '../../plugins/inlineedit/images/inlineedit.png',
            'sample' => '',
            'open' => '<inlineedit>',
            'close'=>'</inlineedit>',
            'insert'=>'',
        );
    }
    
    function _ajax_call(Doku_Event $event, $param) {
        if ($event->data !== 'plugin_inlineedit') {
            return;
        }
        //no other ajax call handlers needed
        $event->stopPropagation();
        $event->preventDefault();
        
        /* @var $INPUT \Input */
        global $INPUT;
        
        $itemPos = $INPUT->int('id'); //input index on the server
        $input_str = trim($INPUT->str('input')); //input string

        /* @var $Hajax \helper_plugin_ajaxedit */
        $Hajax = $this->loadHelper('ajaxedit');
                
        $data=$Hajax->getWikiPage(); //
        
        //find "our" fsinput fields
        $found=explode("<inlineedit>",$data);
        
        //find pagemods
        @preg_match_all('=<pagemod.*?>[\w\W]*?</pagemod>=',$data,$pagemod_found, PREG_OFFSET_CAPTURE);        

        $calc_index = -1;
        if ($itemPos < count($found)) {
            for($index = 0; $index < count($found); $index++) {
                $offset = 0;
                for($ii = 0; $ii<=$index; $ii++) {
                    $offset += strlen($found[$ii])+8;
                }
                
                $pagemod_found_flag = 0;
                foreach($pagemod_found[0] as $pagemod_f) { //check if fsinput is in pagemod area
                    if($offset > $pagemod_f[1] && $offset< ($pagemod_f[1]+strlen($pagemod_f[0]))) {
                        $pagemod_found_flag = 1;
                    } 
//                     print_r([$index,$calc_index,$input_index,$offset,$pagemod_f,($pagemod_f[1]+strlen($pagemod_f[0]))]);
                }
                if(!$pagemod_found_flag) { //we are looking for fsinput outside of pagemod
                    $calc_index++;
                }
                if($calc_index == $itemPos){
                    break ;
                }
            }
            
            $found[$index+1] = ltrim($found[$index+1]);
            $stop=strpos($found[$index+1],"</inlineedit>");
            if ($stop === FALSE) {
                $Hajax->error('Cannot find object, please contact your admin!');
            }
            else {
                $oldstr = substr($found[$index+1],0,$stop);

                $newstr=$input_str;
        
                if($stop == 0){
                    $found[$index+1]= $newstr.$found[$index+1];
                }
                else {
                    $found[$index+1]=str_replace($oldstr,$newstr,$found[$index+1]);
                }
            }
            
            $displayStr = $newstr ? $newstr : '...';
            $data=implode("<inlineedit>",$found);
            $param = array(
                    'text'        => $displayStr,
                    'msg'        => $this->getLang('success'),
            );
            $summary = "Inlineedit ".$itemPos." changed from ".$oldstr." to ".$newstr;
            $Hajax->saveWikiPage($data,$summary,false,$param);
        }
    }

}
