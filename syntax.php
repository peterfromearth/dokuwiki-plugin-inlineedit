<?php
/*
 * Plugin: inlineedit
 *
 * @license    
 * @author   peterfromearth
 */

if(!defined('DOKU_INC')) die();

/*
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_inlineedit extends DokuWiki_Syntax_Plugin {

    private $_itemPos = array();
    function incItemPos() {
        global $ID;
        if(array_key_exists($ID,$this->_itemPos)) {
            return $this->_itemPos[$ID]++;
        } else {
            $this->_itemPos[$ID] = 1;
            return 0;
        }
    }
    function getItemPos(){
        global $ID;
        if(array_key_exists($ID,$this->_itemPos)) {
            $this->_itemPos[$ID];
        } else {
            return 0;
        }
    }

    /*
     * What kind of syntax are we?
     */
    function getType() {
        return 'formatting';
    }

    /*
     * Where to sort in?
     */
    function getSort() {
        return 201;
    }

    /*
     * Paragraph Type
     */
    function getPType() {
        return 'normal';
    }
    
    function getAllowedTypes() {
        return array(
            'disabled'
        );
    }

    /**
     * Connect pattern to lexer
     */
    function connectTo($mode) {
        $this->Lexer->addEntryPattern('<inlineedit>',$mode,'plugin_inlineedit');
    }
 
    function postConnect() {
        $this->Lexer->addExitPattern('</inlineedit>','plugin_inlineedit');
    }

    /*
     * Handle the matches
     */
    function handle($match, $state, $pos, Doku_Handler $handler){
        global $ID;
        
        $opts = array(
            'pageid' => $ID,
        );
        switch ($state) {
            case DOKU_LEXER_ENTER :
                $opts['itemPos']=$this->incItemPos();
                break;
            case DOKU_LEXER_UNMATCHED :
                $text = $match ? trim($match) : '...';
                $opts['text'] =  $text;
                break;
            case DOKU_LEXER_EXIT :
                break;
        }
        return array($state, $opts);
    }

    /*
     * Create output
     */
    function render($mode, Doku_Renderer $renderer, $data)
    {
        global $INFO;
        
        list($state, $opts) = $data;
        
        if($mode == 'xhtml' && $opts['pageid'] === $INFO['id']) {    
            
            switch ($state) {
                case DOKU_LEXER_ENTER :
                    $itemPos = $opts['itemPos'];
                    $pageid = hsc($INFO['id']);
                    $renderer->doc .= "<span class='plugin__inlineedit' id='inlineedit_span_$itemPos' data-plugin-inlineedit-pageid='$pageid' data-plugin-inlineedit-itempos='$itemPos'>";
                    break;
                case DOKU_LEXER_UNMATCHED :
                    $text = $opts['text'];
                    if(!$text) $text = '...';
                    $renderer->doc .= $renderer->_xmlEntities($text);
                    break;
                case DOKU_LEXER_EXIT :
                    $renderer->doc .= "</span>";
                    break;
            }
        } else {
            if($state === DOKU_LEXER_UNMATCHED) {
                $renderer->doc .= $opts['text'];
            }
        }
        return true;
    }

}

//Setup VIM: ex: et ts=4 enc=utf-8 :
