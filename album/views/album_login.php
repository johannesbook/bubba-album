
<script>
$(function(){
	$('#content_wrapper').position({
		'my': 'top',
		'at': 'top',
		'of': window,
		'offset': '0 100'
	});
});
</script>
<div id="login">

<?php echo form_open('album/login'); ?>

<table>
<tr>
	<td>Username</td>
	<td><input type="text" name="username" size="20"/></td>
</tr>
<tr>
	<td>Password</td>
	<td><input type="password" name="password" size="20"/></td>
</tr>
<tr><td colspan="2"><strong><?=$this->validation->error_string;?></strong></td></tr>
<tr><td colspan="2"><input type="hidden" value="<?=$album?>" name="album"><input type="submit" value="Login"/></td></tr>
</table>
</form>
</div>
