<div class="album_caption album_section"><?=$caption?$caption:"&nbsp;"?></div>

	<?if(isset($albums)):?>
<div class="fn-subalbum-container ui-widget ui-corner-all">
		<div class="controls" id="fn-subalbum-header">Subalbums</div>
		<div class="fn-subalbum-content">
		<?foreach( array_chunk( $albums, $this->config->item('album_width') ) as $row ):?>
			<?foreach( $row as $album ):?>
					<div class="fn-subalbum-item">
						<?if(is_null($album['image_id'])):?>
						<?=anchor("album/section/$album[id]",img("image/blank/$album[id]", true))?>
						<?else:?>
						<?=anchor("album/section/$album[id]",img("image/thumb/$album[image_id]", true),array("class" => "thumb"))?>
						<?endif?>
					<div class="album_name subalbum"><?=$album['name']?></div>
					<div class="album_caption subalbum"><?=$album['caption']?></div>
					</div>
			<?endforeach?>
		<?endforeach?>
		</div>
</div>
	<?endif?>
<div id="fn-album-container">


<?if(isset($images)):?>

<div id="gallery-section" class="content">
	<div id="controls-section" class="controls"></div>
	<div id="slideshow-section" class="slideshow"></div>
	<div id="loading-section" class="loader"></div>
	<div id="caption-section" class="embox"></div>
</div>
<div id="thumbs-section" class="navigation">
	<?foreach( array_chunk( $images, $this->config->item('thumbs_col') ) as $row ):?>
		<ul class="thumbs noscript">
		<?foreach( $row as $image ):?>
			<li>
	
			<?=anchor("image/medium/$image[id]",$image['name'],array( 'title' => $image['name'], 'class' => 'thumb' ,"name" => "image/thumb/$image[id]" ))?>
	
			<div class="caption">
				<div class="download">
				<?=anchor("image/view/$image[id]","[View original]")?>
				<?=anchor("image/download/$image[id]","[Download original]")?>
				</div>
				<div class="image-title"><?=$image['name']?></div>
				<div class="image-desc"><?=$image['caption']?></div>
			</div>
			</li>
		<?endforeach?>
		</ul>
	<?endforeach?>
</div>
</div>

<?endif?>
<script type="text/javascript">
			// Initially set opacity on thumbs and add
			// additional styling for hover effect on thumbs
			var onMouseOutOpacity = 0.67;
			$('#thumbs-section ul.thumbs li').css('opacity', onMouseOutOpacity)
				.hover(
					function () {
						$(this).not('.selected').fadeTo('fast', 1.0);
					}, 
					function () {
						$(this).not('.selected').fadeTo('fast', onMouseOutOpacity);
					}
				);

			$('#subalbum_container img').css('opacity', onMouseOutOpacity)
				.hover(
					function () {
						$(this).not('.selected').fadeTo('fast', 1.0);
					}, 
					function () {
						$(this).not('.selected').fadeTo('fast', onMouseOutOpacity);
					}
				);

$(document).ready(function() {
    var gallery = $('#gallery-section').galleriffic('#thumbs-section', {
				delay:                  	5000,
					numThumbs:              10,
					preloadAhead:           10,
					enableTopPager:         true,
					enableBottomPager:      false,
					imageContainerSel:      '#slideshow-section',
					controlsContainerSel:   '#controls-section',
					captionContainerSel:    '#caption-section',
					loadingContainerSel:    '#loading-section',
					renderSSControls:       true,
					renderNavControls:      true,
					playLinkText:           'Play Slideshow',
					pauseLinkText:          'Pause Slideshow',
					prevLinkText:           '&lsaquo; Previous Photo',
					nextLinkText:           'Next Photo &rsaquo;',
					nextPageLinkText:       '&rsaquo;&rsaquo;',
					prevPageLinkText:       '&lsaquo;&lsaquo;',
					enableHistory:          true,
					autoStart:              false,
					onChange:               function(prevIndex, nextIndex) {
						$('#thumbs-section ul.thumbs').children()
							.eq(prevIndex).fadeTo('fast', onMouseOutOpacity).end()
							.eq(nextIndex).fadeTo('fast', 1.0);
					},
					onTransitionOut:        function(callback) {
						$('#slideshow-section, #caption-section').fadeOut('fast', callback);
					},
					onTransitionIn:         function() {
						$('#slideshow-section, #caption-section').fadeIn('fast');
					},
					onPageTransitionOut:    function(callback) {
						$('#thumbs-section ul.thumbs').fadeOut('fast', callback);
					},
					onPageTransitionIn:     function() {
						$('#thumbs-section ul.thumbs').fadeIn('fast');
					}
    });
	$(document).keydown(function(e) {
		if (e.keyCode == 37) {
			gallery.goto(gallery.getPrevIndex(gallery.currentIndex));
		}
		if (e.keyCode == 39 || e.keyCode == 32) {
			gallery.goto(gallery.getNextIndex(gallery.currentIndex));
		}
	});
    
    $("#thumbs-section a.thumb").each(function() {
    	$(this).html("<img src='../../" + $(this).attr('name') + "' alt='"+$(this).text()+"' />");
    });
    	
});
</script>
		<script type="text/javascript">
		</script>

