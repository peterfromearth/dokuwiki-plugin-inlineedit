/**
 * DokuWiki Plugin inlineedit (Script Component)
 *
 * @author  lisps <lisps@users.noreply.github.com>
 */
jQuery(function(){
    //
    if(JSINFO && JSINFO['acl_write']) {
        var itemPos = jQuery('span.plugin__inlineedit[data-plugin-inlineedit-pageid="'+JSINFO['id']+'"]').addClass('active');
        
    } 
    

    jQuery('#dokuwiki__content').on('click', 'span.plugin__inlineedit.active', function() {
        var $self = jQuery(this);
        var input = prompt(LANG.plugins.inlineedit.promt_title, $self.html());    
        if(input == null) return;
        if(input == $self.html()) return;
        var idx = null;
		if(jQuery($self).parents('div.sortable').length != 0) {
			idx = jQuery($self).data("plugin-inlineedit-itempos");
		} else {
			idx =  ajaxedit_getIdxBySelector($self,'span.plugin__inlineedit[data-plugin-inlineedit-pageid="'+JSINFO['id']+'"]');
		}
        ajaxedit_send2(
            'inlineedit',
            idx,
            function (data) {
                var ret = ajaxedit_parse(data);
                ajaxedit_checkResponse(ret);
                
                $self.html(ret.text);
            },
            {
                input:input,
            }        
        );
    });

});

function ajaxedit_getIdxBySelector($elem,selector) {
    var id = $elem.attr('id');
    var $els = jQuery(selector);

    for(var ii=0;ii<$els.size();ii++){
        if($els[ii].id == id) {
            return ii;
        }     
    }
}
 

