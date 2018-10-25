<?php
/**
 * @group plugin_inlineedit
 * @group plugins
 */
class plugin_inlineedit_syntax_test extends DokuWikiTest {

    public function setup() {
        $this->pluginsEnabled[] = 'inlineedit';
        $this->pluginsEnabled[] = 'ajaxedit';
        parent::setup();
    }

    
    public function test_basic_syntax() {
        global $INFO;
        $INFO['id'] = 'test:plugin_inlineedit:syntax';
        saveWikiText('test:plugin_inlineedit:syntax','<inlineedit>Test</inlineedit>','test');
        
        $xhtml = p_wiki_xhtml('test:plugin_inlineedit:syntax');

        $doc = phpQuery::newDocument($xhtml);
        
        $mselector = pq("span.plugin__inlineedit",$doc);
        $this->assertTrue($mselector->length === 1);
        $this->assertContains('Test', $mselector->text());
        
    }
}
