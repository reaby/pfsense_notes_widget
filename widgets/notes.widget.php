<?php
require_once("guiconfig.inc");
require_once("pfsense-utils.inc");
require_once("functions.inc");

if(isset($_POST['notes-json'])) {
	file_put_contents("/conf/widget_notes.json", addslashes(htmlspecialchars($_POST['notes-json'], ENT_NOQUOTES)));
	header("Location: ../../index.php");
}

$notes = "[]";
if (file_exists("/conf/widget_notes.json")) {
	$notes = file_get_contents("/conf/widget_notes.json");
	if (empty($notes) || json_decode($notes) === false) $notes = "[]";
}
?>

<div>
	<form action="/widgets/widgets/notes.widget.php" method="post" id="notes-form">
		<input type="hidden" id="notes-json" name="notes-json" value=""/>
		<div id="notesContainer">
		</div>
	</form>
</div>

<script>
var notes = JSON.parse("<?= $notes ?>");

function notes_displayMenu() {
	let container = document.getElementById("notesContainer");
	let output = "";
	output +="<div class='list-group'>";
	for (let i in notes) {
		var title = notes[i].title;			
		 
		output += "<span data-note='"+i+"' draggable='true' ondragover='dragOver(event)' ondragstart='dragStart(event)'  ondragend='dragEnd(event)' class='list-group-item noteItem' onclick='notes_displayNote("+i+");'>"+
		title +
		"<span onclick='notes_deleteNote("+i+")' class='badge btn btn-danger'>delete</span>"+	
		"</span>";	
	}	  
	output +="<span class='list-group-item btn btn-default' onclick='notes_newNote()'>Create new note</button>";
	output +="</div>";
	container.innerHTML = output;
}

function notes_displayNote(index) {
	let container = document.getElementById("notesContainer");
	let output = "<input id='note-title' class='form-control' value='"+notes[index].title+"'>"+	
	"<textarea id='note-text' class='form-control' rows='15'>"+
	notes[index].note+
	"</textarea>"+
	"<span onclick='notes_save("+index+")' class='btn btn-default'>Save</span>"+
	"<span onclick='notes_displayMenu()' class='btn btn-success'>Close</span>";
	container.innerHTML = output;
}

function notes_newNote() {
	notes.push({"title": "", "note": ""});
	notes_displayNote(notes.length-1);
}

function notes_save(index) {
	notes[index].title = document.getElementById("note-title").value;
	notes[index].note = document.getElementById("note-text").value;
	let elem = document.getElementById("notes-json");
	elem.value = JSON.stringify(notes);
	document.getElementById("notes-form").submit(); 
}

function notes_deleteNote(index) {
	if (confirm("Really remove note?")) {
	notes.splice(index, 1);
	let elem = document.getElementById("notes-json");
	elem.value = JSON.stringify(notes);
	document.getElementById("notes-form").submit();
	}		
}

let _el;
let _el2;

function dragOver(e) {
	_el2 = e.target;
	if (isBefore(_el, _el2)) {
		e.target.parentNode.insertBefore(_el, _el2);
	}
	else {
		e.target.parentNode.insertBefore(_el, _el2.nextSibling);
	}
}

function dragStart(e) {
	e.dataTransfer.effectAllowed = "move";
	e.dataTransfer.setData("text/plain", null);
	_el = e.target;	
}

function dragEnd(e) {	
	let list = document.getElementsByClassName("noteItem");
	let newNotes = [];
	for  (i in list) {
		if (list[i].nodeType == 1) {
			let index= parseInt(list[i].dataset.note);
		newNotes.push(notes[index]);
		}
	}
	
	let elem = document.getElementById("notes-json");
	elem.value = JSON.stringify(newNotes);
	document.getElementById("notes-form").submit();
}


function isBefore(el1, el2) {
  if (el2.parentNode === el1.parentNode)
    for (var cur = el1.previousSibling; cur && cur.nodeType !== 9; cur = cur.previousSibling)
      if (cur === el2)
        return true;
  return false;
}

notes_displayMenu();
</script>