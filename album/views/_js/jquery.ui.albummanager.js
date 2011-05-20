function mysqlTimeStampToDate(timestamp) {
	//function parses mysql datetime string and returns javascript Date object
	//input has to be in this format: 2007-06-05 15:26:02
	var regex=/^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9]) (?:([0-2][0-9]):([0-5][0-9]):([0-5][0-9]))?$/;
	var parts=timestamp.replace(regex,"$1 $2 $3 $4 $5 $6").split(' ');
	return new Date(parts[0],parts[1]-1,parts[2],parts[3],parts[4],parts[5]);
}
	
jQuery.fn.dataTableExt._fnFeatureHtmlProcessing = function ( oSettings )
{
	var nProcessing = jQuery( 'div', 
		{
			'class': oSettings.oClasses.sProcessing, 
			'html': oSettings.oLanguage.sProcessing,
			'color': 'red'
		} 
	);

	if ( oSettings.sTableId !== '' && typeof oSettings.aanFeatures.r == "undefined" )
	{
		nProcessing.attr('id', oSettings.sTableId+'_processing' );
	}
	jQuery(oSettings.nTable).insertBefore( nProcessing );

	return nProcessing[0];
};
jQuery.fn.dataTableExt.oApi. _fnProcessingDisplay = function ( oSettings, bShow )	
{
	if ( oSettings.oFeatures.bProcessing )
	{
		var an = oSettings.aanFeatures.r;
		for ( var i=0, iLen=an.length ; i<iLen ; i++ )
		{
			jQuery(an[i])[bShow ? "show" : "hide"]('slow');
		}
	}
}

