messages = {
	'next': 'Next',
	'back': 'Back',
	'ui-albummanager-album-name' : "Album name",
	'ui-albummanager-album-caption' : "Description",
	'ui-albummangaer-images-in-album' : "in",
	/* Topnav */
	"topnav-authorized-bubba" : "Logged in as " + config.name + " user '%s'",
	"topnav-authorized-album" : "Viewing album as '%s'",
	"topnav-authorized" : "Logged in as '%s'",
	"topnav-not-authorized" : "Viewing album anonymously",
	"topnav-login" : "Login",
	"topnav-logout" : "Logout",
	/* Log out */
	"logout-dialog-title" : "Proceed with logout?",
	"logout-dialog-message" : "",
	"logout-dialog-button-logout" : "Logout",

	/* Log in */
	"login-dialog-continue" : "Login",

	"albummanager-move-notice": "Select destination to move %d albums and %d images?",
	"albummanager-move-no": "No",
	"albummanager-move-yes": "Yes",

	"albummanager-delete-dialog-button-label": "Delete",
	"albummanager-create-dialog-button-label": "Create",
	"albummanager-perm-dialog-button-label": "Update",
	"albummanager-modify-dialog-button-label": "Update",
	"albummanager-add-dialog-button-label": "Add",

	"album-image-count": "%d images",
	"album-image-subalbum-count": "%d images and %d sub-albums",

	/* Permissions */
	"albummanager-label-public": "Allow anonymous access?",
	"albummanager-label-recursive": "Apply permissions recursive?",
	"album-users-entry":"%s (%s)",
	"album-users-delete-message": "Delete user <strong>%s</strong> (\"%s\")?",

	/* User manager */
	"albummanager-del-no": "Cancel",
	"albummanager-del-yes": "Delete user",
	"albummanager-users-add": "Add user",
	"albummanager-button-edit": "Edit selected",
	
	
	/* Help messages */
	"help-box-header" : config.name + " Album help",
	"help-info::anon::main":
	"<h3>Viewing photos</h3>"
	+"<ul>"
		+"<li>To enter an album double click it or click the arrow to the right on the page.</li>"
		+"<li>Click a picture to view it and click 'Slide show' for automatic picture browsing.</li>"
	+"</ul>"
	
	+"<h3>Log in to...</h3>"
	+"<ul>"
		+"<li>View private photo albums.</li>"
		+"<li>Create photo albums.</li>"
		+"<li>Edit photo albums.</li>"
	+"</ul>"
	
	+"<p>" + config.name + " users (not administrator) may log in and create albums and create album users. Album users may log in and view albums.</p>"
	,
	
	"help-info::user::main":
	"<h3>Viewing photos</h3>"
	+"<ul>"
		+"<li>To enter an album double click it or click the arrow to the right on the page.</li>"
		+"<li>Click a picture to view it and click 'Slide show' for automatic picture browsing.</li>"
	+"</ul>"
	
	+"<h3>Managing photos</h3>"
	+"<p>Click on the button with a wrench symbol to enter 'Manager mode'. You have to be logged in as your standard B3 user (not as administrator) to add and edit albums.</p>"
	,
	
	"help-info::manager::main":
	"<h3>Menu items</h3>"
	+"<p>Observe that you have to highlight an album or a photo for some menu items to be selectable.</p>"
	+"<ul>"
		+"<li><strong>Create album</strong> - Add a new album. Before creating an album your photos must be located in your storage/pictures catalog.</li>"
		+"<li><strong>Add images</strong> - Add images into the album you are currantly located in.</li>"
		+"<li><strong>Move</strong> - Move an album or photos. Highlight one or more items, click Move, browse to the desired destination and confirm the move with the Yes button.</li>"
		+"<li><strong>Rename</strong> - Rename or edit the description for an item.</li>"
		+"<li><strong>Permissions</strong> - Edit album permissions. Decide who should be able to see your photo album. Set different permissions for different albums. To leave an album open for everyone to see, choose 'Allow anonymous access'. You may edit permissions for albums recursively (sub albums will gain the same permissions as the selected album).</li>"
		+"<li><strong>Manage users</strong> - Create and edit 'Album users'. 'Album users' only has permission to view albums, not log into B3 itself. Tip! Create different 'Album users' with access to different albums...</li>"
		+"<li><strong>Delete</strong> - Delete albums or photos. The photos will not be deleted from B3, only from the album.</li>"
	+"</ul>"
	,

	/* Create wizard */
	"albummanager-create-button-finish":"Create album"

};
