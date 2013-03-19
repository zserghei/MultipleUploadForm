<?php 
function saveForm() {
	$savedData = "Saved title='" . $_POST['title'] . "'\n\n";
	if (strlen($_POST['attachments']) > 0) {
		$att = json_decode($_POST['attachments']);
		if ($att && is_array($att->{'upload'}) && count($att->{'upload'}) > 0) {
			for ($i = 0; $i < count($att->{'upload'}); $i++) {
				$savedData .= "Saved [" . ($i + 1) . "] file='" . $att->{'upload'}[$i]->{'links'}->{'original'} . "', name='"
					. $att->{'upload'}[$i]->{'file'}->{'name'} . "', description='" . $att->{'upload'}[$i]->{'info'} . "'\n";
			}
		}
	}
	echo $savedData;
}

switch ($_GET['func']) {
	case 'uploadFiles':
		uploadFiles();
		break;
	case 'saveForm':
		saveForm();
		break;
	default:
		break;
}
?>
