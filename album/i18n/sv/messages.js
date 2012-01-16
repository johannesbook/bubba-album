messages = {
	'next': 'Nästa',
	'back': 'Tillbaka',
	'ui-albummanager-album-name' : "Albumnamn",
	'ui-albummanager-album-caption' : "Beskrivning",
	'ui-albummangaer-images-in-album' : "i",
	/* Topnav */
	"topnav-authorized-bubba" : "Inloggad som " + config.name + " användare '%s'",
	"topnav-authorized-album" : "Tittar på album inloggad som '%s'",
	"topnav-authorized" : "Inloggad som '%s'",
	"topnav-not-authorized" : "Tittar på album anonymt",
	"topnav-login" : "Logga in",
	"topnav-logout" : "Logga ut",
	/* Log out */
	"logout-dialog-title" : "Vill du verkligen logga ut?",
	"logout-dialog-message" : "",
	"logout-dialog-button-logout" : "Logga ut",

	/* Log in */
	"login-dialog-continue" : "Logga in",

	"albummanager-move-notice": "Välj destination %d album och %d bilder?",
	"albummanager-move-no": "Nej",
	"albummanager-move-yes": "Ja",

	"albummanager-delete-dialog-button-label": "Ta bort",
	"albummanager-create-dialog-button-label": "Skapa",
	"albummanager-perm-dialog-button-label": "Uppdatera",
	"albummanager-modify-dialog-button-label": "Uppdatera",
	"albummanager-add-dialog-button-label": "Lägg till",

	"album-image-count": "%d bilder",
	"album-image-subalbum-count": "%d bilder och %d sub-album",

	/* Permissions */
	"albummanager-label-public": "Tillåta anonym åtkomst?",
	"albummanager-label-recursive": "Ställa åtkomst vidare upp/ner i trädet?",
	"album-users-entry":"%s (%s)",
	"album-users-delete-message": "Ta bort användare <strong>%s</strong> (\"%s\")?",

	/* User manager */
	"albummanager-del-no": "Avbryt",
	"albummanager-del-yes": "Ta bort användare",
	"albummanager-users-add": "Lägg till användare",
	"albummanager-button-edit": "Ändra",
	
	
	/* Help messages */
	"help-box-header" : config.name + " Album-hjälp",
	"help-info::anon::main":
	"<h3>Att titta på foton</h3>"
	+"<ul>"
		+"<li>För att gå in i ett album, dubbelkloicka på det eller använd pilen till höger på sidan.</li>"
		+"<li>Klicka på en bild för att se den i fullstorlek, och klicka 'bildspel' för att låta bilderna bläddra själva.</li>"
	+"</ul>"
	
	+"<h3>Logga in för att...</h3>"
	+"<ul>"
		+"<li>Se privata album.</li>"
		+"<li>Skapa fotoalbum.</li>"
		+"<li>Ändra fotoalbum.</li>"
	+"</ul>"
	
	+"<p>" + config.name + " användare (inte administratören) kan logga in och skapa album och albumanvändare. Albumanvändare kan endast se på album, inte skapa nya.</p>"
	,
	
	"help-info::user::main":
	"<h3>Se på foton</h3>"
	+"<ul>"
		+"<li>För att gå in i ett album, dubbelkloicka på det eller använd pilen till höger på sidan.</li>"
		+"<li>Klicka på en bild för att se den i fullstorlek, och klicka 'bildspel' för att låta bilderna bläddra själva.</li>"
	+"</ul>"
	
	+"<h3>Hantera bilder</h3>"
	+"<p>Klicka på knappen med skiftnyckelsymbolen för att gå in i 'hanteringsläge'. Du måste vara inloggad som en vanlig B3-användare (inte administratör).</p>"
	,
	
	"help-info::manager::main":
	"<h3>Menyalternativ</h3>"
	+"<p>Observera att du måste markera ett album eller foto för att vissa av funktionerna ska gå att välja.</p>"
	+"<ul>"
		+"<li><strong>Skapa album</strong> - Lägg till ett nytt album. Innan du gör detta måste bilderna vara lagrade i /home/storage/pictures-katalogen.</li>"
		+"<li><strong>Lägg till bilder</strong> - Lägger till bilder till ett befintligt album.</li>"
		+"<li><strong>Flytta</strong> - Flyttar ett album eller foto. Markera en eller flera album/foton och klicka Flytta, gå sedan  till önskad destination och bekräfta med Ja-knappen.</li>"
		+"<li><strong>Döp om</strong> - Döp om eller redigera beskrivningen för ett album eller foto.</li>"
		+"<li><strong>Åtkomst</strong> - Ändra albumrättigheter. Bestäm vem som ska kunna titta på ditt album. Ställ olika åtkomsträttigheter för olika album. För att låta alla titta, välj 'Tillåt anonym åtkomst'. Du kan ändra åtkomst för flera album samtidigt (sub-album kommer att få samma åtkomsträttigheter som deras moderalbum).</li>"
		+"<li><strong>Hantera användare</strong> - Skapa och redigera albumanvändare. Albumanvändare kan endast se album, inte logga in på B3 eller använda andra tjänster på B3. Tips! Skapa olika albumanvändare med tillgång till olika album.</li>"
		+"<li><strong>Ta bort</strong> - Ta bort album eller foton. Fotona kommer inte att försvinna från B3, endast från albumet.</li>"
	+"</ul>"
	,

	/* Create wizard */
	"albummanager-create-button-finish":"Create album"

};
