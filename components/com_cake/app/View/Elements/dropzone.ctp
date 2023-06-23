<?php 
$id = ucfirst($id);
// debug($id);
echo '<form action="" class="dropzone" id="my-dropzone'.$id.'"></form>';
?>

<script>
  Dropzone.options.myDropzone<?php echo $id;?> = { // camelized version of the `id`
    url: 'index.php?option=com_cake&controller=Articles&action=upload&id=<?php echo $id;?>&article_organization_id=<?php echo $organization_id;?>', 
	dictDefaultMessage: "Trascina qui la foto dell'articolo",
	dictRemoveFile: "Elimina foto",
	dictFallbackMessage: "Il tuo browser non supporta il drag'n'drop dei file.",
	dictFallbackText: "Please use the fallback form below to upload your files like in the olden days.",
	dictFileTooBig: "Il file è troppo grande ({{filesize}}MiB). Grande massima consentita: {{maxFilesize}}MiB.",
	dictInvalidFileType: "Non puoi uploadare file di questo tipo.",
	dictResponseError: "Server responded with {{statusCode}} code.",
	dictCancelUpload: "Cancel upload",
	dictCancelUploadConfirmation: "Are you sure you want to cancel this upload?",
	dictMaxFilesExceeded: "Non puoi uploadare più file.",	
	parallelUploads: 1,
	addRemoveLinks: true,
	uploadMultiple:false,
	maxFiles: 1,
	// resizeWidth: 175,
	// acceptedFiles: 'image/*',
	acceptedFiles: ".jpeg,.jpg,.png,.gif",
	paramName: "img1", // The name that will be used to transfer the file
    maxFilesize: 5, // MB
	init: function() {

		console.log('myDropzone<?php echo $id;?>', 'init');

		<?php 
		$img_path_complete = Configure::read('App.root').Configure::read('App.img.upload.article').DS.$organization_id.DS.$img1;
		if(!empty($img1) && 
		   file_exists($img_path_complete)) {

			$size = filesize($img_path_complete);
		?>			
			let myDropzone = this;
			let mockFile = { name: "Foto articolo", size: <?php echo $size;?> };
			myDropzone.displayExistingFile(mockFile, "<?php echo Configure::read('App.server');?>/images/articles/<?php echo $organization_id;?>/<?php echo $img1;?>");
			this.files.push(mockFile)
		<?php 
  		}
		?>	
		this.on('addedfile', function(file) {
			console.log('addedfile - this.files.length '+this.files.length);
			if (this.files.length > 1) {
			this.removeFile(this.files[0]);
			}
		});		
		this.on('maxfilesexceeded', function(file) {
            console.log('maxfilesexceeded');
			this.removeAllFiles();
            this.addFile(file);
      	});
		this.on('success', function(file, response) {
			if(response.esito) {

			}
			console.log(response, 'success response'); 

		});		
		this.on('removedfile', function(file) {
			console.log(file, 'removedfile'); 
			$.post('index.php?option=com_cake&controller=Articles&action=uploadRemove&id=<?php echo $id;?>&article_organization_id=<?php echo $organization_id;?>',); 
		});		
	},
    accept: function(file, done) {
      if (file.name == "justinbieber.jpg") {
        done("dropzone eseguito");
      }
      else { done(); }
    }
  };
</script>