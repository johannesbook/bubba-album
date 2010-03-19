<?if(isset($albums)):?>

<div id="album_index_view">
	
	<div class="album_header">Albums</div>
	<div class="album_caption"></div>
		<table class="album_table">
		<?foreach( array_chunk( $albums, $this->config->item('album_width') ) as $row ):?>
		<tr class="album_row">
		<?foreach( $row as $album ):?>
		<td class="album_list">
		<?if(is_null($album['image_id'])):?>
		<?=anchor("album/section/$album[id]",img("image/blank/$album[id]", true))?>
		<?else:?>
		<?=anchor("album/section/$album[id]",img("image/thumb/$album[image_id]", true),array("class" => "thumb"))?>
		<?endif?>
		<div class="album_name"><?=$album['name']?></div>
		<div class="album_caption"><?=$album['caption']?></div>
		</td>
		<?endforeach?>
		</tr>
		<?endforeach?>
		</table>

</div>
<?endif?>

<script type="text/javascript">

	var onMouseOutOpacity = 0.80;
	$('#album_index_view img').css('opacity', onMouseOutOpacity)
		.hover(
			function () {
				$(this).not('.selected').fadeTo('fast', 1.0);
			}, 
			function () {
				$(this).not('.selected').fadeTo('fast', onMouseOutOpacity);
			}
		);
</script>
