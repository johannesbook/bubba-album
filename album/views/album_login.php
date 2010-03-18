
<div id="login">

<h1 class="segment_title"><span class="title">Login</span></h1>

<?php echo form_open('album/login'); ?>
<? /*
<h5>Username</h5>
<input type="text" name="username" value="" size="50" />

<h5>Password</h5>
<input type="text" name="password" value="" size="50" />

<div><input type="submit" value="Submit" /></div>

</form>
*/ ?>

<fieldset>
<legend>Login</legend>
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
</fieldset>
</form>
</div>
