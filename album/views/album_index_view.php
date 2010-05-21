<div class="ui-albummanager-panel">
	<div id="fn-albummanager-information-panel" class=
		"ui-helper-hidden ui-albummanager-information-panel"></div>

	<div id="fn-albummanager-action-panel" class=
		"ui-helper-hidden ui-action-panel"></div>
</div>

<table id="albumtable" class="ui-table-outline">
	<thead>
		<tr>
			<td colspan="4"></td>
		</tr>

		<tr class=
			"ui-state-default ui-widget-header ui-albummanager-header">
			<th>Album</th>

			<th>Created</th>

			<th>Modified</th>

			<th></th>
		</tr>

		<tr class="ui-header">
			<td colspan="4" class="ui-albummanager-fake-updir"></td>
		</tr>

		<tr>
			<td colspan="4" class=
				"ui-helper-hidden ui-albummanager-permission-denied">
				<?=t("Permission denied")?>
			</td>
		</tr>
	</thead>
	<tbody>
	</tbody>

	<tfoot>
		<tr id="fn-album-infobox">
			<td>
				<div class="ui-album-title"></div>

				<div class="ui-album-caption"></div>
			</td>

			<td class="ui-album-created"></td>

			<td class="ui-album-modified"></td>

			<td></td>
		</tr>

		<tr>
			<td colspan="4">
				<div id="fn-images" class="ui-album-images"></div>
			</td>
		</tr>
	</tfoot>
</table>

