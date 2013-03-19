MultipleUploadForm
==================

PHP + jQuery solution for uploading multiple files to server without reloading page using a hidden iframe.

Contents of the project:

* upload.php
PHP script for handling asynchronous uploading of files to server.
It can be configured by changing the next named constants:

// Set the path (relative or absolute) to the directory to save image files
define('UPLOAD_PATH', '../upload/files');
// Set the URL (relative or absolute) to the directory defined above
define('UPLOAD_URI', '../upload/files');

The script saves files to server and echoes a javascript string that is expected to be shown in a hidden iframe. It will call parent.uploadComplete(formName, data) function where the first argument is name of the form that initiated upload ($_POST['upload-form-name']) and the second argument is JSON-encoded string containing information about the uploaded files in the following format:
In case of error: { "error" : { "message" : "<error message>", "format" : "json" } }
In case of success:
 {
  "upload" : [
    {
      "file" : {
        "size" : file size in bytes,
        "name" : "file name as provided by client"
      },
      "links" : {
        "original" : "link to the file saved on server"
      },
      "info" : "description of file as provided by client"
    },
    { info about other uploaded files }
  ],
  "done" : 1
 }

Parameters used by this script:
$_GET['func'] String "uploadFiles".
$_FILES['attachment'] Array of files.
$_POST['attachment-info'] Array of descriptions of files.
$_POST['upload-form-name'] Name of form. Needed for correct return of result.

* form-simple.html
Example of simple form written in plain HTML and jQuery that can send arbitrary number of files to server without reloading page. If you need to extend it, change contents of sendFailed() and sendSuccess() methods.

* form-advanced.html and form-advanced.php
Example of forms written in plain HTML and jQuery that can send arbitrary number of files to server without reloading page. This form has an additional field that is sent to server in POST request after uploading files. In order to extend this solution, change $.post() method, contents of sendFailed() and sendSuccess() methods. Change processing of form on server in form-advanced.php.
This example contains two forms in order to show that any number of forms can coexist on the same page. Every form has to have unique id, name and target attributes.