jQuery.widget("ui.albummanager", {
   // default options
   options: {
	   root: '/',
	   ajaxSource: "",
	   columns: null,
	   sorting: false,
	   fixedSorting: [[0, "asc"]],
	   prevDirIcon: 'ui-icon-arrowthick-1-w',
	   nextDirIcon: 'ui-icon-arrowthick-1-e',
	   fileDownloadIcon: 'ui-icon-download-1',
	   icons: {
		   'dir': 'ui-icon-folder-collapsed',
		   'file': 'ui-icon-document'
	   },
	   dirPostOpenCallback: null,
	   dirDoubleClickCallback: null,
	   fileDoubleClickCallback: null,
	   mouseDownCallback: null,
	   serverData: null,
	   rowCallback: null,
	   animationSpeed: 600,
	   managerMode: false
   },
   _create: function() {
	   var self = this;
	   cols = this.options.columns;
	   
	   if(!cols) {
		   cols = [
		   { "sWidth": "auto", "bSortable": false, "aaSorting": [ "asc" ], "sClass": "ui-albummanager-column-album" },
		   { "sWidth": "15%", "bSortable": false, "aaSorting": [ "asc" ], "sClass": "ui-albummanager-column-created" },
		   { "sWidth": "15%", "bSortable": false, "aaSorting": [ "asc" ], "sClass": "ui-albummanager-column-modified" },
		   { "sWidth": "30px", "bSortable": false, "sClass": "ui-albummanager-column-next" }
	   ];
	   }
	   this.is_disabled = false;
	   this.multiselect = false;
	   this.last_selected = null;
	   this.was_shift_key = false;
	   this._images = [];
	   this.element.addClass("ui-albummanager");

	   var buttonbars = jQuery("<div/>", {'class': 'ui-albummanager-buttonbar' });
	   this.managerBar = jQuery("<div/>", {'class': 'ui-albummanager-subbuttonbar ui-album-managerbar ui-helper-hidden' }).buttonset().appendTo(buttonbars);
	   this.buttonBar = jQuery("<div/>", {'class': 'ui-albummanager-subbuttonbar' }).buttonset().appendTo(buttonbars);
	   this.pathWidget = jQuery('<div/>', {'class': 'ui-albummanager-path-widget' });
	   jQuery(window).bind('resize.albummanager', function() {
			   self.pathWidget.width(self.element.width() - self.buttonBar.width() - self.managerBar.width())
		   }
	   ).triggerHandler('resize.albummanager');
	   this.element.dataTable({
			   'oClasses': {
				   'sSortJUIAsc': 'ui-icon ui-icon-triangle-1-n',
				   'sSortJUIDesc': 'ui-icon ui-icon-triangle-1-s',
				   'sSortJUI': 'ui-icon ui-icon-carat-2-n-s'
			   },
			   "oLanguage": {
				   'sZeroRecords': ''
			   },
			   "sDom": '<"H"r>t',
			   "asStripClasses": [ "ui-albummanager-row-odd", "ui-albummanager-row-even" ],
			   "bJQueryUI": true,
			   "bFilter": false,
			   "bInfo": false,
			   "bSort": !!this.options.sorting,
			   "bPaginate": false,
			   "bProcessing": false,
			   "sAjaxSource": this.options.ajaxSource,
			   "aaSorting": this.options.sorting,
			   "aaSortingFixed": this.options.fixedSorting,
			   "bAutoWidth": false,
			   "aoColumns": cols,
			   "fnServerData": this.options.serverData ? jQuery.proxy(this.options.serverData,this) : function ( source, indata, callback ) {
				   jQuery.throbber.show();
				   indata = jQuery.extend({
							   'manager_mode': self.options.managerMode
						   },
					   indata);				   
				   jQuery.ajax( {
						   "dataType": 'json', 
						   "type": "POST", 
						   "url": source, 
						   "data": jQuery.isEmptyObject(indata) ? { path: self.options.root } : indata, 
						   "success": function(data){
							   self.options.root = data.root;
							   self._images = data.images;
							   self.current = data.meta;
							   var out_data = [];
							   jQuery.each(data.albums, function( index, value ) {
									   out_data.push( [JSON.stringify(value),'','',''] );
								   }
							   );
							   var param = { 'aaData': out_data };
							   callback.apply(this, [param]);

							   self.pathWidget.empty();
							   //jQuery(window).triggerHandler('resize.albummanager');

							   divider = jQuery('<span/>', 
								   {
									   text : '', 
									   'class': 'ui-albummanager-path-divider'
								   }
							   );
							   var parent_albums = data.parent_albums.slice();
							   parent_albums.unshift( { 'id': null, 'name': 'Home' } );

							   jQuery.each( parent_albums, function(index, value) {
									   if(index != 0){
										   self.pathWidget.append( divider.clone() );
									   }
									   var a = jQuery('<a/>', 
										   {
											   data : {'path':value.id}, 
											   html : value.name, 
											   'class':  'ui-albummanager-path-link'
										   }
									   ).click(function(){
											   self._reloadAjax( { 'data': { 'path': value.id } }, function(){
													   self.options.dirPostOpenCallback.apply( self, arguments );
												   } 
											   );
										   }
									   );
									   self.pathWidget.append( a );
								   }
							   );
							   divider.remove(); // the last one
							   if( data.parents.length ) {
								   var last = data.parents[data.parents.length-1];
							   } else {
								   var last = null;
							   }
							   jQuery('.ui-albummanager-fake-updir', self.element).html(jQuery('<a/>',{
										   text: '',
										   'class': 'ui-albummanager-prev-arrow ui-icon ' + self.options.prevDirIcon,
										   click: function() {
											   self._dirCallback.apply( self, [ this, { path : last, direction: 'right' } ] );
										   } 
									   }
								   )
							   );

							   jQuery.throbber.hide();
							   if($("#fn-images").children("a").length) {
								$("#fn-albummanager-button-slideshow").removeClass("ui-state-disabled");
								$("#fn-albummanager-button-slideshow").removeClass("ui-button-disabled");
							   } else {
								$("#fn-albummanager-button-slideshow").addClass("ui-state-disabled");
								$("#fn-albummanager-button-slideshow").addClass("ui-button-disabled");
							   }
						   }
					   } 
				   );
			   },
			   "fnDrawCallback": function() {
				   var images = $('#fn-images');
				   images.empty();
				   $("#fn-albummanager-image-header-albumname").empty();

				   $.each( self._images.sort(function(a,b){return naturalSort(a.name, b.name)}), function() {
						   var acell = jQuery('#fn-templates .ui-album-image').clone();
						   acell.css(
							   {
								   'background-image': "url("+ config.prefix + "/image/thumb/" + this.id +")",
								   'background-position': 'center center'
							   }

						   ).attr(
							   {
								   'href': config.prefix + "/image/medium/" + this.id,
								   'rel': 'fn-image',
								   'title': this.name,
								   'caption': this.caption
							   }
						   ).data({
						   'path': this.id,
						   'name': this.name,
						   'caption': this.caption
					   });
						   images.append(acell);

					   }
				   );
				   if( self.options.afterDrawImagesCallback ) {
					   self.options.afterDrawImagesCallback.apply( this, [ self.element ] );
				   }
				   var infobox = $('#fn-album-infobox');
				   if( self.current ) {
					   if( self.current.created == '0000-00-00 00:00:00' ) {
						   var created = '';
					   } else {
						   var created = mysqlTimeStampToDate(self.current.created).toDateString();
					   }
					   if( self.current.modified == '0000-00-00 00:00:00' ) {
						   var modified = '';
					   } else {
						   var modified = mysqlTimeStampToDate(self.current.modified).toDateString();
					   }

					   /*
					   $('.ui-album-title', infobox).html(
							   "<span class='ui-albummanager-album-name'>"+$.message("ui-albummanager-album-name")+":</span> " + self.current.name
					   		);
					   */
					   if(self.current.name) {
						   $("#fn-albummanager-image-header-albumname").text($.message("ui-albummangaer-images-in-album") + " '" + self.current.name +"'");
					   }
					   if( self.current.caption ) {
						   $('.ui-album-caption', infobox).html(
								   "<span class='ui-albummanager-album-caption'>"+$.message("ui-albummanager-album-caption")+":</span> " + self.current.caption.replace( /\n/g , "<br/>" )
						   );
					   } else {
						   $('.ui-album-caption', infobox).empty();
					   }

					   $('.ui-album-created', infobox).html(created);
					   $('.ui-album-modified', infobox).html(modified);
				   } else {
					   $('.ui-album-title, .ui-album-caption, .ui-album-created, .ui-album-modified', infobox).empty();
				   }

				   if( self.options.mouseDownCallback ) {
					   self.options.mouseDownCallback.apply( this, [ self.element ] );
				   }
			   },
			   "fnRowCallback": this.options.rowCallback ? this.options.rowCallback : function( nRow, aData, iDisplayIndex ) {
				   var data = JSON.parse(aData[0]);
				   var cell = jQuery('#fn-templates .ui-album-body').clone();
				   if( data.image_id ) {
					   cell.find(".ui-album-thumbnail").css(
						   {
							   'background-image': "url("+ config.prefix + "/image/thumb/" + data.image_id +")",
							   'background-position': 'center center'
						   }

					   );
				   }
				   cell.find(".ui-album-public").toggleClass( 'ui-album-private', !data.public );

				   cell.find(".ui-album-title").html(data.name);
				   cell.find(".ui-album-caption").html(data.caption);
				   if( data.subalbum_count ) {
					   cell.find(".ui-album-count").html($.message("album-image-subalbum-count", data.image_count, data.subalbum_count));
				   } else {
					   cell.find(".ui-album-count").html($.message("album-image-count", data.image_count));
				   }
				   jQuery('td:eq(0)',nRow).html( cell );
				   if( data.created == '0000-00-00 00:00:00' ) {
					   var created = '';
				   } else {
					   var created = mysqlTimeStampToDate(data.created).toDateString();
				   }
				   if( data.modified == '0000-00-00 00:00:00' ) {
					   var modified = '';
				   } else {
					   var modified = mysqlTimeStampToDate(data.modified).toDateString();
				   }

				   jQuery('td:eq(1)',nRow).text( created );
				   jQuery('td:eq(2)',nRow).text( modified );
				   jQuery("td:eq(3)",nRow).html(
					   jQuery("<span/>",
						   {
							   text: "", 
							   'class': 'ui-albummanager-next-arrow ui-icon ' + self.options.nextDirIcon
						   }
					   )
				   ).data('path', data.id )
				   .bind( 'click.albummanager', function() {
						   self._dirCallback.apply( self, [ this, {path: data.id} ] );
					   }
				   );				   
				   jQuery("td:eq(3)",nRow).hover(function(){jQuery(this).toggleClass("ui-state-hover")});
				   jQuery(nRow).addClass("ui-albummanager-state-hover ui-albummanager-type-dir" )
				   .data({
						   'path': data.id,
						   'name': data.name,
						   'caption': data.caption
					   }
				   );

				   return nRow;
			   }


		   }
	   );
	   this.toolbar = this.element.prev();
	   this.toolbar.prepend( this.pathWidget );
	   this.toolbar.append( buttonbars );

	   var select_callback = function(event, image ) {
			   if( this.is_disabled ) {
				   return false;
			   }
			   if( image ) {
				   var objs = jQuery('tbody tr',self.element);
			   } else {
				   var objs = jQuery('tfoot .ui-album-images a.ui-album-image',self.element);
			   }

			   if( objs.filter('.ui-albummanager-state-selected') ) {
				   objs.removeClass("ui-albummanager-state-selected");
				   this.multiselect = false;
				   this.last_selected = null;
				   this.was_shift_key = false;
			   }

			   jQuery(this).siblings().andSelf().removeClass("ui-albummanager-state-dblckick");
			   if( event.shiftKey ) {
				   jQuery(this).siblings().andSelf().removeClass("ui-albummanager-state-selected");
				   var last = self.last_selected;
				   if( last ) {
					   self.multiselect = true;
					   var cur_idx = jQuery(this).index();
					   var last_idx = jQuery(last).index();
					   var objs;

					   if( cur_idx < last_idx ) {
						   objs = jQuery(this).siblings().andSelf().filter(function(){
								   return jQuery(this).index() >= cur_idx && jQuery(this).index() <= last_idx;
							   }
						   );
					   } else {
						   objs = jQuery(this).siblings().andSelf().filter(function(){
								   return jQuery(this).index() <= cur_idx && jQuery(this).index() >= last_idx;
							   }
						   );
					   }
					   objs.addClass('ui-albummanager-state-selected');
					   if( ! self.was_shift_key ) {
						   self.last_selected = this;
					   }
					   self.was_shift_key = true;
				   }

			   } else if( event.ctrlKey ) {
				   self.was_shift_key = true;
				   self.multiselect = true;
				   jQuery(this).toggleClass('ui-albummanager-state-selected');
				   self.last_selected = this;
			   } else {
				   self.was_shift_key = true;
				   if( self.multiselect ) {
					   // We where in multi-select mode, 
					   // thus we should act as there wasn't anything selected in the first place
					   self.multiselect = false;
					   self.last_selected = this;
					   jQuery(this).siblings().andSelf().removeClass("ui-albummanager-state-selected");
					   jQuery(this).addClass('ui-albummanager-state-selected');
				   } else {
					   last = self.last_selected;
					   if( last ) {
						   if( last == this ) {
							   jQuery(this).toggleClass('ui-albummanager-state-selected');
						   } else {
							   jQuery(last).removeClass('ui-albummanager-state-selected');
							   jQuery(this).addClass('ui-albummanager-state-selected');
							   self.last_selected = this;
						   }
					   } else {
						   jQuery(this).addClass('ui-albummanager-state-selected');
						   self.last_selected = this;
					   }
				   }

			   }

			   if( self.options.mouseDownCallback ) {
				   self.options.mouseDownCallback.apply( this, [ self.element ] );
			   }
			   return false;

		   };

		   jQuery("tbody", this.element)
		   .delegate( 
			   'tr.ui-album-manager-mode', 
			   'mousedown',	
			   function(event){
				   select_callback.apply(this, [event,false])
			   } 
		   );	   

		   jQuery("tfoot .ui-album-images", this.element)
		   .delegate( 
			   'a.ui-album-image.ui-album-manager-mode', 
			   'mousedown',	
			   function(event){
				   select_callback.apply(this, [event,true])
			   } 
		   );	   

	   jQuery("tbody", this.element).delegate( 'tr', 'dblclick', function(event) {
			   // MSIE did it again
			   if(document.selection && document.selection.empty){
				   document.selection.empty() ;
			   } else if(window.getSelection) {
				   var sel=window.getSelection();
				   if(sel && sel.removeAllRanges)
					   sel.removeAllRanges() ;
			   }
			   event.preventDefault();

			   jQuery(this).addClass("ui-albummanager-state-dblckick");
			   self._dirCallback.apply( self, [ this, {path:jQuery(this).data('path')} ] );
			   return false;
		   }
	   );	   
   },
   setButtons: function( buttons ) {
	   var self = this;
	   this.buttonBar.empty();
	   this.managerBar.empty();
	   jQuery.each(buttons, function(index, value) {
			   jQuery("<button/>", {'html': value.alt, 'id': value.id, 'class': value.klass})
			   .appendTo( value.manager ? self.managerBar : self.buttonBar).button( { 
					   'text': false, 
					   'icons': { 
						   'primary': value.type 
					   }
				   } ).data('is_disabled', value.disabled )
			   .button( value.disabled ? 'disable': 'enable' )
			   .click(function(e){
				   jQuery(this).blur();
			   	})
			   .click(function(){
					   if(! jQuery(this).hasClass("ui-state-disabled") ) {
						   value.callback.apply(self.element, arguments);
					   }
				});
		   });
   },
   disableButtons: function( disable ) {
	   if( typeof disable == 'undefined' || disable ) {
		   this.buttonBar.find('button').button("disable");
		   this.managerBar.find('button').button("disable");
	   } else {
		   this.buttonBar.find("button").each(function(){$(this).button( $(this).data("is_disabled") ? 'disable': 'enable' )});
		   this.managerBar.find("button").each(function(){$(this).button( $(this).data("is_disabled") ? 'disable': 'enable' )});
	   }
   },
   setActive: function( active ) {
	   this.is_disabled = ! (typeof active == 'undefined' || active);
   },
   setManagerMode: function( enabled ) {
	   this.options.managerMode = typeof enabled == 'undefined' || !!enabled;
   },
   _reloadAjax: function( options, callback ) {
	   var self = this;

	   var settings = self.element.fnSettings();
	   options = jQuery.extend(true, {
			   'path': settings.sAjaxSource,
			   'data': {
				   'path': self.options.root
			   },
			   'redraw': true
		   }, options);
	   
	   settings.sAjaxSource = options.path;
	   self.element.oApi._fnProcessingDisplay( settings, true );

	   settings.fnServerData( settings.sAjaxSource, options.data, function(json) {
			   // Clear the old information from the table
			   self.element.oApi._fnClearTable( settings );

			   // Got the data - add it to the table
			   for ( var i=0 ; i<json.aaData.length ; i++ ) {
				   self.element.oApi._fnAddData( settings, json.aaData[i] );
			   }

			   settings.aiDisplay = settings.aiDisplayMaster.slice();
			   if( options.redraw ) {
				   self.element.fnDraw( self.element );
			   }
			   self.element.oApi._fnProcessingDisplay( settings, false );

			   // Callback user function - for event handlers etc 
			   if ( typeof callback == 'function' )
			   {
				   callback.apply( self, [ json ] );
			   }
		   }
	   );
   },
   reload: function( callback ) {
	   this._reloadAjax(
		   {}, 
		   callback
	   );
   },
   getFirstSelectedMetadata: function() {
	   var albums = jQuery("tbody .ui-albummanager-state-selected", this.element );
	   if( albums.size() ) {
		   return {
			   'name': albums.data('name'),
			   'caption': albums.data('caption'),
			   'id': albums.data('path'),
			   'type': 'album'
		   };
	   }
	   var images = jQuery("tfoot .ui-albummanager-state-selected", this.element );
	   if( images.size() ) {
		   return {
			   'name': images.data('name'),
			   'caption': images.data('caption'),
			   'id': images.data('path'),
			   'type': 'image'
		   };
	   }
	   return null;
   },
   getSelectedAlbums: function() {
	   return jQuery("tbody .ui-albummanager-state-selected", this.element ).map(function(){ return jQuery(this).data('path') }).get();
   },
   getSelectedImages: function() {
	   return jQuery("tfoot .ui-albummanager-state-selected", this.element ).map(function(){ return jQuery(this).data('path') }).get();
   },
  
   _fileCallback: function( row, options ){
	   var self = this;
	   if( self.options.fileDoubleClickCallback ) {
		   self.options.fileDoubleClickCallback.apply( self, [ row, options ] );
	   }
   },
   _dirCallback: function( row, options ){
	   var self = this;
	   options = jQuery.extend({direction: "left"},options);
	   var orig_width = self.element.outerWidth();
	   var orig_height = self.element.outerHeight();
	   var offset = self.element.offset();

	   var fake = self.element.clone();
	   fake.css({
			   width: orig_width,
			   height: orig_height

		   }
	   );
	   fake.find('.ui-albummanager-state-hover').removeClass('ui-albummanager-state-hover');
	   fake.removeAttr('id').css({margin: 0}).addClass('ui-fake');
	   var wrap = $('<div/>');
	   wrap.append(fake);
	   wrap.hide().appendTo('body').css(
		   {
			   position: 'absolute',
			   width: orig_width,
			   height: orig_height,
			   left: offset.left,
			   top: offset.top,
			   margin: 0,
			   padding: 0
		   }
	   ).show();
	   self.element.hide();

	   self._reloadAjax( { path: self.element.data('path'), redraw: false, data: { path: options.path }  }, function( json ){

			   self.element.fnDraw();
			   var fake2 = self.element.clone();
			   fake2.removeAttr('id').css({margin: 0}).addClass('ui-fake').hide();
			   fake2.find('.ui-albummanager-state-hover').removeClass('ui-albummanager-state-hover');
			   wrap.append(fake2);
			   fake2.css(
				   {
					   position: 'absolute',
					   left: options.direction == 'left' ? orig_width : -orig_width,
					   width: orig_width,
					   top: 0,
					   margin: 0,
					   padding: 0
				   }
			   ).show();
			   wrap2 = $('<div/>');
			   wrap2.appendTo('body');
			   wrap2.css({
					   position: 'absolute',
					   overflow: 'hidden',
					   width: orig_width,
					   height: $(window).height() - offset.top,
					   left: offset.left,
					   top: offset.top,
					   margin: 0,
					   padding: 0					   
				   }
			   );
			   wrap2.append(wrap);
			   wrap.css({
					   position: 'absolute',
					   width: orig_width + self.element.outerWidth(),
					   height: orig_height,
					   left: options.direction == 'left' ? 0: 0,
					   top: 0,
					   margin: 0,
					   padding: 0
				   }
			   );
			   fake.data('name', 'fake');
			   fake2.data('name', 'fake2');
			   wrap.animate(
				   {
					   left: options.direction == 'left' ? -orig_width : orig_width
				   },
				   {
					   duration: 1000,
					   easing: "easeOutExpo",
					   complete:function() {
						   self.element.show();
						   wrap2.remove();

					   }
				   }
			   );
			   if( self.options.dirPostOpenCallback ) {
				   self.options.dirPostOpenCallback.apply( self, arguments );
			   }
		   }
	   );

	   if( self.options.dirDoubleClickCallback ) {
		   self.options.dirDoubleClickCallback.apply( self, [ row, options ] );
	   }
   },
   value: function() {
	   return this.options.root;
   },
   albumLength: function() {
	   return jQuery("tbody .ui-albummanager-state-selected", this.element ).length;
   },
   imageLength: function() {
	   return jQuery("tfoot .ui-albummanager-state-selected", this.element ).length;
   },
   length: function() {
	   return this.albumLength + this.imageLength;
   },
   
   destroy: function() {

       jQuery.Widget.prototype.destroy.apply(this, arguments); // default destroy
        // now do other stuff particular to this widget
   }
 });