<div id="fn-templates" class="ui-helper-hidden">
	<div class="ui-album-body">
		<div class="ui-album-thumbnail ui-corner-all"></div>

		<div class="ui-album-text">
			<div>
				<span class="ui-album-public ui-helper-hidden"></span>
				<span class="ui-album-title"></span>
			</div>

			<div class="ui-album-caption"></div>

			<div class="ui-album-count"></div>
		</div>
	</div><a class="ui-album-image ui-corner-all"></a>

	<div id="fn-albummanager-create-dialog">
		<h2 class="ui-text-center">
			<?=t('albummanager-create-dialog-title')?>
		</h2>

		<form id="fn-albummanager-create">
			<div class="ui-form-wrapper">
				<div id="fn-albummanager-create-form-step-1" class="step">
					<h3><?=t('albummanager-create-dialog-step1-title')?>
					</h3>

					<table>
						<tr>
							<td><label for="fn-albummanager-create-name">
									<?=t('albummanager-label-name')?>
									:</label> <input type="text" id=
								"fn-albummanager-create-name" name="name" class=
								"ui-input-text fn-primary-field" value=
								"New album" /></td>
						</tr>

						<tr>
							<td><label for="fn-albummanager-create-caption">
									<?=t('albummanager-label-caption')?>
									:</label> 
								<textarea id="fn-albummanager-create-caption" name=
									"caption" class="ui-input-text">
							</textarea></td>
						</tr>
					</table>
				</div>

				<div id="fn-albummanager-create-form-step-2" class="step">
					<h3><?=t('albummanager-create-dialog-step2-title')?>
					</h3>

					<div>
						<label for="fn-albummanager-create-public">
							<?=t('albummanager-label-public')?>
							:</label> <input type="checkbox" id=
						"fn-albummanager-create-public" name="public" class=
						"" />
					</div>

					<div>
						<table class="ui-table-outline ui-album-usertable">
							<thead>
								<tr class=
									"ui-state-default ui-widget-header ui-albummanager-header">
									<td></td>

									<td>Access allowed</td>
								</tr>
							</thead>
							<tbody>
							</tbody>
						</table>
					</div>
					<button id="fn-albummanager-create-form-step-button-branch-adduser" type="button" value="fn-albummanager-create-form-step-offstep-adduser"><?=t('albummanager-create-dialog-manage-users')?></button>
				</div>

				<div id="fn-albummanager-create-form-step-3" class="step submit_step">
					<h3><?=t('albummanager-create-dialog-step3-title')?>
					</h3>

					<div class="fn-placeholder-filemanager"></div>
				</div>
			</div>
		</form>
	</div>

	<div id="fn-albummanager-delete-dialog">
		<h2><?=t('albummanager-delete-dialog-message')?>
		</h2>
	</div>

	<div id="fn-albummanager-modify-dialog">
		<h2><?=t('albummanager-modify-dialog-message')?>
		</h2>

		<table>
			<tr>
				<td><label for="fn-albummanager-modify-name">
						<?=t('albummanager-label-name')?>
						:</label> <input type="text" id=
					"fn-albummanager-modify-name" name="name" class=
					"ui-input-text fn-primary-field" value="New album" /></td>
			</tr>

			<tr>
				<td><label for="fn-albummanager-modify-caption">
						<?=t('albummanager-label-caption')?>
						:</label> 
					<textarea id="fn-albummanager-modify-caption" name=
						"caption" class="ui-input-text">
				</textarea></td>
			</tr>
		</table>
	</div>

	<div id="fn-albummanager-perm-dialog" class="step">
		<h2><?=t('albummanager-perm-dialog-title')?>
		</h2>

		<div>
			<label for="fn-albummanager-perm-public">
				<?=t('albummanager-label-public')?>
				:</label> <input type="checkbox" id=
			"fn-albummanager-perm-public" name="public" class="" />
		</div>

		<div>
			<label for="fn-albummanager-perm-recursive">
				<?=t('albummanager-label-recursive')?>
				:</label> <input type="checkbox" id=
			"fn-albummanager-perm-recursive" name="recursive" checked=
			"checked" class="" />
		</div>

		<div>
			<table class="ui-table-outline ui-album-usertable">
				<thead>
					<tr class=
						"ui-state-default ui-widget-header ui-albummanager-header">
						<td></td>

						<td>Access allowed</td>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
	</div>

	<div id="fn-albummanager-users-dialog">
		<h2><?=t('albummanager-users-dialog-title')?>
		</h2>

		<div class=
			"ui-albummanager-buttonbar-wrapper ui-widget-header ui-corner-tl ui-corner-tr ui-helper-clearfix ui-albummanager-buttonbar ui-albummanager-subbuttonbar"
			id="fn-albummanager-users-dialog-buttons">
			<button id="fn-albummanager-users-dialog-button-add" disabled="disabled">
				<?=t('albummanager-users-dialog-button-add')?>
			</button>
			<button id="fn-albummanager-users-dialog-button-edit" disabled="disabled">
				<?=t('albummanager-users-dialog-button-edit')?>
			</button>
			<button id=	"fn-albummanager-users-dialog-button-delete" disabled="disabled">
				<?=t('albummanager-users-dialog-button-delete')?>
			</button>
		</div>

		<table class="ui-table-outline ui-album-usertable">
			<thead>
				<tr class="ui-state-default ui-widget-header ui-albummanager-header">
					<td><?=t('albummanager-users-table-header')?></td>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
	</div>

	<div id="fn-albummanager-add-dialog">
		<h2><?=t('albummanager-add-dialog-message')?>
		</h2>

		<div class="fn-placeholder-filemanager"></div>
	</div>

	<form id="fn-albummanager-users-edit-template">

		<div class="ui-helper-inline">
			<label for="fn-albummanager-users-edit-username">
				<?=t('albummanager-users-label-username')?>
			</label>
			<br/>
			<input id="fn-albummanager-users-edit-username" type=
			"text" name="username" disabled="disabled" />
			
			<span id="fn-albummanager-users-edit-username"></span>
		</div>

		<div class="ui-helper-inline">
			<label for="fn-albummanager-users-edit-realname">
				<?=t('albummanager-users-label-realname')?>
			</label>
			<br/>
			<input id="fn-albummanager-users-edit-realname" type=
			"text" name="realname" />
		</div>

		<div class="ui-helper-inline">
			<label for="fn-albummanager-users-edit-password1">
				<?=t('albummanager-users-label-password1')?>
			</label>
			<br/>
			<input id="fn-albummanager-users-edit-password1"
			type="password" name="password1" />
		</div>


		<div class="ui-helper-inline">
			<label for="fn-albummanager-users-edit-password2">
				<?=t('albummanager-users-label-password2')?>
			</label>
			<br/>
			<input id="fn-albummanager-users-edit-password2"
			type="password" name="password2" />
		</div>

		<div class="ui-album-users-actions">
			<button id="fn-albummanager-users-edit-cancel" type="button">
				<?=t('albummanager-users-button-cancel')?>
			</button>
			<button id="fn-albummanager-users-edit-ok" type="button">
				<?=t('albummanager-users-button-ok')?>
			</button>
		</div>



	</form>

	<form id="fn-albummanager-users-add-template">

		<div class="ui-helper-inline">
			<label for="fn-albummanager-users-add-username">
				<?=t('albummanager-users-label-username')?>
			</label>
			<br/>
			<input id="fn-albummanager-users-add-username" type=
			"text" name="username" />
		</div>

		<div class="ui-helper-inline">
			<label for="fn-albummanager-users-add-realname">
				<?=t('albummanager-users-label-realname')?>
			</label>
			<br/>
			<input id="fn-albummanager-users-add-realname" type=
			"text" name="realname" />
		</div>

		<div class="ui-helper-inline">
			<label for="fn-albummanager-users-add-password1">
				<?=t('albummanager-users-label-password1')?>
			</label>
			<br/>
			<input id="fn-albummanager-users-add-password1"
			type="password" name="password1" />
		</div>


		<div class="ui-helper-inline">
			<label for="fn-albummanager-users-add-password2">
				<?=t('albummanager-users-label-password2')?>
			</label>
			<br/>
			<input id="fn-albummanager-users-add-password2"
			type="password" name="password2" />
		</div>

		<div class="ui-album-users-actions">
			<button id="fn-albummanager-users-add-cancel" type="button">
				<?=t('albummanager-users-button-cancel')?>
			</button>
			<button id="fn-albummanager-users-add-ok" type="button">
				<?=t('albummanager-users-button-ok')?>
			</button>
		</div>

	</form>


	<table id="fn-filemanager" class="ui-table-outline">
		<thead>
			<tr class="ui-state-default ui-widget-header">
				<th></th>

				<th>Name</th>

				<th>Date</th>

				<th>Size</th>

				<th></th>
			</tr>

			<tr class="ui-header">
				<td colspan="5" class="ui-filemanager-fake-updir"></td>
			</tr>

			<tr>
				<td colspan="5" class=
					"ui-helper-hidden ui-filemanager-permission-denied">
					<?=t("Permission denied")?>
				</td>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
</div>
